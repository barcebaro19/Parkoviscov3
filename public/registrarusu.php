<?php
session_start();
require_once __DIR__ . "/../app/Models/conexion.php";



// Procesar el formulario de registro de propietarios
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!empty($_POST["id"]) && !empty($_POST["nombre"]) && !empty($_POST["apellido"]) && 
       !empty($_POST["email"]) && !empty($_POST["celular"]) && 
       !empty($_POST["torre"]) && !empty($_POST["piso"]) && !empty($_POST["apartamento"]) &&
       !empty($_POST["contrasena"]) && !empty($_POST["confirmar_contrasena"])) {
        
        $id = $_POST["id"];
        $nombre = $_POST["nombre"];
        $apellido = $_POST["apellido"];
        $email = $_POST["email"];
        $celular = $_POST["celular"];
        $torre = $_POST["torre"];
        $piso = $_POST["piso"];
        $apartamento = $_POST["apartamento"];
        $contrasena = $_POST["contrasena"];
        $confirmar_contrasena = $_POST["confirmar_contrasena"];
        
        // Asignar automáticamente el rol de propietario (ID 3)
        $rol_id = 3;
        
        
        
        // Validar que las contraseñas coincidan
        if($contrasena !== $confirmar_contrasena) {
            $_SESSION['error_message'] = "Las contraseñas no coinciden";
        } elseif(strlen($contrasena) < 8) {
            $_SESSION['error_message'] = "La contraseña debe tener al menos 8 caracteres";
        } else {
            $conexion = Conexion::getInstancia()->getConexion();
            
            try {
                // Verificar si el propietario ya existe
                $check_id_sql = $conexion->prepare("SELECT id FROM propietarios WHERE id = ?");
                $check_email_sql = $conexion->prepare("SELECT email FROM propietarios WHERE email = ?");
                
                $check_id_sql->bind_param("i", $id);
                $check_id_sql->execute();
                $id_result = $check_id_sql->get_result();
                
                $check_email_sql->bind_param("s", $email);
                $check_email_sql->execute();
                $email_result = $check_email_sql->get_result();
                
                if($id_result->num_rows > 0) {
                    $_SESSION['error_message'] = "El propietario con ID '$id' ya existe";
                } elseif($email_result->num_rows > 0) {
                    $_SESSION['error_message'] = "El email '$email' ya está registrado";
                } else {
                    // Iniciar transacción
                    $conexion->begin_transaction();
                    
                    try {
                        // Insertar nuevo propietario directamente en la tabla propietarios
                        $sql_propietario = $conexion->prepare("INSERT INTO propietarios (id, nombre, apellido, email, celular, torre, piso, apartamento, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $sql_propietario->bind_param("isssissss", $id, $nombre, $apellido, $email, $celular, $torre, $piso, $apartamento, $contrasena_hash);
                        
                        if(!$sql_propietario->execute()) {
                            throw new Exception("Error al insertar propietario: " . $sql_propietario->error);
                        }
                        
                        // También insertar en usuarios para mantener compatibilidad con el sistema
                        $sql_usuario = $conexion->prepare("INSERT INTO usuarios (id, nombre, apellido, email, celular) VALUES (?, ?, ?, ?, ?)");
                        $sql_usuario->bind_param("isssi", $id, $nombre, $apellido, $email, $celular);
                        
                        if(!$sql_usuario->execute()) {
                            throw new Exception("Error al insertar usuario: " . $sql_usuario->error);
                        }
                        
                        // Insertar rol del usuario
                        $sql_rol = $conexion->prepare("INSERT INTO usu_roles (usuarios_id, roles_idroles, contraseña) VALUES (?, ?, ?)");
                        $contrasena_legacy = substr(md5($contrasena), 0, 8);
                        $sql_rol->bind_param("iis", $id, $rol_id, $contrasena_legacy);
                        
                        if(!$sql_rol->execute()) {
                            throw new Exception("Error al insertar rol: " . $sql_rol->error);
                        }
                        
                        // Confirmar transacción
                        $conexion->commit();
                        
                        
                        // Mostrar mensaje de éxito y redirigir con JavaScript
                        echo "<script>alert('Propietario registrado exitosamente. Ya puedes iniciar sesión.'); window.location.href = 'login.php?mensaje=registrado';</script>";
                        exit();
                        
                    } catch (Exception $e) {
                        // Revertir transacción en caso de error
                        $conexion->rollback();
                        $_SESSION['error_message'] = "Error: " . $e->getMessage();
                    }
                }
            } catch(Exception $e) {
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['error_message'] = "Todos los campos son obligatorios";
    }
    
    // No redirigir, mostrar mensaje en la misma página
}

// Obtener mensaje de error de la sesión
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, showPassword: false, showConfirmPassword: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Propietario | Quintanares by Parkovisco</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
        }

        /* Fondo principal cyberpunk */
        .register-container {
            background: linear-gradient(135deg, 
                #0a0a0a 0%, 
                #1a1a2e 25%, 
                #16213e 50%, 
                #0f3460 75%, 
                #533483 100%
            );
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Particles container */
        .particles-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        /* Cyber Navigation Bar */
        .cyber-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
            height: 70px;
        }

        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .brand-link:hover {
            transform: translateY(-2px);
        }

        .brand-link i {
            font-size: 1.8rem;
            color: #10b981;
        }

        .brand-text {
            font-size: 1.5rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            background: linear-gradient(45deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .navbar-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .nav-link:hover {
            color: #10b981;
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.3);
            transform: translateY(-2px);
        }

        .nav-link i {
            font-size: 1rem;
        }

        /* Register card */
        .register-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 800px;
            margin: 0 auto;
        }

        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transition: left 0.6s;
        }

        .register-card:hover::before {
            left: 100%;
        }

        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
        }

        /* Header del card */
        .register-header {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(59, 130, 246, 0.2));
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
            padding: 2rem;
            text-align: center;
        }

        .register-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
            animation: float 3s ease-in-out infinite;
        }

        /* Input cyberpunk */
        .cyber-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
        }

        .cyber-input:focus {
            outline: none;
            border-color: rgba(16, 185, 129, 0.6);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        .cyber-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Botón cyberpunk */
        .cyber-button {
            position: relative;
            padding: 12px 24px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            color: white;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
        }

        .cyber-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: left 0.6s;
        }

        .cyber-button:hover::before {
            left: 100%;
        }

        .cyber-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }

        .cyber-button:active {
            transform: translateY(0);
        }

        /* Text effects */
        .text-cyber {
            background: linear-gradient(45deg, #10b981, #3b82f6);
            background-size: 400% 400%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient 4s ease infinite;
        }

        .text-gradient {
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 50%, #8b5cf6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        @keyframes gradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Animaciones */
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.5); }
            50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.8); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form styling */
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            z-index: 2;
        }

        .cyber-input.with-icon {
            padding-left: 48px;
        }

        /* Estilos para select */
        .cyber-input select,
        select.cyber-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            width: 100%;
            cursor: pointer;
        }

        select.cyber-input:focus {
            outline: none;
            border-color: rgba(16, 185, 129, 0.6);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            background: rgba(255, 255, 255, 0.08);
        }

        select.cyber-input option {
            background: #1a1a2e;
            color: white;
            padding: 8px;
        }

        select.cyber-input.with-icon {
            padding-left: 48px;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: color 0.3s ease;
            z-index: 2;
        }

        .password-toggle:hover {
            color: #10b981;
        }

        /* Grid responsive */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        /* Footer cyberpunk */
        .cyber-footer {
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(25px);
            border-top: 1px solid rgba(16, 185, 129, 0.3);
            margin-top: auto;
            position: relative;
            overflow: hidden;
            width: 100%;
            margin-bottom: 0;
        }

        .cyber-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: shimmer 3s linear infinite;
        }

        .footer-content {
            padding: 1.5rem 1.5rem 2rem 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .footer-section h3 {
            color: #10b981;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-section p,
        .footer-section li {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-section a:hover {
            color: #10b981;
            transform: translateX(5px);
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #10b981;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        @media (min-width: 768px) {
            .footer-bottom {
                flex-direction: row;
            }
        }

        .footer-bottom p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            font-family: 'JetBrains Mono', monospace;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #10b981;
        }

        /* Registration Visual Container */
        .registration-visual-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .registration-visual {
            position: relative;
            width: 100%;
            max-width: 600px;
            height: 500px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Form Elements */
        .form-elements-container {
            position: relative;
            width: 100%;
            height: 200px;
            margin-bottom: 40px;
        }

        .floating-form-element {
            position: absolute;
            width: 120px;
            height: 60px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.4);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            animation: formElementFloat 6s ease-in-out infinite;
            backdrop-filter: blur(10px);
        }

        .form-element-1 {
            top: 20px;
            left: 50px;
            animation-delay: 0s;
        }

        .form-element-2 {
            top: 20px;
            right: 50px;
            animation-delay: 1s;
        }

        .form-element-3 {
            top: 100px;
            left: 20px;
            animation-delay: 2s;
        }

        .form-element-4 {
            top: 100px;
            right: 20px;
            animation-delay: 3s;
        }

        .form-element-5 {
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            animation-delay: 4s;
        }

        .floating-form-element i {
            font-size: 20px;
            color: #10b981;
        }

        .floating-form-element span {
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Registration Icons */
        .registration-icons {
            display: flex;
            gap: 40px;
            margin-bottom: 30px;
        }

        .registration-icon {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: registrationIconFloat 5s ease-in-out infinite;
        }

        .registration-shield { animation-delay: 0s; }
        .registration-check { animation-delay: 1.7s; }
        .registration-user-plus { animation-delay: 3.4s; }

        .registration-icon i {
            font-size: 32px;
            color: #10b981;
        }

        /* Registration Text */
        .registration-text {
            text-align: center;
        }

        .registration-text h3 {
            color: #10b981;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .registration-text p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            font-weight: 500;
        }

        /* Animations */
        @keyframes formElementFloat {
            0%, 100% { 
                transform: translateY(0px) scale(1);
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
                border-color: rgba(16, 185, 129, 0.4);
            }
            50% { 
                transform: translateY(-15px) scale(1.05);
                box-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
                border-color: rgba(16, 185, 129, 0.6);
            }
        }

        @keyframes registrationIconFloat {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg);
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            }
            50% { 
                transform: translateY(-10px) rotate(5deg);
                box-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-card {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .register-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .navbar-content {
                padding: 0 1rem;
            }

            .navbar-links {
                gap: 1rem;
            }

            .nav-link {
                padding: 6px 12px;
                font-size: 0.8rem;
            }

            .nav-link span {
                display: none;
            }

            .brand-text {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body class="register-container flex flex-col min-h-screen" x-data="registerSystem()">
    <!-- Particles Background -->
    <div id="particles-js" class="particles-container"></div>

    <!-- Cyber Navigation Bar -->
    <nav class="cyber-navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <a href="index.php" class="brand-link">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-building text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold gradient-text">Quintanares</h1>
                        <p class="text-sm text-emerald-400 font-mono">by Parkovisco</p>
                    </div>
                </a>
            </div>
            <div class="navbar-links">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Inicio</span>
                </a>
                <a href="login.php" class="nav-link">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                </a>
                <a href="about.php" class="nav-link">
                    <i class="fas fa-info-circle"></i>
                    <span>Nosotros</span>
                </a>
                <a href="contact.php" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Contacto</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Register Container -->
    <div class="flex-1 flex flex-col pt-20 pb-8">
        <div class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-6xl flex gap-12 items-center">
                <!-- Visual Registration Container -->
                <div class="hidden lg:flex w-1/2 registration-visual-container" data-aos="fade-right">
                    <div class="registration-visual">
                        <div class="form-elements-container">
                            <div class="floating-form-element form-element-1">
                                <i class="fas fa-user"></i>
                                <span>Nombre</span>
                            </div>
                            <div class="floating-form-element form-element-2">
                                <i class="fas fa-id-card"></i>
                                <span>Cédula</span>
                            </div>
                            <div class="floating-form-element form-element-3">
                                <i class="fas fa-envelope"></i>
                                <span>Email</span>
                            </div>
                            <div class="floating-form-element form-element-4">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Celular</span>
                            </div>
                            <div class="floating-form-element form-element-5">
                                <i class="fas fa-lock"></i>
                                <span>Contraseña</span>
                            </div>
                        </div>
                        <div class="registration-icons">
                            <div class="registration-icon registration-shield">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="registration-icon registration-check">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="registration-icon registration-user-plus">
                                <i class="fas fa-user-plus"></i>
                            </div>
                        </div>
                        <div class="registration-text">
                            <h3>REGISTRO SEGURO</h3>
                            <p>Únete a la comunidad Quintanares by Parkovisco</p>
                        </div>
                    </div>
                </div>
                
                <!-- Register Card Container -->
                <div class="w-full lg:w-1/2">
                <!-- Register Card -->
                <div class="register-card" data-aos="fade-up">
                    <!-- Header -->
                    <div class="register-header">
                        <div class="register-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h1 class="text-3xl font-bold text-white mb-2 text-cyber">
                            REGISTRO DE PROPIETARIO
                        </h1>
                        <p class="text-white/60 text-sm font-mono">
                            ÚNETE A QUINTANARES BY PARKOVISCO
                        </p>
                    </div>

                    <!-- Register Form -->
                    <form action="" method="POST" class="p-8">
                        <?php if(isset($error_message)): ?>
                            <div class="alert alert-danger mb-4">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <div class="form-grid">
                            <!-- Cédula -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-id-card mr-2"></i>ID o Cédula
                                </label>
                                <div class="relative">
                                    <i class="fas fa-id-card input-icon"></i>
                                    <input type="number" name="id" id="id" 
                                           class="cyber-input with-icon" 
                                           placeholder="Ingrese su ID o cédula" required>
                                </div>
                            </div>

                            <!-- Nombre -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user mr-2"></i>Nombre
                                </label>
                                <div class="relative">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="nombre" id="nombre" 
                                           class="cyber-input with-icon" 
                                           placeholder="Ingrese su nombre" required>
                                </div>
                            </div>

                            <!-- Apellido -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-user mr-2"></i>Apellido
                                </label>
                                <div class="relative">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="apellido" id="apellido" 
                                           class="cyber-input with-icon" 
                                           placeholder="Ingrese su apellido" required>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                                </label>
                                <div class="relative">
                                    <i class="fas fa-envelope input-icon"></i>
                                    <input type="email" name="email" id="email" 
                                           class="cyber-input with-icon" 
                                           placeholder="Ingrese su correo" required>
                                </div>
                            </div>

                            <!-- Celular -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-mobile-alt mr-2"></i>Celular
                                </label>
                                <div class="relative">
                                    <i class="fas fa-mobile-alt input-icon"></i>
                                    <input type="number" name="celular" id="celular" 
                                           class="cyber-input with-icon" 
                                           placeholder="Ingrese su celular" required>
                                </div>
                            </div>

                            <!-- Contraseña -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock mr-2"></i>Contraseña
                                </label>
                                <div class="relative">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input :type="showPassword ? 'text' : 'password'" name="contrasena" id="contrasena" 
                                           class="cyber-input with-icon" 
                                           placeholder="••••••••" required>
                                    <i class="fas password-toggle" 
                                       :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
                                       @click="showPassword = !showPassword"></i>
                                </div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-lock mr-2"></i>Confirmar Contraseña
                                </label>
                                <div class="relative">
                                    <i class="fas fa-lock input-icon"></i>
                                    <input :type="showConfirmPassword ? 'text' : 'password'" name="confirmar_contrasena" id="confirmar_contrasena" 
                                           class="cyber-input with-icon" 
                                           placeholder="••••••••" required>
                                    <i class="fas password-toggle" 
                                       :class="showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'"
                                       @click="showConfirmPassword = !showConfirmPassword"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Campos específicos de Propietario -->
                        <div class="mt-6">
                            <h3 class="text-white font-semibold mb-4 text-lg">
                                <i class="fas fa-home mr-2"></i>Información de la Propiedad
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Torre -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-building mr-2"></i>Torre
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-building input-icon"></i>
                                        <input type="text" name="torre" id="torre" 
                                               class="cyber-input with-icon" 
                                               placeholder="Ej: Torre A" required>
                                    </div>
                                </div>

                                <!-- Piso -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-layer-group mr-2"></i>Piso
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-layer-group input-icon"></i>
                                        <input type="text" name="piso" id="piso" 
                                               class="cyber-input with-icon" 
                                               placeholder="Ej: 5" required>
                                    </div>
                                </div>

                                <!-- Apartamento -->
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-door-open mr-2"></i>Apartamento
                                    </label>
                                    <div class="relative">
                                        <i class="fas fa-door-open input-icon"></i>
                                        <input type="text" name="apartamento" id="apartamento" 
                                               class="cyber-input with-icon" 
                                               placeholder="Ej: 501" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-8">
                            <button type="submit" name="registrar" class="cyber-button">
                                <i class="fas fa-home"></i>
                                <span>Registrar como Propietario</span>
                            </button>
                        </div>

                        <!-- Additional Info -->
                        <div class="mt-6 text-center">
                            <p class="text-white/50 text-sm">
                                ¿Ya tienes una cuenta?
                                <a href="login.php" class="text-emerald-400 hover:text-emerald-300 transition-colors font-semibold">
                                    Inicia sesión aquí
                                </a>
                            </p>
                        </div>

                        <!-- Security Notice -->
                        <div class="mt-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-shield-alt text-emerald-400"></i>
                                <div>
                                    <p class="text-emerald-300 font-semibold text-sm">Registro Seguro</p>
                                    <p class="text-emerald-200/80 text-xs">Tu información está protegida con encriptación de grado militar</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cyber Footer -->
    <footer class="cyber-footer">
        <div class="footer-content">
            <div class="footer-grid">
                <!-- Información de Quintanares -->
                <div class="footer-section">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold gradient-text">Quintanares</h1>
                            <p class="text-emerald-400 font-mono">by Parkovisco</p>
                        </div>
                    </div>
                    <p>Tu hogar ideal en conjunto residencial con tecnología de vanguardia</p>
                    <div class="social-links">
                        <a href="#" class="social-link">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>

                <!-- Enlaces Rápidos -->
                <div class="footer-section">
                    <h3>Enlaces Rápidos</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;">
                            <a href="index.php">
                                <i class="fas fa-chevron-right mr-2 text-xs"></i>Inicio
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="login.php">
                                <i class="fas fa-chevron-right mr-2 text-xs"></i>Iniciar Sesión
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="Administrador1.php">
                                <i class="fas fa-chevron-right mr-2 text-xs"></i>Dashboard
                            </a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="tablausu.php">
                                <i class="fas fa-chevron-right mr-2 text-xs"></i>Usuarios
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Contacto -->
                <div class="footer-section">
                    <h3>Contacto</h3>
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; align-items: flex-start; margin-bottom: 0.75rem;">
                            <i class="fas fa-envelope mt-1.5 mr-3 text-emerald-400"></i>
                            <div>
                                <p style="font-weight: 600; margin: 0;">Email:</p>
                                <a href="mailto:info@quintanares.com" style="color: rgba(255, 255, 255, 0.7);">
                                    info@quintanares.com
                                </a>
                            </div>
                        </div>
                        <div style="display: flex; align-items: flex-start;">
                            <i class="fas fa-phone-alt mt-1.5 mr-3 text-emerald-400"></i>
                            <div>
                                <p style="font-weight: 600; margin: 0;">Teléfono:</p>
                                <a href="tel:(123)456-7890" style="color: rgba(255, 255, 255, 0.7);">
                                    (123) 456-7890
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>© <?php echo date('Y'); ?> Quintanares by Parkovisco. Todos los derechos reservados.</p>
                <div class="footer-links">
                    <a href="#">Política de Privacidad</a>
                    <a href="#">Términos de Servicio</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function registerSystem() {
            return {
                showPassword: false,
                showConfirmPassword: false,
                
                init() {
                    this.initParticles();
                    AOS.init({
                        duration: 800,
                        easing: 'ease-out-cubic',
                        once: true
                    });
                },
                
                initParticles() {
                    if (typeof particlesJS !== 'undefined') {
                        particlesJS('particles-js', {
                            particles: {
                                number: { value: 50, density: { enable: true, value_area: 800 } },
                                color: { value: ["#10b981", "#3b82f6", "#8b5cf6"] },
                                shape: { type: "circle" },
                                opacity: { value: 0.3, random: true },
                                size: { value: 3, random: true },
                                line_linked: {
                                    enable: true,
                                    distance: 150,
                                    color: "#10b981",
                                    opacity: 0.2,
                                    width: 1
                                },
                                move: {
                                    enable: true,
                                    speed: 2,
                                    direction: "none",
                                    random: false,
                                    straight: false,
                                    out_mode: "out",
                                    bounce: false
                                }
                            },
                            interactivity: {
                                detect_on: "canvas",
                                events: {
                                    onhover: { enable: true, mode: "repulse" },
                                    onclick: { enable: true, mode: "push" },
                                    resize: true
                                }
                            },
                            retina_detect: true
                        });
                    }
                }
            }
        }
    </script>
</body>
</html>

