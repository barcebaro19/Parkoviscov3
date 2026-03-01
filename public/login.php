<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: true, showPassword: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Quintanares by Parkovisco</title>
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
        .login-container {
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

        .login-container::before {
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

        /* Login card */
        .login-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            max-width: 600px;
            margin: 0 auto;
        }

        .login-card::before {
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

        .login-card:hover::before {
            left: 100%;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 0 80px rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.4);
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

        /* Logo y branding */
        .logo-container {
            position: relative;
            z-index: 10;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #10b981, #3b82f6);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            margin: 0 auto 1rem;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
            animation: float 3s ease-in-out infinite;
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

        /* Status indicator */
        .status-indicator {
            position: absolute;
            top: 90px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 20px;
            padding: 8px 16px;
            backdrop-filter: blur(10px);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Form styling */
        .form-group {
            position: relative;
            margin-bottom: 1rem;
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
            padding: 1.5rem 1.5rem 3rem 1.5rem;
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

        /* Social Login Buttons */
        .social-cyber-btn {
            position: relative;
            width: 100%;
            padding: 12px 24px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.05);
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            overflow: hidden;
            transition: all 0.4s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .social-cyber-btn::before {
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

        .social-cyber-btn:hover::before {
            left: 100%;
        }

        .social-cyber-btn:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.1);
        }

        .google-btn:hover {
            box-shadow: 0 10px 25px rgba(66, 133, 244, 0.3);
            border-color: rgba(66, 133, 244, 0.5);
        }

        .apple-btn:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .social-icon {
            width: 20px;
            height: 20px;
            transition: transform 0.3s ease;
        }

        .social-cyber-btn:hover .social-icon {
            transform: scale(1.1);
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 1rem 0;
            position: relative;
        }

        .divider-line {
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        }

        .divider-text {
            padding: 0 1rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(10, 10, 10, 0.8);
        }

        /* Secondary Button */
        .cyber-button.secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .cyber-button.secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.4);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.1);
        }

        /* Modal Styles */
        .cyber-modal {
            background: rgba(10, 10, 10, 0.95);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 24px;
            color: white;
        }

        .cyber-modal::backdrop {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
        }

        .modal-title {
            color: #10b981;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Security Visual Container */
        .animation-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .security-visual {
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

        /* Buildings */
        .buildings-container {
            display: flex;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 30px;
        }

        .building {
            width: 100px;
            height: 150px;
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border: 2px solid rgba(16, 185, 129, 0.3);
            border-radius: 8px 8px 0 0;
            position: relative;
            animation: buildingGlow 3s ease-in-out infinite;
        }

        .building-1 { height: 120px; animation-delay: 0s; }
        .building-2 { height: 180px; animation-delay: 0.5s; }
        .building-3 { height: 150px; animation-delay: 1s; }

        .building-windows {
            position: absolute;
            top: 20px;
            left: 15px;
            right: 15px;
            height: 80px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .building-windows::before,
        .building-windows::after {
            content: '';
            background: rgba(16, 185, 129, 0.6);
            border-radius: 2px;
            animation: windowBlink 2s ease-in-out infinite;
        }

        .building-windows::after {
            animation-delay: 1s;
        }

        .building-door {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 25px;
            height: 40px;
            background: rgba(16, 185, 129, 0.8);
            border-radius: 3px 3px 0 0;
        }

        /* Security Elements */
        .security-elements {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .security-shield,
        .security-camera,
        .security-lock {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: securityFloat 4s ease-in-out infinite;
        }

        .security-shield { animation-delay: 0s; }
        .security-camera { animation-delay: 1.3s; }
        .security-lock { animation-delay: 2.6s; }

        .security-shield i,
        .security-camera i,
        .security-lock i {
            font-size: 32px;
            color: #10b981;
        }

        /* Security Text */
        .security-text {
            text-align: center;
        }

        .security-text h3 {
            color: #10b981;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .security-text p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            font-weight: 500;
        }

        /* Animations */
        @keyframes buildingGlow {
            0%, 100% { 
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
                border-color: rgba(16, 185, 129, 0.3);
            }
            50% { 
                box-shadow: 0 0 40px rgba(16, 185, 129, 0.6);
                border-color: rgba(16, 185, 129, 0.6);
            }
        }

        @keyframes windowBlink {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        @keyframes securityFloat {
            0%, 100% { 
                transform: translateY(0px);
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
            }
            50% { 
                transform: translateY(-10px);
                box-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
            
            .logo-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }

            .social-cyber-btn {
                padding: 10px 20px;
                font-size: 0.8rem;
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

            .status-indicator {
                top: 80px;
                right: 10px;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body class="login-container flex flex-col min-h-screen" x-data="loginSystem()">
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

    <!-- Status Indicator -->
    <div class="status-indicator">
        <div class="status-dot"></div>
        <span class="text-emerald-400 font-bold text-xs text-cyber">SISTEMA ACTIVO</span>
            </div>
            
    <!-- Main Login Container -->
    <div class="flex-1 flex flex-col pt-20 pb-4">
        <div class="flex-1 flex items-center justify-center px-4 py-8">
            <div class="w-full max-w-6xl flex gap-12 items-center">
                <!-- Visual Security Container -->
                <div class="hidden lg:flex w-1/2 animation-container" data-aos="fade-right">
                    <div class="security-visual">
                        <div class="buildings-container">
                            <div class="building building-1">
                                <div class="building-windows"></div>
                                <div class="building-door"></div>
                            </div>
                            <div class="building building-2">
                                <div class="building-windows"></div>
                                <div class="building-door"></div>
                            </div>
                            <div class="building building-3">
                                <div class="building-windows"></div>
                                <div class="building-door"></div>
                            </div>
                        </div>
                        <div class="security-elements">
                            <div class="security-shield">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="security-camera">
                                <i class="fas fa-video"></i>
                            </div>
                            <div class="security-lock">
                                <i class="fas fa-lock"></i>
                            </div>
                        </div>
                        <div class="security-text">
                            <h3>SEGURIDAD RESIDENCIAL</h3>
                            <p>Protección 24/7 para tu hogar</p>
                        </div>
                    </div>
                    </div>

                <!-- Login Card Container -->
                <div class="w-full lg:w-1/2">
                <!-- Login Card -->
                <div class="login-card p-6" data-aos="fade-up">
                <!-- Logo -->
                <div class="logo-container text-center mb-6">
                    <div class="logo-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2 text-cyber">
                        Quintanares by Parkovisco
                    </h1>
                    <p class="text-white/60 text-xs font-mono">
                        SISTEMA DE ACCESO SEGURO
                    </p>
                            </div>

                <!-- Login Form -->
                <form method="POST" action="auth/authenticate" class="space-y-4">
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="bg-red-500/20 border border-red-500/30 text-red-300 p-3 rounded-lg mb-4">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                    <!-- ID/Cédula Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-id-card mr-2"></i>ID o Cédula
                        </label>
                        <div class="relative">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" name="cedula" class="cyber-input with-icon" 
                                   placeholder="Ingrese su ID o cédula" required>
                        </div>
                        </div>

                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock mr-2"></i>Contraseña
                        </label>
                        <div class="relative">
                            <i class="fas fa-lock input-icon"></i>
                            <input :type="showPassword ? 'text' : 'password'" name="contrasena" 
                                   class="cyber-input with-icon" placeholder="••••••••" required>
                            <i class="fas password-toggle" 
                               :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"
                               @click="showPassword = !showPassword"></i>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 bg-transparent border-gray-300 rounded focus:ring-emerald-500">
                            <span class="ml-2 text-sm text-white/70">Recordar sesión</span>
                        </label>
                        <a href="#" class="text-sm text-emerald-400 hover:text-emerald-300 transition-colors">
                            ¿Olvidaste tu contraseña?
                        </a>
                        </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login" class="cyber-button">
                        <i class="fas fa-sign-in-alt"></i>
                        <span>Iniciar Sesión</span>
                        </button>
                </form>

                        <!-- Divider -->
                        <div class="divider">
                    <div class="divider-line"></div>
                    <span class="divider-text">o</span>
                    <div class="divider-line"></div>
                        </div>

                <!-- Social Login Buttons -->
                <div class="social-buttons space-y-2">
                            <!-- Google -->
                    <button type="button" class="social-cyber-btn google-btn" onclick="loginWithGoogle()">
                                <img src="https://www.svgrepo.com/show/475656/google-color.svg" 
                             alt="Google" class="social-icon">
                                <span>Continuar con Google</span>
                            </button>
                </div>


                <!-- Additional Info -->
                <div class="mt-8 text-center">
                    <p class="text-white/50 text-sm">
                        ¿No tienes una cuenta?
                        <a href="registrarusu.php" class="text-emerald-400 hover:text-emerald-300 transition-colors font-semibold">
                            Regístrate aquí
                        </a>
                    </p>
                        </div>

                <!-- Security Notice -->
                <div class="mt-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shield-alt text-emerald-400"></i>
                        <div>
                            <p class="text-emerald-300 font-semibold text-sm">Acceso Seguro</p>
                            <p class="text-emerald-200/80 text-xs">Tu información está protegida con encriptación de grado militar</p>
                        </div>
                    </div>
                </div>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Restablecer Contraseña -->
    <dialog id="resetModal" class="cyber-modal">
        <div class="modal-box">
            <h3 class="modal-title">Restablecer Contraseña</h3>
            <form action="auth/reset-password" method="POST">
                <div class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope mr-2"></i>Correo Electrónico
                        </label>
                        <div class="relative">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" name="email" class="cyber-input with-icon" 
                                   placeholder="tu@email.com" required>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button type="submit" class="cyber-button flex-1">
                            <i class="fas fa-paper-plane"></i>
                            <span>Enviar Enlace</span>
                        </button>
                        <button type="button" onclick="document.getElementById('resetModal').close()" 
                                class="cyber-button secondary flex-1">
                            <i class="fas fa-times"></i>
                            <span>Cancelar</span>
                        </button>
                    </div>
                </div>
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

    <script>
        function loginSystem() {
            return {
                showPassword: false,
                
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

        // Función para login con Google
        function loginWithGoogle() {
            // Mostrar loading
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Conectando con Google...';
            button.disabled = true;
            
            // Redirigir a Google OAuth
            window.location.href = '../app/Services/google_auth.php';
        }
    </script>
</body>
</html> 
