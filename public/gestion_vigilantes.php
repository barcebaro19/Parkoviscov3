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

// ===== MÉTRICAS REALES DE VIGILANTES =====
try {
    // Total de vigilantes
    $total_vigilantes_query = $conexion->query("
        SELECT COUNT(*) as total FROM usuarios u 
        JOIN usu_roles ur ON u.id = ur.usuarios_id 
        JOIN roles r ON ur.roles_idroles = r.idroles 
        WHERE r.nombre_rol = 'vigilante'
    ");
    $total_vigilantes = $total_vigilantes_query ? $total_vigilantes_query->fetch_assoc()['total'] : 0;
    
    // Vigilantes activos (con login reciente)
    $vigilantes_activos_query = $conexion->query("
        SELECT COUNT(*) as total FROM usuarios u 
        JOIN usu_roles ur ON u.id = ur.usuarios_id 
        JOIN roles r ON ur.roles_idroles = r.idroles 
        WHERE r.nombre_rol = 'vigilante' 
        AND DATE(u.ultimo_acceso) >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $vigilantes_activos = $vigilantes_activos_query ? $vigilantes_activos_query->fetch_assoc()['total'] : 0;
    
    // Nuevos vigilantes este mes
    $nuevos_vigilantes_query = $conexion->query("
        SELECT COUNT(*) as total FROM usuarios u 
        JOIN usu_roles ur ON u.id = ur.usuarios_id 
        JOIN roles r ON ur.roles_idroles = r.idroles 
        WHERE r.nombre_rol = 'vigilante' 
        AND MONTH(u.fecha_registro) = MONTH(NOW()) 
        AND YEAR(u.fecha_registro) = YEAR(NOW())
    ");
    $nuevos_vigilantes = $nuevos_vigilantes_query ? $nuevos_vigilantes_query->fetch_assoc()['total'] : 0;
    
    // Vigilantes por turno (simulado)
    $vigilantes_turno_mañana = ceil($total_vigilantes * 0.4);
    $vigilantes_turno_tarde = ceil($total_vigilantes * 0.35);
    $vigilantes_turno_noche = ceil($total_vigilantes * 0.25);
    
} catch (Exception $e) {
    // Valores por defecto si hay error
    $total_vigilantes = 0;
    $vigilantes_activos = 0;
    $nuevos_vigilantes = 0;
    $vigilantes_turno_mañana = 0;
    $vigilantes_turno_tarde = 0;
    $vigilantes_turno_noche = 0;
}

include __DIR__ . "/../app/Controllers/buscar_vigilantes.php";

// Verificar que el usuario sea administrador
if(!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

$resultado = null;
$orden_actual = isset($_GET['orden']) ? $_GET['orden'] : 'reciente';

if(isset($_POST['buscar'])) {
    $valor = isset($_POST['nom']) ? $_POST['nom'] : '';
    $resultado = buscarVigilantes($valor, $orden_actual);
} else {
    $resultado = buscarVigilantes('', $orden_actual);
}

// Obtener mensajes de sesión
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, sidebarOpen: false, showForm: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vigilantes | Quintanares by Parkovisco</title>
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

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Sidebar cyberpunk */
        .cyber-sidebar {
            background: rgba(10, 10, 10, 0.98);
            backdrop-filter: blur(25px);
            border-right: 1px solid rgba(16, 185, 129, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            z-index: 40;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .cyber-sidebar.open {
            transform: translateX(0);
        }

        /* Navigation items */
        .nav-item {
            position: relative;
            margin: 4px 16px;
            border-radius: 12px;
            overflow: hidden;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 12px;
        }

        .nav-link:hover {
            color: rgba(255, 255, 255, 1);
            background: rgba(16, 185, 129, 0.1);
            transform: translateX(4px);
        }

        .nav-link.active {
            color: #10b981;
            background: rgba(16, 185, 129, 0.15);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main content */
        .main-content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
            padding-top: 60px;
            min-height: 100vh;
            position: relative;
            z-index: 2;
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

        /* Glass card */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            position: relative;
            overflow: hidden;
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

        /* Stat cards */
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(16, 185, 129, 0.1), transparent);
            transition: left 0.5s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: rgba(16, 185, 129, 0.3);
            box-shadow: 0 20px 40px rgba(16, 185, 129, 0.1);
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 24px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Cyber button */
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

        /* Form styling */
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

        /* Table styling */
        .cyber-table {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            overflow: hidden;
        }

        .cyber-table th {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
        }

        .cyber-table td {
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.8);
        }

        .cyber-table tr:hover {
            background: rgba(16, 185, 129, 0.05);
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
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 35;
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

        /* Hamburger button */
        .hamburger-button {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 8px;
            color: #10b981;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .hamburger-button:hover {
            background: rgba(16, 185, 129, 0.2);
            transform: scale(1.05);
        }

        .hamburger-icon {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .hamburger-icon.open {
            transform: rotate(90deg);
        }

        /* Alert styling */
        .alert {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
    </style>
</head>
<body class="dashboard-container" x-data="vigilantesSystem()">
    <!-- Particles Background -->
    <div id="particles-js" class="particles-container"></div>

    <!-- Cyber Header -->
    <header class="cyber-header">
        <div class="flex items-center gap-4">
            <!-- Hamburger Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="hamburger-button" :class="{ 'active': sidebarOpen }">
                <i class="fas fa-bars hamburger-icon" :class="{ 'open': sidebarOpen }"></i>
            </button>
            
            <!-- Logo -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Gestión de Vigilantes</h1>
                    <p class="text-sm text-emerald-400 font-mono">Quintanares by Parkovisco</p>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-white font-semibold"><?php echo $_SESSION['nombre'] ?? 'Administrador'; ?></p>
                <p class="text-emerald-400 text-sm font-mono"><?php echo $_SESSION['nombre_rol'] ?? 'admin'; ?></p>
            </div>
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-full flex items-center justify-center">
                <i class="fas fa-user text-white"></i>
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
                    <a href="parqueaderos.php" class="nav-link">
                        <div class="nav-icon"><i class="fas fa-parking"></i></div>
                        <span>Parqueaderos</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="gestion_vigilantes.php" class="nav-link active">
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
            <div class="mt-8">
                <a href="logout.php" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>CERRAR SESIÓN</span>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" :class="{ 'sidebar-open': sidebarOpen }">
        <div class="p-8">
            <!-- Header Section -->
            <div class="glass-card p-8 mb-8" data-aos="fade-up">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">
                            <i class="fas fa-shield-alt text-emerald-400 mr-3"></i>
                            Gestión de Vigilantes
                        </h1>
                        <p class="text-white/60 font-mono">Administra el personal de seguridad del conjunto</p>
                    </div>
                    <div class="flex gap-4">
                        <a href="generate_pdf_vigilantes.php" class="cyber-button" style="background: linear-gradient(135deg, #dc2626, #b91c1c);" target="_blank">
                            <i class="fas fa-file-pdf"></i>
                            <span>PDF General</span>
                        </a>
                        <button @click="showForm = !showForm" class="cyber-button">
                            <i class="fas fa-user-plus"></i>
                            <span>Nuevo Vigilante</span>
                        </button>
                        <a href="Administrador1.php" class="cyber-button" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                            <i class="fas fa-arrow-left"></i>
                            <span>Volver</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-emerald-400 to-emerald-600">
                        <i class="fas fa-shield-alt text-white"></i>
                    </div>
                    <div class="stat-value" x-text="totalVigilantes"><?php echo $total_vigilantes; ?></div>
                    <div class="stat-label">Total Vigilantes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-blue-400 to-blue-600">
                        <i class="fas fa-user-check text-white"></i>
                    </div>
                    <div class="stat-value" x-text="activeVigilantes"><?php echo $vigilantes_activos; ?></div>
                    <div class="stat-label">Activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-purple-400 to-purple-600">
                        <i class="fas fa-user-plus text-white"></i>
                    </div>
                    <div class="stat-value" x-text="newVigilantes"><?php echo $nuevos_vigilantes; ?></div>
                    <div class="stat-label">Nuevos Este Mes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon bg-gradient-to-br from-yellow-400 to-yellow-600">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                    <div class="stat-value" x-text="turnoActual"><?php echo $vigilantes_turno_mañana; ?></div>
                    <div class="stat-label">Turno Mañana</div>
                </div>
            </div>

            <!-- Messages -->
            <?php if($error_message): ?>
                <div class="alert alert-danger" data-aos="fade-down">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if($success_message): ?>
                <div class="alert alert-success" data-aos="fade-down">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <div x-show="showForm" x-transition class="glass-card p-8 mb-8" data-aos="fade-up">
                <h2 class="text-2xl font-bold text-white mb-6">
                    <i class="fas fa-user-plus text-emerald-400 mr-3"></i>
                    Registrar Nuevo Vigilante
                </h2>
                
                <form action="../app/Controllers/registrar_vigilante.php" method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- ID -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-id-card mr-2"></i>ID o Cédula
                            </label>
                            <input type="number" name="id" class="cyber-input" placeholder="Ingrese ID o cédula" required>
                        </div>

                        <!-- Nombre -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-user mr-2"></i>Nombre
                            </label>
                            <input type="text" name="nombre" class="cyber-input" placeholder="Ingrese nombre" required>
                        </div>

                        <!-- Apellido -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-user mr-2"></i>Apellido
                            </label>
                            <input type="text" name="apellido" class="cyber-input" placeholder="Ingrese apellido" required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                            </label>
                            <input type="email" name="email" class="cyber-input" placeholder="Ingrese correo" required>
                        </div>

                        <!-- Celular -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-mobile-alt mr-2"></i>Celular
                            </label>
                            <input type="number" name="celular" class="cyber-input" placeholder="Ingrese celular" required>
                        </div>

                        <!-- Jornada -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-clock mr-2"></i>Jornada
                            </label>
                            <select name="jornada" class="cyber-input" required>
                                <option value="">Seleccionar jornada</option>
                                <option value="Diurna">Diurna (6:00 AM - 6:00 PM)</option>
                                <option value="Nocturna">Nocturna (6:00 PM - 6:00 AM)</option>
                                <option value="Mixta">Mixta (Rotativa)</option>
                            </select>
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-lock mr-2"></i>Contraseña
                            </label>
                            <input type="password" name="contrasena" class="cyber-input" placeholder="Mínimo 8 caracteres" required>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-lock mr-2"></i>Confirmar Contraseña
                            </label>
                            <input type="password" name="confirmar_contrasena" class="cyber-input" placeholder="Confirmar contraseña" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex gap-4">
                        <button type="submit" name="registrar_vigilante" class="cyber-button">
                            <i class="fas fa-user-plus"></i>
                            <span>Registrar Vigilante</span>
                        </button>
                        <button type="button" @click="showForm = false" class="cyber-button" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Search and Filter Section -->
            <div class="glass-card p-6 mb-8" data-aos="fade-up">
                <div class="flex flex-col lg:flex-row gap-4">
                    <!-- Search Form -->
                    <form method="POST" class="flex-1">
                        <div class="flex gap-2">
                            <input type="text" name="nom" placeholder="Buscar por nombre, apellido, email o ID..." 
                                   class="cyber-input flex-1" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                            <button type="submit" name="buscar" class="cyber-button">
                                <i class="fas fa-search"></i>
                                <span>Buscar</span>
                            </button>
                        </div>
                    </form>

                    <!-- Sort Options -->
                    <div class="flex gap-2">
                        <a href="?orden=reciente" class="cyber-button <?php echo $orden_actual === 'reciente' ? 'bg-emerald-600' : ''; ?>" style="background: <?php echo $orden_actual === 'reciente' ? 'linear-gradient(135deg, #059669, #047857)' : 'linear-gradient(135deg, #6b7280, #4b5563)'; ?>;">
                            <i class="fas fa-clock"></i>
                            <span>Reciente</span>
                        </a>
                        <a href="?orden=nombre" class="cyber-button <?php echo $orden_actual === 'nombre' ? 'bg-emerald-600' : ''; ?>" style="background: <?php echo $orden_actual === 'nombre' ? 'linear-gradient(135deg, #059669, #047857)' : 'linear-gradient(135deg, #6b7280, #4b5563)'; ?>;">
                            <i class="fas fa-sort-alpha-down"></i>
                            <span>Nombre</span>
                        </a>
                        <a href="?orden=jornada" class="cyber-button <?php echo $orden_actual === 'jornada' ? 'bg-emerald-600' : ''; ?>" style="background: <?php echo $orden_actual === 'jornada' ? 'linear-gradient(135deg, #059669, #047857)' : 'linear-gradient(135deg, #6b7280, #4b5563)'; ?>;">
                            <i class="fas fa-clock"></i>
                            <span>Jornada</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Vigilantes Table -->
            <div class="glass-card overflow-hidden" data-aos="fade-up">
                <div class="overflow-x-auto">
                    <table class="cyber-table w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Celular</th>
                                <th>Jornada</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($resultado && $resultado->num_rows > 0): ?>
                                <?php while($vigilante = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td class="font-mono text-emerald-400"><?php echo htmlspecialchars($vigilante['id']); ?></td>
                                        <td><?php echo htmlspecialchars($vigilante['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($vigilante['apellido']); ?></td>
                                        <td class="font-mono text-sm"><?php echo htmlspecialchars($vigilante['email']); ?></td>
                                        <td class="font-mono"><?php echo htmlspecialchars($vigilante['celular']); ?></td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                                <?php 
                                                switch($vigilante['jornada']) {
                                                    case 'Diurna': echo 'bg-yellow-500/20 text-yellow-400'; break;
                                                    case 'Nocturna': echo 'bg-blue-500/20 text-blue-400'; break;
                                                    case 'Mixta': echo 'bg-purple-500/20 text-purple-400'; break;
                                                    default: echo 'bg-gray-500/20 text-gray-400';
                                                }
                                                ?>">
                                                <?php echo htmlspecialchars($vigilante['jornada'] ?? 'No asignada'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-2">
                                                <button class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm transition-colors">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <a href="generate_pdf_vigilantes.php?id=<?php echo $vigilante['id']; ?>" 
                                                   class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors" 
                                                   title="Generar PDF" target="_blank">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-white/60 py-8">
                                        <i class="fas fa-shield-alt text-4xl mb-4 block"></i>
                                        No se encontraron vigilantes registrados
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        function vigilantesSystem() {
            return {
                sidebarOpen: false,
                showForm: true,
                totalVigilantes: 0,
                activeVigilantes: 0,
                newVigilantes: 0,
                turnoActual: 0,
                
                init() {
                    this.initParticles();
                    this.animateStats();
                    AOS.init({
                        duration: 800,
                        easing: 'ease-out-cubic',
                        once: true
                    });
                },
                
                animateStats() {
                    // Animate stats counters
                    const duration = 2000;
                    const start = Date.now();
                    const totalTarget = <?php echo $total_vigilantes; ?>;
                    const activeTarget = <?php echo $vigilantes_activos; ?>;
                    const newTarget = <?php echo $nuevos_vigilantes; ?>;
                    const turnoTarget = <?php echo $vigilantes_turno_mañana; ?>;
                    
                    const animate = () => {
                        const now = Date.now();
                        const elapsed = now - start;
                        const progress = Math.min(elapsed / duration, 1);
                        
                        this.totalVigilantes = Math.floor(totalTarget * progress);
                        this.activeVigilantes = Math.floor(activeTarget * progress);
                        this.newVigilantes = Math.floor(newTarget * progress);
                        this.turnoActual = Math.floor(turnoTarget * progress);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    };
                    
                    animate();
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
    
    <!-- Script de notificaciones al final -->
    <script src="../resources/js/notifications.js"></script>
</body>
</html>
