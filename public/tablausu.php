<?php
session_start();
require_once __DIR__ . "/../app/Models/conexion.php";

// Obtener información completa del administrador desde la base de datos
$conexion = Conexion::getInstancia()->getConexion();

$sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular, r.nombre_rol, ur.contraseña
        FROM usuarios u 
        JOIN usu_roles ur ON u.id = ur.usuarios_id 
        JOIN roles r ON ur.roles_idroles = r.idroles 
        WHERE u.id = ? AND r.nombre_rol = 'administrador'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
$admin_info = $result->fetch_assoc();
include __DIR__ . "/../app/Controllers/buscar_usuario.php";

// ===== MÉTRICAS REALES DE USUARIOS =====
try {
    // Total de usuarios
    $total_usuarios_query = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $total_usuarios_query ? $total_usuarios_query->fetch_assoc()['total'] : 0;
    
    // Usuarios por rol
    $usuarios_por_rol = [];
    $roles_query = $conexion->query("
        SELECT r.nombre_rol, COUNT(*) as cantidad 
        FROM usuarios u 
        JOIN usu_roles ur ON u.id = ur.usuarios_id 
        JOIN roles r ON ur.roles_idroles = r.idroles 
        GROUP BY r.nombre_rol
    ");
    if ($roles_query) {
        while ($row = $roles_query->fetch_assoc()) {
            $usuarios_por_rol[$row['nombre_rol']] = $row['cantidad'];
        }
    }
    
    // Usuarios activos (con login reciente)
    $usuarios_activos_query = $conexion->query("
        SELECT COUNT(*) as total FROM usuarios 
        WHERE DATE(ultimo_acceso) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $usuarios_activos = $usuarios_activos_query ? $usuarios_activos_query->fetch_assoc()['total'] : 0;
    
    // Nuevos usuarios este mes
    $nuevos_usuarios_query = $conexion->query("
        SELECT COUNT(*) as total FROM usuarios 
        WHERE MONTH(fecha_registro) = MONTH(NOW()) 
        AND YEAR(fecha_registro) = YEAR(NOW())
    ");
    $nuevos_usuarios = $nuevos_usuarios_query ? $nuevos_usuarios_query->fetch_assoc()['total'] : 0;
    
} catch (Exception $e) {
    // Valores por defecto si hay error
    $total_usuarios = 0;
    $usuarios_por_rol = ['administrador' => 0, 'propietario' => 0, 'vigilante' => 0];
    $usuarios_activos = 0;
    $nuevos_usuarios = 0;
}

// Manejar resultados de validación de email
$email_validation_result = null;
if (isset($_SESSION['email_validation_result'])) {
    $email_validation_result = $_SESSION['email_validation_result'];
    unset($_SESSION['email_validation_result']); // Limpiar después de usar
}

$resultado = null;
$orden_actual = isset($_GET['orden']) ? $_GET['orden'] : 'reciente';

if(isset($_POST['buscar'])) {
    $valor = isset($_POST['nom']) ? $_POST['nom'] : '';
    $resultado = buscarUsuarios($valor, $orden_actual);
} else {
    $resultado = buscarUsuarios('', $orden_actual);
}

// Solo mostrar el formulario si el usuario es administrador
if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'administrador') {
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, sidebarOpen: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Quintanares by Parkovisco</title>
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
        .dashboard-container {
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
        }

        .dashboard-container::before {
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

        /* Header cyberpunk */
        .cyber-header {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
        }

        .cyber-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: shimmer 3s linear infinite;
        }

        /* Hamburger button */
        .hamburger-button {
            position: relative;
            padding: 8px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            cursor: pointer;
        }

        .hamburger-button.active {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.6);
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.4);
        }

        .hamburger-button:hover {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.5);
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }

        .hamburger-icon {
            font-size: 18px;
            color: #ffffff;
            transition: all 0.3s ease;
        }

        .hamburger-icon.open {
            transform: rotate(90deg);
        }

        /* Sidebar cyberpunk */
        .cyber-sidebar {
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(25px);
            border-right: 1px solid rgba(16, 185, 129, 0.3);
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            z-index: 45;
            transform: translateX(-100%);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            overflow-y: auto;
        }

        .cyber-sidebar.open {
            transform: translateX(0);
        }

        /* Navigation items */
        .nav-item {
            position: relative;
            margin: 4px 16px;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            z-index: 2;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .nav-link:hover {
            color: rgba(255, 255, 255, 1);
            background: rgba(16, 185, 129, 0.1);
            transform: translateX(8px);
        }

        .nav-link.active {
            color: #10b981;
            background: rgba(16, 185, 129, 0.15);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        /* Glass cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card::before {
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

        .glass-card:hover::before {
            left: 100%;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
        }

        /* Botones cyberpunk */
        .cyber-button {
            position: relative;
            padding: 12px 24px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.4s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .cyber-button.secondary {
            background: linear-gradient(135deg, #6b7280, #374151);
        }

        .cyber-button.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }

        /* Tabla cyberpunk */
        .cyber-table {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
        }

        .cyber-table thead {
            background: rgba(16, 185, 129, 0.1);
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
        }

        .cyber-table th {
            color: #10b981;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
            padding: 16px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
        }

        .cyber-table td {
            color: rgba(255, 255, 255, 0.9);
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .cyber-table tbody tr:hover {
            background: rgba(16, 185, 129, 0.05);
            transform: scale(1.01);
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

        /* Main content */
        .main-content {
            margin-left: 0;
            margin-top: 60px;
            padding: 1.5rem;
            min-height: calc(100vh - 60px);
            transition: margin-left 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 10;
        }

        .main-content.sidebar-open {
            margin-left: 280px;
        }

        /* Content wrapper */
        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* Stats cards */
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'JetBrains Mono', monospace;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 600;
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

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content.sidebar-open {
                margin-left: 0;
            }
            
            .cyber-sidebar {
                z-index: 60;
            }
        }

        /* Sidebar overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(4px);
            z-index: 40;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        @media (min-width: 1024px) {
            .sidebar-overlay {
                display: none;
            }
        }

        /* Footer cyberpunk */
        .cyber-footer {
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(25px);
            border-top: 1px solid rgba(16, 185, 129, 0.3);
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
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
            padding: 2rem 1.5rem;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
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
            padding-top: 1.5rem;
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

        /* Action buttons */
        .action-button {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.05);
            color: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-button:hover {
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
            transform: scale(1.1);
        }

        .action-button.edit:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: rgba(59, 130, 246, 0.4);
        }

               .action-button.delete:hover {
                   background: rgba(239, 68, 68, 0.2);
                   border-color: rgba(239, 68, 68, 0.4);
               }

               /* Botones de ordenamiento activos */
               .cyber-button.active {
                   background: linear-gradient(135deg, #10b981, #059669);
                   box-shadow: 0 0 25px rgba(16, 185, 129, 0.6);
                   transform: scale(1.05);
               }

               .cyber-button.active::before {
                   background: linear-gradient(
                       90deg,
                       transparent,
                       rgba(255, 255, 255, 0.3),
                       transparent
                   );
               }
    </style>
</head>
<body class="dashboard-container" x-data="userManagement()">
    <!-- Particles Background -->
    <div id="particles-js" class="particles-container"></div>

    <!-- Cyber Header -->
    <header class="cyber-header">
        <div class="flex items-center gap-4">
            <!-- Hamburger Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="hamburger-button" :class="{ 'active': sidebarOpen }">
                <i class="fas fa-bars hamburger-icon" :class="{ 'open': sidebarOpen }"></i>
            </button>
            
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-users text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-cyber text-gradient">GESTIÓN DE USUARIOS</h1>
                    <p class="text-xs text-white/60 font-mono">QUINTANARES BY PARKOVISCO ADMIN PANEL</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-emerald-500/20 px-4 py-2 rounded-full border border-emerald-500/40">
                <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-emerald-400 font-bold text-xs text-cyber">SISTEMA ACTIVO</span>
            </div>
        </div>
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" :class="{ 'active': sidebarOpen }" @click="sidebarOpen = false"></div>

    <!-- Cyber Sidebar -->
    <aside class="cyber-sidebar" :class="{ 'open': sidebarOpen }">
        <div class="p-6 pt-16">
            <!-- Profile Section -->
            <div class="glass-card p-4 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-user-shield text-lg text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-white text-cyber">
                            <?php echo htmlspecialchars($admin_info['nombre'] . ' ' . $admin_info['apellido']); ?>
                        </h3>
                        <p class="text-xs text-emerald-400 font-mono">ADMINISTRADOR</p>
                        <p class="text-xs text-white/60 font-mono">ID: <?php echo htmlspecialchars($admin_info['id']); ?></p>
                    </div>
                </div>
                
                <!-- Información adicional del perfil -->
                <div class="mt-4 pt-4 border-t border-white/10">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs">
                            <i class="fas fa-envelope text-emerald-400 w-3"></i>
                            <span class="text-white/70 font-mono"><?php echo htmlspecialchars($admin_info['email']); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <i class="fas fa-phone text-emerald-400 w-3"></i>
                            <span class="text-white/70 font-mono"><?php echo htmlspecialchars($admin_info['celular']); ?></span>
                        </div>
                        <div class="flex items-center gap-2 text-xs">
                            <i class="fas fa-shield-alt text-emerald-400 w-3"></i>
                            <span class="text-emerald-400 font-mono"><?php echo strtoupper($admin_info['nombre_rol']); ?></span>
                        </div>
                    </div>
                    
                    <!-- Botón para editar perfil -->
                    <div class="mt-3">
                        <a href="modificarusu.php?id=<?php echo $admin_info['id']; ?>" 
                           class="w-full bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 text-xs font-mono py-2 px-3 rounded-lg border border-emerald-500/30 transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-edit"></i>
                            <span>Editar Perfil</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-2">
                <div class="nav-item">
                    <a href="Administrador1.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-tachometer-alt"></i></div>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="tablausu.php" class="nav-link active">
                        <div class="nav-icon"><i class="fas fa-users"></i></div>
                        <span>Usuarios</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="parqueaderos.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-parking"></i></div>
                        <span>Parqueaderos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="gestion_vigilantes.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-shield-alt"></i></div>
                        <span>Vigilantes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="gestion_propietarios.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-home"></i></div>
                        <span>Propietarios</span>
                    </a>
                </div>
            </nav>

            <!-- Logout -->
            <div class="mt-6">
                <a href="logout.php" class="cyber-button danger w-full justify-center">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                    <span>Cerrar Sesión</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" :class="{ 'sidebar-open': sidebarOpen }">
        <!-- Hero Section -->
        <div class="glass-card p-8 mb-6" data-aos="fade-up">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-4xl font-bold text-white mb-2 text-cyber">
                        CONTROL DE <span class="text-gradient">USUARIOS</span>
                    </h2>
                    <p class="text-lg text-white/80">Administra y gestiona todos los usuarios del sistema</p>
                </div>
                <div class="text-6xl text-emerald-400 animate-pulse">
                    <i class="fas fa-users-cog"></i>
                </div>
            </div>
        </div>

        <!-- Mensajes de Éxito y Error -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="glass-card p-4 mb-6 border border-emerald-500/30" data-aos="fade-up" data-aos-delay="50">
                <div class="flex items-center gap-3">
                    <i class="fas fa-check-circle text-emerald-400 text-xl"></i>
                    <div>
                        <h3 class="text-emerald-400 font-bold">Operación Exitosa</h3>
                        <p class="text-white/80 text-sm"><?php echo htmlspecialchars($_SESSION['success']); ?></p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="glass-card p-4 mb-6 border border-red-500/30" data-aos="fade-up" data-aos-delay="50">
                <div class="flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                    <div>
                        <h3 class="text-red-400 font-bold">Error</h3>
                        <p class="text-white/80 text-sm"><?php echo nl2br(htmlspecialchars($_SESSION['error'])); ?></p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-emerald-400 to-emerald-600">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div class="stat-value" x-text="totalUsers"><?php echo $total_usuarios; ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-blue-400 to-blue-600">
                    <i class="fas fa-user-check text-white"></i>
                </div>
                <div class="stat-value" x-text="activeUsers"><?php echo $usuarios_activos; ?></div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-purple-400 to-purple-600">
                    <i class="fas fa-user-plus text-white"></i>
                </div>
                <div class="stat-value" x-text="newUsersToday"><?php echo $nuevos_usuarios; ?></div>
                <div class="stat-label">Nuevos Este Mes</div>
            </div>
        </div>

               <!-- Search and Actions -->
               <div class="glass-card p-6 mb-6" data-aos="fade-up" data-aos-delay="200">
                   <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                       <div class="flex-1">
                           <label class="block text-white/80 text-sm font-bold mb-2">
                               <i class="fas fa-search mr-2"></i>Buscar Usuario
                           </label>
                           <input type="text" name="nom" class="cyber-input w-full" 
                                  placeholder="Nombre, email, teléfono..." 
                                  value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                       </div>
                       <div class="flex gap-3">
                           <button type="submit" name="buscar" class="cyber-button">
                               <i class="fas fa-search"></i>
                               <span>Buscar</span>
                           </button>
                           <button type="button" onclick="limpiarBusqueda()" class="cyber-button secondary">
                               <i class="fas fa-sync-alt"></i>
                               <span>Limpiar</span>
                           </button>
                           <a href="../app/Controllers/exportar_usuarios.php" class="cyber-button" style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                               <i class="fas fa-file-excel"></i>
                               <span>Excel</span>
                           </a>
                           <a href="registrarusu.php" class="cyber-button">
                               <i class="fas fa-user-plus"></i>
                               <span>Nuevo Usuario</span>
                           </a>
                           <button type="button" onclick="eliminarUsuariosSeleccionados()" class="cyber-button danger">
                               <i class="fas fa-trash-alt"></i>
                               <span>Eliminar Seleccionados</span>
                           </button>
                       </div>
                   </form>
                   
                   <!-- Ordenamiento -->
                   <div class="mt-6 pt-6 border-t border-white/10">
                       <div class="flex items-center justify-between">
                           <div class="flex items-center gap-3">
                               <i class="fas fa-sort text-emerald-400"></i>
                               <span class="text-white/80 text-sm font-bold">Ordenar usuarios:</span>
                           </div>
                           <div class="flex gap-2">
                               <?php if($orden_actual === 'reciente'): ?>
                                   <a href="?orden=antiguo" class="cyber-button" style="padding: 10px 20px; font-size: 0.85rem;">
                                       <i class="fas fa-sort-numeric-up"></i>
                                       <span>Más Antiguos Primero</span>
                                   </a>
                               <?php else: ?>
                                   <a href="?orden=reciente" class="cyber-button" style="padding: 10px 20px; font-size: 0.85rem;">
                                       <i class="fas fa-sort-numeric-down"></i>
                                       <span>Más Recientes Primero</span>
                                   </a>
                               <?php endif; ?>
                           </div>
                       </div>
                   </div>
               </div>

        <!-- Users Table -->
        <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-white text-cyber">
                    <i class="fas fa-table mr-3"></i>Lista de Usuarios
                </h3>
                <div class="text-white/60 font-mono">
                    Total: <span class="text-emerald-400 font-bold"><?php echo $resultado ? $resultado->num_rows : 0; ?></span> usuarios
                </div>
            </div>

            <?php if ($resultado && $resultado->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="cyber-table w-full">
                     <thead>
                         <tr>
                             <th>
                                 <input type="checkbox" id="selectAllUsuarios" onclick="toggleSelectAllUsuarios()" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500">
                             </th>
                             <th>ID</th>
                             <th>Nombre</th>
                             <th>Apellido</th>
                             <th>Email</th>
                             <th>Celular</th>
                             <th>Rol</th>
                             <th>Acciones</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php while($row = $resultado->fetch_assoc()): ?>
                         <tr>
                             <td>
                                 <input type="checkbox" name="usuarios[]" value="<?php echo $row['id']; ?>" class="w-4 h-4 text-emerald-600 bg-gray-100 border-gray-300 rounded focus:ring-emerald-500">
                             </td>
                             <td class="font-mono"><?php echo $row['id']; ?></td>
                             <td class="font-semibold"><?php echo htmlspecialchars($row['nombre']); ?></td>
                             <td class="font-semibold"><?php echo htmlspecialchars($row['apellido']); ?></td>
                             <td class="font-mono text-sm"><?php echo htmlspecialchars($row['email']); ?></td>
                             <td class="font-mono"><?php echo htmlspecialchars($row['celular']); ?></td>
                             <td>
                                 <?php
                                 $rol = strtolower($row['nombre_rol']);
                                 $clases_rol = [
                                     'administrador' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                     'propietario' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                                     'vigilante' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
                                     'usuario' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
                                 ];
                                 $clase_rol = $clases_rol[$rol] ?? $clases_rol['usuario'];
                                 ?>
                                 <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $clase_rol; ?>">
                                     <?php echo strtoupper($row['nombre_rol']); ?>
                                 </span>
                             </td>
                             <td>
                                 <div class="flex gap-2">
                                     <a href="modificarusu.php?id=<?php echo $row['id']; ?>" 
                                        class="action-button edit" title="Editar">
                                         <i class="fas fa-edit"></i>
                                     </a>
                                     <button onclick="abrirModalCorreo('<?php echo $row['email']; ?>')" 
                                             class="action-button" style="background: rgba(139, 92, 246, 0.2); border-color: rgba(139, 92, 246, 0.4);" title="Enviar correo">
                                         <i class="fas fa-envelope"></i>
                                     </button>
                                     <button onclick="confirmarEliminar(<?php echo $row['id']; ?>)" 
                                             class="action-button delete" title="Eliminar">
                                         <i class="fas fa-trash"></i>
                                     </button>
                                     <a href="generate_pdf_profesional.php?id=<?php echo $row['id']; ?>" 
                                        class="action-button" style="background: rgba(34, 197, 94, 0.2); border-color: rgba(34, 197, 94, 0.4);"
                                        target="_blank" title="Generar PDF">
                                         <i class="fas fa-file-pdf"></i>
                                     </a>
                                 </div>
                             </td>
                         </tr>
                         <?php endwhile; ?>
                     </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <div class="text-6xl text-white/20 mb-4">
                    <i class="fas fa-users-slash"></i>
                </div>
                <h3 class="text-xl font-bold text-white/60 mb-2">No se encontraron usuarios</h3>
                <p class="text-white/40">Intenta con otros términos de búsqueda</p>
             </div>
             <?php endif; ?>
         </div>

         <!-- Email Validation Section -->
         <div class="glass-card p-6 mb-6" data-aos="fade-up" data-aos-delay="400">
             <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-3 text-cyber">
                 <i class="fas fa-globe text-blue-400 text-sm"></i>
                 VALIDACIÓN DE EMAIL
             </h3>
             <form action="webservice_demo.php" method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                 <div class="flex-1">
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-envelope mr-2"></i>Email a Validar
                     </label>
                     <input type="email" name="email" class="cyber-input w-full" 
                            placeholder="ejemplo@email.com" required>
                 </div>
                 <div>
                     <button type="submit" class="cyber-button" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                         <i class="fas fa-check-circle"></i>
                         <span>Validar</span>
                     </button>
                 </div>
             </form>
             
             <!-- Mostrar resultados de validación -->
             <?php if ($email_validation_result): ?>
             <div class="mt-6 p-4 rounded-lg <?php echo $email_validation_result['valido'] ? 'bg-green-900/30 border border-green-500/30' : 'bg-red-900/30 border border-red-500/30'; ?>">
                 <h4 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                     <i class="fas <?php echo $email_validation_result['valido'] ? 'fa-check-circle text-green-400' : 'fa-times-circle text-red-400'; ?>"></i>
                     Resultado de Validación
                 </h4>
                 <div class="space-y-2">
                     <div class="flex items-center gap-3">
                         <i class="fas fa-envelope text-blue-400"></i>
                         <span class="text-white">Email: <strong><?php echo htmlspecialchars($email_validation_result['email']); ?></strong></span>
                     </div>
                     <div class="flex items-center gap-3">
                         <i class="fas <?php echo $email_validation_result['formato_valido'] ? 'fa-check text-green-400' : 'fa-times text-red-400'; ?>"></i>
                         <span class="text-white">Formato: <strong><?php echo $email_validation_result['formato_valido'] ? 'Válido' : 'Inválido'; ?></strong></span>
                     </div>
                     <div class="flex items-center gap-3">
                         <i class="fas <?php echo $email_validation_result['dominio_valido'] ? 'fa-check text-green-400' : 'fa-times text-red-400'; ?>"></i>
                         <span class="text-white">Dominio: <strong><?php echo $email_validation_result['dominio_valido'] ? 'Válido' : 'Inválido'; ?></strong></span>
                     </div>
                     <div class="flex items-center gap-3">
                         <i class="fas <?php echo $email_validation_result['mx_record'] ? 'fa-check text-green-400' : 'fa-times text-red-400'; ?>"></i>
                         <span class="text-white">MX Record: <strong><?php echo $email_validation_result['mx_record'] ? 'Válido' : 'Inválido'; ?></strong></span>
                     </div>
                     <div class="flex items-center gap-3">
                         <i class="fas <?php echo $email_validation_result['disponible'] ? 'fa-check text-green-400' : 'fa-times text-red-400'; ?>"></i>
                         <span class="text-white">Disponible: <strong><?php echo $email_validation_result['disponible'] ? 'Sí' : 'No'; ?></strong></span>
                     </div>
                     
                     <!-- Mostrar fuente de validación -->
                     <div class="flex items-center gap-3">
                         <i class="fas fa-database text-blue-400"></i>
                         <span class="text-white">Fuente: <strong><?php echo ucfirst($email_validation_result['fuente'] ?? 'local'); ?></strong></span>
                     </div>
                     
                     <!-- Mostrar detalles adicionales de Hunter.io si están disponibles -->
                     <?php if (isset($email_validation_result['detalles']) && is_array($email_validation_result['detalles'])): ?>
                     <div class="mt-4 p-3 rounded-lg bg-blue-900/20 border border-blue-500/30">
                         <h5 class="text-white font-semibold mb-2 flex items-center gap-2">
                             <i class="fas fa-info-circle text-blue-400"></i>
                             Detalles Avanzados (Hunter.io)
                         </h5>
                         <div class="grid grid-cols-2 gap-2 text-sm">
                             <?php if (isset($email_validation_result['detalles']['score'])): ?>
                             <div class="flex justify-between">
                                 <span class="text-gray-300">Puntuación:</span>
                                 <span class="text-white font-semibold"><?php echo $email_validation_result['detalles']['score']; ?>/100</span>
                             </div>
                             <?php endif; ?>
                             
                             <?php if (isset($email_validation_result['detalles']['result'])): ?>
                             <div class="flex justify-between">
                                 <span class="text-gray-300">Resultado:</span>
                                 <span class="text-white font-semibold"><?php echo ucfirst($email_validation_result['detalles']['result']); ?></span>
                             </div>
                             <?php endif; ?>
                             
                             <?php if (isset($email_validation_result['detalles']['disposable'])): ?>
                             <div class="flex justify-between">
                                 <span class="text-gray-300">Temporal:</span>
                                 <span class="text-white font-semibold"><?php echo $email_validation_result['detalles']['disposable'] ? 'Sí' : 'No'; ?></span>
                             </div>
                             <?php endif; ?>
                             
                             <?php if (isset($email_validation_result['detalles']['webmail'])): ?>
                             <div class="flex justify-between">
                                 <span class="text-gray-300">Webmail:</span>
                                 <span class="text-white font-semibold"><?php echo $email_validation_result['detalles']['webmail'] ? 'Sí' : 'No'; ?></span>
                             </div>
                             <?php endif; ?>
                             
                             <?php if (isset($email_validation_result['detalles']['role_based'])): ?>
                             <div class="flex justify-between">
                                 <span class="text-gray-300">Email de Rol:</span>
                                 <span class="text-white font-semibold"><?php echo $email_validation_result['detalles']['role_based'] ? 'Sí' : 'No'; ?></span>
                             </div>
                             <?php endif; ?>
                             
                             <?php if (isset($email_validation_result['detalles']['catch_all'])): ?>
                             <div class="flex justify-between">
                                 <span class="text-gray-300">Catch-All:</span>
                                 <span class="text-white font-semibold"><?php echo $email_validation_result['detalles']['catch_all'] ? 'Sí' : 'No'; ?></span>
                             </div>
                             <?php endif; ?>
                         </div>
                     </div>
                     <?php endif; ?>
                     
                     <div class="mt-3 p-3 rounded-lg <?php echo $email_validation_result['valido'] ? 'bg-green-800/20' : 'bg-red-800/20'; ?>">
                         <p class="text-white"><strong>Mensaje:</strong> <?php echo htmlspecialchars($email_validation_result['mensaje']); ?></p>
                         <?php if (isset($email_validation_result['error'])): ?>
                         <p class="text-yellow-300 text-sm mt-1"><strong>Nota:</strong> <?php echo htmlspecialchars($email_validation_result['error']); ?></p>
                         <?php endif; ?>
                     </div>
                 </div>
             </div>
             <?php endif; ?>
         </div>

         <!-- Bulk Actions -->
         <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="500">
             <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                 <div class="flex items-center gap-4">
                     <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                         <i class="fas fa-paper-plane text-white text-lg"></i>
                     </div>
                     <div>
                         <h3 class="text-lg font-bold text-white text-cyber">ACCIONES MASIVAS</h3>
                         <p class="text-white/60 text-sm">Selecciona usuarios y envía correos masivos</p>
                     </div>
                 </div>
                 <div class="flex gap-3">
                     <a href="generate_pdf_profesional.php" class="cyber-button" style="background: linear-gradient(135deg, #dc2626, #b91c1c);" target="_blank">
                         <i class="fas fa-file-pdf"></i>
                         <span>PDF General</span>
                     </a>
                     <button type="button" class="cyber-button" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);" onclick="abrirModalCorreoMasivo()">
                         <i class="fas fa-paper-plane"></i>
                         <span>Correo Masivo</span>
                     </button>
                 </div>
             </div>
         </div>
     </main>

     <!-- Delete Confirmation Modal -->
     <dialog id="modalEliminar" class="backdrop:bg-black/50 bg-transparent">
         <div class="glass-card p-8 max-w-md mx-auto mt-20">
             <div class="flex items-center gap-4 mb-6">
                 <div class="w-12 h-12 bg-red-500/20 rounded-xl flex items-center justify-center">
                     <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                 </div>
                 <div>
                     <h3 class="font-bold text-xl text-white text-cyber">Confirmar Eliminación</h3>
                     <p class="text-white/60">Esta acción no se puede deshacer</p>
                 </div>
             </div>
             <div class="bg-red-500/10 border border-red-500/20 rounded-xl p-4 mb-6">
                 <div class="flex items-center gap-2">
                     <i class="fas fa-info-circle text-red-400"></i>
                     <span class="text-red-300 font-medium">El usuario seleccionado será eliminado permanentemente</span>
                 </div>
             </div>
             <div class="flex gap-3">
                 <button class="flex-1 cyber-button secondary" onclick="cerrarModalEliminar()">Cancelar</button>
                 <button class="flex-1 cyber-button danger" onclick="eliminarUsuario()">
                     <i class="fas fa-trash-alt"></i>
                     <span>Eliminar</span>
                 </button>
             </div>
         </div>
     </dialog>

     <!-- Individual Email Modal -->
     <dialog id="modalCorreo" class="backdrop:bg-black/50 bg-transparent">
         <div class="glass-card p-8 max-w-2xl mx-auto mt-10">
             <div class="flex items-center justify-between mb-6">
                 <div class="flex items-center gap-4">
                     <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                         <i class="fas fa-envelope text-blue-400 text-xl"></i>
                     </div>
                     <div>
                         <h2 class="text-2xl font-bold text-white text-cyber">Enviar Correo Individual</h2>
                         <p class="text-white/60">Comunícate directamente con el usuario</p>
                     </div>
                 </div>
                 <button onclick="cerrarModalCorreo()" class="text-white/40 hover:text-white/60">
                     <i class="fas fa-times text-xl"></i>
                 </button>
             </div>

             <form id="formCorreo" enctype="multipart/form-data" class="space-y-6">
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-envelope mr-2"></i>Correo Destinatario
                     </label>
                     <input type="email" name="correo" id="correo_destino" class="cyber-input w-full" required />
                 </div>
                 
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-tag mr-2"></i>Asunto
                     </label>
                     <input type="text" name="asunto" class="cyber-input w-full" required />
                 </div>
                 
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-comment mr-2"></i>Mensaje
                     </label>
                     <textarea name="mensaje" class="cyber-input w-full h-32 resize-none" required></textarea>
                 </div>
                 
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-paperclip mr-2"></i>Adjuntar Archivos
                     </label>
                     <input type="file" name="adjuntos[]" id="adjuntos_individual" class="cyber-input w-full" multiple accept="image/*,video/*,application/pdf">
                     <div id="listaAdjuntosIndividual" class="mt-3 text-sm text-white/60"></div>
                 </div>
                 
                 <button type="submit" class="cyber-button w-full">
                     <i class="fas fa-paper-plane"></i>
                     <span>Enviar Correo</span>
                 </button>
                 
                 <div id="correoMsg" class="mt-4 text-center text-sm"></div>
             </form>
         </div>
     </dialog>

     <!-- Mass Email Modal -->
     <dialog id="modalCorreoMasivo" class="backdrop:bg-black/50 bg-transparent">
         <div class="glass-card p-8 max-w-2xl mx-auto mt-10">
             <div class="flex items-center justify-between mb-6">
                 <div class="flex items-center gap-4">
                     <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                         <i class="fas fa-users text-purple-400 text-xl"></i>
                     </div>
                     <div>
                         <h2 class="text-2xl font-bold text-white text-cyber">Enviar Correo Masivo</h2>
                         <p class="text-white/60">Comunícate con múltiples usuarios</p>
                     </div>
                 </div>
                 <button onclick="cerrarModalCorreoMasivo()" class="text-white/40 hover:text-white/60">
                     <i class="fas fa-times text-xl"></i>
                 </button>
             </div>

             <form id="formCorreoMasivo" enctype="multipart/form-data" class="space-y-6">
                 <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-4">
                     <div class="flex items-center gap-3">
                         <i class="fas fa-users text-emerald-400 text-lg"></i>
                         <div>
                             <span class="font-semibold text-emerald-300">Destinatarios Seleccionados:</span>
                             <div id="resumenCorreos" class="text-emerald-400 text-sm font-mono mt-1"></div>
                         </div>
                     </div>
                 </div>
                 
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-tag mr-2"></i>Asunto
                     </label>
                     <input type="text" name="asunto" class="cyber-input w-full" required />
                 </div>
                 
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-comment mr-2"></i>Mensaje
                     </label>
                     <textarea name="mensaje" class="cyber-input w-full h-32 resize-none" required></textarea>
                 </div>
                 
                 <div>
                     <label class="block text-white/80 text-sm font-bold mb-2">
                         <i class="fas fa-paperclip mr-2"></i>Adjuntar Archivos
                     </label>
                     <input type="file" name="adjuntos[]" id="adjuntos" class="cyber-input w-full" multiple accept="image/*,video/*,application/pdf">
                     <div id="listaAdjuntos" class="mt-3 text-sm text-white/60"></div>
                 </div>
                 
                 <input type="hidden" name="correos" id="correos_masivos" />
                 
                 <button type="submit" class="cyber-button w-full" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                     <i class="fas fa-paper-plane"></i>
                     <span>Enviar Correos Masivos</span>
                 </button>
                 
                 <div id="correoMasivoMsg" class="mt-4 text-center text-sm"></div>
             </form>
         </div>
           </dialog>

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
                                   <a href="Administrador1.php">
                                       <i class="fas fa-chevron-right mr-2 text-xs"></i>Dashboard
                                   </a>
                               </li>
                               <li style="margin-bottom: 0.5rem;">
                                   <a href="tablausu.php">
                                       <i class="fas fa-chevron-right mr-2 text-xs"></i>Usuarios
                                   </a>
                               </li>
                               <li style="margin-bottom: 0.5rem;">
                                   <a href="parqueaderos.php">
                                       <i class="fas fa-chevron-right mr-2 text-xs"></i>Parqueaderos
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
    </main>

    <script>
        function userManagement() {
            return {
                sidebarOpen: false,
                totalUsers: <?php echo $resultado ? $resultado->num_rows : 0; ?>,
                activeUsers: <?php echo $resultado ? $resultado->num_rows : 0; ?>,
                newUsersToday: 0,
                
                init() {
                    this.initParticles();
                    this.animateStats();
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
                },
                
                animateStats() {
                    // Animate stats counters
                    const duration = 2000;
                    const start = Date.now();
                    const totalTarget = <?php echo $total_usuarios; ?>;
                    const activeTarget = <?php echo $usuarios_activos; ?>;
                    const newTarget = <?php echo $nuevos_usuarios; ?>;
                    
                    const animate = () => {
                        const now = Date.now();
                        const elapsed = now - start;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        this.totalUsers = Math.floor(totalTarget * progress);
                        this.activeUsers = Math.floor(activeTarget * progress);
                        this.newUsersToday = Math.floor(newTarget * progress);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    };
                    
                    animate();
                }
            }
        }
        
         // Variables globales
         let usuarioAEliminar = null;

                // Funciones de utilidad
                function limpiarBusqueda() {
                    const ordenActual = '<?php echo $orden_actual; ?>';
                    window.location.href = 'tablausu.php?orden=' + ordenActual;
                }

         // Funciones de eliminación
         function confirmarEliminar(id) {
             usuarioAEliminar = id;
             document.getElementById('modalEliminar').showModal();
         }

         function cerrarModalEliminar() {
             document.getElementById('modalEliminar').close();
             usuarioAEliminar = null;
         }

         function eliminarUsuario() {
             if (usuarioAEliminar) {
                 window.location.href = '../app/Controllers/eliminar_usuario.php?id=' + usuarioAEliminar;
             }
         }

         // Funciones de correo individual
         function abrirModalCorreo(email) {
             document.getElementById('correo_destino').value = email;
             document.getElementById('modalCorreo').showModal();
         }

         function cerrarModalCorreo() {
             document.getElementById('modalCorreo').close();
         }

         // Funciones de correo masivo
         function abrirModalCorreoMasivo() {
             const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');
             if (checkboxes.length === 0) {
                 alert('Por favor selecciona al menos un usuario');
                 return;
             }
             
             const emails = Array.from(checkboxes).map(cb => {
                 const row = cb.closest('tr');
                 return row.cells[4].textContent.trim(); // Email column
             });
             
             document.getElementById('correos_masivos').value = emails.join(',');
             document.getElementById('resumenCorreos').textContent = `${emails.length} usuarios seleccionados`;
             document.getElementById('modalCorreoMasivo').showModal();
         }

         function cerrarModalCorreoMasivo() {
             document.getElementById('modalCorreoMasivo').close();
         }

        // Función para seleccionar todos los usuarios
        function toggleSelectAllUsuarios() {
            const selectAll = document.getElementById('selectAllUsuarios');
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        // Función para eliminar usuarios seleccionados
        function eliminarUsuariosSeleccionados() {
            const checkboxes = document.querySelectorAll('input[name="usuarios[]"]:checked');
            
            if (checkboxes.length === 0) {
                alert('Por favor selecciona al menos un usuario para eliminar');
                return;
            }

            // Verificar si el usuario actual está seleccionado
            const usuarioActual = <?php echo $_SESSION['id']; ?>;
            const usuariosSeleccionados = Array.from(checkboxes).map(cb => parseInt(cb.value));
            
            if (usuariosSeleccionados.includes(usuarioActual)) {
                alert('No puedes eliminar tu propia cuenta ya que hay una sesión activa con esta misma cuenta');
                return;
            }

            // Mostrar confirmación con detalles
            const nombresUsuarios = Array.from(checkboxes).map(cb => {
                const row = cb.closest('tr');
                return row.cells[2].textContent.trim() + ' ' + row.cells[3].textContent.trim();
            });

            const confirmacion = confirm(
                `¿Estás seguro de que quieres eliminar los siguientes usuarios?\n\n` +
                `${nombresUsuarios.join('\n')}\n\n` +
                `Esta acción no se puede deshacer.`
            );

            if (confirmacion) {
                // Crear formulario para enviar los IDs
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../app/Controllers/eliminar_usuarios_multiples.php';
                
                usuariosSeleccionados.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'usuarios_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
            }
        }

         // Form submission handlers
         document.getElementById('formCorreo').onsubmit = function(e) {
             e.preventDefault();
             var form = this;
             var data = new FormData(form);
             var msgDiv = document.getElementById('correoMsg');
             var submitBtn = form.querySelector('button[type="submit"]');
             
             // Show loading state
             msgDiv.innerHTML = '<div class="flex items-center justify-center gap-2 text-blue-400"><i class="fas fa-spinner fa-spin"></i>Enviando correo...</div>';
             submitBtn.disabled = true;
             submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Enviando...</span>';
             
             fetch('../app/Controllers/enviar_correo_phpmailer_manual.php', { method: 'POST', body: data })
                 .then(r => r.json())
                 .then(response => { 
                     if (response.success) {
                         msgDiv.innerHTML = `<div class="text-emerald-400"><i class="fas fa-check-circle mr-2"></i>${response.message}</div>`; 
                         form.reset(); 
                         document.getElementById('listaAdjuntosIndividual').innerHTML = '';
                     } else {
                         msgDiv.innerHTML = `<div class="text-red-400"><i class="fas fa-times-circle mr-2"></i>${response.message}</div>`;
                     }
                     submitBtn.disabled = false;
                     submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Enviar Correo</span>';
                 })
                 .catch(error => { 
                     console.error('Error:', error);
                     msgDiv.innerHTML = '<div class="text-red-400"><i class="fas fa-times-circle mr-2"></i>Error al procesar la respuesta del servidor.</div>'; 
                     submitBtn.disabled = false;
                     submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Enviar Correo</span>';
                 });
         };

         document.getElementById('formCorreoMasivo').onsubmit = function(e) {
             e.preventDefault();
             var form = this;
             var data = new FormData(form);
             const btn = form.querySelector('button[type="submit"]');
             const msgDiv = document.getElementById('correoMasivoMsg');
             
             btn.disabled = true;
             btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Enviando...</span>';
             msgDiv.innerHTML = '<div class="flex items-center justify-center gap-2 text-purple-400"><i class="fas fa-spinner fa-spin"></i>Enviando correos masivos...</div>';
             
             fetch('../app/Controllers/enviar_correo_masivo.php', { method: 'POST', body: data })
                 .then(r => r.text())
                 .then(msg => {
                     msgDiv.innerHTML = `<div class="text-emerald-400"><i class="fas fa-check-circle mr-2"></i>${msg}</div>`;
                     btn.disabled = false;
                     btn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Enviar Correos Masivos</span>';
                     form.reset();
                     document.getElementById('listaAdjuntos').innerHTML = '';
                 })
                 .catch(() => {
                     msgDiv.innerHTML = '<div class="text-red-400"><i class="fas fa-times-circle mr-2"></i>Error al enviar correos.</div>';
                     btn.disabled = false;
                     btn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Enviar Correos Masivos</span>';
                 });
         };

         // File attachment handlers
         document.getElementById('adjuntos_individual').addEventListener('change', function() {
             const lista = document.getElementById('listaAdjuntosIndividual');
             lista.innerHTML = '';
             for (let i = 0; i < this.files.length; i++) {
                 lista.innerHTML += `<div class="text-sm text-white/60">• ${this.files[i].name}</div>`;
             }
         });

         document.getElementById('adjuntos').addEventListener('change', function() {
             const lista = document.getElementById('listaAdjuntos');
             lista.innerHTML = '';
             for (let i = 0; i < this.files.length; i++) {
                 lista.innerHTML += `<div class="text-sm text-white/60">• ${this.files[i].name}</div>`;
             }
         });
    </script>
    
    <!-- Script de notificaciones al final -->
    <script src="../resources/js/notifications.js"></script>
</body>
</html>
<?php
} else {
    header("Location: login.php");
    exit();
}
?>
