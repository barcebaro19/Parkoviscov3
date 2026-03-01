<?php
session_start();
require_once __DIR__ . "/../app/Models/conexion.php";

// Verificar que el usuario sea administrador
if(!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

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

// Obtener el ID del usuario a modificar
$id = $_GET['id'];

// Consultar los datos del usuario
$sql = "SELECT u.*, ur.contraseña 
        FROM usuarios u 
        INNER JOIN usu_roles ur ON u.id = ur.usuarios_id 
        WHERE u.id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado && $resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();
} else {
    echo "Error al obtener los datos del usuario";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, sidebarOpen: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario | Quintanares by Parkovisco</title>
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

        .cyber-input:disabled {
            background: rgba(255, 255, 255, 0.02);
            color: rgba(255, 255, 255, 0.4);
            cursor: not-allowed;
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
    </style>
</head>
<body class="dashboard-container" x-data="modificarSystem()">
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
                    <i class="fas fa-user-edit text-white text-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white">Modificar Usuario</h1>
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
                            <i class="fas fa-user-edit text-emerald-400 mr-3"></i>
                            Modificar Usuario
                        </h1>
                        <p class="text-white/60 font-mono">Actualice los campos permitidos del usuario</p>
                    </div>
                    <div class="flex gap-4">
                        <a href="tablausu.php" class="cyber-button" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                            <i class="fas fa-arrow-left"></i>
                            <span>Volver</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <div class="glass-card p-8" data-aos="fade-up">
                <form action="../app/Controllers/modificar_usuario.php" method="POST" class="space-y-6">
                    <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                    <input type="hidden" name="btnmodificar" value="ok">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- ID (Read Only) -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-id-card mr-2"></i>ID
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['id']); ?>" 
                                   class="cyber-input" disabled>
                        </div>

                        <!-- Nombre (Read Only) -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-user mr-2"></i>Nombre
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" 
                                   class="cyber-input" disabled>
                        </div>

                        <!-- Apellido (Read Only) -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-user mr-2"></i>Apellido
                            </label>
                            <input type="text" value="<?php echo htmlspecialchars($usuario['apellido']); ?>" 
                                   class="cyber-input" disabled>
                        </div>

                        <!-- Email (Editable) -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email
                            </label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" 
                                   class="cyber-input" required>
                        </div>

                        <!-- Celular (Editable) -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-mobile-alt mr-2"></i>Celular
                            </label>
                            <input type="tel" name="celular" value="<?php echo htmlspecialchars($usuario['celular']); ?>" 
                                   class="cyber-input" required>
                        </div>

                        <!-- Contraseña (Editable) -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-lock mr-2"></i>Contraseña
                            </label>
                            <input type="text" name="contraseña" value="<?php echo htmlspecialchars($usuario['contraseña']); ?>" 
                                   class="cyber-input" required>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-4 pt-6">
                        <button type="submit" class="cyber-button">
                            <i class="fas fa-save"></i>
                            <span>Guardar Cambios</span>
                        </button>
                        <a href="tablausu.php" class="cyber-button" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-black/50 backdrop-blur-lg border-t border-emerald-500/30 py-8 mt-16">
        <div class="max-w-7xl mx-auto px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Información de la empresa -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-home text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">Quintanares Residencial</h3>
                            <p class="text-emerald-400 text-sm font-mono">by Parkovisco</p>
                        </div>
                    </div>
                    <p class="text-white/70 text-sm">
                        Sistema de gestión integral para conjuntos residenciales. 
                        Administración moderna y eficiente de parqueaderos, usuarios y seguridad.
                    </p>
                </div>

                <!-- Enlaces rápidos -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-emerald-400">Enlaces Rápidos</h4>
                    <div class="space-y-2">
                        <a href="Administrador1.php" class="block text-white/70 hover:text-emerald-400 transition-colors">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="tablausu.php" class="block text-white/70 hover:text-emerald-400 transition-colors">
                            <i class="fas fa-users mr-2"></i>Usuarios
                        </a>
                        <a href="parqueaderos.php" class="block text-white/70 hover:text-emerald-400 transition-colors">
                            <i class="fas fa-parking mr-2"></i>Parqueaderos
                        </a>
                        <a href="gestion_vigilantes.php" class="block text-white/70 hover:text-emerald-400 transition-colors">
                            <i class="fas fa-shield-alt mr-2"></i>Vigilantes
                        </a>
                    </div>
                </div>

                <!-- Información de contacto -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-emerald-400">Contacto</h4>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-map-marker-alt text-emerald-400"></i>
                            <span class="text-white/70 text-sm">Calle 123 #45-67, Bogotá, Colombia</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-phone text-emerald-400"></i>
                            <span class="text-white/70 text-sm">(601) 123-4567</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-envelope text-emerald-400"></i>
                            <span class="text-white/70 text-sm">info@quintanares.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Línea separadora -->
            <div class="border-t border-emerald-500/30 mt-8 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-white/60 text-sm">
                        © 2025 Quintanares Residencial. Todos los derechos reservados.
                    </p>
                    <div class="flex items-center gap-4">
                        <span class="text-white/60 text-sm">Desarrollado por</span>
                        <span class="text-emerald-400 font-semibold">Parkovisco</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function modificarSystem() {
            return {
                sidebarOpen: false,
                
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
    
    <!-- Script de notificaciones al final -->
    <script src="../resources/js/notifications.js"></script>
</body>
</html> 