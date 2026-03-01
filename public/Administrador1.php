<?php
session_start();
if(!isset($_SESSION['nombre']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: ./login.php');
    exit();
}

// Obtener información completa del administrador
require_once __DIR__ . "/../app/Models/conexion.php";
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

// ===== MÉTRICAS DE GRAFANA INTEGRADAS =====
// Obtener métricas reales del sistema
try {
    // Total de usuarios
    $usuarios_query = $conexion->query("SELECT COUNT(*) as total FROM usuarios");
    $total_usuarios = $usuarios_query ? $usuarios_query->fetch_assoc()['total'] : 0;
    
    // Total de vehículos
    $vehiculos_query = $conexion->query("SELECT COUNT(*) as total FROM vehiculos");
    $total_vehiculos = $vehiculos_query ? $vehiculos_query->fetch_assoc()['total'] : 0;
    
    // Total de parqueaderos
    $parqueaderos_query = $conexion->query("SELECT COUNT(*) as total FROM parqueadero");
    $total_parqueaderos = $parqueaderos_query ? $parqueaderos_query->fetch_assoc()['total'] : 0;
    
    // Parqueaderos ocupados
    $ocupados_query = $conexion->query("SELECT COUNT(*) as total FROM parqueadero WHERE disponibilidad = 'ocupado'");
    $parqueaderos_ocupados = $ocupados_query ? $ocupados_query->fetch_assoc()['total'] : 0;
    
    // Porcentaje de ocupación
    $porcentaje_ocupacion = $total_parqueaderos > 0 ? round(($parqueaderos_ocupados / $total_parqueaderos) * 100) : 0;
    
    // ===== VEHÍCULOS POR TIPO =====
    $vehiculos_por_tipo = [];
    $vehiculos_tipo_query = $conexion->query("SELECT tipo_vehiculo, COUNT(*) as cantidad FROM vehiculos GROUP BY tipo_vehiculo");
    if ($vehiculos_tipo_query) {
        while ($row = $vehiculos_tipo_query->fetch_assoc()) {
            $vehiculos_por_tipo[$row['tipo_vehiculo']] = $row['cantidad'];
        }
    }
    
    // Si no hay datos, usar datos de ejemplo
    if (empty($vehiculos_por_tipo)) {
        $vehiculos_por_tipo = [
            'Carro' => 60,
            'Moto' => 25,
            'Bicicleta' => 15
        ];
    }
    
    // ===== KPIS DEL NEGOCIO =====
    // Ocupación promedio
    $ocupacion_promedio = $total_parqueaderos > 0 ? round(($parqueaderos_ocupados / $total_parqueaderos) * 100) : 0;
    
    // Ingresos del mes (simulado)
    $ingresos_mes = 2450000; // $2,450,000
    
    // Pagos pendientes (simulado)
    $pagos_pendientes = 8;
    
    // Satisfacción (simulado)
    $satisfaccion = 4.8;
    
} catch (Exception $e) {
    // Valores por defecto si hay error
    $total_usuarios = 0;
    $total_vehiculos = 0;
    $total_parqueaderos = 0;
    $parqueaderos_ocupados = 0;
    $porcentaje_ocupacion = 0;
    $sistema_db = 98;
    $sistema_servidor = 95;
    $sistema_almacenamiento = 72;
    $sistema_red = 99;
}
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, sidebarOpen: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador | Quintanares by Parkovisco</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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

        /* Chart container */
        .chart-container {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .chart-container canvas {
            max-height: 300px !important;
            width: 100% !important;
        }

        /* Activity feed */
        .activity-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            background: rgba(16, 185, 129, 0.05);
            border-color: rgba(16, 185, 129, 0.3);
            transform: translateX(5px);
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

        /* Progress bars */
        .progress-bar {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            position: relative;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #3b82f6);
            border-radius: 10px;
            transition: width 0.3s ease;
            position: relative;
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 2s linear infinite;
        }
    </style>
</head>
<body class="dashboard-container" x-data="adminDashboard()">
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
                    <i class="fas fa-tachometer-alt text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-cyber text-gradient">DASHBOARD ADMINISTRADOR</h1>
                    <p class="text-xs text-white/60 font-mono">QUINTANARES BY PARKOVISCO CONTROL CENTER</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-emerald-500/20 px-4 py-2 rounded-full border border-emerald-500/40">
                <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-emerald-400 font-bold text-xs text-cyber">SISTEMA ACTIVO</span>
            </div>
            <div class="text-white/60 font-mono text-xs" x-text="currentTime"></div>
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
                    <a href="Administrador1.php" class="nav-link active">
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
                    <a href="parqueaderos.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-parking"></i></div>
                        <span>Parqueaderos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="grafana_dashboard.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-chart-line"></i></div>
                        <span>Grafana</span>
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
        <div class="glass-card p-10 scan-lines mb-8" data-aos="fade-up">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-5xl font-bold text-white mb-4 text-cyber">
                        BIENVENIDO AL <span class="text-gradient">CENTRO DE CONTROL</span>
                    </h1>
                    <p class="text-2xl text-white/80 mb-2">Monitoreo y gestión integral del sistema Quintanares by Parkovisco</p>
                    <div class="text-lg text-white/60 font-mono matrix-text" x-text="'SISTEMA ACTIVO • ' + fullDateTime"></div>
                    <div class="mt-6 flex items-center gap-4">
                        <div class="px-4 py-2 bg-emerald-500/20 border border-emerald-500/40 rounded-full">
                            <span class="text-emerald-400 font-bold text-sm text-cyber">TODOS LOS SISTEMAS OPERATIVOS</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-8xl font-bold text-gradient text-cyber" x-text="currentDate.split('/')[0]"></div>
                    <div class="text-2xl text-white/70 text-cyber" x-text="currentDay.toUpperCase()"></div>
                </div>
            </div>
        </div>

        <!-- Admin Profile Section -->
        <div class="glass-card p-6 mb-6" data-aos="fade-up" data-aos-delay="50">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-white text-cyber">
                    <i class="fas fa-user-shield mr-3 text-emerald-400"></i>
                    INFORMACIÓN DEL ADMINISTRADOR
                </h2>
                <div class="px-3 py-1 bg-emerald-500/20 border border-emerald-500/40 rounded-full">
                    <span class="text-emerald-400 font-bold text-xs text-cyber">SESIÓN ACTIVA</span>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-black/30 rounded-lg p-4 border border-emerald-500/20">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-id-card text-emerald-400"></i>
                        <span class="text-white/70 font-mono text-sm">ID Usuario</span>
                    </div>
                    <div class="text-xl font-bold text-white"><?php echo htmlspecialchars($admin_info['id']); ?></div>
                </div>
                
                <div class="bg-black/30 rounded-lg p-4 border border-emerald-500/20">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-user text-emerald-400"></i>
                        <span class="text-white/70 font-mono text-sm">Nombre Completo</span>
                    </div>
                    <div class="text-lg font-bold text-white"><?php echo htmlspecialchars($admin_info['nombre'] . ' ' . $admin_info['apellido']); ?></div>
                </div>
                
                <div class="bg-black/30 rounded-lg p-4 border border-emerald-500/20">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-envelope text-emerald-400"></i>
                        <span class="text-white/70 font-mono text-sm">Email</span>
                    </div>
                    <div class="text-sm font-mono text-white/80"><?php echo htmlspecialchars($admin_info['email']); ?></div>
                </div>
                
                <div class="bg-black/30 rounded-lg p-4 border border-emerald-500/20">
                    <div class="flex items-center gap-3 mb-2">
                        <i class="fas fa-phone text-emerald-400"></i>
                        <span class="text-white/70 font-mono text-sm">Teléfono</span>
                    </div>
                    <div class="text-sm font-mono text-white/80"><?php echo htmlspecialchars($admin_info['celular']); ?></div>
                </div>
            </div>
            
            <div class="mt-4 flex justify-end">
                <a href="modificarusu.php?id=<?php echo $admin_info['id']; ?>" 
                   class="bg-emerald-500/20 hover:bg-emerald-500/30 text-emerald-400 text-sm font-mono py-2 px-4 rounded-lg border border-emerald-500/30 transition-all duration-300 flex items-center gap-2">
                    <i class="fas fa-edit"></i>
                    <span>Editar Perfil</span>
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-emerald-400 to-emerald-600">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div class="stat-value" x-text="totalUsers">0</div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-blue-400 to-blue-600">
                    <i class="fas fa-car text-white"></i>
                </div>
                <div class="stat-value" x-text="totalVehicles">0</div>
                <div class="stat-label">Vehículos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-purple-400 to-purple-600">
                    <i class="fas fa-parking text-white"></i>
                </div>
                <div class="stat-value" x-text="occupiedSpaces">0</div>
                <div class="stat-label">Espacios Ocupados</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon bg-gradient-to-br from-orange-400 to-orange-600">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
                <div class="stat-value" x-text="systemHealth">0</div>
                <div class="stat-label">% Sistema</div>
            </div>
        </div>

        <!-- Charts and Analytics -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6" data-aos="fade-up" data-aos-delay="200">
            <!-- Vehicles by Type Chart -->
            <div class="chart-container">
                <h3 class="text-xl font-bold text-white mb-4 text-cyber">
                    <i class="fas fa-car mr-3"></i>Vehículos por Tipo
                </h3>
                <div style="height: 300px; position: relative;">
                    <canvas id="vehiclesTypeChart"></canvas>
                </div>
            </div>

            <!-- Business KPIs -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold text-white mb-4 text-cyber">
                    <i class="fas fa-chart-bar mr-3"></i>KPIs del Negocio
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-white/80 text-sm">Ocupación Promedio</span>
                            <span class="text-emerald-400 font-mono text-sm"><?php echo $ocupacion_promedio; ?>%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $ocupacion_promedio; ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-white/80 text-sm">Ingresos del Mes</span>
                            <span class="text-emerald-400 font-mono text-sm">$<?php echo number_format($ingresos_mes, 0, ',', '.'); ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: 75%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-white/80 text-sm">Pagos Pendientes</span>
                            <span class="text-yellow-400 font-mono text-sm"><?php echo $pagos_pendientes; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo min(($pagos_pendientes / 20) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-white/80 text-sm">Satisfacción</span>
                            <span class="text-emerald-400 font-mono text-sm"><?php echo $satisfaccion; ?>/5</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo ($satisfaccion / 5) * 100; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity and Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" data-aos="fade-up" data-aos-delay="300">
            <!-- Recent Activity -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold text-white mb-4 text-cyber">
                    <i class="fas fa-history mr-3"></i>Actividad Reciente
                </h3>
                <div class="space-y-3">
                    <div class="activity-item">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-emerald-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-plus text-emerald-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white/90 text-sm font-medium">Nuevo usuario registrado</p>
                                <p class="text-white/60 text-xs">Juan Pérez - Torre A, Apt 101</p>
                            </div>
                            <span class="text-white/40 text-xs font-mono">2 min</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-car text-blue-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white/90 text-sm font-medium">Vehículo ingresó al parqueadero</p>
                                <p class="text-white/60 text-xs">ABC-123 - Espacio P-15</p>
                            </div>
                            <span class="text-white/40 text-xs font-mono">5 min</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-purple-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white/90 text-sm font-medium">Acceso autorizado</p>
                                <p class="text-white/60 text-xs">Visitante - María González</p>
                            </div>
                            <span class="text-white/40 text-xs font-mono">12 min</span>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-500/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-orange-400 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white/90 text-sm font-medium">Alerta de sistema</p>
                                <p class="text-white/60 text-xs">Espacio P-23 ocupado sin autorización</p>
                            </div>
                            <span class="text-white/40 text-xs font-mono">18 min</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass-card p-6">
                <h3 class="text-xl font-bold text-white mb-4 text-cyber">
                    <i class="fas fa-bolt mr-3"></i>Acciones Rápidas
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="tablausu.php" class="cyber-button">
                        <i class="fas fa-users"></i>
                        <span>Gestionar Usuarios</span>
                    </a>
                    <a href="parqueaderos.php" class="cyber-button">
                        <i class="fas fa-parking"></i>
                        <span>Parqueaderos</span>
                    </a>
                    <a href="gestion_vigilantes.php" class="cyber-button">
                        <i class="fas fa-shield-alt"></i>
                        <span>Vigilantes</span>
                    </a>
                    <a href="generate_pdf.php" class="cyber-button" target="_blank">
                        <i class="fas fa-file-pdf"></i>
                        <span>Generar Reporte</span>
                    </a>
                </div>
            </div>
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
        function adminDashboard() {
            return {
                sidebarOpen: false,
                currentTime: '',
                currentDate: '',
                currentDay: '',
                fullDateTime: '',
                totalUsers: 0,
                totalVehicles: 0,
                occupiedSpaces: 0,
                systemHealth: 0,
                
                init() {
                    this.initParticles();
                    this.startClock();
                    this.animateStats();
                    
                    // Initialize chart after a small delay to ensure DOM is ready
                    setTimeout(() => {
                        this.initChart();
                    }, 100);
                    
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
                
                startClock() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);
                },
                
                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    
                    this.currentDate = now.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    });
                    
                    this.currentDay = now.toLocaleDateString('es-ES', { weekday: 'long' });
                    
                    this.fullDateTime = now.toLocaleDateString('es-ES', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    }) + ', ' + now.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                },
                
                animateStats() {
                    // Animate stats counters
                    const duration = 2000;
                    const start = Date.now();
                    const targets = {
                        totalUsers: <?php echo $total_usuarios; ?>,
                        totalVehicles: <?php echo $total_vehiculos; ?>,
                        occupiedSpaces: <?php echo $parqueaderos_ocupados; ?>,
                        systemHealth: <?php echo $porcentaje_ocupacion; ?>
                    };
                    
                    const animate = () => {
                        const now = Date.now();
                        const elapsed = now - start;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        this.totalUsers = Math.floor(targets.totalUsers * progress);
                        this.totalVehicles = Math.floor(targets.totalVehicles * progress);
                        this.occupiedSpaces = Math.floor(targets.occupiedSpaces * progress);
                        this.systemHealth = Math.floor(targets.systemHealth * progress);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    };
                    
                    animate();
                },
                
                initChart() {
                    const ctx = document.getElementById('vehiclesTypeChart');
                    if (ctx) {
                        // Datos de vehículos por tipo desde PHP
                        const vehicleData = <?php echo json_encode($vehiculos_por_tipo); ?>;
                        const labels = Object.keys(vehicleData);
                        const data = Object.values(vehicleData);
                        
                        // Colores para cada tipo de vehículo
                        const colors = {
                            'Carro': '#10b981',      // Verde
                            'Moto': '#3b82f6',       // Azul
                            'Bicicleta': '#8b5cf6',  // Morado
                            'Camioneta': '#f59e0b',  // Amarillo
                            'Otro': '#ef4444'        // Rojo
                        };
                        
                        const backgroundColors = labels.map(label => colors[label] || '#6b7280');
                        const borderColors = labels.map(label => colors[label] || '#6b7280');
                        
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Cantidad de Vehículos',
                                    data: data,
                                    backgroundColor: backgroundColors,
                                    borderColor: borderColors,
                                    borderWidth: 2,
                                    borderRadius: 8,
                                    borderSkipped: false,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                        titleColor: '#ffffff',
                                        bodyColor: '#ffffff',
                                        borderColor: '#10b981',
                                        borderWidth: 1,
                                        callbacks: {
                                            label: function(context) {
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                                                return context.label + ': ' + context.parsed.y + ' vehículos (' + percentage + '%)';
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        display: true,
                                        ticks: {
                                            color: '#ffffff',
                                            font: {
                                                size: 12,
                                                weight: 'bold'
                                            }
                                        },
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)',
                                            drawBorder: false
                                        }
                                    },
                                    y: {
                                        display: true,
                                        beginAtZero: true,
                                        ticks: {
                                            color: '#ffffff',
                                            font: {
                                                size: 12
                                            },
                                            stepSize: 1
                                        },
                                        grid: {
                                            color: 'rgba(255, 255, 255, 0.1)',
                                            drawBorder: false
                                        }
                                    }
                                },
                                animation: {
                                    duration: 2000,
                                    easing: 'easeInOutQuart'
                                }
                            }
                        });
                    }
                }
            }
        }
        
        // ===== AUTO-REFRESH PARA MÉTRICAS EN TIEMPO REAL =====
        // Actualizar métricas cada 30 segundos
        setInterval(() => {
            // Recargar la página para obtener datos actualizados
            // En una implementación más avanzada, se podría usar AJAX
            location.reload();
        }, 30000); // 30 segundos
        
        // Mostrar notificación de auto-refresh
        console.log('🔄 Dashboard con métricas en tiempo real activado');
        console.log('📊 Auto-refresh cada 30 segundos');
    </script>
</body>
</html>
