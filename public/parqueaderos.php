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

// ===== MÉTRICAS REALES DE PARQUEADEROS =====
try {
    // Total de parqueaderos
    $total_parqueaderos_query = $conexion->query("SELECT COUNT(*) as total FROM parqueadero");
    $total_parqueaderos = $total_parqueaderos_query ? $total_parqueaderos_query->fetch_assoc()['total'] : 0;
    
    // Parqueaderos ocupados
    $ocupados_query = $conexion->query("SELECT COUNT(*) as total FROM parqueadero WHERE disponibilidad = 'ocupado'");
    $parqueaderos_ocupados = $ocupados_query ? $ocupados_query->fetch_assoc()['total'] : 0;
    
    // Parqueaderos disponibles
    $disponibles_query = $conexion->query("SELECT COUNT(*) as total FROM parqueadero WHERE disponibilidad = 'disponible'");
    $parqueaderos_disponibles = $disponibles_query ? $disponibles_query->fetch_assoc()['total'] : 0;
    
    // Parqueaderos reservados
    $reservados_query = $conexion->query("SELECT COUNT(*) as total FROM parqueadero WHERE disponibilidad = 'reservado'");
    $parqueaderos_reservados = $reservados_query ? $reservados_query->fetch_assoc()['total'] : 0;
    
    // Porcentaje de ocupación
    $porcentaje_ocupacion = $total_parqueaderos > 0 ? round(($parqueaderos_ocupados / $total_parqueaderos) * 100) : 0;
    
    // Ingresos del día (simulado)
    $ingresos_dia = $parqueaderos_ocupados * 5000; // $5,000 por parqueadero ocupado
    
} catch (Exception $e) {
    // Valores por defecto si hay error
    $total_parqueaderos = 0;
    $parqueaderos_ocupados = 0;
    $parqueaderos_disponibles = 0;
    $parqueaderos_reservados = 0;
    $porcentaje_ocupacion = 0;
    $ingresos_dia = 0;
}

// Función para buscar parqueaderos (integrada directamente)
function buscarParqueaderos($valor) {
    $conn = Conexion::getInstancia()->getConexion();
    
    // Crear tabla de parqueaderos si no existe
    $createTable = "CREATE TABLE IF NOT EXISTS parqueaderos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_espacio INT NOT NULL,
        estado ENUM('disponible', 'ocupado', 'reservado') DEFAULT 'disponible',
        tipo ENUM('propietario', 'visitante', 'reservado') DEFAULT 'visitante',
        ocupante_nombre VARCHAR(100),
        vehiculo_placa VARCHAR(20),
        hora_entrada DATETIME,
        hora_salida DATETIME,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        die("Error creando tabla parqueaderos: " . $conn->error);
    }
    
    // Insertar datos de ejemplo si la tabla está vacía
    $checkCount = "SELECT COUNT(*) as total FROM parqueaderos";
    $result = $conn->query($checkCount);
    $count = $result->fetch_assoc()['total'];
    
    if ($count == 0) {
        // Insertar 100 espacios de ejemplo
        for ($i = 1; $i <= 100; $i++) {
            $estados = ['disponible', 'ocupado', 'reservado'];
            $tipos = ['propietario', 'visitante', 'reservado'];
            $nombres = ['Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez', 'Luis Rodríguez', 'Carmen Silva', 'Roberto Díaz', 'Laura Vega'];
            $placas = ['ABC-123', 'XYZ-789', 'DEF-456', 'GHI-012', 'JKL-345', 'MNO-678', 'PQR-901', 'STU-234'];
            
            $estado = $estados[array_rand($estados)];
            $tipo = $tipos[array_rand($tipos)];
            $nombre = $nombres[array_rand($nombres)];
            $placa = $placas[array_rand($placas)];
            
            $hora_entrada = null;
            if ($estado != 'disponible') {
                $hora_entrada = date('Y-m-d H:i:s', strtotime('-' . rand(1, 8) . ' hours'));
            }
            
            $insert = "INSERT INTO parqueaderos (numero_espacio, estado, tipo, ocupante_nombre, vehiculo_placa, hora_entrada) 
                      VALUES ($i, '$estado', '$tipo', '$nombre', '$placa', " . ($hora_entrada ? "'$hora_entrada'" : "NULL") . ")";
            $conn->query($insert);
        }
    }
    
    $sql = "SELECT * FROM parqueaderos";
    
    if(!empty($valor)) {
        $sql .= " WHERE numero_espacio LIKE ? OR ocupante_nombre LIKE ? OR vehiculo_placa LIKE ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $busqueda = "%$valor%";
        $stmt->bind_param("sss", $busqueda, $busqueda, $busqueda);
    } else {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
    }
    
    if (!$stmt->execute()) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    return $stmt->get_result();
}

$resultado = null;

if(isset($_POST['buscar'])) {
    $valor = isset($_POST['nom']) ? $_POST['nom'] : '';
    $resultado = buscarParqueaderos($valor);
} else {
    $resultado = buscarParqueaderos('');
}

// Solo mostrar el formulario si el usuario es administrador
if (isset($_SESSION['nombre_rol']) && $_SESSION['nombre_rol'] === 'administrador') {
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, sidebarOpen: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Parqueaderos | Quintanares by Parkovisco</title>
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

        /* Parking grid */
        .parking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 12px;
            margin-top: 1rem;
        }

        .parking-space {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .parking-space:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .parking-space.disponible {
            border-color: rgba(34, 197, 94, 0.5);
            background: rgba(34, 197, 94, 0.1);
        }

        .parking-space.ocupado {
            border-color: rgba(239, 68, 68, 0.5);
            background: rgba(239, 68, 68, 0.1);
        }

        .parking-space.reservado {
            border-color: rgba(245, 158, 11, 0.5);
            background: rgba(245, 158, 11, 0.1);
        }

        .space-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 4px;
        }

        .space-status {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .space-status.disponible {
            color: #22c55e;
        }

        .space-status.ocupado {
            color: #ef4444;
        }

        .space-status.reservado {
            color: #f59e0b;
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
    </style>
</head>
<body class="dashboard-container" x-data="parkingManagement()">
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
                    <i class="fas fa-parking text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-cyber text-gradient">GESTIÓN DE PARQUEADEROS</h1>
                    <p class="text-xs text-white/60 font-mono">QUINTANARES BY PARKOVISCO PARKING SYSTEM</p>
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
                    <a href="tablausu.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-users"></i></div>
                        <span>Usuarios</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="parqueaderos.php" class="nav-link active">
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
                        CONTROL DE <span class="text-gradient">PARQUEADEROS</span>
                    </h2>
                    <p class="text-lg text-white/80">Monitoreo y gestión de espacios de estacionamiento</p>
                </div>
                <div class="text-6xl text-emerald-400 animate-pulse">
                    <i class="fas fa-car"></i>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-emerald-400 to-emerald-600">
                    <i class="fas fa-parking text-white"></i>
                </div>
                <div class="stat-value" x-text="totalSpaces"><?php echo $total_parqueaderos; ?></div>
                <div class="stat-label">Total Espacios</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-green-400 to-green-600">
                    <i class="fas fa-check-circle text-white"></i>
                </div>
                <div class="stat-value" x-text="availableSpaces"><?php echo $parqueaderos_disponibles; ?></div>
                <div class="stat-label">Disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-red-400 to-red-600">
                    <i class="fas fa-times-circle text-white"></i>
                </div>
                <div class="stat-value" x-text="occupiedSpaces"><?php echo $parqueaderos_ocupados; ?></div>
                <div class="stat-label">Ocupados</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-yellow-400 to-yellow-600">
                    <i class="fas fa-clock text-white"></i>
                </div>
                <div class="stat-value" x-text="reservedSpaces"><?php echo $parqueaderos_reservados; ?></div>
                <div class="stat-label">Reservados</div>
            </div>
        </div>

        <!-- Search and Actions -->
        <div class="glass-card p-6 mb-6" data-aos="fade-up" data-aos-delay="200">
            <form method="POST" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-white/80 text-sm font-bold mb-2">
                        <i class="fas fa-search mr-2"></i>Buscar Parqueadero
                    </label>
                    <input type="text" name="nom" class="cyber-input w-full" 
                           placeholder="Número de espacio, nombre, placa..." 
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
                </div>
            </form>
        </div>

        <!-- Parking Grid -->
        <div class="glass-card p-6" data-aos="fade-up" data-aos-delay="300">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-white text-cyber">
                    <i class="fas fa-th-large mr-3"></i>Mapa de Parqueaderos
                </h3>
                <div class="text-white/60 font-mono">
                    Total: <span class="text-emerald-400 font-bold"><?php echo $resultado ? $resultado->num_rows : 0; ?></span> espacios
                </div>
            </div>

            <?php if ($resultado && $resultado->num_rows > 0): ?>
            <div class="parking-grid">
                <?php while($row = $resultado->fetch_assoc()): ?>
                <div class="parking-space <?php echo $row['estado']; ?>" 
                     onclick="mostrarDetalles(<?php echo $row['id']; ?>)">
                    <div class="space-number"><?php echo $row['numero_espacio']; ?></div>
                    <div class="space-status <?php echo $row['estado']; ?>">
                        <?php echo strtoupper($row['estado']); ?>
                    </div>
                    <?php if($row['estado'] != 'disponible'): ?>
                    <div class="text-xs text-white/60 mt-1">
                        <?php echo htmlspecialchars($row['ocupante_nombre'] ?? ''); ?>
                    </div>
                    <div class="text-xs text-white/40">
                        <?php echo htmlspecialchars($row['vehiculo_placa'] ?? ''); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <div class="text-6xl text-white/20 mb-4">
                    <i class="fas fa-parking"></i>
                </div>
                <h3 class="text-xl font-bold text-white/60 mb-2">No se encontraron espacios</h3>
                <p class="text-white/40">Intenta con otros términos de búsqueda</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

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

    <script>
        function parkingManagement() {
            return {
                sidebarOpen: false,
                totalSpaces: 0,
                availableSpaces: 0,
                occupiedSpaces: 0,
                reservedSpaces: 0,
                
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
                    const targets = {
                        totalSpaces: <?php echo $total_parqueaderos; ?>,
                        availableSpaces: <?php echo $parqueaderos_disponibles; ?>,
                        occupiedSpaces: <?php echo $parqueaderos_ocupados; ?>,
                        reservedSpaces: <?php echo $parqueaderos_reservados; ?>
                    };
                    
                    const animate = () => {
                        const now = Date.now();
                        const elapsed = now - start;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        this.totalSpaces = Math.floor(targets.totalSpaces * progress);
                        this.availableSpaces = Math.floor(targets.availableSpaces * progress);
                        this.occupiedSpaces = Math.floor(targets.occupiedSpaces * progress);
                        this.reservedSpaces = Math.floor(targets.reservedSpaces * progress);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    };
                    
                    animate();
                }
            }
        }
        
        // Funciones de utilidad
        function limpiarBusqueda() {
            window.location.href = 'parqueaderos.php';
        }
        
        function mostrarDetalles(id) {
            // Función para mostrar detalles del espacio
            alert('Detalles del espacio ' + id);
        }
    </script>
</body>
</html>
<?php
} else {
    header("Location: login.php");
    exit();
}
?>
