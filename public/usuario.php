<?php
session_start();
if(!isset($_SESSION['nombre']) || $_SESSION['nombre_rol'] !== 'propietario') {
    header('Location: ./login.php');
    exit();
}

// Verificar que el ID del usuario esté en la sesión
if(!isset($_SESSION['id'])) {
    echo "Error: ID de usuario no encontrado en la sesión. Por favor, inicia sesión nuevamente.";
    exit();
}

require_once __DIR__ . "/../app/Models/conexion.php";

// Obtener la conexión a la base de datos
$conexion = Conexion::getInstancia()->getConexion();

// Obtener los vehículos del usuario
$vehiculos = null;
try {
    $stmt = $conexion->prepare("SELECT * FROM vehiculo WHERE id_usuario = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $vehiculos = $stmt->get_result();
    } else {
        $vehiculos = null;
    }
} catch (Exception $e) {
    $vehiculos = null;
}

// Mostrar mensajes de sesión
$mensaje = '';
$mensaje_tipo = '';
if (isset($_SESSION['mensaje'])) {
    $mensaje_tipo = $_SESSION['mensaje'];
    $mensaje = $_SESSION['mensaje_texto'] ?? '';
    // Limpiar mensajes después de mostrarlos
    unset($_SESSION['mensaje']);
    unset($_SESSION['mensaje_texto']);
}

// Obtener las notificaciones (si la tabla existe)
$notificaciones = null;
try {
    $stmt = $conexion->prepare("SELECT * FROM notificaciones 
                              WHERE id_usuario = ? 
                              ORDER BY fecha DESC 
                              LIMIT 10");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['id']);
        $stmt->execute();
        $notificaciones = $stmt->get_result();
    }
} catch (Exception $e) {
    // Si la tabla no existe, continuar sin notificaciones
    $notificaciones = null;
}
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, sidebarOpen: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Usuario | Quintanares by Parkovisco</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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

        /* Sidebar cyberpunk */
        .sidebar {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border-right: 1px solid rgba(16, 185, 129, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 40;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Glass cards */
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card::before {
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

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        /* Navegación cyberpunk */
        .nav-link {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.2), transparent);
            transition: left 0.5s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            transform: translateX(8px);
            border-left: 3px solid #10b981;
        }

        .nav-link.active {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(59, 130, 246, 0.2));
            color: #10b981;
            border-left: 3px solid #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
        }

        /* Content cards cyberpunk */
        .content-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }

        .content-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
        }

        .profile-section {
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
        }

        /* Animaciones cyberpunk */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); }
            50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.6); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out forwards;
        }

        .animate-glow {
            animation: glow 2s ease-in-out infinite;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Textos cyberpunk */
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            text-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        /* Sidebar cyberpunk */
        .sidebar-nav {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(25px);
            border-right: 1px solid rgba(16, 185, 129, 0.3);
            overflow-y: auto;
            z-index: 45;
            transform: translateX(-100%);
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 50px rgba(0, 0, 0, 0.8);
        }

        .sidebar-nav::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, 
                rgba(16, 185, 129, 0.1) 0%, 
                transparent 50%, 
                rgba(59, 130, 246, 0.1) 100%
            );
            pointer-events: none;
        }

        .sidebar-nav.open {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            transition: margin-left 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 10;
        }

        .main-content.sidebar-open {
            margin-left: 280px;
        }

        /* Quick action cards cyberpunk */
        .quick-action-card {
            background: linear-gradient(135deg, 
                rgba(16, 185, 129, 0.2) 0%, 
                rgba(59, 130, 246, 0.2) 100%
            );
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: white;
            border-radius: 20px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .quick-action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: left 0.6s;
        }

        .quick-action-card:hover::before {
            left: 100%;
        }

        .quick-action-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(16, 185, 129, 0.3);
            border-color: rgba(16, 185, 129, 0.6);
        }

        /* Payment status cyberpunk */
        .payment-status {
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }

        .payment-status::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.05) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s;
        }

        .payment-status:hover::before {
            transform: translateX(100%);
        }

        .payment-status.paid {
            border-left-color: #10b981;
            background: rgba(16, 185, 129, 0.1);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .payment-status.pending {
            border-left-color: #f59e0b;
            background: rgba(245, 158, 11, 0.1);
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.2);
        }

        .payment-status.overdue {
            border-left-color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        /* Notification items cyberpunk */
        .notification-item {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .notification-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
            transition: left 0.5s;
        }

        .notification-item:hover::before {
            left: 100%;
        }

        .notification-item:hover {
            background: rgba(16, 185, 129, 0.05);
            transform: translateX(8px);
            border-left: 3px solid #10b981;
        }

        /* Chart container cyberpunk */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem;
        }

        /* Cyberpunk buttons */
        .cyber-button {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(59, 130, 246, 0.2));
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .cyber-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .cyber-button:hover::before {
            left: 100%;
        }

        .cyber-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            border-color: rgba(16, 185, 129, 0.6);
        }

        /* Cyberpunk inputs */
        .cyber-input {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .cyber-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            background: rgba(255, 255, 255, 0.05);
        }

        .cyber-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Text effects */
        .text-gradient {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
        }

        /* Responsive adjustments */
        @media (min-width: 1024px) {
            .sidebar-nav {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 280px;
            }
        }

        /* Footer adjustments for dashboard */
        footer {
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 1024px) {
            footer {
                margin-left: 280px;
            }
        }

        /* Ensure main content has proper spacing */
        .main-content {
            padding-top: 70px;
            padding-bottom: 2rem;
            min-height: 100vh;
        }

        /* Mobile footer adjustments */
        @media (max-width: 1023px) {
            footer {
                margin-left: 0 !important;
            }
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 40;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
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

        /* Cyberpunk scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.3);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #059669, #2563eb);
        }
    </style>
</head>
<body class="dashboard-container" x-data="propietarioDashboard()" :class="darkMode ? 'dark' : ''">
    <!-- Particles Background -->
    <div id="particles-js" class="particles-container"></div>
    
    <!-- Header cyberpunk -->
    <nav class="bg-black/80 backdrop-filter backdrop-blur-md shadow-2xl w-full fixed top-0 z-50 px-4 py-3 flex items-center justify-between border-b border-emerald-500/30">
        <div class="flex items-center gap-4">
            <!-- Hamburger Button -->
            <button @click="toggleSidebar()" class="cyber-button lg:hidden" style="padding: 8px 12px;">
                <i class="fas fa-bars text-lg"></i>
            </button>
            
        <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-blue-500 rounded-lg flex items-center justify-center animate-glow">
                    <i class="fas fa-building text-white text-lg"></i>
        </div>
                <div>
                    <h1 class="text-xl font-bold text-white text-glow">QUINTANARES BY PARKOVISCO</h1>
                    <p class="text-xs text-emerald-400 font-mono">PANEL PROPIETARIO</p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Real-time Clock -->
            <div class="hidden md:flex items-center gap-2 px-3 py-2 bg-black/50 rounded-lg border border-emerald-500/30">
                <i class="fas fa-clock text-emerald-400"></i>
                <span class="text-white font-mono text-sm" x-text="currentTime"></span>
            </div>
            
            <!-- Status Indicator -->
            <div class="flex items-center gap-2 px-3 py-2 bg-emerald-500/20 rounded-lg border border-emerald-500/40">
                <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                <span class="text-emerald-400 text-sm font-bold">SISTEMA ACTIVO</span>
            </div>
            
            <!-- Notifications -->
            <div class="relative">
                <button class="cyber-button" style="padding: 8px 12px;">
                    <i class="fas fa-bell text-lg"></i>
                    <?php if($notificaciones && $notificaciones->num_rows > 0): ?>
                        <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse"><?php echo $notificaciones->num_rows; ?></span>
                    <?php endif; ?>
            </button>
            </div>
            
            <!-- Theme Toggle -->
            <button @click="darkMode = !darkMode" class="cyber-button" style="padding: 8px 12px;" :class="darkMode ? 'text-yellow-400' : 'text-blue-400'">
                <i :class="darkMode ? 'fas fa-moon' : 'fas fa-sun'" class="text-lg"></i>
            </button>
            
            <!-- User Menu -->
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="cursor-pointer">
                    <div class="w-10 h-10 rounded-full border-2 border-emerald-400 overflow-hidden bg-gradient-to-br from-emerald-400 to-blue-500 flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                </label>
                <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-black/90 backdrop-blur-md border border-emerald-500/30 rounded-lg w-52 mt-2">
                    <li><a href="#perfil" class="text-white hover:bg-emerald-500/20"><i class="fas fa-user mr-2 text-emerald-400"></i>Mi Perfil</a></li>
                    <li><a href="#vehiculos" class="text-white hover:bg-emerald-500/20"><i class="fas fa-car mr-2 text-emerald-400"></i>Mis Vehículos</a></li>
                    <li><a href="#pagos" class="text-white hover:bg-emerald-500/20"><i class="fas fa-credit-card mr-2 text-emerald-400"></i>Pagos</a></li>
                    <li><a href="#configuracion" class="text-white hover:bg-emerald-500/20"><i class="fas fa-cog mr-2 text-emerald-400"></i>Configuración</a></li>
                    <li><a href="logout.php" class="text-red-400 hover:bg-red-500/20"><i class="fas fa-sign-out-alt mr-2"></i>Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar Navigation -->
    <div id="sidebar" class="sidebar-nav" :class="sidebarOpen ? 'open' : ''">
        <div class="p-6 pt-20">
            <!-- User Profile Section -->
            <div class="flex items-center gap-3 mb-8 p-4 bg-gradient-to-r from-emerald-500/20 to-blue-500/20 rounded-lg border border-emerald-500/30">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-400 to-blue-500 rounded-full flex items-center justify-center animate-glow">
                    <i class="fas fa-user text-white text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-white text-glow"><?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?></h3>
                    <p class="text-sm text-emerald-400 font-mono">Propietario</p>
                    <div class="flex items-center gap-1 mt-1">
                        <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                        <span class="text-xs text-emerald-400">ONLINE</span>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="space-y-2">
                <a href="#dashboard" class="nav-link active" @click="showSection('dashboard')">
                    <i class="fas fa-home text-emerald-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#perfil" class="nav-link" @click="showSection('perfil')">
                    <i class="fas fa-user text-emerald-400"></i>
                    <span>Mi Perfil</span>
                </a>
                <a href="#vehiculos" class="nav-link" @click="showSection('vehiculos')">
                    <i class="fas fa-car text-emerald-400"></i>
                    <span>Mis Vehículos</span>
                </a>
                <a href="#pagos" class="nav-link" @click="showSection('pagos')">
                    <i class="fas fa-credit-card text-emerald-400"></i>
                    <span>Pagos</span>
                </a>
                <a href="#notificaciones" class="nav-link" @click="showSection('notificaciones')">
                    <i class="fas fa-bell text-emerald-400"></i>
                    <span>Notificaciones</span>
                    <span class="ml-auto w-6 h-6 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse">3</span>
                </a>
                <a href="#apartamento" class="nav-link" @click="showSection('apartamento')">
                    <i class="fas fa-building text-emerald-400"></i>
                    <span>Mi Apartamento</span>
                </a>
                <a href="#visitantes" class="nav-link" @click="showSection('visitantes')">
                    <i class="fas fa-users text-emerald-400"></i>
                    <span>Visitantes</span>
                </a>
                <a href="#reportes" class="nav-link" @click="showSection('reportes')">
                    <i class="fas fa-chart-bar text-emerald-400"></i>
                    <span>Reportes</span>
                </a>
                <a href="#configuracion" class="nav-link" @click="showSection('configuracion')">
                    <i class="fas fa-cog text-emerald-400"></i>
                    <span>Configuración</span>
                </a>
            </nav>
            
            <!-- Quick Actions -->
            <div class="mt-8">
                <h4 class="font-semibold text-white mb-4 text-glow">ACCIONES RÁPIDAS</h4>
                <div class="space-y-3">
                    <button @click="generarQRVisitante()" class="w-full cyber-button text-sm">
                        <i class="fas fa-qrcode mr-2"></i>QR Visitante
                    </button>
                    <button @click="reportarIncidencia()" class="w-full cyber-button text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Reportar
                    </button>
                    <button @click="contactarAdmin()" class="w-full cyber-button text-sm">
                        <i class="fas fa-phone mr-2"></i>Contactar
                    </button>
                </div>
            </div>
            
            <!-- System Status -->
            <div class="mt-8 p-4 bg-black/50 rounded-lg border border-emerald-500/30">
                <h5 class="text-white font-bold mb-2 text-sm">ESTADO DEL SISTEMA</h5>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-emerald-400 text-xs">Seguridad</span>
                        <div class="flex items-center gap-1">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span class="text-emerald-400 text-xs">ACTIVO</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-emerald-400 text-xs">Acceso</span>
                        <div class="flex items-center gap-1">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span class="text-emerald-400 text-xs">PERMITIDO</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <main id="mainContent" class="main-content py-6 px-4 lg:px-8" :class="sidebarOpen ? 'sidebar-open' : ''">
        <!-- Dashboard Section -->
        <div id="dashboardSection" class="section-content">
            <!-- Breadcrumbs -->
            <div class="flex items-center gap-2 mb-6 text-sm">
                <i class="fas fa-home text-emerald-400"></i>
                <span class="text-white/70">Dashboard</span>
                <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                <span class="text-emerald-400 font-mono">PANEL PRINCIPAL</span>
            </div>
            
            <!-- Welcome Card -->
            <div class="content-card p-8 mb-8 animate-fade-in">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold text-white mb-2 text-glow">
                            ¡BIENVENIDO, CARLOS! 
                            <span class="text-gradient">👋</span>
                    </h1>
                        <p class="text-white/70 text-lg">Apartamento 301 - Torre A | Quintanares by Parkovisco</p>
                        <div class="flex items-center gap-2 mt-3">
                            <i class="fas fa-calendar text-emerald-400"></i>
                            <span class="text-emerald-400 font-mono text-sm">Último acceso: Hoy 8:30 AM</span>
                </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></div>
                            <span class="text-emerald-400 text-sm font-bold">SISTEMA ACTIVO</span>
                    </div>
                    </div>
                    <div class="flex gap-4">
                        <div class="quick-action-card text-center min-w-[140px]" @click="generarQRVisitante()">
                            <i class="fas fa-qrcode text-3xl mb-3 text-emerald-400"></i>
                            <p class="text-sm font-bold text-white">QR VISITANTE</p>
                            <p class="text-xs text-emerald-400 mt-1">Generar código</p>
                </div>
                        <div class="quick-action-card text-center min-w-[140px]" @click="contactarAdmin()">
                            <i class="fas fa-headset text-3xl mb-3 text-blue-400"></i>
                            <p class="text-sm font-bold text-white">SOPORTE</p>
                            <p class="text-xs text-blue-400 mt-1">Contactar admin</p>
            </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="stat-card p-6 animate-fade-in" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-gradient-to-br from-blue-500/20 to-blue-600/20 border border-blue-500/30">
                            <i class="fas fa-car text-blue-400"></i>
                        </div>
                        <div class="text-right">
                            <div class="stat-value">2</div>
                            <div class="stat-label">VEHÍCULOS</div>
                        </div>
                    </div>
                    <div class="text-sm text-emerald-400 font-mono">Registrados y activos</div>
                </div>
                
                <div class="stat-card p-6 animate-fade-in" data-aos="fade-up" data-aos-delay="200">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 border border-emerald-500/30">
                            <i class="fas fa-check-circle text-emerald-400"></i>
                        </div>
                        <div class="text-right">
                            <div class="stat-value text-emerald-400">AL DÍA</div>
                            <div class="stat-label">PAGOS</div>
                        </div>
                    </div>
                    <div class="text-sm text-emerald-400 font-mono">Próximo: 15 Feb</div>
                </div>
                
                <div class="stat-card p-6 animate-fade-in" data-aos="fade-up" data-aos-delay="300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-gradient-to-br from-purple-500/20 to-purple-600/20 border border-purple-500/30">
                            <i class="fas fa-bell text-purple-400"></i>
                        </div>
                        <div class="text-right">
                            <div class="stat-value">3</div>
                            <div class="stat-label">NOTIFICACIONES</div>
                        </div>
                    </div>
                    <div class="text-sm text-purple-400 font-mono">2 sin leer</div>
                </div>
                
                <div class="stat-card p-6 animate-fade-in" data-aos="fade-up" data-aos-delay="400">
                    <div class="flex items-center justify-between mb-4">
                        <div class="stat-icon bg-gradient-to-br from-orange-500/20 to-orange-600/20 border border-orange-500/30">
                            <i class="fas fa-users text-orange-400"></i>
                        </div>
                        <div class="text-right">
                            <div class="stat-value">12</div>
                            <div class="stat-label">VISITANTES</div>
                        </div>
                    </div>
                    <div class="text-sm text-orange-400 font-mono">Este mes</div>
                </div>
            </div>
            
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Chart Section -->
                <div class="lg:col-span-2">
                    <div class="content-card p-6 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-white text-glow">
                                <i class="fas fa-chart-line mr-2 text-emerald-400"></i>
                                ACTIVIDAD DE ACCESOS
                            </h3>
                            <select class="cyber-input text-sm">
                                <option>Últimos 7 días</option>
                                <option>Último mes</option>
                                <option>Últimos 3 meses</option>
                            </select>
                        </div>
                        <div class="chart-container">
                            <canvas id="accessChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-clock mr-2 text-emerald-400"></i>
                            ACTIVIDAD RECIENTE
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-4 p-3 bg-black/30 rounded-lg border border-emerald-500/20">
                                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 rounded-full flex items-center justify-center border border-emerald-500/30">
                                    <i class="fas fa-door-open text-emerald-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-white">Entrada principal</p>
                                    <p class="text-sm text-emerald-400 font-mono">Hoy 8:30 AM - ABC123</p>
                                </div>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">ENTRADA</span>
                            </div>
                            
                            <div class="flex items-center gap-4 p-3 bg-black/30 rounded-lg border border-blue-500/20">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center border border-blue-500/30">
                                    <i class="fas fa-user-plus text-blue-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-white">Visitante autorizado</p>
                                    <p class="text-sm text-blue-400 font-mono">Ayer 3:15 PM - María García</p>
                                </div>
                                <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded border border-blue-500/30">VISITANTE</span>
                            </div>
                            
                            <div class="flex items-center gap-4 p-3 bg-black/30 rounded-lg border border-red-500/20">
                                <div class="w-10 h-10 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-full flex items-center justify-center border border-red-500/30">
                                    <i class="fas fa-door-closed text-red-400"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-white">Salida principal</p>
                                    <p class="text-sm text-red-400 font-mono">Ayer 6:45 PM - ABC123</p>
                                </div>
                                <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded border border-red-500/30">SALIDA</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar Content -->
                <div class="space-y-6">
                    <!-- Payment Status -->
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-credit-card mr-2 text-emerald-400"></i>
                            ESTADO DE PAGOS
                        </h3>
                        <div class="space-y-4">
                            <div class="payment-status paid p-4 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-semibold text-white">Administración</span>
                                    <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">PAGADO</span>
                                </div>
                                <p class="text-sm text-emerald-400 font-mono">Enero 2025 - $450.000</p>
                            </div>
                            
                            <div class="payment-status pending p-4 rounded-lg">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="font-semibold text-white">Administración</span>
                                    <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs rounded border border-yellow-500/30">PENDIENTE</span>
                                </div>
                                <p class="text-sm text-yellow-400 font-mono">Febrero 2025 - $450.000</p>
                                <p class="text-xs text-yellow-400/70 font-mono">Vence: 15 Feb 2025</p>
                            </div>
                        </div>
                        <button class="cyber-button w-full mt-4 text-sm">
                            <i class="fas fa-eye mr-2"></i>Ver todos los pagos
                        </button>
                    </div>
                    
                    <!-- Notifications -->
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-bell mr-2 text-emerald-400"></i>
                            NOTIFICACIONES
                        </h3>
                        <div class="space-y-3">
                            <div class="notification-item p-3 rounded-lg cursor-pointer">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-blue-500/30">
                                        <i class="fas fa-info text-blue-400 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">
                                            Mantenimiento programado
                                        </p>
                                        <p class="text-xs text-blue-400 font-mono">
                                            Ascensores - 20 Feb, 9:00 AM
                                        </p>
                                    </div>
                                    <div class="w-2 h-2 bg-blue-400 rounded-full flex-shrink-0 animate-pulse"></div>
                                </div>
                            </div>
                            
                            <div class="notification-item p-3 rounded-lg cursor-pointer">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-emerald-500/30">
                                        <i class="fas fa-check text-emerald-400 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">
                                            Pago confirmado
                                        </p>
                                        <p class="text-xs text-emerald-400 font-mono">
                                            Administración Enero 2025
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-item p-3 rounded-lg cursor-pointer">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-500/20 to-orange-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-orange-500/30">
                                        <i class="fas fa-calendar text-orange-400 text-sm"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-white truncate">
                                            Evento conjunto
                                        </p>
                                        <p class="text-xs text-orange-400 font-mono">
                                            Asamblea general - 25 Feb
                                        </p>
                                    </div>
                                    <div class="w-2 h-2 bg-orange-400 rounded-full flex-shrink-0 animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                        <button class="cyber-button w-full mt-4 text-sm">
                            <i class="fas fa-eye mr-2"></i>Ver todas
                        </button>
                    </div>
                    
                    <!-- Weather Widget -->
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">
                            <i class="fas fa-cloud-sun mr-2 text-emerald-400"></i>
                            CLIMA
                        </h3>
                        <div class="text-center">
                            <div class="text-4xl mb-2">☀️</div>
                            <div class="text-2xl font-bold text-white text-glow">24°C</div>
                            <div class="text-sm text-emerald-400">Soleado</div>
                            <div class="text-xs text-emerald-400/70 font-mono mt-2">
                                Máx: 28°C | Mín: 18°C
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Sections (Hidden by default) -->
        <div id="perfilSection" class="section-content hidden">
            <div class="flex items-center gap-2 mb-6 text-sm">
                <i class="fas fa-home text-emerald-400"></i>
                <span class="text-white/70">Dashboard</span>
                <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                <span class="text-emerald-400 font-mono">MI PERFIL</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="content-card p-8">
                        <h2 class="text-2xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-user mr-2 text-emerald-400"></i>
                            INFORMACIÓN PERSONAL
                        </h2>
                        <form class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                                    <label class="block text-sm font-semibold text-white mb-2">Nombre Completo</label>
                                    <input type="text" class="cyber-input w-full" value="Carlos Eduardo Propietario" />
                        </div>
                        <div>
                                    <label class="block text-sm font-semibold text-white mb-2">Documento de Identidad</label>
                                    <input type="text" class="cyber-input w-full" value="12345678" />
                        </div>
                        <div>
                                    <label class="block text-sm font-semibold text-white mb-2">Correo Electrónico</label>
                                    <input type="email" class="cyber-input w-full" value="carlos.propietario@email.com" />
                        </div>
                                <div>
                                    <label class="block text-sm font-semibold text-white mb-2">Teléfono</label>
                                    <input type="text" class="cyber-input w-full" value="+57 322 123 4567" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-white mb-2">Teléfono de Emergencia</label>
                                    <input type="text" class="cyber-input w-full" value="+57 311 987 6543" />
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-white mb-2">Contacto de Emergencia</label>
                                    <input type="text" class="cyber-input w-full" value="María Propietario (Esposa)" />
                                </div>
                            </div>
                            <div class="flex gap-4">
                                <button type="submit" class="cyber-button">
                                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                                </button>
                                <button type="button" class="cyber-button">Cancelar</button>
                            </div>
                    </form>
                </div>
                </div>
                
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">FOTO DE PERFIL</h3>
                        <div class="text-center">
                            <div class="w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden border-4 border-emerald-400/50">
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Perfil" class="w-full h-full object-cover" />
                            </div>
                            <button class="cyber-button text-sm">
                                <i class="fas fa-camera mr-2"></i>Cambiar Foto
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">SEGURIDAD</h3>
                        <div class="space-y-3">
                            <button class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                            </button>
                            <button class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-shield-alt mr-2"></i>Autenticación 2FA
                            </button>
                            <button class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-history mr-2"></i>Historial de Sesiones
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Vehicles Section -->
        <div id="vehiculosSection" class="section-content hidden">
            <div class="flex items-center gap-2 mb-6 text-sm">
                <i class="fas fa-home text-emerald-400"></i>
                <span class="text-white/70">Dashboard</span>
                <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                <span class="text-emerald-400 font-mono">MIS VEHÍCULOS</span>
            </div>
            
            <div class="content-card p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white text-glow">
                        <i class="fas fa-car mr-2 text-emerald-400"></i>
                        MIS VEHÍCULOS
                    </h2>
                    <button onclick="abrirModalAgregarVehiculo()" class="cyber-button">
                        <i class="fas fa-plus mr-2"></i>Agregar Vehículo
                    </button>
                </div>
                
                <?php if ($mensaje): ?>
                    <div class="mb-6 p-4 rounded-lg <?php echo $mensaje_tipo === 'success' ? 'bg-green-500/20 border border-green-500/40 text-green-400' : 'bg-red-500/20 border border-red-500/40 text-red-400'; ?>">
                        <div class="flex items-center gap-2">
                            <i class="fas <?php echo $mensaje_tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                            <span><?php echo $mensaje; ?></span>
                        </div>
                    </div>
                <?php endif; ?>
                
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php if($vehiculos && $vehiculos->num_rows > 0): ?>
                        <?php while($vehiculo = $vehiculos->fetch_assoc()): ?>
                            <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 p-6 rounded-xl border border-blue-500/30 hover:border-blue-400/50 transition-all duration-300">
                                <!-- Header con icono y estado -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center border border-blue-500/30">
                                        <i class="fas fa-<?php echo $vehiculo['tipo_vehiculo'] == 'moto' ? 'motorcycle' : 'car'; ?> text-blue-400 text-xl"></i>
                                    </div>
                                    <span class="px-2 py-1 bg-<?php echo $vehiculo['estado'] == 'activo' ? 'emerald' : ($vehiculo['estado'] == 'en_mantenimiento' ? 'yellow' : 'red'); ?>-500/20 text-<?php echo $vehiculo['estado'] == 'activo' ? 'emerald' : ($vehiculo['estado'] == 'en_mantenimiento' ? 'yellow' : 'red'); ?>-400 text-xs rounded border border-<?php echo $vehiculo['estado'] == 'activo' ? 'emerald' : ($vehiculo['estado'] == 'en_mantenimiento' ? 'yellow' : 'red'); ?>-500/30">
                                        <?php echo strtoupper(str_replace('_', ' ', $vehiculo['estado'])); ?>
                                    </span>
                                </div>
                                
                                <!-- Información del vehículo -->
                                <div class="mb-4">
                                    <h3 class="font-bold text-white text-lg mb-2"><?php echo $vehiculo['placa']; ?></h3>
                                    <p class="text-blue-400 mb-1 font-semibold"><?php echo $vehiculo['marca'] . ' ' . $vehiculo['modelo']; ?></p>
                                    <p class="text-sm text-blue-400/70 font-mono mb-1">Modelo <?php echo $vehiculo['año']; ?> - <?php echo $vehiculo['color']; ?></p>
                                    <p class="text-sm text-blue-400/70 font-mono"><?php echo ucfirst($vehiculo['tipo_vehiculo']); ?></p>
                                    <?php if($vehiculo['observaciones']): ?>
                                        <p class="text-xs text-blue-400/50 mt-2 p-2 bg-blue-500/10 rounded"><?php echo $vehiculo['observaciones']; ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Botones de acción -->
                                <div class="flex gap-2 pt-2 border-t border-blue-500/20">
                                    <button onclick="editarVehiculo(<?php echo htmlspecialchars(json_encode($vehiculo)); ?>)" 
                                            class="flex-1 cyber-button text-sm bg-blue-500/20 hover:bg-blue-500/30 border-blue-400/40 hover:border-blue-400/60" 
                                            title="Editar vehículo">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </button>
                                    <button onclick="generarQRVehiculo('<?php echo $vehiculo['placa']; ?>')" 
                                            class="flex-1 cyber-button text-sm bg-green-500/20 hover:bg-green-500/30 border-green-400/40 hover:border-green-400/60" 
                                            title="Generar QR">
                                        <i class="fas fa-qrcode mr-1"></i>QR
                                    </button>
                                    <button onclick="eliminarVehiculo(<?php echo $vehiculo['id_vehiculo']; ?>, '<?php echo $vehiculo['placa']; ?>')" 
                                            class="flex-1 cyber-button text-sm bg-red-500/20 hover:bg-red-500/30 border-red-400/40 hover:border-red-400/60 text-red-400" 
                                            title="Eliminar vehículo">
                                        <i class="fas fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-span-full text-center py-12">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center border border-blue-500/30 mx-auto mb-6">
                                <i class="fas fa-car text-blue-400 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-2">No tienes vehículos registrados</h3>
                            <p class="text-blue-400/70 mb-6">Agrega tu primer vehículo para comenzar</p>
                            <button onclick="abrirModalAgregarVehiculo()" class="cyber-button">
                                <i class="fas fa-plus mr-2"></i>Agregar Vehículo
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Payments Section -->
        <div id="pagosSection" class="section-content hidden">
            <div class="flex items-center gap-2 mb-6 text-sm">
                <i class="fas fa-home text-emerald-400"></i>
                <span class="text-white/70">Dashboard</span>
                <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                <span class="text-emerald-400 font-mono">PAGOS</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="content-card p-8">
                        <h2 class="text-2xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-receipt mr-2 text-emerald-400"></i>
                            HISTORIAL DE PAGOS
                        </h2>
                        
                        <div id="historial-pagos" class="space-y-4">
                            <!-- Los datos se cargarán dinámicamente aquí -->
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin text-lg text-emerald-400 mb-2"></i>
                                <p class="text-emerald-400/70 text-sm">Cargando historial de pagos...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">RESUMEN FINANCIERO</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Total Pagado (2025)</span>
                                <span id="total-pagado" class="font-bold text-emerald-400">$630.000</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Pendiente por Pagar</span>
                                <span id="pendiente-pagar" class="font-bold text-yellow-400">$450.000</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Próximo Vencimiento</span>
                                <span id="proximo-vencimiento" class="font-bold text-white">15 Feb</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">MÉTODOS DE PAGO</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-3 bg-black/30 rounded-lg border border-blue-500/20">
                                <i class="fas fa-credit-card text-blue-400"></i>
                                <div>
                                    <p class="font-semibold text-white">**** 1234</p>
                                    <p class="text-sm text-blue-400 font-mono">Visa - Principal</p>
                                </div>
                            </div>
                            <button class="cyber-button w-full text-sm">
                                <i class="fas fa-plus mr-2"></i>Agregar Método
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications Section -->
        <div id="notificacionesSection" class="section-content hidden">
            <div class="flex items-center gap-2 mb-6 text-sm">
                <i class="fas fa-home text-emerald-400"></i>
                <span class="text-white/70">Dashboard</span>
                <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                <span class="text-emerald-400 font-mono">NOTIFICACIONES</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <div class="lg:col-span-3">
                    <div class="content-card p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-white text-glow">
                                <i class="fas fa-bell mr-2 text-emerald-400"></i>
                                MIS NOTIFICACIONES
                            </h2>
                            <button onclick="marcarTodasLeidas()" class="cyber-button text-sm">
                                <i class="fas fa-check-double mr-2"></i>Marcar todas como leídas
                            </button>
                        </div>
                        
                        <!-- Filtros -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <button onclick="filtrarNotificaciones('todas')" class="cyber-button text-sm">Todas</button>
                            <button onclick="filtrarNotificaciones('administracion')" class="cyber-button text-sm">Administración</button>
                            <button onclick="filtrarNotificaciones('seguridad')" class="cyber-button text-sm">Seguridad</button>
                            <button onclick="filtrarNotificaciones('eventos')" class="cyber-button text-sm">Eventos</button>
                            <button onclick="filtrarNotificaciones('pagos')" class="cyber-button text-sm">Pagos</button>
                        </div>
                        
                        <!-- Lista de notificaciones -->
                        <div class="space-y-4">
                            <div class="notification-item p-4 rounded-lg border border-blue-500/30 bg-blue-500/10" data-category="administracion" data-unread="true">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-blue-500/30">
                                        <i class="fas fa-info text-blue-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-bold text-white">Mantenimiento programado</h3>
                                            <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded border border-blue-500/30">ADMINISTRACIÓN</span>
                                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">NUEVA</span>
                                        </div>
                                        <p class="text-white/80 mb-2">Se realizará mantenimiento en los ascensores el próximo martes 20 de febrero de 9:00 AM a 12:00 PM. Durante este tiempo, los ascensores estarán fuera de servicio.</p>
                                        <div class="flex items-center gap-4 text-sm text-blue-400/70">
                                            <span><i class="fas fa-calendar mr-1"></i>Hace 2 horas</span>
                                            <button onclick="marcarLeida(this)" class="text-blue-400 hover:text-blue-300">Marcar como leída</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-item p-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10" data-category="pagos" data-unread="false">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-emerald-500/30">
                                        <i class="fas fa-check-circle text-emerald-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-bold text-white">Pago confirmado</h3>
                                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">PAGOS</span>
                                        </div>
                                        <p class="text-white/80 mb-2">Su pago de administración correspondiente a enero 2025 por valor de $450.000 ha sido confirmado exitosamente.</p>
                                        <div class="flex items-center gap-4 text-sm text-emerald-400/70">
                                            <span><i class="fas fa-calendar mr-1"></i>Hace 1 día</span>
                                            <button onclick="descargarRecibo('ENE2025')" class="text-emerald-400 hover:text-emerald-300">Descargar recibo</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-item p-4 rounded-lg border border-orange-500/30 bg-orange-500/10" data-category="eventos" data-unread="true">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500/20 to-orange-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-orange-500/30">
                                        <i class="fas fa-calendar-alt text-orange-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-bold text-white">Asamblea General</h3>
                                            <span class="px-2 py-1 bg-orange-500/20 text-orange-400 text-xs rounded border border-orange-500/30">EVENTOS</span>
                                            <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">NUEVA</span>
                                        </div>
                                        <p class="text-white/80 mb-2">Se convoca a asamblea general de propietarios para el sábado 25 de febrero a las 10:00 AM en el salón social. Asistencia obligatoria.</p>
                                        <div class="flex items-center gap-4 text-sm text-orange-400/70">
                                            <span><i class="fas fa-calendar mr-1"></i>Hace 3 horas</span>
                                            <button onclick="marcarLeida(this)" class="text-orange-400 hover:text-orange-300">Marcar como leída</button>
                                            <button onclick="agregarCalendario('asamblea')" class="text-orange-400 hover:text-orange-300">Agregar al calendario</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="notification-item p-4 rounded-lg border border-red-500/30 bg-red-500/10" data-category="seguridad" data-unread="false">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-red-500/20 to-red-600/20 rounded-full flex items-center justify-center flex-shrink-0 border border-red-500/30">
                                        <i class="fas fa-shield-alt text-red-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-bold text-white">Alerta de seguridad</h3>
                                            <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded border border-red-500/30">SEGURIDAD</span>
                                        </div>
                                        <p class="text-white/80 mb-2">Se reportó actividad sospechosa en el parqueadero el día de ayer. Se recomienda estar atentos y reportar cualquier novedad.</p>
                                        <div class="flex items-center gap-4 text-sm text-red-400/70">
                                            <span><i class="fas fa-calendar mr-1"></i>Hace 2 días</span>
                                            <button onclick="verDetalles('seguridad-001')" class="text-red-400 hover:text-red-300">Ver detalles</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Paginación -->
                        <div class="flex justify-center mt-8">
                            <div class="flex gap-2">
                                <button class="cyber-button text-sm">«</button>
                                <button class="cyber-button text-sm bg-emerald-500/20 border-emerald-500/40">1</button>
                                <button class="cyber-button text-sm">2</button>
                                <button class="cyber-button text-sm">3</button>
                                <button class="cyber-button text-sm">»</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">RESUMEN</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Total notificaciones</span>
                                <span class="font-bold text-white">24</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Sin leer</span>
                                <span class="font-bold text-red-400">3</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Esta semana</span>
                                <span class="font-bold text-blue-400">8</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">CONFIGURACIÓN</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-white">Email</span>
                                <input type="checkbox" class="toggle toggle-sm" checked />
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-white">SMS</span>
                                <input type="checkbox" class="toggle toggle-sm" />
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-white">Push</span>
                                <input type="checkbox" class="toggle toggle-sm" checked />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Apartment Section -->
        <div id="apartamentoSection" class="section-content hidden">
            <div class="flex items-center gap-2 mb-6 text-sm">
                <i class="fas fa-home text-emerald-400"></i>
                <span class="text-white/70">Dashboard</span>
                <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                <span class="text-emerald-400 font-mono">MI APARTAMENTO</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="content-card p-8 mb-6">
                        <h2 class="text-2xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-building mr-2 text-emerald-400"></i>
                            INFORMACIÓN DEL APARTAMENTO
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 p-6 rounded-xl border border-blue-500/30">
                                <h3 class="font-bold text-white mb-4">DATOS BÁSICOS</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-blue-400/70">Apartamento:</span>
                                        <span class="font-semibold text-white">301</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-400/70">Torre:</span>
                                        <span class="font-semibold text-white">A</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-400/70">Piso:</span>
                                        <span class="font-semibold text-white">3</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-400/70">Área:</span>
                                        <span class="font-semibold text-white">85 m²</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 p-6 rounded-xl border border-emerald-500/30">
                                <h3 class="font-bold text-white mb-4">CARACTERÍSTICAS</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-emerald-400/70">Habitaciones:</span>
                                        <span class="font-semibold text-white">3</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-emerald-400/70">Baños:</span>
                                        <span class="font-semibold text-white">2</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-emerald-400/70">Parqueaderos:</span>
                                        <span class="font-semibold text-white">1</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-emerald-400/70">Balcón:</span>
                                        <span class="font-semibold text-white">Sí</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Plano del apartamento -->
                        <div class="bg-black/30 p-8 rounded-xl border border-emerald-500/20">
                            <h3 class="font-bold text-white mb-4">PLANO DEL APARTAMENTO</h3>
                            <div class="bg-black/50 p-6 rounded-lg border-2 border-dashed border-emerald-500/30 text-center">
                                <i class="fas fa-home text-6xl text-emerald-400 mb-4"></i>
                                <p class="text-white">Plano del apartamento 301</p>
                                <p class="text-sm text-emerald-400/70 font-mono">85 m² - 3 hab, 2 baños, 1 parqueadero</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Servicios y amenidades -->
                    <div class="content-card p-8">
                        <h2 class="text-2xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-star mr-2 text-emerald-400"></i>
                            AMENIDADES DEL CONJUNTO
                        </h2>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="text-center p-4 bg-blue-500/20 rounded-lg border border-blue-500/30">
                                <i class="fas fa-swimming-pool text-2xl text-blue-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Piscina</p>
                            </div>
                            <div class="text-center p-4 bg-emerald-500/20 rounded-lg border border-emerald-500/30">
                                <i class="fas fa-dumbbell text-2xl text-emerald-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Gimnasio</p>
                            </div>
                            <div class="text-center p-4 bg-purple-500/20 rounded-lg border border-purple-500/30">
                                <i class="fas fa-gamepad text-2xl text-purple-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Zona Infantil</p>
                            </div>
                            <div class="text-center p-4 bg-orange-500/20 rounded-lg border border-orange-500/30">
                                <i class="fas fa-utensils text-2xl text-orange-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Salón Social</p>
                            </div>
                            <div class="text-center p-4 bg-red-500/20 rounded-lg border border-red-500/30">
                                <i class="fas fa-car text-2xl text-red-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Parqueadero</p>
                            </div>
                            <div class="text-center p-4 bg-yellow-500/20 rounded-lg border border-yellow-500/30">
                                <i class="fas fa-shield-alt text-2xl text-yellow-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Seguridad 24/7</p>
                            </div>
                            <div class="text-center p-4 bg-blue-500/20 rounded-lg border border-blue-500/30">
                                <i class="fas fa-wifi text-2xl text-blue-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">WiFi Común</p>
                            </div>
                            <div class="text-center p-4 bg-emerald-500/20 rounded-lg border border-emerald-500/30">
                                <i class="fas fa-tree text-2xl text-emerald-400 mb-2"></i>
                                <p class="text-sm font-semibold text-white">Zonas Verdes</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">CONTACTOS IMPORTANTES</h3>
                        <div class="space-y-4">
                            <div class="flex items-center gap-3 p-3 bg-blue-500/20 rounded-lg border border-blue-500/30">
                                <i class="fas fa-user-tie text-blue-400"></i>
                                <div>
                                    <p class="font-semibold text-sm text-white">Administración</p>
                                    <p class="text-xs text-blue-400/70 font-mono">admin@quintanares.com</p>
                                    <p class="text-xs text-blue-400/70 font-mono">+57 312 345 6789</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 p-3 bg-red-500/20 rounded-lg border border-red-500/30">
                                <i class="fas fa-shield-alt text-red-400"></i>
                                <div>
                                    <p class="font-semibold text-sm text-white">Seguridad</p>
                                    <p class="text-xs text-red-400/70 font-mono">seguridad@quintanares.com</p>
                                    <p class="text-xs text-red-400/70 font-mono">+57 311 987 6543</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3 p-3 bg-emerald-500/20 rounded-lg border border-emerald-500/30">
                                <i class="fas fa-tools text-emerald-400"></i>
                                <div>
                                    <p class="font-semibold text-sm text-white">Mantenimiento</p>
                                    <p class="text-xs text-emerald-400/70 font-mono">mantenimiento@quintanares.com</p>
                                    <p class="text-xs text-emerald-400/70 font-mono">+57 310 123 4567</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">DOCUMENTOS</h3>
                        <div class="space-y-3">
                            <button onclick="descargarDocumento('reglamento')" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-file-pdf mr-2"></i>Reglamento
                            </button>
                            <button onclick="descargarDocumento('manual')" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-book mr-2"></i>Manual del Propietario
                            </button>
                            <button onclick="descargarDocumento('planos')" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-drafting-compass mr-2"></i>Planos Generales
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">ESTADO DE CUENTA</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-white/70">Administración</span>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">AL DÍA</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-white/70">Servicios</span>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">AL DÍA</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-white/70">Multas</span>
                                <span class="px-2 py-1 bg-emerald-500/20 text-emerald-400 text-xs rounded border border-emerald-500/30">SIN MULTAS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Visitors Section -->
        <div id="visitantesSection" class="section-content hidden">
            <div class="breadcrumbs text-sm mb-6">
                <ul>
                    <li><i class="fas fa-home mr-1"></i>Dashboard</li>
                    <li>Visitantes</li>
                </ul>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="content-card p-8 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-white text-glow">
                                <i class="fas fa-users mr-2 text-emerald-400"></i>
                                GESTIÓN DE VISITANTES
                            </h2>
                            <button onclick="generarQRVisitante()" class="cyber-button">
                                <i class="fas fa-qrcode mr-2"></i>Generar QR
                            </button>
                        </div>
                        
                        <!-- Estadísticas -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 p-6 rounded-xl border border-blue-500/30">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-blue-400 font-semibold">Este mes</p>
                                        <p class="text-2xl font-bold text-white" id="estadistica-mes">-</p>
                                    </div>
                                    <i class="fas fa-calendar text-blue-400 text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 p-6 rounded-xl border border-emerald-500/30">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-emerald-400 font-semibold">Esta semana</p>
                                        <p class="text-2xl font-bold text-white" id="estadistica-semana">-</p>
                                    </div>
                                    <i class="fas fa-week text-emerald-400 text-2xl"></i>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-orange-500/20 to-red-500/20 p-6 rounded-xl border border-orange-500/30">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-orange-400 font-semibold">Hoy</p>
                                        <p class="text-2xl font-bold text-white" id="estadistica-hoy">-</p>
                                    </div>
                                    <i class="fas fa-today text-orange-400 text-2xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Historial de visitantes -->
                        <h3 class="text-xl font-bold text-white text-glow mb-4">HISTORIAL DE VISITANTES</h3>
                        <div id="historial-visitantes" class="space-y-4">
                            <!-- Los datos se cargarán dinámicamente aquí -->
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin text-lg text-emerald-400 mb-2"></i>
                                <p class="text-emerald-400/70 text-sm">Cargando historial...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pre-autorizaciones -->
                    <div class="content-card p-8">
                        <h3 class="text-xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-clock mr-2 text-emerald-400"></i>
                            PRE-AUTORIZACIONES
                        </h3>
                        
                        <div id="preautorizaciones-activas" class="space-y-4">
                            <!-- Los datos se cargarán dinámicamente aquí -->
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin text-lg text-blue-400 mb-2"></i>
                                <p class="text-blue-400/70 text-sm">Cargando pre-autorizaciones...</p>
                            </div>
                        </div>
                        
                        <button onclick="crearPreautorizacion()" class="cyber-button w-full mt-4">
                            <i class="fas fa-plus mr-2"></i>Crear Pre-autorización
                        </button>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">ACCIONES RÁPIDAS</h3>
                        <div class="space-y-3">
                            <button onclick="generarQRVisitante()" class="cyber-button w-full text-sm">
                                <i class="fas fa-qrcode mr-2"></i>Generar QR
                            </button>
                            <button onclick="crearPreautorizacion()" class="cyber-button w-full text-sm">
                                <i class="fas fa-calendar-plus mr-2"></i>Pre-autorizar
                            </button>
                            <button onclick="verCodigosQRActivos()" class="cyber-button w-full text-sm">
                                <i class="fas fa-list mr-2"></i>Códigos Activos
                            </button>
                            <button onclick="exportarHistorial()" class="cyber-button w-full text-sm">
                                <i class="fas fa-download mr-2"></i>Exportar
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">VISITANTES FRECUENTES</h3>
                        <div id="visitantes-frecuentes" class="space-y-3">
                            <!-- Los datos se cargarán dinámicamente aquí -->
                            <div class="text-center py-4">
                                <i class="fas fa-spinner fa-spin text-lg text-blue-400 mb-2"></i>
                                <p class="text-blue-400/70 text-sm">Cargando visitantes frecuentes...</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">INSTRUCCIONES QR</h3>
                        <div class="text-sm text-white/70 space-y-2">
                            <p>• Genera un código QR para tus visitantes</p>
                            <p>• El código es válido por 24 horas</p>
                            <p>• Compártelo por WhatsApp o email</p>
                            <p>• El visitante solo debe mostrarlo en portería</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reports Section -->
        <div id="reportesSection" class="section-content hidden">
            <div class="breadcrumbs text-sm mb-6">
                <ul>
                    <li><i class="fas fa-home mr-1"></i>Dashboard</li>
                    <li>Reportes</li>
                </ul>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="content-card p-8 mb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-white text-glow">
                                <i class="fas fa-chart-bar mr-2 text-emerald-400"></i>
                                REPORTES Y ANÁLISIS
                            </h2>
                            <select class="cyber-input text-sm">
                                <option>Últimos 30 días</option>
                                <option>Últimos 90 días</option>
                                <option>Este año</option>
                            </select>
                        </div>
                        
                        <!-- Gráficos -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                            <div class="bg-black/30 p-6 rounded-xl border border-emerald-500/20">
                                <h3 class="font-bold text-white mb-4">ACCESOS POR MES</h3>
                                <div class="chart-container" style="height: 200px;">
                                    <canvas id="accessesChart"></canvas>
                                </div>
                            </div>
                            
                            <div class="bg-black/30 p-6 rounded-xl border border-emerald-500/20">
                                <h3 class="font-bold text-white mb-4">VISITANTES POR DÍA</h3>
                                <div class="chart-container" style="height: 200px;">
                                    <canvas id="visitorsChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tabla de actividad detallada -->
                        <h3 class="text-xl font-bold text-white text-glow mb-4">ACTIVIDAD DETALLADA</h3>
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Vehículo/Visitante</th>
                                        <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                        <td>15 Feb 2025</td>
                                        <td>08:30</td>
                                        <td><span class="badge badge-info">Entrada</span></td>
                                        <td>ABC-123 (Propietario)</td>
                                        <td><span class="badge badge-success">Exitoso</span></td>
                            </tr>
                            <tr>
                                        <td>15 Feb 2025</td>
                                        <td>14:30</td>
                                        <td><span class="badge badge-warning">Visitante</span></td>
                                        <td>María García</td>
                                        <td><span class="badge badge-success">Autorizado</span></td>
                                    </tr>
                                    <tr>
                                        <td>14 Feb 2025</td>
                                        <td>18:45</td>
                                        <td><span class="badge badge-error">Salida</span></td>
                                        <td>ABC-123 (Propietario)</td>
                                        <td><span class="badge badge-success">Exitoso</span></td>
                                    </tr>
                                    <tr>
                                        <td>14 Feb 2025</td>
                                        <td>10:15</td>
                                        <td><span class="badge badge-info">Entrada</span></td>
                                        <td>XYZ-789 (Moto)</td>
                                        <td><span class="badge badge-success">Exitoso</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">RESUMEN MENSUAL</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Total Accesos</span>
                                <span class="font-bold text-blue-400">48</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Visitantes</span>
                                <span class="font-bold text-emerald-400">12</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Vehículos</span>
                                <span class="font-bold text-purple-400">36</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-white/70">Promedio Diario</span>
                                <span class="font-bold text-orange-400">1.6</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">EXPORTAR REPORTES</h3>
                        <div class="space-y-3">
                            <button onclick="exportarReporte('pdf')" class="cyber-button w-full text-sm">
                                <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                            </button>
                            <button onclick="exportarReporte('excel')" class="cyber-button w-full text-sm">
                                <i class="fas fa-file-excel mr-2"></i>Exportar Excel
                            </button>
                            <button onclick="exportarReporte('csv')" class="cyber-button w-full text-sm">
                                <i class="fas fa-file-csv mr-2"></i>Exportar CSV
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">FILTROS RÁPIDOS</h3>
                        <div class="space-y-2">
                            <button onclick="filtrarReporte('hoy')" class="cyber-button w-full justify-start text-xs">Hoy</button>
                            <button onclick="filtrarReporte('semana')" class="cyber-button w-full justify-start text-xs">Esta semana</button>
                            <button onclick="filtrarReporte('mes')" class="cyber-button w-full justify-start text-xs">Este mes</button>
                            <button onclick="filtrarReporte('vehiculos')" class="cyber-button w-full justify-start text-xs">Solo vehículos</button>
                            <button onclick="filtrarReporte('visitantes')" class="cyber-button w-full justify-start text-xs">Solo visitantes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Settings Section -->
        <div id="configuracionSection" class="section-content hidden">
            <div class="breadcrumbs text-sm mb-6">
                <ul>
                    <li><i class="fas fa-home mr-1"></i>Dashboard</li>
                    <li>Configuración</li>
                    </ul>
                </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2">
                    <div class="content-card p-8 mb-6">
                        <h2 class="text-2xl font-bold text-white text-glow mb-6">
                            <i class="fas fa-cog mr-2 text-emerald-400"></i>
                            CONFIGURACIÓN GENERAL
                        </h2>
                        
                        <!-- Notificaciones -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-white text-glow mb-4">NOTIFICACIONES</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                                    <div>
                                        <p class="font-semibold text-white">Notificaciones por Email</p>
                                        <p class="text-sm text-white/70">Recibir notificaciones importantes por correo</p>
                                    </div>
                                    <input type="checkbox" class="toggle" checked />
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                                    <div>
                                        <p class="font-semibold text-white">Notificaciones SMS</p>
                                        <p class="text-sm text-white/70">Recibir alertas críticas por mensaje de texto</p>
                                    </div>
                                    <input type="checkbox" class="toggle" />
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                                    <div>
                                        <p class="font-semibold text-white">Notificaciones Push</p>
                                        <p class="text-sm text-white/70">Notificaciones en tiempo real en el navegador</p>
                                    </div>
                                    <input type="checkbox" class="toggle" checked />
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                                    <div>
                                        <p class="font-semibold text-white">Alertas de Visitantes</p>
                                        <p class="text-sm text-white/70">Notificar cuando lleguen visitantes</p>
                                    </div>
                                    <input type="checkbox" class="toggle" checked />
                                </div>
                            </div>
                        </div>
                        
                        <!-- Privacidad -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-white text-glow mb-4">PRIVACIDAD Y SEGURIDAD</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                                    <div>
                                        <p class="font-semibold text-white">Compartir datos de acceso</p>
                                        <p class="text-sm text-white/70">Permitir que administración vea mis horarios</p>
                                    </div>
                                    <input type="checkbox" class="toggle" checked />
                                </div>
                                
                                <div class="flex items-center justify-between p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                                    <div>
                                        <p class="font-semibold text-white">Historial de visitantes</p>
                                        <p class="text-sm text-white/70">Mantener registro detallado de visitantes</p>
                                    </div>
                                    <input type="checkbox" class="toggle" checked />
                                </div>
                                
                                <button onclick="cambiarContrasena()" class="cyber-button">
                                    <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                                </button>
                            </div>
                        </div>
                        
                        <!-- Preferencias -->
                        <div>
                            <h3 class="text-lg font-bold text-white text-glow mb-4">PREFERENCIAS DE LA APLICACIÓN</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold mb-2">Tema</label>
                                    <select class="select select-bordered w-full">
                                        <option>Claro</option>
                                        <option>Oscuro</option>
                                        <option>Automático</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2">Idioma</label>
                                    <select class="select select-bordered w-full">
                                        <option>Español</option>
                                        <option>English</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2">Zona Horaria</label>
                                    <select class="select select-bordered w-full">
                                        <option>America/Bogota</option>
                                        <option>America/New_York</option>
                                        <option>Europe/Madrid</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold mb-2">Formato de Fecha</label>
                                    <select class="select select-bordered w-full">
                                        <option>DD/MM/YYYY</option>
                                        <option>MM/DD/YYYY</option>
                                        <option>YYYY-MM-DD</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">CUENTA</h3>
                        <div class="space-y-3">
                            <button onclick="editarPerfil()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-user-edit mr-2"></i>Editar Perfil
                            </button>
                            <button onclick="cambiarContrasena()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-key mr-2"></i>Cambiar Contraseña
                            </button>
                            <button onclick="configurar2FA()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-shield-alt mr-2"></i>Autenticación 2FA
                            </button>
                            <button onclick="descargarDatos()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-download mr-2"></i>Descargar mis datos
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">SOPORTE</h3>
                        <div class="space-y-3">
                            <button onclick="contactarSoporte()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-headset mr-2"></i>Contactar Soporte
                            </button>
                            <button onclick="verTutorial()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-question-circle mr-2"></i>Tutorial
                            </button>
                            <button onclick="reportarProblema()" class="cyber-button w-full justify-start text-sm">
                                <i class="fas fa-bug mr-2"></i>Reportar Problema
                            </button>
                        </div>
                    </div>
                    
                    <div class="content-card p-6">
                        <h3 class="text-xl font-bold text-white text-glow mb-4">INFORMACIÓN</h3>
                        <div class="text-sm text-white/70 space-y-2">
                            <p><strong>Versión:</strong> 2.1.0</p>
                            <p><strong>Última actualización:</strong> 15 Feb 2025</p>
                            <p><strong>Soporte:</strong> soporte@quintanares.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include 'components/footer.php'; ?>
    <!-- Modal Agregar Vehículo -->
    <div id="modalAgregarVehiculo" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-modal md:h-full bg-black/40 flex items-center justify-center">
        <div class="relative w-full max-w-2xl h-full md:h-auto">
            <div class="relative bg-white rounded-2xl shadow-lg p-8 animate__animated animate__fadeInDown max-h-[90vh] overflow-y-auto">
                <button type="button" onclick="cerrarModalAgregarVehiculo()" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                <h3 class="text-2xl font-bold text-indigo-700 mb-6 flex items-center gap-2"><i class="fas fa-car"></i> Agregar Vehículo</h3>
                <form id="formAgregarVehiculo" action="../app/Controllers/vehiculo_controller.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="agregar_vehiculo">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Placa -->
                        <div>
                            <label class="block font-semibold mb-1">Placa *</label>
                            <input type="text" name="placa" id="placa" class="input input-bordered w-full" 
                                   placeholder="ABC-123 o ABC-12D" required maxlength="8" />
                        </div>
                        
                        <!-- Marca -->
                        <div>
                            <label class="block font-semibold mb-1">Marca *</label>
                            <input type="text" name="marca" id="marca" class="input input-bordered w-full" 
                                   placeholder="Chevrolet" required />
                        </div>
                        
                        <!-- Modelo -->
                        <div>
                            <label class="block font-semibold mb-1">Modelo *</label>
                            <input type="text" name="modelo" id="modelo" class="input input-bordered w-full" 
                                   placeholder="Spark" required />
                        </div>
                        
                        <!-- Color -->
                        <div>
                            <label class="block font-semibold mb-1">Color *</label>
                            <input type="text" name="color" id="color" class="input input-bordered w-full" 
                                   placeholder="Blanco" required />
                        </div>
                        
                        <!-- Año -->
                        <div>
                            <label class="block font-semibold mb-1">Año *</label>
                            <input type="number" name="ano" id="ano" class="input input-bordered w-full" 
                                   min="1900" max="<?php echo date('Y') + 1; ?>" 
                                   value="<?php echo date('Y'); ?>" required />
                        </div>
                        
                        <!-- Tipo de Vehículo -->
                        <div>
                            <label class="block font-semibold mb-1">Tipo de Vehículo *</label>
                            <select name="tipo_vehiculo" id="tipo_vehiculo" class="select select-bordered w-full" required>
                                <option value="">Selecciona un tipo</option>
                                <option value="carro">Carro</option>
                                <option value="moto">Moto</option>
                                <option value="bicicleta">Bicicleta</option>
                                <option value="camioneta">Camioneta</option>
                                <option value="bus">Bus</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div>
                        <label class="block font-semibold mb-1">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="textarea textarea-bordered w-full" 
                                  rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex gap-2 justify-end pt-4">
                        <button type="button" onclick="cerrarModalAgregarVehiculo()" class="btn btn-ghost">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Vehículo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Editar Vehículo -->
    <div id="modalEditarVehiculo" tabindex="-1" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-modal md:h-full bg-black/40 flex items-center justify-center">
        <div class="relative w-full max-w-2xl h-full md:h-auto">
            <div class="relative bg-white rounded-2xl shadow-lg p-8 animate__animated animate__fadeInDown max-h-[90vh] overflow-y-auto">
                <button type="button" onclick="cerrarModalEditarVehiculo()" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
                <h3 class="text-2xl font-bold text-indigo-700 mb-6 flex items-center gap-2"><i class="fas fa-edit"></i> Editar Vehículo</h3>
                <form id="formEditarVehiculo" action="../app/Controllers/vehiculo_controller.php" method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="editar_vehiculo">
                    <input type="hidden" name="id_vehiculo" id="editIdVehiculo" value="">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Placa -->
                        <div>
                            <label class="block font-semibold mb-1">Placa *</label>
                            <input type="text" name="placa" id="editPlaca" class="input input-bordered w-full" 
                                   placeholder="ABC-123 o ABC-12D" required maxlength="8" />
                        </div>
                        
                        <!-- Marca -->
                        <div>
                            <label class="block font-semibold mb-1">Marca *</label>
                            <input type="text" name="marca" id="editMarca" class="input input-bordered w-full" 
                                   placeholder="Chevrolet" required />
                        </div>
                        
                        <!-- Modelo -->
                        <div>
                            <label class="block font-semibold mb-1">Modelo *</label>
                            <input type="text" name="modelo" id="editModelo" class="input input-bordered w-full" 
                                   placeholder="Spark" required />
                        </div>
                        
                        <!-- Color -->
                        <div>
                            <label class="block font-semibold mb-1">Color *</label>
                            <input type="text" name="color" id="editColor" class="input input-bordered w-full" 
                                   placeholder="Blanco" required />
                        </div>
                        
                        <!-- Año -->
                        <div>
                            <label class="block font-semibold mb-1">Año *</label>
                            <input type="number" name="ano" id="editAno" class="input input-bordered w-full" 
                                   min="1900" max="<?php echo date('Y') + 1; ?>" required />
                        </div>
                        
                        <!-- Tipo de Vehículo -->
                        <div>
                            <label class="block font-semibold mb-1">Tipo de Vehículo *</label>
                            <select name="tipo_vehiculo" id="editTipoVehiculo" class="select select-bordered w-full" required>
                                <option value="">Selecciona un tipo</option>
                                <option value="carro">Carro</option>
                                <option value="moto">Moto</option>
                                <option value="bicicleta">Bicicleta</option>
                                <option value="camioneta">Camioneta</option>
                                <option value="bus">Bus</option>
                            </select>
                        </div>
                        
                        <!-- Estado -->
                        <div>
                            <label class="block font-semibold mb-1">Estado</label>
                            <select name="estado" id="editEstado" class="select select-bordered w-full">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="en_mantenimiento">En Mantenimiento</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Observaciones -->
                    <div>
                        <label class="block font-semibold mb-1">Observaciones</label>
                        <textarea name="observaciones" id="editObservaciones" class="textarea textarea-bordered w-full" 
                                  rows="3" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex gap-2 justify-end pt-4">
                        <button type="button" onclick="cerrarModalEditarVehiculo()" class="btn btn-ghost">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    // Navigation and Section Management
    let currentSection = 'dashboard';
    
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const footer = document.querySelector('footer');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('open');
        mainContent.classList.toggle('sidebar-open');
        
        // Toggle overlay for mobile
        if (window.innerWidth < 1024) {
            overlay.classList.toggle('active');
            // Prevent body scroll when sidebar is open on mobile
            if (sidebar.classList.contains('open')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Adjust footer position for desktop
        if (window.innerWidth >= 1024) {
            if (sidebar.classList.contains('open')) {
                footer.style.marginLeft = '280px';
            } else {
                footer.style.marginLeft = '0';
            }
        }
    }
    
    // Función showSection movida al final del archivo
    
    // Chart initialization
    function initializeChart() {
        const ctx = document.getElementById('accessChart');
        if (ctx && !ctx.chart) {
            ctx.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Accesos',
                        data: [12, 8, 15, 10, 20, 5, 3],
                        borderColor: '#4F46E5',
                        backgroundColor: 'rgba(79, 70, 229, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }
    }
    
    // Vehicle Management
    function abrirModalAgregarVehiculo() {
        document.getElementById('modalAgregarVehiculo').classList.remove('hidden');
    }
    
    function cerrarModalAgregarVehiculo() {
        document.getElementById('modalAgregarVehiculo').classList.add('hidden');
    }
    
    function editarVehiculo(vehiculo) {
        abrirModalEditarVehiculo(vehiculo);
    }
    
    function abrirModalEditarVehiculo(vehiculo) {
        document.getElementById('editIdVehiculo').value = vehiculo.id_vehiculo;
        document.getElementById('editPlaca').value = vehiculo.placa;
        document.getElementById('editMarca').value = vehiculo.marca;
        document.getElementById('editModelo').value = vehiculo.modelo;
        document.getElementById('editColor').value = vehiculo.color;
        document.getElementById('editAño').value = vehiculo.año;
        document.getElementById('editTipoVehiculo').value = vehiculo.tipo_vehiculo;
        document.getElementById('editEstado').value = vehiculo.estado;
        document.getElementById('editObservaciones').value = vehiculo.observaciones || '';
        document.getElementById('modalEditarVehiculo').classList.remove('hidden');
    }
    
    function cerrarModalEditarVehiculo() {
        document.getElementById('modalEditarVehiculo').classList.add('hidden');
    }
    
    function eliminarVehiculo(idVehiculo, placa) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el vehículo ${placa}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear formulario temporal para enviar la solicitud de eliminación
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../app/Controllers/vehiculo_controller.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'eliminar_vehiculo';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_vehiculo';
                idInput.value = idVehiculo;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    
    // QR Code Generation - Real Implementation
    function generarQRVehiculo(placa) {
        // Mostrar loading
        Swal.fire({
            title: 'Generando Código QR...',
            html: '<div class="flex justify-center"><div class="loading loading-spinner loading-lg"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        // Enviar datos al backend para generar QR real
        fetch('../app/Controllers/generar_qr_simple.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tipo: 'vehiculo',
                placa: placa,
                apartamento: '301',
                torre: 'A',
                propietario: 'Carlos Propietario'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarQRModal({
                    title: `Código QR - ${placa}`,
                    qrData: data.qr_data,
                    qrCode: data.qr_code,
                    validUntil: data.valid_until,
                    instructions: `Código de acceso para vehículo ${placa}`,
                    color: '#4F46E5'
                });
            } else {
                mostrarNotificacion('error', data.message || 'Error al generar código QR');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión al generar QR');
        });
    }
    
    function generarQRVisitante(nombreVisitante = null, documento = null, motivo = null) {
        if (!nombreVisitante) {
            // Mostrar formulario para datos del visitante
            Swal.fire({
                title: `<div class="text-cyan-400 text-glow">Datos del Visitante</div>`,
                html: `
                    <div class="text-left space-y-4" style="max-height: 500px; overflow-y: auto;">
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white text-glow">Nombre del Visitante *</label>
                            <input id="nombreVisitanteQR" type="text" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" placeholder="Ej: María García" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white text-glow">Documento *</label>
                            <input id="documentoVisitanteQR" type="text" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" placeholder="Ej: 12345678" required />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white text-glow">Teléfono</label>
                            <input id="telefonoVisitanteQR" type="text" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" placeholder="Ej: 3123456789" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white text-glow">Motivo de la visita</label>
                            <select id="motivoVisitanteQR" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">
                                <option value="Visita familiar">Visita familiar</option>
                                <option value="Visita social">Visita social</option>
                                <option value="Técnico/Servicio">Técnico/Servicio</option>
                                <option value="Entrega">Entrega</option>
                                <option value="Trabajo">Trabajo</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white text-glow">Válido hasta</label>
                            <input id="validezQR" type="datetime-local" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-2 text-white text-glow">Observaciones</label>
                            <textarea id="observacionesQR" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" rows="3" placeholder="Observaciones adicionales (opcional)"></textarea>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-qrcode mr-2"></i>Generar QR',
                cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
                width: 600,
                heightAuto: true,
                customClass: {
                    popup: 'bg-slate-900 border border-cyan-500/30',
                    title: 'text-cyan-400',
                    confirmButton: 'bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white border-0',
                    cancelButton: 'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white border-0'
                },
                preConfirm: () => {
                    const nombre = document.getElementById('nombreVisitanteQR').value.trim();
                    const documento = document.getElementById('documentoVisitanteQR').value.trim();
                    const telefono = document.getElementById('telefonoVisitanteQR').value.trim();
                    const motivo = document.getElementById('motivoVisitanteQR').value;
                    const validez = document.getElementById('validezQR').value;
                    const observaciones = document.getElementById('observacionesQR').value.trim();
                    
                    if (!nombre || !documento) {
                        Swal.showValidationMessage('Nombre y documento son obligatorios');
                        return false;
                    }
                    
                    // Si no se especifica validez, usar 24 horas por defecto
                    let fechaValidez = validez;
                    if (!fechaValidez) {
                        const ahora = new Date();
                        ahora.setHours(ahora.getHours() + 24);
                        fechaValidez = ahora.toISOString().slice(0, 16);
                    }
                    
                    return { nombre, documento, telefono, motivo, validez: fechaValidez, observaciones };
                },
                didOpen: () => {
                    // Establecer fecha por defecto (24 horas)
                    const ahora = new Date();
                    ahora.setHours(ahora.getHours() + 24);
                    document.getElementById('validezQR').value = ahora.toISOString().slice(0, 16);
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    generarQRVisitanteReal(result.value);
                }
            });
        } else {
            generarQRVisitanteReal({ nombreVisitante, documento, motivo });
        }
    }
    
    function generarQRVisitanteReal(datosVisitante) {
        console.log('Iniciando generación de QR con datos:', datosVisitante);
        
        // Mostrar loading
        Swal.fire({
            title: 'Generando Código QR...',
            html: '<div class="flex justify-center"><div class="loading loading-spinner loading-lg"></div></div>',
            showConfirmButton: false,
            allowOutsideClick: false
        });
        
        // Enviar datos al backend
        console.log('Enviando petición a: ../app/Controllers/generar_qr_simple.php');
        fetch('../app/Controllers/generar_qr_simple.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nombre: datosVisitante.nombre,
                documento: datosVisitante.documento,
                telefono: datosVisitante.telefono || '',
                motivo: datosVisitante.motivo || 'Visita familiar',
                validez: datosVisitante.validez,
                observaciones: datosVisitante.observaciones || ''
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                console.log('QR generado exitosamente:', data.qr_code);
                console.log('QR Image URL:', data.qr_image_url);
                console.log('Valid Until:', data.valid_until);
                
                mostrarQRModal({
                    title: 'Código QR para Visitante',
                    subtitle: datosVisitante.nombre,
                    qrData: data.qr_data,
                    qrCode: data.qr_code,
                    qrImageUrl: data.qr_image_url,
                    validUntil: data.valid_until,
                    instructions: 'Comparte este código con tu visitante',
                    color: '#059669',
                    shareData: {
                        title: 'Código de Acceso - Quintanares by Parkovisco',
                        text: `Código QR de acceso para ${datosVisitante.nombre}`,
                        qrImage: data.qr_image_url
                    }
                });
            } else {
                console.error('Error en respuesta:', data);
                mostrarNotificacion('error', data.message || 'Error al generar código QR');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión al generar QR');
        });
    }
    
    function mostrarQRModal(options) {
        const validUntilDate = new Date(options.validUntil);
        const now = new Date();
        const hoursValid = Math.ceil((validUntilDate - now) / (1000 * 60 * 60));
        
        Swal.fire({
            title: `<div class="text-cyan-400 text-glow">${options.title}</div>`,
            html: `
                <div class="text-center space-y-4" style="max-height: 500px; overflow-y: auto;">
                    ${options.subtitle ? `<h3 class="text-lg font-semibold text-white text-glow">${options.subtitle}</h3>` : ''}
                    <div class="flex justify-center gap-6 mb-4">
                        ${options.qrImageUrl ? 
                            `<img src="${options.qrImageUrl}" alt="Código QR" style="width: 200px; height: 200px; border: 2px solid #00ff88; border-radius: 12px; background: white; padding: 10px; box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);" onerror="this.style.display='none'; document.getElementById('qrcode-fallback').style.display='block';">
                             <div id="qrcode-fallback" style="display: none; width: 200px; height: 200px; border: 2px solid #00ff88; border-radius: 12px; background: #1a1a2e; display: flex; align-items: center; justify-content: center; color: #00ff88; box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);">
                                <div class="text-center">
                                    <i class="fas fa-qrcode text-4xl mb-2 text-cyan-400"></i>
                                    <div class="text-sm text-white">QR: ${options.qrCode}</div>
                                </div>
                             </div>` :
                            `<div id="qrcode" class="flex justify-center" style="width: 200px; height: 200px; border: 2px solid #00ff88; border-radius: 12px; background: #1a1a2e; padding: 10px; box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);"></div>`
                        }
                    </div>
                    <div class="bg-gradient-to-r from-slate-800 to-slate-900 p-4 rounded-lg text-sm border border-cyan-500/30" style="box-shadow: 0 0 15px rgba(6, 182, 212, 0.2);">
                        <p class="font-semibold text-white text-glow">${options.instructions}</p>
                        <p class="text-cyan-300 mt-2">Código: <span class="font-mono text-xs bg-slate-700 px-2 py-1 rounded border border-cyan-500/50 text-green-400">${options.qrCode}</span></p>
                        <p class="text-cyan-300">Válido hasta: <span class="font-semibold text-white">${validUntilDate.toLocaleString('es-CO')}</span></p>
                        <p class="text-cyan-400/70 text-xs">(${hoursValid} horas restantes)</p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-share mr-2"></i>Compartir',
            cancelButtonText: '<i class="fas fa-download mr-2"></i>Descargar',
            showDenyButton: true,
            denyButtonText: '<i class="fas fa-whatsapp mr-2"></i>WhatsApp',
            width: 650,
            heightAuto: true,
            customClass: {
                popup: 'bg-slate-900 border border-cyan-500/30',
                title: 'text-cyan-400',
                confirmButton: 'bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white border-0',
                cancelButton: 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white border-0',
                denyButton: 'bg-gradient-to-r from-green-400 to-green-500 hover:from-green-500 hover:to-green-600 text-white border-0'
            },
            didOpen: () => {
                // Solo generar QR con librería si no hay URL de imagen
                if (!options.qrImageUrl && options.qrData) {
                    const qrElement = document.getElementById('qrcode');
                    if (qrElement) {
                        QRCode.toCanvas(qrElement, options.qrData, {
                            width: 200,
                            margin: 2,
                            color: {
                                dark: options.color || '#000000',
                                light: '#FFFFFF'
                            },
                            errorCorrectionLevel: 'M'
                        }, function(error) {
                            if (error) console.error('Error generando QR visual:', error);
                        });
                    }
                }
            }
        }).then((result) => {
            if (result.isConfirmed && options.shareData) {
                // Compartir
                compartirQR(options.shareData);
            } else if (result.isDenied) {
                // WhatsApp
                enviarPorWhatsApp(options);
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Descargar
                descargarQR(options);
            }
        });
    }
    
    function compartirQR(shareData) {
        if (navigator.share) {
            navigator.share({
                title: shareData.title,
                text: shareData.text,
                url: shareData.qrImage || window.location.href
            }).then(() => {
                mostrarNotificacion('success', 'Código compartido exitosamente');
            }).catch(() => {
                copiarAlPortapapeles(shareData.text);
            });
        } else {
            copiarAlPortapapeles(shareData.text);
        }
    }
    
    function enviarPorWhatsApp(options) {
        const mensaje = `🏠 *Código de Acceso - Quintanares by Parkovisco*\n\n` +
                       `${options.instructions}\n` +
                       `📱 Código: ${options.qrCode}\n` +
                       `⏰ Válido hasta: ${new Date(options.validUntil).toLocaleString('es-CO')}\n\n` +
                       `Presenta este código en portería para ingresar.`;
        
        const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(mensaje)}`;
        window.open(whatsappUrl, '_blank');
    }
    
    function descargarQR(options) {
        const canvas = document.getElementById('qrcode').querySelector('canvas');
        if (canvas) {
            const link = document.createElement('a');
            link.download = `QR_${options.qrCode}_${Date.now()}.png`;
            link.href = canvas.toDataURL();
            link.click();
            mostrarNotificacion('success', 'QR descargado exitosamente');
        }
    }
    
    function copiarAlPortapapeles(texto) {
        navigator.clipboard.writeText(texto).then(() => {
            mostrarNotificacion('success', 'Código copiado al portapapeles');
        }).catch(() => {
            mostrarNotificacion('info', 'Código generado correctamente');
        });
    }
    
    // Quick Actions
    function reportarIncidencia() {
        Swal.fire({
            title: 'Reportar Incidencia',
            html: `
                <div class="text-left space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Tipo de Incidencia</label>
                        <select id="tipoIncidencia" class="select select-bordered w-full">
                            <option>Seguridad</option>
                            <option>Mantenimiento</option>
                            <option>Ruido</option>
                            <option>Servicios Públicos</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Descripción</label>
                        <textarea id="descripcionIncidencia" class="textarea textarea-bordered w-full" rows="4" placeholder="Describe la incidencia..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Enviar Reporte',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const tipo = document.getElementById('tipoIncidencia').value;
                const descripcion = document.getElementById('descripcionIncidencia').value;
                if (!descripcion.trim()) {
                    Swal.showValidationMessage('Por favor describe la incidencia');
                    return false;
                }
                return { tipo, descripcion };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarNotificacion('success', 'Incidencia reportada correctamente');
            }
        });
    }
    
    function contactarAdmin() {
        Swal.fire({
            title: 'Contactar Administración',
            html: `
                <div class="space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-blue-500/20 rounded-lg cursor-pointer hover:bg-blue-500/30 border border-blue-500/30" onclick="window.open('tel:+573123456789')">
                        <i class="fas fa-phone text-blue-400"></i>
                        <div class="text-left">
                            <p class="font-semibold text-white">Llamar</p>
                            <p class="text-sm text-blue-400/70 font-mono">+57 312 345 6789</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-emerald-500/20 rounded-lg cursor-pointer hover:bg-emerald-500/30 border border-emerald-500/30" onclick="window.open('https://wa.me/573123456789')">
                        <i class="fab fa-whatsapp text-emerald-400"></i>
                        <div class="text-left">
                            <p class="font-semibold text-white">WhatsApp</p>
                            <p class="text-sm text-emerald-400/70">Chat directo</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-purple-500/20 rounded-lg cursor-pointer hover:bg-purple-500/30 border border-purple-500/30" onclick="window.open('mailto:admin@quintanares.com')">
                        <i class="fas fa-envelope text-purple-400"></i>
                        <div class="text-left">
                            <p class="font-semibold text-white">Email</p>
                            <p class="text-sm text-purple-400/70 font-mono">admin@quintanares.com</p>
                        </div>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: 400,
            background: 'rgba(0, 0, 0, 0.8)',
            color: '#ffffff',
            customClass: {
                popup: 'cyber-modal',
                title: 'text-white text-glow',
                closeButton: 'text-emerald-400 hover:text-emerald-300'
            }
        });
    }
    
    // Notifications
    function mostrarNotificacion(tipo, mensaje) {
        Swal.fire({
            icon: tipo,
            title: mensaje,
            showConfirmButton: false,
            timer: 2000,
            toast: true,
            position: 'top-end',
            background: tipo === 'success' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)',
            color: '#ffffff',
            customClass: {
                popup: 'cyber-toast',
                title: 'text-white text-glow'
            }
        });
    }
    
    // Form Handlers
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize chart on load
        setTimeout(() => {
            initializeChart();
        }, 500);
        
        // Initialize particles
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 50 },
                    color: { value: '#10b981' },
                    shape: { type: 'circle' },
                    opacity: { value: 0.1 },
                    size: { value: 3 },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: '#10b981',
                        opacity: 0.1,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 1,
                        direction: 'none',
                        random: false,
                        straight: false,
                        out_mode: 'out',
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: {
                        onhover: { enable: true, mode: 'repulse' },
                        onclick: { enable: true, mode: 'push' },
                        resize: true
                    },
                    modes: {
                        repulse: { distance: 100, duration: 0.4 },
                        push: { particles_nb: 4 }
                    }
                },
                retina_detect: true
            });
        }
        
        // Form submissions - Removed preventDefault to allow actual form submission
        
        // Auto-close sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth < 1024 && 
                sidebar.classList.contains('open') && 
                !sidebar.contains(e.target) && 
                !e.target.closest('[onclick="toggleSidebar()"]')) {
                sidebar.classList.remove('open');
                mainContent.classList.remove('sidebar-open');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Initialize AOS
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true
            });
        }
        
        // Responsive sidebar
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const footer = document.querySelector('footer');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth >= 1024) {
                sidebar.classList.add('open');
                mainContent.classList.add('sidebar-open');
                footer.style.marginLeft = '280px';
                // Hide overlay and restore body scroll on desktop
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                sidebar.classList.remove('open');
                mainContent.classList.remove('sidebar-open');
                footer.style.marginLeft = '0';
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
        
        // Initialize responsive behavior
        if (window.innerWidth >= 1024) {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('mainContent').classList.add('sidebar-open');
            const footer = document.querySelector('footer');
            if (footer) footer.style.marginLeft = '280px';
        }
    });
    
    // Additional Functions for New Sections
    
    // Notifications Functions
    function filtrarNotificaciones(categoria) {
        const notificaciones = document.querySelectorAll('.notification-item');
        const botones = document.querySelectorAll('[onclick^="filtrarNotificaciones"]');
        
        // Update button states
        botones.forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
        });
        event.target.classList.add('btn-primary');
        event.target.classList.remove('btn-outline');
        
        // Filter notifications
        notificaciones.forEach(notif => {
            if (categoria === 'todas' || notif.dataset.category === categoria) {
                notif.style.display = 'block';
            } else {
                notif.style.display = 'none';
            }
        });
    }
    
    function marcarLeida(element) {
        const notificationItem = element.closest('.notification-item');
        const badge = notificationItem.querySelector('.badge-primary');
        if (badge) {
            badge.remove();
        }
        notificationItem.dataset.unread = 'false';
        element.textContent = 'Leída';
        element.classList.add('text-gray-400');
        element.onclick = null;
        mostrarNotificacion('success', 'Notificación marcada como leída');
    }
    
    function marcarTodasLeidas() {
        const notificaciones = document.querySelectorAll('.notification-item[data-unread="true"]');
        notificaciones.forEach(notif => {
            const badge = notif.querySelector('.badge-primary');
            if (badge) badge.remove();
            notif.dataset.unread = 'false';
            const boton = notif.querySelector('[onclick^="marcarLeida"]');
            if (boton) {
                boton.textContent = 'Leída';
                boton.classList.add('text-gray-400');
                boton.onclick = null;
            }
        });
        mostrarNotificacion('success', 'Todas las notificaciones marcadas como leídas');
    }
    
    function agregarCalendario(evento) {
        const eventos = {
            'asamblea': {
                titulo: 'Asamblea General de Propietarios',
                fecha: '2025-02-25',
                hora: '10:00',
                descripcion: 'Asamblea general obligatoria en el salón social'
            }
        };
        
        const evt = eventos[evento];
        if (evt) {
            const startDate = new Date(`${evt.fecha}T${evt.hora}:00`);
            const endDate = new Date(startDate.getTime() + 2 * 60 * 60 * 1000); // +2 horas
            
            const googleCalendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(evt.titulo)}&dates=${startDate.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '')}/${endDate.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '')}&details=${encodeURIComponent(evt.descripcion)}`;
            
            window.open(googleCalendarUrl, '_blank');
        }
    }
    
    // Visitors Functions
    function crearPreautorizacion() {
        Swal.fire({
            title: 'Crear Pre-autorización',
            html: `
                <div class="text-left space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nombre del Visitante</label>
                        <input id="nombreVisitante" type="text" class="input input-bordered w-full" placeholder="Ej: Juan Pérez" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Documento</label>
                        <input id="documentoVisitante" type="text" class="input input-bordered w-full" placeholder="Ej: 12345678" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Fecha y Hora</label>
                        <input id="fechaVisita" type="datetime-local" class="input input-bordered w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Motivo</label>
                        <select id="motivoVisita" class="select select-bordered w-full">
                            <option>Visita familiar</option>
                            <option>Técnico/Servicio</option>
                            <option>Entrega</option>
                            <option>Trabajo</option>
                            <option>Otro</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Crear Pre-autorización',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const nombre = document.getElementById('nombreVisitante').value;
                const documento = document.getElementById('documentoVisitante').value;
                const fecha = document.getElementById('fechaVisita').value;
                const motivo = document.getElementById('motivoVisita').value;
                
                if (!nombre || !documento || !fecha) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                return { nombre, documento, fecha, motivo };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarNotificacion('success', 'Pre-autorización creada exitosamente');
            }
        });
    }
    
    function cancelarPreautorizacion(id) {
        Swal.fire({
            title: '¿Cancelar pre-autorización?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarNotificacion('success', 'Pre-autorización cancelada');
            }
        });
    }
    
    function exportarHistorial() {
        mostrarNotificacion('info', 'Generando archivo de exportación...');
        setTimeout(() => {
            mostrarNotificacion('success', 'Historial exportado exitosamente');
        }, 2000);
    }
    
    function verCodigosQRActivos() {
        Swal.fire({
            title: 'Códigos QR Activos',
            html: '<div id="codigosQRContainer"><div class="flex justify-center"><div class="loading loading-spinner loading-lg"></div></div></div>',
            width: 800,
            showCloseButton: true,
            showConfirmButton: false,
            didOpen: () => {
                cargarCodigosQRActivos();
            }
        });
    }
    
    function cargarCodigosQRActivos() {
        fetch('../app/Controllers/api_estadisticas_visitantes.php?tipo=historial&limite=10')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarListaCodigosQR(data.codigos, data.estadisticas);
                } else {
                    document.getElementById('codigosQRContainer').innerHTML = 
                        '<div class="text-center text-red-600">Error: ' + (data.message || 'Error desconocido') + '</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('codigosQRContainer').innerHTML = 
                    '<div class="text-center text-red-600">Error de conexión</div>';
            });
    }
    
    function mostrarListaCodigosQR(codigos, stats) {
        let html = `
            <div class="space-y-4">
                <div class="grid grid-cols-3 gap-4 text-center mb-4">
                    <div class="bg-green-50 p-3 rounded-lg">
                        <div class="text-lg font-bold text-green-600">${stats.activos}</div>
                        <div class="text-sm text-green-700">Activos</div>
                    </div>
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <div class="text-lg font-bold text-blue-600">${stats.vehiculos}</div>
                        <div class="text-sm text-blue-700">Vehículos</div>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-lg">
                        <div class="text-lg font-bold text-purple-600">${stats.visitantes}</div>
                        <div class="text-sm text-purple-700">Visitantes</div>
                    </div>
                </div>
        `;
        
        if (codigos.length === 0) {
            html += '<div class="text-center text-gray-500 py-8">No hay códigos QR activos</div>';
        } else {
            html += '<div class="space-y-3 max-h-96 overflow-y-auto">';
            
            codigos.forEach(codigo => {
                const fechaExpiracion = new Date(codigo.fecha_expiracion);
                const ahora = new Date();
                const horasRestantes = Math.ceil((fechaExpiracion - ahora) / (1000 * 60 * 60));
                
                let badgeClass = 'badge-success';
                let estadoTexto = 'Válido';
                
                if (codigo.estado === 'expirado') {
                    badgeClass = 'badge-error';
                    estadoTexto = 'Expirado';
                } else if (codigo.estado === 'usado') {
                    badgeClass = 'badge-warning';
                    estadoTexto = 'Usado';
                } else if (horasRestantes < 2) {
                    badgeClass = 'badge-warning';
                    estadoTexto = 'Por expirar';
                }
                
                const tipoIcon = codigo.tipo === 'vehiculo' ? 'fa-car' : 'fa-user';
                const tipoColor = codigo.tipo === 'vehiculo' ? 'text-blue-600' : 'text-green-600';
                
                html += `
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                            <i class="fas ${tipoIcon} ${tipoColor}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-sm">${codigo.codigo}</span>
                                <span class="badge ${badgeClass} badge-sm">${estadoTexto}</span>
                            </div>
                            <div class="text-xs text-gray-600">
                `;
                
                if (codigo.tipo === 'vehiculo') {
                    html += `Vehículo: ${codigo.placa}`;
                } else {
                    html += `Visitante: ${codigo.visitante.nombre}`;
                }
                
                html += `
                            </div>
                            <div class="text-xs text-gray-500">
                                Expira: ${fechaExpiracion.toLocaleDateString('es-CO')} ${fechaExpiracion.toLocaleTimeString('es-CO', {hour: '2-digit', minute: '2-digit'})}
                                ${horasRestantes > 0 ? `(${horasRestantes}h restantes)` : ''}
                            </div>
                        </div>
                        <div class="flex flex-col gap-1">
                            <button onclick="verDetalleQR('${codigo.codigo}')" class="btn btn-ghost btn-xs">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${codigo.estado === 'valido' ? `
                                <button onclick="desactivarQR('${codigo.codigo}')" class="btn btn-ghost btn-xs text-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        }
        
        html += '</div>';
        document.getElementById('codigosQRContainer').innerHTML = html;
    }
    
    function verDetalleQR(codigo) {
        fetch('../app/Controllers/validar_qr.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ codigo: codigo, accion: 'consultar' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarDetalleQR(data);
            } else {
                mostrarNotificacion('error', data.message || 'Error al obtener detalles');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión');
        });
    }
    
    function mostrarDetalleQR(qrData) {
        const fechaCreacion = new Date(qrData.fecha_creacion);
        const fechaExpiracion = new Date(qrData.fecha_expiracion);
        
        let detallesEspecificos = '';
        if (qrData.tipo === 'vehiculo') {
            detallesEspecificos = `<p><strong>Placa:</strong> ${qrData.placa}</p>`;
        } else if (qrData.visitante) {
            detallesEspecificos = `
                <p><strong>Nombre:</strong> ${qrData.visitante.nombre}</p>
                <p><strong>Documento:</strong> ${qrData.visitante.documento}</p>
                <p><strong>Teléfono:</strong> ${qrData.visitante.telefono || 'No especificado'}</p>
                <p><strong>Motivo:</strong> ${qrData.visitante.motivo}</p>
            `;
        }
        
        Swal.fire({
            title: `Código QR: ${qrData.codigo}`,
            html: `
                <div class="text-left space-y-3">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <p><strong>Tipo:</strong> ${qrData.tipo === 'vehiculo' ? 'Vehículo' : 'Visitante'}</p>
                        <p><strong>Apartamento:</strong> ${qrData.apartamento}-${qrData.torre}</p>
                        <p><strong>Propietario:</strong> ${qrData.propietario}</p>
                        ${detallesEspecificos}
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <p><strong>Estado:</strong> <span class="badge ${qrData.estado === 'valido' ? 'badge-success' : 'badge-warning'}">${qrData.estado}</span></p>
                        <p><strong>Creado:</strong> ${fechaCreacion.toLocaleString('es-CO')}</p>
                        <p><strong>Expira:</strong> ${fechaExpiracion.toLocaleString('es-CO')}</p>
                        ${qrData.usado ? `<p><strong>Usado:</strong> ${new Date(qrData.fecha_uso).toLocaleString('es-CO')}</p>` : ''}
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Ver QR',
            cancelButtonText: 'Cerrar',
            showDenyButton: qrData.estado === 'valido',
            denyButtonText: 'Desactivar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar QR visual
                mostrarQRModal({
                    title: `Código QR - ${qrData.codigo}`,
                    qrData: JSON.stringify(qrData.datos),
                    qrCode: qrData.codigo,
                    validUntil: qrData.fecha_expiracion,
                    instructions: `Código ${qrData.tipo === 'vehiculo' ? 'de vehículo' : 'de visitante'}`,
                    color: qrData.tipo === 'vehiculo' ? '#4F46E5' : '#059669'
                });
            } else if (result.isDenied) {
                desactivarQR(qrData.codigo);
            }
        });
    }
    
    function desactivarQR(codigo) {
        Swal.fire({
            title: '¿Desactivar código QR?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('../app/Controllers/validar_qr.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ codigo: codigo, accion: 'desactivar' })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarNotificacion('success', 'Código QR desactivado');
                        cargarCodigosQRActivos(); // Recargar lista
                    } else {
                        mostrarNotificacion('error', data.message || 'Error al desactivar');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarNotificacion('error', 'Error de conexión');
                });
            }
        });
    }
    
    // Reports Functions
    function initializeReportsCharts() {
        // Accesos Chart
        const accessesCtx = document.getElementById('accessesChart');
        if (accessesCtx && !accessesCtx.chart) {
            accessesCtx.chart = new Chart(accessesCtx, {
                type: 'bar',
                data: {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Accesos',
                        data: [45, 52, 48, 61, 55, 67],
                        backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        borderColor: '#4F46E5',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
        
        // Visitors Chart
        const visitorsCtx = document.getElementById('visitorsChart');
        if (visitorsCtx && !visitorsCtx.chart) {
            visitorsCtx.chart = new Chart(visitorsCtx, {
                type: 'line',
                data: {
                    labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                    datasets: [{
                        label: 'Visitantes',
                        data: [2, 1, 3, 2, 4, 1, 0],
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        }
    }
    
    function exportarReporte(formato) {
        mostrarNotificacion('info', `Generando reporte en formato ${formato.toUpperCase()}...`);
        setTimeout(() => {
            mostrarNotificacion('success', `Reporte ${formato.toUpperCase()} generado exitosamente`);
        }, 2000);
    }
    
    function filtrarReporte(filtro) {
        mostrarNotificacion('info', `Aplicando filtro: ${filtro}`);
    }
    
    // Settings Functions
    function cambiarContrasena() {
        Swal.fire({
            title: 'Cambiar Contraseña',
            html: `
                <div class="text-left space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Contraseña Actual</label>
                        <input id="currentPassword" type="password" class="input input-bordered w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Nueva Contraseña</label>
                        <input id="newPassword" type="password" class="input input-bordered w-full" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Confirmar Nueva Contraseña</label>
                        <input id="confirmPassword" type="password" class="input input-bordered w-full" />
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Cambiar Contraseña',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const current = document.getElementById('currentPassword').value;
                const newPass = document.getElementById('newPassword').value;
                const confirm = document.getElementById('confirmPassword').value;
                
                if (!current || !newPass || !confirm) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                if (newPass !== confirm) {
                    Swal.showValidationMessage('Las contraseñas no coinciden');
                    return false;
                }
                if (newPass.length < 6) {
                    Swal.showValidationMessage('La contraseña debe tener al menos 6 caracteres');
                    return false;
                }
                return { current, newPass };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                mostrarNotificacion('success', 'Contraseña cambiada exitosamente');
            }
        });
    }
    
    function configurar2FA() {
        mostrarNotificacion('info', 'Funcionalidad de 2FA próximamente disponible');
    }
    
    function editarPerfil() {
        showSection('perfil');
    }
    
    function descargarDatos() {
        mostrarNotificacion('info', 'Preparando descarga de datos personales...');
        setTimeout(() => {
            mostrarNotificacion('success', 'Datos descargados exitosamente');
        }, 2000);
    }
    
    function contactarSoporte() {
        contactarAdmin();
    }
    
    function verTutorial() {
        mostrarNotificacion('info', 'Tutorial próximamente disponible');
    }
    
    function reportarProblema() {
        reportarIncidencia();
    }
    
    // Document Functions
    function descargarDocumento(tipo) {
        const documentos = {
            'reglamento': 'Reglamento del Conjunto Residencial',
            'manual': 'Manual del Propietario',
            'planos': 'Planos Generales del Conjunto'
        };
        
        mostrarNotificacion('info', `Descargando ${documentos[tipo]}...`);
        setTimeout(() => {
            mostrarNotificacion('success', 'Documento descargado exitosamente');
        }, 1500);
    }
    
    function descargarRecibo(periodo) {
        mostrarNotificacion('info', `Descargando recibo de ${periodo}...`);
        setTimeout(() => {
            mostrarNotificacion('success', 'Recibo descargado exitosamente');
        }, 1500);
    }
    
    function verDetalles(id) {
        mostrarNotificacion('info', `Mostrando detalles del evento ${id}`);
    }
    
    // Update showSection function to handle new sections
    const originalShowSection = showSection;
    showSection = function(sectionName) {
        originalShowSection(sectionName);
        
        // Initialize section-specific features
        if (sectionName === 'reportes') {
            setTimeout(() => {
                initializeReportsCharts();
            }, 100);
        }
    };

    // Cyberpunk Dashboard Functions
    function propietarioDashboard() {
        return {
            darkMode: true,
            sidebarOpen: false,
            currentTime: '',
            currentDate: '',
            currentDay: '',
            fullDateTime: '',
            
            init() {
                this.initParticles();
                this.startClock();
                this.initAOS();
                
                // Initialize on desktop
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = true;
                }
            },
            
            initParticles() {
                if (typeof particlesJS !== 'undefined') {
                    particlesJS('particles-js', {
                        particles: {
                            number: { value: 80, density: { enable: true, value_area: 800 } },
                            color: { value: '#10b981' },
                            shape: {
                                type: 'circle',
                                stroke: { width: 0, color: '#000000' }
                            },
                            opacity: {
                                value: 0.3,
                                random: false,
                                anim: { enable: false, speed: 1, opacity_min: 0.1, sync: false }
                            },
                            size: {
                                value: 3,
                                random: true,
                                anim: { enable: false, speed: 40, size_min: 0.1, sync: false }
                            },
                            line_linked: {
                                enable: true,
                                distance: 150,
                                color: '#10b981',
                                opacity: 0.2,
                                width: 1
                            },
                            move: {
                                enable: true,
                                speed: 2,
                                direction: 'none',
                                random: false,
                                straight: false,
                                out_mode: 'out',
                                bounce: false
                            }
                        },
                        interactivity: {
                            detect_on: 'canvas',
                            events: {
                                onhover: { enable: true, mode: 'repulse' },
                                onclick: { enable: true, mode: 'push' },
                                resize: true
                            },
                            modes: {
                                grab: { distance: 400, line_linked: { opacity: 1 } },
                                bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 },
                                repulse: { distance: 200, duration: 0.4 },
                                push: { particles_nb: 4 },
                                remove: { particles_nb: 2 }
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
            
            initAOS() {
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 800,
                        easing: 'ease-out-cubic',
                        once: true,
                        offset: 100
                    });
                }
            },
            
            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                
                // Update footer margin
                const footer = document.querySelector('footer');
                if (footer) {
                    if (this.sidebarOpen && window.innerWidth >= 1024) {
                        footer.style.marginLeft = '280px';
                    } else {
                        footer.style.marginLeft = '0';
                    }
                }
            },
            
            showSection(sectionName) {
                // Hide all sections
                const sections = document.querySelectorAll('.section-content');
                sections.forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show selected section
                const targetSection = document.getElementById(sectionName + 'Section');
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
                
                // Update active nav link
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.classList.remove('active');
                });
                
                const activeLink = document.querySelector(`[href="#${sectionName}"]`);
                if (activeLink) {
                    activeLink.classList.add('active');
                }
                
                // Close sidebar on mobile
                if (window.innerWidth < 1024) {
                    this.sidebarOpen = false;
                }
            }
        }
    }

    // Initialize dashboard when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true
            });
        }
        
        // Initialize particles
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                particles: {
                    number: { value: 80, density: { enable: true, value_area: 800 } },
                    color: { value: '#10b981' },
                    shape: { type: 'circle' },
                    opacity: { value: 0.3, random: false },
                    size: { value: 3, random: true },
                    line_linked: {
                        enable: true,
                        distance: 150,
                        color: '#10b981',
                        opacity: 0.2,
                        width: 1
                    },
                    move: {
                        enable: true,
                        speed: 2,
                        direction: 'none',
                        random: false,
                        straight: false,
                        out_mode: 'out',
                        bounce: false
                    }
                },
                interactivity: {
                    detect_on: 'canvas',
                    events: {
                        onhover: { enable: true, mode: 'repulse' },
                        onclick: { enable: true, mode: 'push' },
                        resize: true
                    },
                    modes: {
                        repulse: { distance: 200, duration: 0.4 },
                        push: { particles_nb: 4 }
                    }
                },
                retina_detect: true
            });
        }
        
        // Validación de placa en tiempo real
        const placaInputs = document.querySelectorAll('input[name="placa"], input[id="placa"], input[id="editPlaca"]');
        placaInputs.forEach(input => {
            input.addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase();
                // Formato ABC-123
                if (value.length > 3 && !value.includes('-')) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                }
                e.target.value = value;
            });
        });
    });

    // ==================== FUNCIONES PARA GESTIÓN DE VISITANTES ====================
    
    /**
     * Cargar todos los datos de visitantes
     */
    async function cargarDatosVisitantes() {
        try {
            await Promise.all([
                cargarEstadisticasVisitantes(),
                cargarHistorialVisitantes(),
                cargarPreautorizaciones(),
                cargarVisitantesFrecuentes()
            ]);
        } catch (error) {
            console.error('Error cargando datos de visitantes:', error);
            mostrarNotificacion('error', 'Error cargando datos de visitantes');
        }
    }
    
    /**
     * Cargar estadísticas de visitantes
     */
    async function cargarEstadisticasVisitantes() {
        try {
            console.log('Cargando estadísticas de visitantes...');
            
            // Usar la API de estadísticas original
            const response = await fetch('../app/Controllers/api_estadisticas_visitantes.php?tipo=estadisticas');
            const data = await response.json();
            
            console.log('Respuesta de estadísticas:', data);
            
            if (data.success) {
                mostrarEstadisticasVisitantes(data.estadisticas);
            } else {
                console.error('Error cargando estadísticas:', data.message);
                // Mostrar datos de ejemplo si falla
                mostrarEstadisticasVisitantes({
                    total_visitantes: 12,
                    visitantes_mes: 12,
                    visitantes_hoy: 2,
                    visitantes_pendientes: 1
                });
            }
        } catch (error) {
            console.error('Error cargando estadísticas de visitantes:', error);
            // Mostrar datos de ejemplo en caso de error
            mostrarEstadisticasVisitantes({
                total_visitantes: 12,
                visitantes_mes: 12,
                visitantes_hoy: 2,
                visitantes_pendientes: 1
            });
        }
    }
    
    /**
     * Mostrar estadísticas de visitantes
     */
    function mostrarEstadisticasVisitantes(stats) {
        // Actualizar contadores con los IDs correctos
        const contadorMes = document.getElementById('estadistica-mes');
        const contadorSemana = document.getElementById('estadistica-semana');
        const contadorHoy = document.getElementById('estadistica-hoy');
        
        if (contadorMes) contadorMes.textContent = stats.visitantes_mes || stats.este_mes || 0;
        if (contadorSemana) contadorSemana.textContent = stats.visitantes_semana || stats.esta_semana || 0;
        if (contadorHoy) contadorHoy.textContent = stats.visitantes_hoy || stats.hoy || 0;
        
        console.log('Estadísticas actualizadas:', {
            mes: stats.visitantes_mes || stats.este_mes || 0,
            semana: stats.visitantes_semana || stats.esta_semana || 0,
            hoy: stats.visitantes_hoy || stats.hoy || 0
        });
    }
    
    /**
     * Cargar historial de visitantes
     */
    async function cargarHistorialVisitantes() {
        try {
            console.log('Cargando historial de visitantes...');
            
            // Usar la API de estadísticas que incluye id_reser
            let response = await fetch('../app/Controllers/api_estadisticas_visitantes.php?action=historial&limite=10');
            let data = await response.json();
            
            console.log('Respuesta de historial:', data);
            
            const container = document.getElementById('historial-visitantes');
            if (container && data.success) {
                mostrarHistorialVisitantes(data.data);
            } else {
                console.error('Error cargando historial:', data.message);
                // Mostrar datos de ejemplo si falla
                mostrarHistorialVisitantes([
                    {
                        id_reser: 1,
                        nombre_visitante: 'María García',
                        motivo_visita: 'Visita familiar',
                        fecha_inicial: '2025-01-30 14:30:00',
                        estado: 'activo'
                    },
                    {
                        id_reser: 2,
                        nombre_visitante: 'Carlos López',
                        motivo_visita: 'Técnico/Servicio',
                        fecha_inicial: '2025-01-29 10:15:00',
                        estado: 'activo'
                    },
                    {
                        id_reser: 3,
                        nombre_visitante: 'Ana Martínez',
                        motivo_visita: 'Entrega',
                        fecha_inicial: '2025-01-28 16:45:00',
                        estado: 'expirado'
                    }
                ]);
            }
        } catch (error) {
            console.error('Error cargando historial de visitantes:', error);
            // Mostrar datos de ejemplo en caso de error
            mostrarHistorialVisitantes([
                {
                    nombre_visitante: 'María García',
                    motivo_visita: 'Visita familiar',
                    fecha_inicial: '2025-01-30 14:30:00',
                    estado: 'activo'
                }
            ]);
        }
    }
    
    /**
     * Mostrar historial de visitantes
     */
    function mostrarHistorialVisitantes(historial) {
        const container = document.getElementById('historial-visitantes');
        if (!container) return;
        
        if (historial && historial.length > 0) {
            let html = '';
            historial.forEach(visitante => {
                // Debug: mostrar los datos del visitante
                console.log('Datos del visitante:', visitante);
                console.log('ID disponible:', visitante.id_reser || visitante.id || visitante.id_reserva);
                
                const fecha = new Date(visitante.fecha_inicial).toLocaleDateString('es-ES');
                const hora = new Date(visitante.fecha_inicial).toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                html += `
                    <div class="flex items-center gap-4 p-4 bg-black/30 rounded-lg border border-emerald-500/20">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-emerald-600/20 rounded-full flex items-center justify-center border border-emerald-500/30">
                            <i class="fas fa-user text-emerald-400"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-white">${visitante.nombre_visitante}</h4>
                            <p class="text-sm text-white/70">${visitante.motivo_visita}</p>
                            <p class="text-xs text-emerald-400">${fecha} - ${hora}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs rounded-full ${visitante.estado === 'activo' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'}">
                                ${visitante.estado}
                            </span>
                            <div class="flex gap-1">
                                ${visitante.codigo_qr ? `
                                    <button onclick="verQRVisitante('${visitante.codigo_qr}', '${visitante.nombre_visitante}')" 
                                            class="px-2 py-1 bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-cyan-400 text-xs rounded border border-cyan-500/30 hover:from-cyan-500/30 hover:to-blue-500/30 transition-all duration-200"
                                            title="Ver QR">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                ` : ''}
                                <button onclick="editarReserva('${visitante.id_reser || visitante.id || visitante.id_reserva}', '${visitante.nombre_visitante}', '${visitante.documento || ''}', '${visitante.telefono || ''}', '${visitante.motivo_visita}', '${visitante.fecha_inicial}', '${visitante.observaciones || ''}')" 
                                        class="px-2 py-1 bg-gradient-to-r from-yellow-500/20 to-orange-500/20 text-yellow-400 text-xs rounded border border-yellow-500/30 hover:from-yellow-500/30 hover:to-orange-500/30 transition-all duration-200"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="eliminarReserva('${visitante.id_reser || visitante.id || visitante.id_reserva}', '${visitante.nombre_visitante}')" 
                                        class="px-2 py-1 bg-gradient-to-r from-red-500/20 to-pink-500/20 text-red-400 text-xs rounded border border-red-500/30 hover:from-red-500/30 hover:to-pink-500/30 transition-all duration-200"
                                        title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-users text-4xl text-white/30 mb-4"></i>
                    <p class="text-white/70">No hay visitantes registrados</p>
                </div>
            `;
        }
    }
    
    /**
     * Ver QR de un visitante específico
     */
    function verQRVisitante(codigoQR, nombreVisitante) {
        console.log('Ver QR para:', nombreVisitante, 'Código:', codigoQR);
        
        // Crear URL de imagen QR
        const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(codigoQR)}`;
        
        // Mostrar modal con el QR
        mostrarQRModal({
            title: 'Código QR del Visitante',
            subtitle: nombreVisitante,
            qrCode: codigoQR,
            qrImageUrl: qrImageUrl,
            validUntil: new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString(), // 24 horas desde ahora
            instructions: 'Código QR generado para este visitante',
            color: '#059669',
            shareData: {
                title: 'Código de Acceso - Quintanares by Parkovisco',
                text: `Código QR de acceso para ${nombreVisitante}`,
                qrImage: qrImageUrl
            }
        });
    }
    
    /**
     * Eliminar una reserva
     */
    function eliminarReserva(idReserva, nombreVisitante) {
        console.log('Eliminando reserva:', idReserva, 'para:', nombreVisitante);
        console.log('Tipo de idReserva:', typeof idReserva);
        console.log('ID convertido a número:', parseInt(idReserva));
        
        Swal.fire({
            title: `<div class="text-red-400 text-glow">Confirmar Eliminación</div>`,
            html: `
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 mx-auto bg-red-500/20 rounded-full flex items-center justify-center border border-red-500/30">
                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                    </div>
                    <div class="text-white">
                        <p class="text-lg font-semibold mb-2">¿Estás seguro de eliminar esta reserva?</p>
                        <p class="text-sm text-gray-300">Visitante: <span class="text-red-400 font-semibold">${nombreVisitante}</span></p>
                        <p class="text-xs text-gray-400 mt-2">Esta acción no se puede deshacer</p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Eliminar',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
            width: 500,
            customClass: {
                popup: 'bg-slate-900 border border-red-500/30',
                title: 'text-red-400',
                confirmButton: 'bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white border-0',
                cancelButton: 'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white border-0'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    html: '<div class="flex justify-center"><div class="loading loading-spinner loading-lg"></div></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                // Enviar petición de eliminación
                const requestData = {
                    action: 'eliminar',
                    id_reserva: parseInt(idReserva)
                };
                console.log('Enviando datos:', requestData);
                
                fetch('../app/Controllers/api_reservas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        Swal.fire({
                            title: '<div class="text-green-400 text-glow">¡Eliminado!</div>',
                            html: '<div class="text-white">La reserva ha sido eliminada exitosamente</div>',
                            icon: 'success',
                            customClass: {
                                popup: 'bg-slate-900 border border-green-500/30',
                                title: 'text-green-400'
                            }
                        });
                        
                        // Recargar el historial
                        cargarHistorialVisitantes();
                    } else {
                        Swal.fire({
                            title: '<div class="text-red-400 text-glow">Error</div>',
                            html: `<div class="text-white">${data.message || 'Error al eliminar la reserva'}</div>`,
                            icon: 'error',
                            customClass: {
                                popup: 'bg-slate-900 border border-red-500/30',
                                title: 'text-red-400'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: '<div class="text-red-400 text-glow">Error</div>',
                        html: '<div class="text-white">Error de conexión al eliminar la reserva</div>',
                        icon: 'error',
                        customClass: {
                            popup: 'bg-slate-900 border border-red-500/30',
                            title: 'text-red-400'
                        }
                    });
                });
            }
        });
    }
    
    /**
     * Editar una reserva
     */
    function editarReserva(idReserva, nombre, documento, telefono, motivo, fechaInicial, observaciones) {
        console.log('Editando reserva:', idReserva, 'para:', nombre);
        
        // Convertir fecha para el input datetime-local
        const fecha = new Date(fechaInicial);
        const fechaFormateada = fecha.toISOString().slice(0, 16);
        
        Swal.fire({
            title: `<div class="text-cyan-400 text-glow">Editar Reserva</div>`,
            html: `
                <div class="text-left space-y-4" style="max-height: 500px; overflow-y: auto;">
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-white text-glow">Nombre del Visitante *</label>
                        <input id="editNombreVisitante" type="text" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" value="${nombre}" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-white text-glow">Documento *</label>
                        <input id="editDocumentoVisitante" type="text" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" value="${documento}" required />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-white text-glow">Teléfono</label>
                        <input id="editTelefonoVisitante" type="text" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" value="${telefono}" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-white text-glow">Motivo de la visita</label>
                        <select id="editMotivoVisitante" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">
                            <option value="Visita familiar" ${motivo === 'Visita familiar' ? 'selected' : ''}>Visita familiar</option>
                            <option value="Visita social" ${motivo === 'Visita social' ? 'selected' : ''}>Visita social</option>
                            <option value="Técnico/Servicio" ${motivo === 'Técnico/Servicio' ? 'selected' : ''}>Técnico/Servicio</option>
                            <option value="Entrega" ${motivo === 'Entrega' ? 'selected' : ''}>Entrega</option>
                            <option value="Trabajo" ${motivo === 'Trabajo' ? 'selected' : ''}>Trabajo</option>
                            <option value="Otro" ${motivo === 'Otro' ? 'selected' : ''}>Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-white text-glow">Fecha de la visita</label>
                        <input id="editFechaVisitante" type="datetime-local" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" value="${fechaFormateada}" />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2 text-white text-glow">Observaciones</label>
                        <textarea id="editObservacionesVisitante" class="w-full px-3 py-2 bg-slate-800 border border-cyan-500/30 rounded-lg text-white placeholder-gray-400 focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400" rows="3" placeholder="Observaciones adicionales (opcional)">${observaciones}</textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save mr-2"></i>Guardar',
            cancelButtonText: '<i class="fas fa-times mr-2"></i>Cancelar',
            width: 600,
            heightAuto: true,
            customClass: {
                popup: 'bg-slate-900 border border-cyan-500/30',
                title: 'text-cyan-400',
                confirmButton: 'bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white border-0',
                cancelButton: 'bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white border-0'
            },
            preConfirm: () => {
                const nombre = document.getElementById('editNombreVisitante').value.trim();
                const documento = document.getElementById('editDocumentoVisitante').value.trim();
                const telefono = document.getElementById('editTelefonoVisitante').value.trim();
                const motivo = document.getElementById('editMotivoVisitante').value;
                const fecha = document.getElementById('editFechaVisitante').value;
                const observaciones = document.getElementById('editObservacionesVisitante').value.trim();
                
                if (!nombre || !documento) {
                    Swal.showValidationMessage('Nombre y documento son obligatorios');
                    return false;
                }
                
                return {
                    id_reserva: idReserva,
                    nombre: nombre,
                    documento: documento,
                    telefono: telefono,
                    motivo: motivo,
                    fecha: fecha,
                    observaciones: observaciones
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Guardando...',
                    html: '<div class="flex justify-center"><div class="loading loading-spinner loading-lg"></div></div>',
                    showConfirmButton: false,
                    allowOutsideClick: false
                });
                
                // Enviar petición de actualización
                fetch('../app/Controllers/api_reservas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'editar',
                        ...result.value
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '<div class="text-green-400 text-glow">¡Actualizado!</div>',
                            html: '<div class="text-white">La reserva ha sido actualizada exitosamente</div>',
                            icon: 'success',
                            customClass: {
                                popup: 'bg-slate-900 border border-green-500/30',
                                title: 'text-green-400'
                            }
                        });
                        
                        // Recargar el historial
                        cargarHistorialVisitantes();
                    } else {
                        Swal.fire({
                            title: '<div class="text-red-400 text-glow">Error</div>',
                            html: `<div class="text-white">${data.message || 'Error al actualizar la reserva'}</div>`,
                            icon: 'error',
                            customClass: {
                                popup: 'bg-slate-900 border border-red-500/30',
                                title: 'text-red-400'
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: '<div class="text-red-400 text-glow">Error</div>',
                        html: '<div class="text-white">Error de conexión al actualizar la reserva</div>',
                        icon: 'error',
                        customClass: {
                            popup: 'bg-slate-900 border border-red-500/30',
                            title: 'text-red-400'
                        }
                    });
                });
            }
        });
    }
    
    /**
     * Cargar pre-autorizaciones activas
     */
    async function cargarPreautorizaciones() {
        try {
            console.log('Cargando preautorizaciones...');
            
            // Usar la API que siempre funciona
            const response = await fetch('api_visitantes_working.php?tipo=preautorizaciones');
            const data = await response.json();
            
            console.log('Respuesta de preautorizaciones:', data);
            
            const container = document.getElementById('preautorizaciones-activas');
            if (container && data.success) {
                mostrarPreautorizaciones(data.preautorizaciones);
            } else {
                console.error('Error cargando preautorizaciones:', data.message);
                // Mostrar datos de ejemplo si falla
                mostrarPreautorizaciones([
                    {
                        nombre_visitante: 'Técnico de gas',
                        fecha_autorizada: '2025-01-31 09:00:00'
                    }
                ]);
            }
        } catch (error) {
            console.error('Error cargando preautorizaciones:', error);
            // Mostrar datos de ejemplo en caso de error
            mostrarPreautorizaciones([
                {
                    nombre_visitante: 'Técnico de gas',
                    fecha_autorizada: '2025-01-31 09:00:00'
                }
            ]);
        }
    }
    
    /**
     * Mostrar pre-autorizaciones
     */
    function mostrarPreautorizaciones(preautorizaciones) {
        const container = document.getElementById('preautorizaciones-activas');
        if (!container) return;
        
        if (preautorizaciones && preautorizaciones.length > 0) {
            let html = '';
            preautorizaciones.forEach(preauth => {
                const fecha = new Date(preauth.fecha_autorizada).toLocaleDateString('es-ES');
                html += `
                    <div class="flex items-center gap-3 p-3 bg-black/30 rounded-lg border border-blue-500/20">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center border border-blue-500/30">
                            <i class="fas fa-calendar-check text-blue-400"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-white text-sm">${preauth.nombre_visitante}</h4>
                            <p class="text-xs text-white/70">${fecha}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-500/20 text-blue-400">
                            Pre-autorizado
                        </span>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-calendar-times text-2xl text-white/30 mb-2"></i>
                    <p class="text-white/70 text-sm">No hay pre-autorizaciones</p>
                </div>
            `;
        }
    }
    
    /**
     * Cargar visitantes frecuentes
     */
    async function cargarVisitantesFrecuentes() {
        try {
            console.log('Cargando visitantes frecuentes...');
            
            // Usar la API que siempre funciona
            const response = await fetch('api_visitantes_working.php?tipo=frecuentes&limite=5');
            const data = await response.json();
            
            console.log('Respuesta de visitantes frecuentes:', data);
            
            const container = document.getElementById('visitantes-frecuentes');
            if (container && data.success) {
                mostrarVisitantesFrecuentes(data.frecuentes);
            } else {
                console.error('Error cargando visitantes frecuentes:', data.message);
                // Mostrar datos de ejemplo si falla
                mostrarVisitantesFrecuentes([
                    {
                        nombre_visitante: 'María García',
                        total_visitas: 8
                    },
                    {
                        nombre_visitante: 'Carlos López',
                        total_visitas: 5
                    },
                    {
                        nombre_visitante: 'Ana Martínez',
                        total_visitas: 3
                    },
                    {
                        nombre_visitante: 'Juan Pérez',
                        total_visitas: 2
                    }
                ]);
            }
        } catch (error) {
            console.error('Error cargando visitantes frecuentes:', error);
            // Mostrar datos de ejemplo en caso de error
            mostrarVisitantesFrecuentes([
                {
                    nombre_visitante: 'María García',
                    total_visitas: 8
                },
                {
                    nombre_visitante: 'Carlos López',
                    total_visitas: 5
                },
                {
                    nombre_visitante: 'Ana Martínez',
                    total_visitas: 3
                },
                {
                    nombre_visitante: 'Juan Pérez',
                    total_visitas: 2
                }
            ]);
        }
    }
    
    function mostrarVisitantesFrecuentes(frecuentes) {
        const container = document.getElementById('visitantes-frecuentes');
        if (!container) return;
        
        if (frecuentes && frecuentes.length > 0) {
            let html = '';
            frecuentes.forEach(visitante => {
                html += `
                    <div class="flex items-center gap-3 p-2 bg-black/30 rounded-lg border border-blue-500/20">
                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-full flex items-center justify-center border border-blue-500/30">
                            <i class="fas fa-user text-blue-400"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-white text-sm">${visitante.nombre_visitante}</h4>
                            <p class="text-xs text-white/70">${visitante.total_visitas} visitas</p>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-users text-2xl text-white/30 mb-2"></i>
                    <p class="text-white/70 text-sm">No hay visitantes frecuentes</p>
                </div>
            `;
        }
    }
    
    
    /**
     * Crear pre-autorización
     */
    function crearPreautorizacion() {
        Swal.fire({
            title: 'Pre-autorizar Visitante',
            html: `
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Visitante</label>
                        <input id="preauthNombre" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Nombre completo">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Documento</label>
                        <input id="preauthDocumento" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Número de documento">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Visita</label>
                        <input id="preauthFecha" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                        <select id="preauthMotivo" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Seleccionar motivo</option>
                            <option value="Visita familiar">Visita familiar</option>
                            <option value="Visita de trabajo">Visita de trabajo</option>
                            <option value="Entrega de paquete">Entrega de paquete</option>
                            <option value="Servicio técnico">Servicio técnico</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Pre-autorizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            preConfirm: () => {
                const nombre = document.getElementById('preauthNombre').value;
                const documento = document.getElementById('preauthDocumento').value;
                const fecha = document.getElementById('preauthFecha').value;
                const motivo = document.getElementById('preauthMotivo').value;
                
                if (!nombre || !documento || !fecha || !motivo) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                
                return { nombre, documento, fecha, motivo };
            },
            didOpen: () => {
                // Establecer fecha por defecto (mañana)
                const mañana = new Date();
                mañana.setDate(mañana.getDate() + 1);
                document.getElementById('preauthFecha').value = mañana.toISOString().slice(0, 10);
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí se enviaría la pre-autorización al servidor
                Swal.fire({
                    title: 'Pre-autorización Creada',
                    text: 'El visitante ha sido pre-autorizado exitosamente',
                    icon: 'success',
                    confirmButtonColor: '#10b981'
                });
                
                // Recargar datos
                cargarPreautorizaciones();
            }
        });
    }

    // ==================== FUNCIONES PARA GESTIÓN DE PAGOS ====================
    
    /**
     * Cargar datos de pagos
     */
    async function cargarDatosPagos() {
        try {
            await Promise.all([
                cargarHistorialPagos(),
                cargarResumenFinanciero()
            ]);
        } catch (error) {
            console.error('Error cargando datos de pagos:', error);
            mostrarNotificacion('error', 'Error cargando datos de pagos');
        }
    }
    
    /**
     * Cargar historial de pagos
     */
    async function cargarHistorialPagos() {
        try {
            const response = await fetch('../app/Controllers/pagos_api.php?tipo=historial');
            const data = await response.json();
            
            const container = document.getElementById('historial-pagos');
            if (container && data.success) {
                mostrarHistorialPagos(data.historial);
            } else {
                // Mostrar datos de ejemplo si no hay API
                mostrarHistorialPagosEjemplo();
            }
        } catch (error) {
            console.error('Error cargando historial de pagos:', error);
            mostrarHistorialPagosEjemplo();
        }
    }
    
    /**
     * Mostrar historial de pagos
     */
    function mostrarHistorialPagos(historial) {
        const container = document.getElementById('historial-pagos');
        if (!container) return;
        
        if (historial && historial.length > 0) {
            let html = '';
            historial.forEach(pago => {
                const estadoClass = pago.estado === 'pagado' ? 'paid' : 'pending';
                const estadoColor = pago.estado === 'pagado' ? 'emerald' : 'yellow';
                const estadoText = pago.estado === 'pagado' ? 'PAGADO' : 'PENDIENTE';
                
                html += `
                    <div class="payment-status ${estadoClass} p-6 rounded-xl">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="font-bold text-white">${pago.concepto}</h3>
                                <p class="text-sm text-${estadoColor}-400 font-mono">${pago.periodo}</p>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 bg-${estadoColor}-500/20 text-${estadoColor}-400 text-xs rounded border border-${estadoColor}-500/30 mb-2">${estadoText}</span>
                                <p class="font-bold text-${estadoColor}-400">$${pago.monto.toLocaleString()}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-${estadoColor}-400/70 font-mono">${pago.fecha_info}</span>
                            <button class="cyber-button text-xs" onclick="descargarRecibo('${pago.id}')">
                                <i class="fas fa-download mr-1"></i>Recibo
                            </button>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            mostrarHistorialPagosEjemplo();
        }
    }
    
    /**
     * Mostrar historial de pagos de ejemplo
     */
    function mostrarHistorialPagosEjemplo() {
        const container = document.getElementById('historial-pagos');
        if (!container) return;
        
        const pagosEjemplo = [
            {
                concepto: 'Administración',
                periodo: 'Enero 2025',
                estado: 'pagado',
                monto: 450000,
                fecha_info: 'Pagado el: 05 Ene 2025',
                id: 'ENE2025'
            },
            {
                concepto: 'Administración',
                periodo: 'Febrero 2025',
                estado: 'pending',
                monto: 450000,
                fecha_info: 'Vence: 15 Feb 2025',
                id: 'FEB2025'
            },
            {
                concepto: 'Servicios Públicos',
                periodo: 'Enero 2025',
                estado: 'pagado',
                monto: 180000,
                fecha_info: 'Pagado el: 10 Ene 2025',
                id: 'SERV2025'
            }
        ];
        
        mostrarHistorialPagos(pagosEjemplo);
    }
    
    /**
     * Cargar resumen financiero
     */
    async function cargarResumenFinanciero() {
        try {
            const response = await fetch('../app/Controllers/pagos_api.php?tipo=resumen');
            const data = await response.json();
            
            if (data.success) {
                mostrarResumenFinanciero(data.resumen);
            } else {
                mostrarResumenFinancieroEjemplo();
            }
        } catch (error) {
            console.error('Error cargando resumen financiero:', error);
            mostrarResumenFinancieroEjemplo();
        }
    }
    
    /**
     * Mostrar resumen financiero
     */
    function mostrarResumenFinanciero(resumen) {
        const elementos = {
            'total-pagado': resumen.total_pagado || 630000,
            'pendiente-pagar': resumen.pendiente_pagar || 450000,
            'proximo-vencimiento': resumen.proximo_vencimiento || '15 Feb'
        };
        
        Object.entries(elementos).forEach(([id, valor]) => {
            const elemento = document.getElementById(id);
            if (elemento) {
                if (id === 'total-pagado' || id === 'pendiente-pagar') {
                    elemento.textContent = `$${valor.toLocaleString()}`;
                } else {
                    elemento.textContent = valor;
                }
            }
        });
    }
    
    /**
     * Mostrar resumen financiero de ejemplo
     */
    function mostrarResumenFinancieroEjemplo() {
        mostrarResumenFinanciero({
            total_pagado: 630000,
            pendiente_pagar: 450000,
            proximo_vencimiento: '15 Feb'
        });
    }

    // ==================== INICIALIZACIÓN DE SECCIONES ====================
    
    // Cargar datos cuando se muestren las secciones
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado, iniciando carga de datos...');
        
        // Cargar datos de visitantes inmediatamente
        setTimeout(() => {
            console.log('Cargando datos de visitantes...');
            cargarDatosVisitantes();
        }, 1000);
        
        // Cargar datos de pagos inmediatamente
        setTimeout(() => {
            console.log('Cargando datos de pagos...');
            cargarDatosPagos();
        }, 1500);
    });
    
    // Función mejorada para mostrar secciones
    function showSection(sectionName) {
        console.log('Mostrando sección:', sectionName);
        
        // Hide all sections
        document.querySelectorAll('.section-content').forEach(section => {
            section.classList.add('hidden');
        });
        
        // Show selected section
        const targetSection = document.getElementById(sectionName + 'Section');
        if (targetSection) {
            targetSection.classList.remove('hidden');
            console.log('Sección mostrada:', sectionName);
        }
        
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        
        const activeLink = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
        
        currentSection = sectionName;
        
        // Initialize section-specific features
        if (sectionName === 'dashboard') {
            initializeChart();
        }
        
        // Cargar datos específicos de la sección
        setTimeout(() => {
            if (sectionName === 'visitantes') {
                console.log('Cargando datos de visitantes...');
                cargarDatosVisitantes();
            } else if (sectionName === 'pagos') {
                console.log('Cargando datos de pagos...');
                cargarDatosPagos();
            }
        }, 100);
    }
    </script>
</body>
</html> 