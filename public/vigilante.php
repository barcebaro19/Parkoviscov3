<?php
session_start();
if(!isset($_SESSION['nombre']) || $_SESSION['nombre_rol'] !== 'vigilante') {
    header('Location: ./login.php');
    exit();
}

// Simular datos en tiempo real (en producción esto vendría de la base de datos)
$estadisticas = [
    'vehiculos_actuales' => 45,
    'espacios_libres' => 28,
    'espacios_visitantes' => 15,
    'espacios_usuarios' => 32,
    'total_espacios' => 75,
    'ocupacion_porcentaje' => 62.6,
    'alertas_activas' => 3,
    'incidencias_hoy' => 2,
    'visitantes_hoy' => 18,
    'pico_ocupacion' => 85.2
];

$ultimos_movimientos = [
    ['placa' => 'ABC-123', 'tipo' => 'entrada', 'tiempo' => '5 min', 'apartamento' => '301-A', 'conductor' => 'Juan Pérez'],
    ['placa' => 'XYZ-789', 'tipo' => 'salida', 'tiempo' => '12 min', 'apartamento' => '205-B', 'conductor' => 'María García'],
    ['placa' => 'DEF-456', 'tipo' => 'entrada', 'tiempo' => '18 min', 'apartamento' => 'Visitante', 'conductor' => 'Carlos López'],
    ['placa' => 'GHI-321', 'tipo' => 'salida', 'tiempo' => '25 min', 'apartamento' => '102-C', 'conductor' => 'Ana Rodríguez'],
    ['placa' => 'JKL-654', 'tipo' => 'entrada', 'tiempo' => '32 min', 'apartamento' => '404-D', 'conductor' => 'Luis Martín']
];

$alertas_criticas = [
    ['tipo' => 'Vehículo sospechoso en zona B', 'prioridad' => 'alta', 'tiempo' => '2 min'],
    ['tipo' => 'Espacio reservado ocupado', 'prioridad' => 'media', 'tiempo' => '15 min'],
    ['tipo' => 'Cámara 7 desconectada', 'prioridad' => 'baja', 'tiempo' => '1h']
];
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛡️ Centro de Comando - Vigilancia | Quintanares by Parkovisco</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/@zxing/library@latest"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <script src="https://unpkg.com/typed.js@2.0.16/dist/typed.umd.js"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'Consolas', 'monospace'],
                        'display': ['Orbitron', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 3s infinite',
                        'spin-slow': 'spin 3s linear infinite',
                        'gradient': 'gradient 15s ease infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                        'matrix': 'matrix 20s linear infinite',
                        'scan': 'scan 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        gradient: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        },
                        shimmer: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 5px rgba(59, 130, 246, 0.5)' },
                            '100%': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.8), 0 0 30px rgba(59, 130, 246, 0.6)' },
                        },
                        matrix: {
                            '0%': { transform: 'translateY(-100%)' },
                            '100%': { transform: 'translateY(100vh)' },
                        },
                        scan: {
                            '0%': { transform: 'translateY(-100%)' },
                            '100%': { transform: 'translateY(100%)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Tema futurista cyberpunk */
        .dashboard-container {
            background: 
                radial-gradient(ellipse at top, rgba(16, 185, 129, 0.1) 0%, transparent 70%),
                radial-gradient(ellipse at bottom, rgba(59, 130, 246, 0.1) 0%, transparent 70%),
                linear-gradient(135deg, 
                    #0a0a0a 0%, 
                    #1a1a2e 25%, 
                    #16213e 50%, 
                    #0f3460 75%, 
                    #533483 100%);
            background-size: 400% 400%;
            animation: gradient 20s ease infinite;
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
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(236, 72, 153, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(59, 130, 246, 0.15) 0%, transparent 50%);
            pointer-events: none;
            z-index: 1;
        }

        /* Glassmorphism premium */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                inset 0 -1px 0 rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            transition: left 0.8s;
        }

        .glass-card:hover::before {
            left: 100%;
        }

        .glass-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(59, 130, 246, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        /* Header comando */
        .command-header {
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

        /* Botón hamburguesa mejorado */
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

        .hamburger-button.active .hamburger-icon {
            color: #10b981;
        }

        .hamburger-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(16, 185, 129, 0.2),
                transparent
            );
            transition: left 0.6s;
        }

        .hamburger-button:hover::before {
            left: 100%;
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
            position: relative;
            z-index: 2;
        }

        .hamburger-button:hover .hamburger-icon {
            color: #10b981;
            transform: scale(1.1);
        }

        .hamburger-icon.open {
            transform: rotate(90deg);
        }

        /* Tooltip para el botón hamburguesa */
        .hamburger-button[title]:hover::after {
            content: attr(title);
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(10, 10, 10, 0.95);
            color: white;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 1000;
            border: 1px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .command-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(16, 185, 129, 0.8),
                rgba(59, 130, 246, 0.8),
                rgba(236, 72, 153, 0.8),
                transparent
            );
            animation: shimmer 3s linear infinite;
        }

        /* Sidebar comando */
        .command-sidebar {
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
            scrollbar-width: thin;
            scrollbar-color: rgba(16, 185, 129, 0.5) transparent;
        }

        .command-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .command-sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .command-sidebar::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.5);
            border-radius: 3px;
        }

        .command-sidebar.open {
            transform: translateX(0);
        }

        .command-sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(
                to bottom,
                transparent,
                rgba(16, 185, 129, 1),
                rgba(59, 130, 246, 1),
                rgba(236, 72, 153, 1),
                transparent
            );
            animation: float 6s ease-in-out infinite;
        }

        /* Navegación comando */
        .nav-item {
            position: relative;
            margin: 4px 16px;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(16, 185, 129, 0.2),
                transparent
            );
            transition: left 0.8s;
        }

        .nav-item:hover::before {
            left: 100%;
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
            background: rgba(16, 185, 129, 0.2);
            box-shadow: 
                inset 0 1px 0 rgba(255, 255, 255, 0.1),
                0 0 25px rgba(16, 185, 129, 0.4);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 70%;
            background: linear-gradient(to bottom, #10b981, #3b82f6);
            border-radius: 0 2px 2px 0;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.8);
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

        .nav-link:hover .nav-icon {
            transform: scale(1.3) rotate(10deg);
            color: #10b981;
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

        /* Stats cards futuristas */
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

        .stat-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(
                from 0deg,
                transparent,
                rgba(16, 185, 129, 0.1),
                transparent,
                rgba(59, 130, 246, 0.1),
                transparent
            );
            animation: spin-slow 25s linear infinite;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card:hover {
            transform: translateY(-12px) scale(1.05);
            box-shadow: 
                0 30px 60px rgba(0, 0, 0, 0.6),
                0 0 100px rgba(16, 185, 129, 0.4);
            border-color: rgba(16, 185, 129, 0.5);
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
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            animation: glow 2s ease-in-out infinite alternate;
            transform: scale(1.1);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #ffffff;
            margin-bottom: 8px;
            font-family: 'Orbitron', monospace;
            position: relative;
            z-index: 2;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        /* Botones comando */
        .command-button {
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

        .command-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: left 0.8s;
        }

        .command-button:hover::before {
            left: 100%;
        }

        .command-button:hover {
            transform: translateY(-4px);
            box-shadow: 0 15px 40px rgba(16, 185, 129, 0.5);
            background: linear-gradient(135deg, #059669, #2563eb);
        }

        .command-button:active {
            transform: translateY(-2px);
        }

        /* Efectos de texto */
        .text-gradient {
            background: linear-gradient(135deg, #10b981, #3b82f6, #ec4899);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: gradient 4s ease infinite;
        }

        .text-glow {
            text-shadow: 0 0 20px rgba(16, 185, 129, 0.8);
        }

        .text-cyber {
            font-family: 'Orbitron', monospace;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        /* Barras de progreso futuristas */
        .progress-bar {
            width: 100%;
            height: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 6px;
            overflow: hidden;
            position: relative;
            margin-top: 16px;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #3b82f6);
            border-radius: 6px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.5);
        }

        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.4),
                transparent
            );
            animation: shimmer 2.5s linear infinite;
        }

        /* Toggle switch futurista */
        .cyber-toggle {
            position: relative;
            width: 70px;
            height: 35px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            cursor: pointer;
            transition: all 0.4s ease;
        }

        .cyber-toggle::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 3px;
            width: 27px;
            height: 27px;
            background: #ffffff;
            border-radius: 50%;
            transition: all 0.4s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .cyber-toggle.active {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.5);
        }

        .cyber-toggle.active::after {
            transform: translateX(35px);
        }

        /* Notificaciones premium */
        .notification {
            position: fixed;
            top: 100px;
            right: 2rem;
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            padding: 24px 28px;
            color: white;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.6);
            z-index: 60;
            transform: translateX(500px);
            transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 350px;
            max-width: 500px;
        }

        .notification.show {
            transform: translateX(0);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content.sidebar-open {
                margin-left: 0;
            }
            
            .command-sidebar {
                width: 100vw;
                z-index: 55;
            }
        }

        /* Overlay para móvil */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            z-index: 40;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Efectos de partículas */
        .particles-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }

        /* Scrollbar personalizada */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #059669, #2563eb);
        }

        /* Animaciones de entrada */
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Efecto matrix */
        .matrix-text {
            color: #10b981;
            font-family: 'JetBrains Mono', monospace;
            text-shadow: 0 0 10px #10b981;
        }

        /* Scan lines effect */
        .scan-lines {
            position: relative;
            overflow: hidden;
        }

        .scan-lines::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, #10b981, transparent);
            animation: scan 3s ease-in-out infinite;
            z-index: 10;
        }
    </style>
</head>
<body class="dashboard-container" x-data="vigilanteApp()">
    <!-- Particles Background -->
    <div id="particles-js" class="particles-container"></div>

    <!-- Command Header -->
    <header class="command-header">
        <div class="flex items-center gap-6">
            <!-- Botón hamburguesa siempre visible -->
            <button @click="toggleSidebar" class="hamburger-button" :class="{ 'active': sidebarOpen }" :title="sidebarOpen ? 'Cerrar menú lateral' : 'Abrir menú lateral'">
                <i class="fas fa-bars hamburger-icon" :class="{ 'open': sidebarOpen }"></i>
            </button>
            
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-shield-alt text-xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-cyber text-gradient">CENTRO DE COMANDO</h1>
                    <p class="text-xs text-white/60 font-mono" x-text="'QUINTANARES BY PARKOVISCO SECURITY • ' + currentTime"></p>
                </div>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Status Indicator -->
            <div class="flex items-center gap-2 bg-emerald-500/20 px-4 py-2 rounded-full border border-emerald-500/40 shadow-lg shadow-emerald-500/25">
                <div class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse shadow-lg shadow-emerald-400/50"></div>
                <span class="text-emerald-400 font-bold text-xs text-cyber">OPERATIVO</span>
            </div>
            
            <!-- Alerts -->
            <div class="relative">
                <button @click="showAlerts = !showAlerts" class="relative p-2 rounded-xl hover:bg-white/10 transition-all duration-300 group">
                    <i class="fas fa-bell text-lg text-white group-hover:text-emerald-400 transition-colors"></i>
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center animate-pulse shadow-lg shadow-red-500/50 font-bold">
                        <?php echo $estadisticas['alertas_activas']; ?>
                    </span>
                </button>
            </div>
            
            <!-- Theme Toggle -->
            <div class="cyber-toggle" :class="{ 'active': darkMode }" @click="darkMode = !darkMode">
            </div>
            
            <!-- Emergency Button -->
            <button @click="activarEmergencia" class="px-4 py-2 bg-red-500/20 border border-red-500/40 rounded-xl text-red-400 font-bold hover:bg-red-500/30 transition-all duration-300 animate-pulse shadow-lg shadow-red-500/25 text-cyber text-xs">
                <i class="fas fa-exclamation-triangle mr-2 text-sm"></i>
                EMERGENCIA
            </button>
        </div>
    </header>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" :class="{ 'active': sidebarOpen }" @click="toggleSidebar"></div>

    <!-- Command Sidebar -->
    <aside class="command-sidebar" :class="{ 'open': sidebarOpen }">
        <div class="p-6 pt-16">
            <!-- Profile Section -->
            <div class="glass-card p-4 mb-6 scan-lines">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/25">
                        <i class="fas fa-user-shield text-lg text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white text-cyber">OFICIAL DE SEGURIDAD</h3>
                        <p class="text-xs text-white/70 font-mono">TURNO: 08:00 - 16:00 HRS</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse shadow-lg shadow-emerald-400/50"></div>
                            <span class="text-xs text-emerald-400 font-bold text-cyber">ACTIVO</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="space-y-3">
                <div class="nav-item">
                    <a href="#" class="nav-link active" @click="activeSection = 'dashboard'" :class="{ 'active': activeSection === 'dashboard' }">
                        <div class="nav-icon">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <span class="text-cyber">CENTRO DE CONTROL</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link" @click="activeSection = 'entrada'" :class="{ 'active': activeSection === 'entrada' }">
                        <div class="nav-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <span class="text-cyber">CONTROL DE ENTRADA</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link" @click="activeSection = 'salida'" :class="{ 'active': activeSection === 'salida' }">
                        <div class="nav-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <span class="text-cyber">CONTROL DE SALIDA</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link" @click="activeSection = 'visitantes'" :class="{ 'active': activeSection === 'visitantes' }">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="text-cyber">GESTIÓN VISITANTES</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link" @click="activeSection = 'monitoreo'" :class="{ 'active': activeSection === 'monitoreo' }">
                        <div class="nav-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <span class="text-cyber">MONITOREO EN VIVO</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link" @click="activeSection = 'incidencias'" :class="{ 'active': activeSection === 'incidencias' }">
                        <div class="nav-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <span class="text-cyber">REPORTES INCIDENCIAS</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="#" class="nav-link" @click="activeSection = 'analytics'" :class="{ 'active': activeSection === 'analytics' }">
                        <div class="nav-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="text-cyber">ANÁLISIS & REPORTES</span>
                    </a>
                </div>
            </nav>

            <!-- Emergency Contacts -->
            <div class="glass-card p-4 mt-6">
                <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-2 text-cyber">
                    <i class="fas fa-phone text-red-400 text-xs"></i>
                    CONTACTOS EMERGENCIA
                </h4>
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between items-center p-2 bg-white/5 rounded-lg">
                        <span class="text-white/70">ADMIN:</span>
                        <span class="text-white font-mono font-bold">123-456-7890</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-white/5 rounded-lg">
                        <span class="text-white/70">POLICÍA:</span>
                        <span class="text-white font-mono font-bold">123</span>
                    </div>
                    <div class="flex justify-between items-center p-2 bg-white/5 rounded-lg">
                        <span class="text-white/70">BOMBEROS:</span>
                        <span class="text-white font-mono font-bold">119</span>
                    </div>
                </div>
            </div>

            <!-- Logout -->
            <div class="mt-6">
                <button @click="cerrarSesion" class="w-full command-button bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 justify-center">
                    <i class="fas fa-sign-out-alt text-sm"></i>
                    <span>CERRAR SESIÓN</span>
                </button>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" :class="{ 'sidebar-open': sidebarOpen }">
        <!-- Dashboard Section -->
        <div x-show="activeSection === 'dashboard'" x-transition class="space-y-6">
            <!-- Hero Section -->
            <div class="glass-card p-10 scan-lines" data-aos="fade-up">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-5xl font-bold text-white mb-4 text-cyber">
                            BIENVENIDO AL <span class="text-gradient">CENTRO DE COMANDO</span>
                        </h1>
                        <p class="text-2xl text-white/80 mb-2" x-text="welcomeMessage"></p>
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

            <!-- Real-time Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" data-aos="fade-up" data-aos-delay="100">
                <!-- Vehículos Actuales -->
                <div class="stat-card" data-aos="zoom-in" data-aos-delay="200">
                    <div class="stat-icon bg-gradient-to-br from-emerald-400 to-emerald-600">
                        <i class="fas fa-car text-white"></i>
                    </div>
                    <div class="stat-value" x-text="stats.vehiculos_actuales"></div>
                    <div class="stat-label text-cyber">VEHÍCULOS ACTUALES</div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="`width: ${(stats.vehiculos_actuales / stats.total_espacios) * 100}%`"></div>
                    </div>
                </div>

                <!-- Espacios Libres -->
                <div class="stat-card" data-aos="zoom-in" data-aos-delay="300">
                    <div class="stat-icon bg-gradient-to-br from-blue-400 to-blue-600">
                        <i class="fas fa-parking text-white"></i>
                    </div>
                    <div class="stat-value" x-text="stats.espacios_libres"></div>
                    <div class="stat-label text-cyber">ESPACIOS DISPONIBLES</div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="`width: ${(stats.espacios_libres / stats.total_espacios) * 100}%`"></div>
                    </div>
                </div>

                <!-- Visitantes Hoy -->
                <div class="stat-card" data-aos="zoom-in" data-aos-delay="400">
                    <div class="stat-icon bg-gradient-to-br from-purple-400 to-purple-600">
                        <i class="fas fa-users text-white"></i>
                    </div>
                    <div class="stat-value" x-text="stats.visitantes_hoy"></div>
                    <div class="stat-label text-cyber">VISITANTES HOY</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 75%"></div>
                    </div>
                </div>

                <!-- Ocupación -->
                <div class="stat-card" data-aos="zoom-in" data-aos-delay="500">
                    <div class="stat-icon bg-gradient-to-br from-amber-400 to-amber-600">
                        <i class="fas fa-percentage text-white"></i>
                    </div>
                    <div class="stat-value" x-text="stats.ocupacion_porcentaje + '%'"></div>
                    <div class="stat-label text-cyber">OCUPACIÓN ACTUAL</div>
                    <div class="progress-bar">
                        <div class="progress-fill" :style="`width: ${stats.ocupacion_porcentaje}%`"></div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6" data-aos="fade-up" data-aos-delay="300">
                <!-- Quick Actions -->
                <div class="glass-card p-6">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-3 text-cyber">
                        <i class="fas fa-bolt text-yellow-400 text-sm"></i>
                        ACCIONES RÁPIDAS
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <button @click="abrirRegistroEntrada" class="command-button bg-gradient-to-r from-emerald-500 to-emerald-600 justify-center text-center">
                            <i class="fas fa-plus text-sm"></i>
                            <span>ENTRADA</span>
                        </button>
                        <button @click="abrirRegistroSalida" class="command-button bg-gradient-to-r from-red-500 to-red-600 justify-center text-center">
                            <i class="fas fa-minus text-sm"></i>
                            <span>SALIDA</span>
                        </button>
                        <button @click="abrirEscanerQR" class="command-button bg-gradient-to-r from-blue-500 to-blue-600 justify-center text-center">
                            <i class="fas fa-qrcode text-sm"></i>
                            <span>ESCANEAR QR</span>
                        </button>
                        <button @click="reportarIncidencia" class="command-button bg-gradient-to-r from-yellow-500 to-yellow-600 justify-center text-center">
                            <i class="fas fa-exclamation-triangle text-sm"></i>
                            <span>INCIDENCIA</span>
                        </button>
                    </div>
                </div>

                <!-- Real-time Activity Feed -->
                <div class="glass-card p-6">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-3 text-cyber">
                        <i class="fas fa-stream text-blue-400 text-sm"></i>
                        ACTIVIDAD EN TIEMPO REAL
                    </h2>
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        <?php foreach($ultimos_movimientos as $index => $movimiento): ?>
                        <div class="flex items-center gap-4 p-6 bg-white/5 rounded-2xl hover:bg-white/10 transition-all duration-300 border border-white/10" data-aos="slide-left" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="w-16 h-16 rounded-2xl flex items-center justify-center <?php echo $movimiento['tipo'] == 'entrada' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'; ?>">
                                <i class="fas <?php echo $movimiento['tipo'] == 'entrada' ? 'fa-arrow-right' : 'fa-arrow-left'; ?> text-xl"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="font-bold text-white text-lg text-cyber"><?php echo $movimiento['placa']; ?></span>
                                    <span class="text-xs text-white/50 font-mono">HACE <?php echo strtoupper($movimiento['tiempo']); ?></span>
                                </div>
                                <div class="text-sm text-white/80 font-semibold"><?php echo $movimiento['conductor']; ?></div>
                                <div class="text-xs text-white/60 font-mono"><?php echo $movimiento['apartamento']; ?></div>
                            </div>
                            <div class="px-4 py-2 rounded-full text-xs font-bold text-cyber <?php echo $movimiento['tipo'] == 'entrada' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'; ?>">
                                <?php echo strtoupper($movimiento['tipo']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Advanced Analytics Dashboard -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" data-aos="fade-up" data-aos-delay="400">
                <!-- Occupancy Chart -->
                <div class="glass-card p-8">
                    <h2 class="text-3xl font-bold text-white mb-8 flex items-center gap-4 text-cyber">
                        <i class="fas fa-chart-area text-purple-400 text-2xl"></i>
                        ESTADO DEL PARQUEADERO
                    </h2>
                    <div class="relative h-80">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                </div>

                <!-- Critical Alerts -->
                <div class="glass-card p-8">
                    <h2 class="text-3xl font-bold text-white mb-8 flex items-center gap-4 text-cyber">
                        <i class="fas fa-exclamation-triangle text-red-400 text-2xl"></i>
                        ALERTAS CRÍTICAS
                    </h2>
                    <div class="space-y-6">
                        <?php foreach($alertas_criticas as $alerta): ?>
                        <div class="p-6 bg-red-500/10 border-2 border-red-500/30 rounded-2xl hover:bg-red-500/20 transition-all duration-300">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-bold text-red-400 text-lg mb-2 text-cyber"><?php echo strtoupper($alerta['tipo']); ?></h4>
                                    <p class="text-sm text-white/70 font-mono">REPORTADO HACE <?php echo strtoupper($alerta['tiempo']); ?></p>
                                </div>
                                <span class="px-4 py-2 text-xs font-bold rounded-full text-cyber
                                    <?php 
                                    switch($alerta['prioridad']) {
                                        case 'alta': echo 'bg-red-500/30 text-red-400 border border-red-500/50'; break;
                                        case 'media': echo 'bg-yellow-500/30 text-yellow-400 border border-yellow-500/50'; break;
                                        default: echo 'bg-blue-500/30 text-blue-400 border border-blue-500/50';
                                    }
                                    ?>">
                                    PRIORIDAD <?php echo strtoupper($alerta['prioridad']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Otras secciones se cargarán dinámicamente -->
        <div x-show="activeSection === 'entrada'" x-transition class="space-y-8">
            <div class="glass-card p-8">
                <h1 class="text-4xl font-bold text-white mb-6 text-cyber">CONTROL DE ENTRADA</h1>
                
                <!-- Formulario de entrada -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Registro de Entrada</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Placa del Vehículo</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="ABC-123">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Tipo de Usuario</label>
                                <select class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                                    <option>Propietario</option>
                                    <option>Visitante</option>
                                    <option>Servicio</option>
                                    <option>Delivery</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Apartamento/Destino</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="301-A">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Nombre del Conductor</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="Juan Pérez">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Documento</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="12345678">
                            </div>
                            
                            <button class="btn btn-primary w-full bg-cyan-600 hover:bg-cyan-700 border-cyan-500">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Registrar Entrada
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Entradas Recientes</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div>
                                    <div class="text-white font-medium">ABC-123</div>
                                    <div class="text-white/60 text-sm">Juan Pérez - 301-A</div>
                                </div>
                                <div class="text-cyan-400 text-sm">Hace 5 min</div>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div>
                                    <div class="text-white font-medium">XYZ-789</div>
                                    <div class="text-white/60 text-sm">María García - 205-B</div>
                                </div>
                                <div class="text-cyan-400 text-sm">Hace 12 min</div>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div>
                                    <div class="text-white font-medium">DEF-456</div>
                                    <div class="text-white/60 text-sm">Carlos López - Visitante</div>
                                </div>
                                <div class="text-cyan-400 text-sm">Hace 18 min</div>
                            </div>
                        </div>
                        
                        <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                            <div class="stat">
                                <div class="stat-title text-white/60">Entradas Hoy</div>
                                <div class="stat-value text-cyan-400">47</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">En Parqueadero</div>
                                <div class="stat-value text-green-400">32</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeSection === 'salida'" x-transition class="space-y-8">
            <div class="glass-card p-8">
                <h1 class="text-4xl font-bold text-white mb-6 text-cyber">CONTROL DE SALIDA</h1>
                
                <!-- Formulario de salida -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Registro de Salida</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Placa del Vehículo</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="ABC-123">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Tiempo de Permanencia</label>
                                <div class="text-2xl font-mono text-cyan-400 bg-white/5 p-4 rounded-lg border border-cyan-500/20">
                                    02:45:30
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Tarifa Aplicada</label>
                                <div class="text-xl font-semibold text-green-400 bg-white/5 p-3 rounded-lg border border-green-500/20">
                                    $8,500 COP
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Método de Pago</label>
                                <select class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                                    <option>Efectivo</option>
                                    <option>Tarjeta</option>
                                    <option>Transferencia</option>
                                    <option>Pago Móvil</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Observaciones</label>
                                <textarea class="textarea textarea-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="Observaciones adicionales..."></textarea>
                            </div>
                            
                            <button class="btn btn-primary w-full bg-red-600 hover:bg-red-700 border-red-500">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Registrar Salida
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Salidas Recientes</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div>
                                    <div class="text-white font-medium">XYZ-789</div>
                                    <div class="text-white/60 text-sm">María García - 2h 15min</div>
                                </div>
                                <div class="text-green-400 text-sm">$6,500</div>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div>
                                    <div class="text-white font-medium">GHI-321</div>
                                    <div class="text-white/60 text-sm">Ana Rodríguez - 1h 45min</div>
                                </div>
                                <div class="text-green-400 text-sm">$5,200</div>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div>
                                    <div class="text-white font-medium">JKL-654</div>
                                    <div class="text-white/60 text-sm">Luis Martín - 3h 20min</div>
                                </div>
                                <div class="text-green-400 text-sm">$9,800</div>
                            </div>
                        </div>
                        
                        <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                            <div class="stat">
                                <div class="stat-title text-white/60">Salidas Hoy</div>
                                <div class="stat-value text-red-400">23</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Recaudado</div>
                                <div class="stat-value text-green-400">$187,500</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeSection === 'visitantes'" x-transition class="space-y-8">
            <div class="glass-card p-8">
                <h1 class="text-4xl font-bold text-white mb-6 text-cyber">GESTIÓN DE VISITANTES</h1>
                
                <!-- Gestión de visitantes -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Visitantes Activos</h2>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-cyan-500/20 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-cyan-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">Carlos López</div>
                                        <div class="text-white/60 text-sm">Visitante - Apartamento 301-A</div>
                                        <div class="text-white/40 text-xs">Entrada: 14:30 - Placa: DEF-456</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-cyan-400 text-sm">2h 15min</div>
                                    <button class="btn btn-sm bg-cyan-600 hover:bg-cyan-700 border-cyan-500 mt-2">
                                        <i class="fas fa-eye mr-1"></i>
                                        Ver QR
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center">
                                        <i class="fas fa-tools text-green-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">Roberto Díaz</div>
                                        <div class="text-white/60 text-sm">Técnico - Servicio de Gas</div>
                                        <div class="text-white/40 text-xs">Entrada: 15:45 - Placa: GHI-789</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-green-400 text-sm">1h 30min</div>
                                    <button class="btn btn-sm bg-green-600 hover:bg-green-700 border-green-500 mt-2">
                                        <i class="fas fa-check mr-1"></i>
                                        Autorizado
                                    </button>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-yellow-500/20 rounded-full flex items-center justify-center">
                                        <i class="fas fa-shopping-bag text-yellow-400"></i>
                                    </div>
                                    <div>
                                        <div class="text-white font-medium">Laura Vega</div>
                                        <div class="text-white/60 text-sm">Delivery - Rappi</div>
                                        <div class="text-white/40 text-xs">Entrada: 16:20 - Placa: JKL-012</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-yellow-400 text-sm">45min</div>
                                    <button class="btn btn-sm bg-yellow-600 hover:bg-yellow-700 border-yellow-500 mt-2">
                                        <i class="fas fa-clock mr-1"></i>
                                        En Proceso
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Estadísticas</h2>
                        
                        <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                            <div class="stat">
                                <div class="stat-title text-white/60">Visitantes Hoy</div>
                                <div class="stat-value text-cyan-400">18</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Activos</div>
                                <div class="stat-value text-green-400">3</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Técnicos</div>
                                <div class="stat-value text-blue-400">2</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Deliveries</div>
                                <div class="stat-value text-yellow-400">5</div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-cyan-400">Acciones Rápidas</h3>
                            
                            <button class="btn btn-outline w-full border-cyan-500 text-cyan-400 hover:bg-cyan-500 hover:text-white">
                                <i class="fas fa-qrcode mr-2"></i>
                                Generar QR Visitante
                            </button>
                            
                            <button class="btn btn-outline w-full border-green-500 text-green-400 hover:bg-green-500 hover:text-white">
                                <i class="fas fa-user-plus mr-2"></i>
                                Registrar Visitante
                            </button>
                            
                            <button class="btn btn-outline w-full border-yellow-500 text-yellow-400 hover:bg-yellow-500 hover:text-white">
                                <i class="fas fa-list mr-2"></i>
                                Ver Historial
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeSection === 'monitoreo'" x-transition class="space-y-8">
            <div class="glass-card p-8">
                <h1 class="text-4xl font-bold text-white mb-6 text-cyber">MONITOREO EN VIVO</h1>
                
                <!-- Sistema de monitoreo -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Cámaras de Seguridad</h2>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/50 rounded-lg border border-cyan-500/20 p-4">
                                <div class="text-center text-white/60 mb-2">Cámara 1 - Entrada Principal</div>
                                <div class="bg-gray-800 h-32 rounded flex items-center justify-center">
                                    <i class="fas fa-video text-cyan-400 text-2xl"></i>
                                </div>
                                <div class="text-green-400 text-sm mt-2 text-center">● EN VIVO</div>
                            </div>
                            
                            <div class="bg-black/50 rounded-lg border border-cyan-500/20 p-4">
                                <div class="text-center text-white/60 mb-2">Cámara 2 - Parqueadero A</div>
                                <div class="bg-gray-800 h-32 rounded flex items-center justify-center">
                                    <i class="fas fa-video text-cyan-400 text-2xl"></i>
                                </div>
                                <div class="text-green-400 text-sm mt-2 text-center">● EN VIVO</div>
                            </div>
                            
                            <div class="bg-black/50 rounded-lg border border-cyan-500/20 p-4">
                                <div class="text-center text-white/60 mb-2">Cámara 3 - Parqueadero B</div>
                                <div class="bg-gray-800 h-32 rounded flex items-center justify-center">
                                    <i class="fas fa-video text-cyan-400 text-2xl"></i>
                                </div>
                                <div class="text-green-400 text-sm mt-2 text-center">● EN VIVO</div>
                            </div>
                            
                            <div class="bg-black/50 rounded-lg border border-red-500/20 p-4">
                                <div class="text-center text-white/60 mb-2">Cámara 4 - Salida</div>
                                <div class="bg-gray-800 h-32 rounded flex items-center justify-center">
                                    <i class="fas fa-video-slash text-red-400 text-2xl"></i>
                                </div>
                                <div class="text-red-400 text-sm mt-2 text-center">● DESCONECTADA</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Estado del Sistema</h2>
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-green-500/20">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-white">Sistema Principal</span>
                                </div>
                                <span class="text-green-400">ONLINE</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-green-500/20">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-white">Base de Datos</span>
                                </div>
                                <span class="text-green-400">CONECTADA</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-yellow-500/20">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full animate-pulse"></div>
                                    <span class="text-white">Cámaras IP</span>
                                </div>
                                <span class="text-yellow-400">3/4 ACTIVAS</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg border border-green-500/20">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-white">Sensores de Movimiento</span>
                                </div>
                                <span class="text-green-400">ACTIVOS</span>
                            </div>
                        </div>
                        
                        <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                            <div class="stat">
                                <div class="stat-title text-white/60">Uptime</div>
                                <div class="stat-value text-cyan-400">99.8%</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Alertas Hoy</div>
                                <div class="stat-value text-yellow-400">3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeSection === 'incidencias'" x-transition class="space-y-8">
            <div class="glass-card p-8">
                <h1 class="text-4xl font-bold text-white mb-6 text-cyber">REPORTES DE INCIDENCIAS</h1>
                
                <!-- Sistema de incidencias -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Nueva Incidencia</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Tipo de Incidencia</label>
                                <select class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                                    <option>Vehículo mal estacionado</option>
                                    <option>Acceso no autorizado</option>
                                    <option>Daño a propiedad</option>
                                    <option>Ruido excesivo</option>
                                    <option>Vehículo abandonado</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Ubicación</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="Parqueadero A - Espacio 15">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Placa del Vehículo (si aplica)</label>
                                <input type="text" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="ABC-123">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Prioridad</label>
                                <select class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                                    <option>Baja</option>
                                    <option>Media</option>
                                    <option>Alta</option>
                                    <option>Crítica</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Descripción</label>
                                <textarea class="textarea textarea-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="Describe la incidencia en detalle..."></textarea>
                            </div>
                            
                            <button class="btn btn-primary w-full bg-red-600 hover:bg-red-700 border-red-500">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                Reportar Incidencia
                            </button>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Incidencias Recientes</h2>
                        
                        <div class="space-y-3">
                            <div class="p-4 bg-white/5 rounded-lg border border-red-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-red-400 font-medium">CRÍTICA</span>
                                    <span class="text-white/60 text-sm">Hace 15 min</span>
                                </div>
                                <div class="text-white font-medium">Vehículo mal estacionado</div>
                                <div class="text-white/60 text-sm">Parqueadero B - Espacio 23</div>
                                <div class="text-white/40 text-xs">Placa: XYZ-789</div>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-lg border border-yellow-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-yellow-400 font-medium">ALTA</span>
                                    <span class="text-white/60 text-sm">Hace 1h</span>
                                </div>
                                <div class="text-white font-medium">Acceso no autorizado</div>
                                <div class="text-white/60 text-sm">Entrada principal</div>
                                <div class="text-white/40 text-xs">Persona sin identificación</div>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-lg border border-blue-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-blue-400 font-medium">MEDIA</span>
                                    <span class="text-white/60 text-sm">Hace 2h</span>
                                </div>
                                <div class="text-white font-medium">Ruido excesivo</div>
                                <div class="text-white/60 text-sm">Apartamento 301-A</div>
                                <div class="text-white/40 text-xs">Música alta</div>
                            </div>
                        </div>
                        
                        <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                            <div class="stat">
                                <div class="stat-title text-white/60">Incidencias Hoy</div>
                                <div class="stat-value text-red-400">7</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Pendientes</div>
                                <div class="stat-value text-yellow-400">3</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeSection === 'analytics'" x-transition class="space-y-8">
            <div class="glass-card p-8">
                <h1 class="text-4xl font-bold text-white mb-6 text-cyber">ANÁLISIS Y REPORTES</h1>
                
                <!-- Sistema de análisis -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Generar Reporte</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Tipo de Reporte</label>
                                <select class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                                    <option>Reporte Diario</option>
                                    <option>Reporte Semanal</option>
                                    <option>Reporte Mensual</option>
                                    <option>Reporte de Incidencias</option>
                                    <option>Reporte de Visitantes</option>
                                    <option>Reporte de Ingresos</option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-white/80 mb-2">Fecha Inicio</label>
                                    <input type="date" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-white/80 mb-2">Fecha Fin</label>
                                    <input type="date" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white/80 mb-2">Formato</label>
                                <select class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                                    <option>PDF</option>
                                    <option>Excel</option>
                                    <option>CSV</option>
                                </select>
                            </div>
                            
                            <button class="btn btn-primary w-full bg-cyan-600 hover:bg-cyan-700 border-cyan-500">
                                <i class="fas fa-file-download mr-2"></i>
                                Generar Reporte
                            </button>
                        </div>
                        
                        <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                            <div class="stat">
                                <div class="stat-title text-white/60">Reportes Generados</div>
                                <div class="stat-value text-cyan-400">24</div>
                            </div>
                            <div class="stat">
                                <div class="stat-title text-white/60">Este Mes</div>
                                <div class="stat-value text-green-400">8</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-6">
                        <h2 class="text-2xl font-semibold text-cyan-400 mb-4">Métricas en Tiempo Real</h2>
                        
                        <div class="space-y-4">
                            <div class="p-4 bg-white/5 rounded-lg border border-cyan-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-white font-medium">Ocupación Actual</span>
                                    <span class="text-cyan-400 font-bold">62.6%</span>
                                </div>
                                <div class="w-full bg-gray-700 rounded-full h-2">
                                    <div class="bg-cyan-400 h-2 rounded-full" style="width: 62.6%"></div>
                                </div>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-lg border border-green-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-white font-medium">Ingresos Hoy</span>
                                    <span class="text-green-400 font-bold">$187,500</span>
                                </div>
                                <div class="text-white/60 text-sm">+12% vs ayer</div>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-lg border border-yellow-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-white font-medium">Visitantes Hoy</span>
                                    <span class="text-yellow-400 font-bold">18</span>
                                </div>
                                <div class="text-white/60 text-sm">Promedio: 15/día</div>
                            </div>
                            
                            <div class="p-4 bg-white/5 rounded-lg border border-red-500/20">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-white font-medium">Incidencias</span>
                                    <span class="text-red-400 font-bold">3</span>
                                </div>
                                <div class="text-white/60 text-sm">2 resueltas, 1 pendiente</div>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-cyan-400">Reportes Recientes</h3>
                            
                            <div class="space-y-2">
                                <div class="flex items-center justify-between p-3 bg-white/5 rounded border border-cyan-500/10">
                                    <div>
                                        <div class="text-white text-sm">Reporte Diario - 01/10/2025</div>
                                        <div class="text-white/60 text-xs">PDF - 2.3 MB</div>
                                    </div>
                                    <button class="btn btn-sm bg-cyan-600 hover:bg-cyan-700 border-cyan-500">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-white/5 rounded border border-cyan-500/10">
                                    <div>
                                        <div class="text-white text-sm">Reporte Semanal - Semana 40</div>
                                        <div class="text-white/60 text-xs">Excel - 1.8 MB</div>
                                    </div>
                                    <button class="btn btn-sm bg-cyan-600 hover:bg-cyan-700 border-cyan-500">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                                
                                <div class="flex items-center justify-between p-3 bg-white/5 rounded border border-cyan-500/10">
                                    <div>
                                        <div class="text-white text-sm">Reporte de Incidencias - Sept</div>
                                        <div class="text-white/60 text-xs">PDF - 3.1 MB</div>
                                    </div>
                                    <button class="btn btn-sm bg-cyan-600 hover:bg-cyan-700 border-cyan-500">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Notification Container -->
    <div id="notificationContainer" class="fixed top-4 right-4 z-50 space-y-4"></div>

    <script>
    function vigilanteApp() {
        return {
            darkMode: false,
            sidebarOpen: false,
            activeSection: 'dashboard',
            showAlerts: false,
            currentTime: '',
            currentDate: '',
            currentDay: '',
            fullDateTime: '',
            welcomeMessage: 'Monitorea y controla el acceso al conjunto residencial Quintanares by Parkovisco',
            stats: {
                vehiculos_actuales: <?php echo $estadisticas['vehiculos_actuales']; ?>,
                espacios_libres: <?php echo $estadisticas['espacios_libres']; ?>,
                espacios_visitantes: <?php echo $estadisticas['espacios_visitantes']; ?>,
                espacios_usuarios: <?php echo $estadisticas['espacios_usuarios']; ?>,
                total_espacios: <?php echo $estadisticas['total_espacios']; ?>,
                ocupacion_porcentaje: <?php echo $estadisticas['ocupacion_porcentaje']; ?>,
                alertas_activas: <?php echo $estadisticas['alertas_activas']; ?>,
                incidencias_hoy: <?php echo $estadisticas['incidencias_hoy']; ?>,
                visitantes_hoy: <?php echo $estadisticas['visitantes_hoy']; ?>,
                pico_ocupacion: <?php echo $estadisticas['pico_ocupacion']; ?>
            },

            init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 1000);
                this.initializeParticles();
                this.initializeCharts();
                this.setupResponsive();
                
                // Initialize AOS
                AOS.init({
                    duration: 800,
                    easing: 'ease-out',
                    once: true,
                    offset: 100
                });
            },

            updateTime() {
                const now = new Date();
                this.currentTime = now.toLocaleTimeString('es-CO', { 
                    hour12: false,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                this.currentDate = now.toLocaleDateString('es-CO');
                this.currentDay = now.toLocaleDateString('es-CO', { weekday: 'long' });
                this.fullDateTime = now.toLocaleString('es-CO');
            },

            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
            },

            initializeParticles() {
                if (typeof particlesJS !== 'undefined') {
                    particlesJS('particles-js', {
                        particles: {
                            number: { value: 50, density: { enable: true, value_area: 800 } },
                            color: { value: ['#10b981', '#3b82f6', '#ec4899'] },
                            shape: { type: 'circle' },
                            opacity: { value: 0.3, random: true, anim: { enable: true, speed: 1, opacity_min: 0.1, sync: false } },
                            size: { value: 3, random: true, anim: { enable: true, speed: 2, size_min: 0.5, sync: false } },
                            line_linked: { enable: true, distance: 150, color: '#10b981', opacity: 0.2, width: 1 },
                            move: { enable: true, speed: 1, direction: 'none', random: false, straight: false, out_mode: 'out', bounce: false }
                        },
                        interactivity: {
                            detect_on: 'canvas',
                            events: { onhover: { enable: true, mode: 'repulse' }, onclick: { enable: true, mode: 'push' }, resize: true },
                            modes: { grab: { distance: 140, line_linked: { opacity: 1 } }, bubble: { distance: 400, size: 40, duration: 2, opacity: 8, speed: 3 }, repulse: { distance: 200, duration: 0.4 }, push: { particles_nb: 4 }, remove: { particles_nb: 2 } }
                        },
                        retina_detect: true
                    });
                }
            },

            initializeCharts() {
                const ctx = document.getElementById('occupancyChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Ocupados', 'Libres', 'Reservados'],
                            datasets: [{
                                data: [this.stats.vehiculos_actuales, this.stats.espacios_libres, 0],
                                backgroundColor: ['#ef4444', '#10b981', '#3b82f6'],
                                borderWidth: 0,
                                cutout: '70%'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: '#ffffff',
                                        font: { size: 14, weight: 'bold' },
                                        padding: 20
                                    }
                                }
                            }
                        }
                    });
                }
            },

            setupResponsive() {
                window.addEventListener('resize', () => {
                    // En desktop, mantener el estado actual del usuario
                    // En móvil, cerrar automáticamente si está abierto para evitar problemas
                    if (window.innerWidth < 1024 && this.sidebarOpen) {
                        // Solo cerrar automáticamente en móvil si el overlay está activo
                        // Esto permite que el usuario controle manualmente el estado
                    }
                });

                // Initialize responsive state - empezar cerrado para que el usuario tenga control total
                this.sidebarOpen = false;
            },

            mostrarNotificacion(tipo, mensaje) {
                const container = document.getElementById('notificationContainer');
                const notification = document.createElement('div');
                
                const colors = {
                    'success': 'bg-emerald-500/20 border-emerald-500/50 text-emerald-400',
                    'error': 'bg-red-500/20 border-red-500/50 text-red-400',
                    'warning': 'bg-yellow-500/20 border-yellow-500/50 text-yellow-400',
                    'info': 'bg-blue-500/20 border-blue-500/50 text-blue-400'
                };
                
                const icons = {
                    'success': 'fa-check-circle',
                    'error': 'fa-exclamation-circle',
                    'warning': 'fa-exclamation-triangle',
                    'info': 'fa-info-circle'
                };
                
                notification.className = `notification border-2 ${colors[tipo]} show`;
                notification.innerHTML = `
                    <div class="flex items-center gap-4">
                        <i class="fas ${icons[tipo]} text-2xl"></i>
                        <div class="flex-1">
                            <span class="font-bold text-cyber">${mensaje}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-white/50 hover:text-white transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                `;
                
                container.appendChild(notification);
                
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.remove();
                    }
                }, 5000);
            },

            // Funciones de acciones
            abrirRegistroEntrada() {
                this.activeSection = 'entrada';
                this.mostrarNotificacion('info', 'Abriendo sistema de control de entrada');
            },

            abrirRegistroSalida() {
                this.activeSection = 'salida';
                this.mostrarNotificacion('info', 'Abriendo sistema de control de salida');
            },

            abrirEscanerQR() {
                this.mostrarNotificacion('info', 'Activando escáner QR...');
                // Aquí iría la lógica del escáner QR
            },

            reportarIncidencia() {
                this.activeSection = 'incidencias';
                this.mostrarNotificacion('warning', 'Abriendo sistema de reportes de incidencias');
            },

            activarEmergencia() {
                Swal.fire({
                    title: '🚨 PROTOCOLO DE EMERGENCIA',
                    text: '¿Confirmas que deseas activar el protocolo de emergencia?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'SÍ, ACTIVAR EMERGENCIA',
                    cancelButtonText: 'CANCELAR',
                    background: 'rgba(10, 10, 10, 0.95)',
                    color: '#ffffff',
                    customClass: {
                        popup: 'glass-card',
                        title: 'text-cyber',
                        confirmButton: 'command-button',
                        cancelButton: 'command-button'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: '⚠️ EMERGENCIA ACTIVADA',
                            text: 'Se ha notificado a las autoridades y administración',
                            icon: 'success',
                            timer: 3000,
                            showConfirmButton: false,
                            background: 'rgba(10, 10, 10, 0.95)',
                            color: '#ffffff',
                            customClass: {
                                popup: 'glass-card',
                                title: 'text-cyber'
                            }
                        });
                        
                        this.mostrarNotificacion('error', '🚨 PROTOCOLO DE EMERGENCIA ACTIVADO');
                    }
                });
            },

            cerrarSesion() {
                Swal.fire({
                    title: '¿CERRAR SESIÓN?',
                    text: 'Se cerrará tu sesión actual del sistema',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'SÍ, CERRAR SESIÓN',
                    cancelButtonText: 'CANCELAR',
                    background: 'rgba(10, 10, 10, 0.95)',
                    color: '#ffffff',
                    customClass: {
                        popup: 'glass-card',
                        title: 'text-cyber',
                        confirmButton: 'command-button',
                        cancelButton: 'command-button'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'logout.php';
                    }
                });
            }
        }
    }
    </script>

    <?php include 'components/footer.php'; ?>
</body>
</html>
