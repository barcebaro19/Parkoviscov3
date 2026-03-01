<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
 
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $mensaje = $_POST['mensaje'];

    $mensaje_exito = "Gracias, $nombre. Tu mensaje ha sido enviado.";
}

$noticias = [
    [
        'titulo' => 'Nueva zona de juegos inaugurada',
        'fecha' => '2024-07-01',
        'imagen' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
        'contenido' => '¡Ya está disponible la nueva zona de juegos para niños y familias! Ven a disfrutar de los nuevos espacios seguros y modernos.'
    ],
    [   
        'titulo' => 'Jornada de reciclaje este sábado',
        'fecha' => '2024-07-06',
        'imagen' => 'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=400&q=80',
        'contenido' => 'Participa en nuestra jornada ecológica y trae tus materiales reciclables. Habrá premios y actividades para toda la familia.'
    ],
    [
        'titulo' => 'Mantenimiento de ascensores',
        'fecha' => '2024-07-10',
        'imagen' => 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=400&q=80',
        'contenido' => 'El próximo miércoles se realizará mantenimiento preventivo en los ascensores de la torre B. Agradecemos tu comprensión.'
    ],
    [
        'titulo' => 'Nueva administración',
        'fecha' => '2024-07-15',
        'imagen' => 'https://images.unsplash.com/photo-1521737852567-6949f3f9f2b5?auto=format&fit=crop&w=400&q=80',
        'contenido' => 'Damos la bienvenida a la nueva administración, que trae nuevas ideas y mejoras para la comunidad.'
    ],
];

$testimonios = [
    [
        'nombre' => 'María González',
        'apartamento' => 'Torre A - 502',
        'avatar' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?auto=format&fit=crop&w=150&q=80',
        'calificacion' => 5,
        'comentario' => 'Vivir en Quintanares ha sido la mejor decisión. La seguridad es excelente y las amenidades son de primera calidad. Mi familia se siente muy cómoda aquí.'
    ],
    [
        'nombre' => 'Carlos Rodríguez',
        'apartamento' => 'Torre B - 301',
        'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80',
        'calificacion' => 5,
        'comentario' => 'El gimnasio y la piscina son increíbles. La administración siempre está pendiente de todo. Definitivamente recomiendo este lugar.'
    ],
    [
        'nombre' => 'Ana Martínez',
        'apartamento' => 'Torre A - 801',
        'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=150&q=80',
        'calificacion' => 5,
        'comentario' => 'Los espacios verdes y la zona infantil son perfectos para mis hijos. La comunidad es muy unida y siempre hay actividades para todos.'
    ],
    [
        'nombre' => 'Roberto Silva',
        'apartamento' => 'Torre B - 602',
        'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80',
        'calificacion' => 5,
        'comentario' => 'La ubicación es perfecta, cerca de todo. El parqueadero es amplio y seguro. Muy contento con mi decisión de vivir aquí.'
    ],
    [
        'nombre' => 'Laura Jiménez',
        'apartamento' => 'Torre A - 401',
        'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80',
        'calificacion' => 5,
        'comentario' => 'Las amenidades superan mis expectativas. El WiFi es excelente y la seguridad 24/7 me da mucha tranquilidad.'
    ],
    [
        'nombre' => 'Miguel Torres',
        'apartamento' => 'Torre B - 201',
        'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80',
        'calificacion' => 5,
        'comentario' => 'Excelente inversión. El apartamento es amplio, bien iluminado y con excelentes acabados. La administración es muy profesional.'
    ]
];

$galeria = [
    [
        'imagen' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Vista Principal del Conjunto',
        'categoria' => 'exterior'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Apartamento Tipo 2',
        'categoria' => 'apartamento'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Piscina y Área Social',
        'categoria' => 'amenidades'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Gimnasio Moderno',
        'categoria' => 'amenidades'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1560448204-603b3fc33ddc?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Apartamento Tipo 3',
        'categoria' => 'apartamento'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Zona Verde y Juegos',
        'categoria' => 'exterior'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Apartamento Tipo 1',
        'categoria' => 'apartamento'
    ],
    [
        'imagen' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
        'titulo' => 'Sala de Eventos',
        'categoria' => 'amenidades'
    ]
];

$planes = [
    [
        'nombre' => 'Apartamento Tipo 1',
        'precio' => '180',
        'area' => '65 m²',
        'habitaciones' => '2',
        'banos' => '1',
        'caracteristicas' => [
            'Cocina integral',
            'Balcón privado',
            'Closets empotrados',
            'Pisos en porcelanato',
            'Vista a la ciudad'
        ],
        'featured' => false
    ],
    [
        'nombre' => 'Apartamento Tipo 2',
        'precio' => '220',
        'area' => '85 m²',
        'habitaciones' => '3',
        'banos' => '2',
        'caracteristicas' => [
            'Cocina integral premium',
            'Balcón amplio',
            'Closets empotrados',
            'Pisos en porcelanato',
            'Vista panorámica',
            'Sala-comedor integrada'
        ],
        'featured' => true
    ],
    [
        'nombre' => 'Apartamento Tipo 3',
        'precio' => '280',
        'area' => '110 m²',
        'habitaciones' => '3',
        'banos' => '2',
        'caracteristicas' => [
            'Cocina integral premium',
            'Balcón terraza',
            'Closets empotrados',
            'Pisos en porcelanato',
            'Vista panorámica',
            'Sala-comedor integrada',
            'Estudio/despacho'
        ],
        'featured' => false
    ]
];
?>

<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quintanares by Parkovisco - Tu Hogar Ideal</title>
    
    <!-- Tailwind CSS y DaisyUI -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lottie Animations -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    
    <style>
        :root {
            --primary: #10b981;
            --primary-light: #34d399;
            --primary-dark: #059669;
            --secondary: #3b82f6;
            --accent: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --surface: rgba(255, 255, 255, 0.05);
            --background: #0a0a0a   ;
        }

        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
        }

        body {
            background: linear-gradient(135deg, 
                #0a0a0a 0%, 
                #1a1a2e 25%, 
                #16213e 50%, 
                #0f3460 75%, 
                #533483 100%
            );
            min-height: 100vh;
            color: #ffffff;
            overflow-x: hidden;
        }

        /* Hero Section Cyberpunk */
        .hero-background {
            background: linear-gradient(135deg, 
                rgba(10, 10, 10, 0.95) 0%, 
                rgba(26, 26, 46, 0.9) 25%, 
                rgba(22, 33, 62, 0.85) 50%, 
                rgba(15, 52, 96, 0.8) 75%, 
                rgba(83, 52, 131, 0.75) 100%
            ),
            url('https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            overflow: hidden;
        }

        .hero-background::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(16, 185, 129, 0.1) 50%, transparent 70%);
            animation: cyber-shimmer 4s infinite;
        }

        @keyframes cyber-shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Cyberpunk Glass Morphism */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border-radius: 20px;
        }

        .glass-card-dark {
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.3);
            box-shadow: 0 8px 32px rgba(16, 185, 129, 0.1);
        }

        /* Enhanced Cyberpunk Cards */
        .modern-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 24px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            background-size: 200% 100%;
            animation: neon-flow 2s linear infinite;
        }

        .modern-card::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            border-radius: 26px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.6s ease;
        }

        .modern-card:hover {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 
                0 25px 80px rgba(0, 0, 0, 0.8),
                0 0 100px rgba(16, 185, 129, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border-color: rgba(16, 185, 129, 0.6);
        }

        .modern-card:hover::after {
            opacity: 0.3;
        }

        @keyframes gradient-flow {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes neon-flow {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        @keyframes neon-pulse {
            0%, 100% { 
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.3);
                border-color: rgba(16, 185, 129, 0.3);
            }
            50% { 
                box-shadow: 0 0 40px rgba(16, 185, 129, 0.6);
                border-color: rgba(16, 185, 129, 0.6);
            }
        }

        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            10% { transform: translate(-2px, 2px); }
            20% { transform: translate(2px, -2px); }
            30% { transform: translate(-2px, -2px); }
            40% { transform: translate(2px, 2px); }
            50% { transform: translate(-2px, 2px); }
            60% { transform: translate(2px, -2px); }
            70% { transform: translate(-2px, -2px); }
            80% { transform: translate(2px, 2px); }
            90% { transform: translate(-2px, 2px); }
        }

        @keyframes matrix-rain {
            0% { transform: translateY(-100vh); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }

        /* Enhanced Cyberpunk Buttons */
        .btn-primary-modern {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 16px;
            color: white;
            font-weight: 700;
            padding: 16px 36px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 
                0 6px 20px rgba(16, 185, 129, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .btn-primary-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .btn-primary-modern::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            border-radius: 18px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.4s ease;
        }

        .btn-primary-modern:hover::before {
            left: 100%;
        }

        .btn-primary-modern:hover::after {
            opacity: 0.5;
        }

        .btn-primary-modern:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 
                0 15px 40px rgba(16, 185, 129, 0.6),
                0 0 60px rgba(16, 185, 129, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            border-color: rgba(16, 185, 129, 0.8);
        }

        .btn-secondary-modern {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid #10b981;
            border-radius: 12px;
            color: #10b981;
            font-weight: 600;
            padding: 12px 30px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .btn-secondary-modern:hover {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
            border-color: #34d399;
        }

        /* Enhanced Cyberpunk Feature Icons */
        .feature-icon {
            width: 90px;
            height: 90px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 1.5rem;
            position: relative;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid rgba(16, 185, 129, 0.3);
            backdrop-filter: blur(15px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .feature-icon::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(16, 185, 129, 0.3), transparent);
            transform: translateX(-100%);
            transition: transform 0.8s ease;
        }

        .feature-icon::after {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            background: linear-gradient(45deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            border-radius: 27px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .feature-icon:hover::before {
            transform: translateX(100%);
        }

        .feature-icon:hover::after {
            opacity: 0.4;
        }

        .feature-icon:hover {
            border-color: rgba(16, 185, 129, 0.8);
            box-shadow: 
                0 0 50px rgba(16, 185, 129, 0.5),
                0 15px 35px rgba(0, 0, 0, 0.6),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            transform: scale(1.1) rotate(5deg);
        }

        /* Enhanced Cyberpunk Statistics */
        .stat-number {
            font-size: 4rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            background: linear-gradient(135deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1;
            animation: gradient-shift 2s ease infinite;
            text-shadow: 
                0 0 20px rgba(16, 185, 129, 0.8),
                0 0 40px rgba(59, 130, 246, 0.6),
                0 0 60px rgba(139, 92, 246, 0.4);
            position: relative;
            display: inline-block;
        }

        .stat-number::before {
            content: attr(data-number);
            position: absolute;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 2s ease infinite;
            filter: blur(1px);
            opacity: 0.7;
        }

        @keyframes gradient-shift {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        /* Floating Animation */
        @keyframes cyber-float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }

        .floating {
            animation: cyber-float 6s ease-in-out infinite;
        }

        /* Pulse Animation */
        @keyframes cyber-pulse {
            0%, 100% { transform: scale(1); opacity: 1; box-shadow: 0 0 20px rgba(16, 185, 129, 0.3); }
            50% { transform: scale(1.05); opacity: 0.8; box-shadow: 0 0 40px rgba(16, 185, 129, 0.6); }
        }

        .pulse-custom {
            animation: cyber-pulse 2s infinite;
        }

        /* Enhanced Cyberpunk Gradient Text */
        .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #3b82f6 25%, #8b5cf6 50%, #3b82f6 75%, #10b981 100%);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 2.5s ease infinite;
            text-shadow: 
                0 0 20px rgba(16, 185, 129, 0.6),
                0 0 40px rgba(59, 130, 246, 0.4),
                0 0 60px rgba(139, 92, 246, 0.2);
            position: relative;
            display: inline-block;
        }

        .gradient-text::before {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 300% 300%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 2.5s ease infinite;
            filter: blur(2px);
            opacity: 0.5;
        }

        /* Glitch Effect for Main Title */
        .glitch-title {
            animation: glitch 0.3s infinite;
        }

        .glitch-title:hover {
            animation: glitch 0.1s infinite;
        }

        /* Testimonials Section */
        .testimonial-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 24px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .testimonial-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 200% 100%;
            animation: neon-flow 3s linear infinite;
        }

        .testimonial-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.7),
                0 0 50px rgba(16, 185, 129, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
        }

        .testimonial-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(16, 185, 129, 0.3);
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .testimonial-avatar:hover {
            border-color: rgba(16, 185, 129, 0.8);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
        }

        .stars {
            color: #fbbf24;
            font-size: 1.2rem;
            text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
        }

        /* Gallery Section */
        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .gallery-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 1;
        }

        .gallery-item:hover::before {
            opacity: 1;
        }

        .gallery-item:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }

        .gallery-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            padding: 1.5rem;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            z-index: 2;
        }

        .gallery-item:hover .gallery-overlay {
            transform: translateY(0);
        }

        /* Statistics Section */
        .stat-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 24px;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 200% 100%;
            animation: neon-flow 3s linear infinite;
        }

        .stat-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.7),
                0 0 50px rgba(16, 185, 129, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid rgba(16, 185, 129, 0.3);
            color: #10b981;
            transition: all 0.3s ease;
        }

        .stat-icon:hover {
            background: rgba(16, 185, 129, 0.2);
            border-color: rgba(16, 185, 129, 0.6);
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
            transform: scale(1.1);
        }

        .counter {
            font-size: 3rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            background: linear-gradient(135deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 3s ease infinite;
            text-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
        }

        /* Pricing Section */
        .pricing-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        .pricing-card.featured {
            border-color: rgba(16, 185, 129, 0.5);
            transform: scale(1.05);
        }

        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 200% 100%;
            animation: neon-flow 3s linear infinite;
        }

        .pricing-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.7),
                0 0 50px rgba(16, 185, 129, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
        }

        .pricing-card.featured:hover {
            transform: translateY(-8px) scale(1.07);
        }

        .price {
            font-size: 3.5rem;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            background: linear-gradient(135deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 200% 200%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: gradient-shift 3s ease infinite;
            text-shadow: 0 0 30px rgba(16, 185, 129, 0.5);
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 2rem 0;
        }

        .feature-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .feature-list li:last-child {
            border-bottom: none;
        }

        .feature-list li i {
            color: #10b981;
            margin-right: 0.5rem;
        }

        /* Enhanced Cyberpunk Navbar */
        .navbar-modern {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(30px);
            border-bottom: 2px solid rgba(16, 185, 129, 0.3);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 4px 25px rgba(0, 0, 0, 0.4),
                0 0 30px rgba(16, 185, 129, 0.1);
            position: relative;
        }

        .navbar-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            background-size: 200% 100%;
            animation: neon-flow 3s linear infinite;
        }

        .navbar-scrolled {
            background: rgba(0, 0, 0, 0.95);
            box-shadow: 
                0 8px 35px rgba(0, 0, 0, 0.6),
                0 0 50px rgba(16, 185, 129, 0.3);
            border-bottom-color: rgba(16, 185, 129, 0.6);
        }

        /* Particles Container */
        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        /* Enhanced Cyberpunk News Cards */
        .news-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(30px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.5),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .news-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #10b981, #3b82f6, #8b5cf6);
            background-size: 200% 100%;
            animation: neon-flow 3s linear infinite;
        }

        .news-card::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #10b981, #3b82f6, #8b5cf6, #10b981);
            border-radius: 22px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .news-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.7),
                0 0 50px rgba(16, 185, 129, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            border-color: rgba(16, 185, 129, 0.5);
        }

        .news-card:hover::after {
            opacity: 0.2;
        }

        .news-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .news-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
        }

        /* Enhanced Cyberpunk Contact Form */
        .form-input {
            background: rgba(255, 255, 255, 0.03);
            border: 2px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            padding: 18px 24px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            color: white;
            backdrop-filter: blur(15px);
            box-shadow: 
                0 4px 15px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            font-size: 1rem;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
            font-weight: 400;
        }

        .form-input:focus {
            border-color: #10b981;
            box-shadow: 
                0 0 0 4px rgba(16, 185, 129, 0.2),
                0 8px 25px rgba(0, 0, 0, 0.4),
                0 0 30px rgba(16, 185, 129, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            outline: none;
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-2px);
        }

        /* Loading Animation */
        .loading-dots {
            display: inline-block;
        }

        .loading-dots::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }

        @keyframes dots {
            0%, 20% { color: transparent; text-shadow: .25em 0 0 transparent, .5em 0 0 transparent; }
            40% { color: var(--primary); text-shadow: .25em 0 0 transparent, .5em 0 0 transparent; }
            60% { text-shadow: .25em 0 0 var(--primary), .5em 0 0 transparent; }
            80%, 100% { text-shadow: .25em 0 0 var(--primary), .5em 0 0 var(--primary); }
        }

        /* Floating Social Icons */
        .floating-social {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 12px;
            opacity: 0;
            transform: translateX(100px);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .social-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .social-icon:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .social-facebook {
            background: linear-gradient(135deg, #1877F2, #42A5F5);
        }

        .social-facebook:hover {
            background: linear-gradient(135deg, #166FE5, #1976D2);
        }

        .social-instagram {
            background: linear-gradient(135deg, #E4405F, #F77737, #FCAF45);
        }

        .social-instagram:hover {
            background: linear-gradient(135deg, #D73447, #E91E63);
        }

        .social-twitter {
            background: linear-gradient(135deg, #1DA1F2, #00ACEE);
        }

        .social-twitter:hover {
            background: linear-gradient(135deg, #0D8BD9, #0288D1);
        }

        .social-whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .social-whatsapp:hover {
            background: linear-gradient(135deg, #1CC757, #075E54);
        }

        .social-linkedin {
            background: linear-gradient(135deg, #0077B5, #00A0DC);
        }

        .social-linkedin:hover {
            background: linear-gradient(135deg, #005885, #0077B5);
        }

        /* Pulse animation for floating icons */
        @keyframes pulse-social {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .social-icon.pulse-animation {
            animation: pulse-social 2s infinite;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .stat-number {
                font-size: 2.5rem;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .floating-social {
                bottom: 15px;
                right: 15px;
                gap: 8px;
            }

            .social-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar-modern fixed w-full top-0 z-50 px-4 lg:px-8 py-4" id="navbar">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold gradient-text">Quintanares</h1>
                    <p class="text-sm text-emerald-400 font-mono">by Parkovisco</p>
                </div>
            </div>
            
            <div class="hidden md:flex items-center space-x-8">
                <a href="#inicio" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Inicio</a>
                <a href="#servicios" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Servicios</a>
                <a href="#testimonios" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Testimonios</a>
                <a href="#galeria" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Galería</a>
                <a href="#estadisticas" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Estadísticas</a>
                <a href="#precios" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Precios</a>
                <a href="#noticias" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Noticias</a>
                <a href="#contacto" class="text-white/80 hover:text-emerald-400 font-medium transition-colors">Contacto</a>
                <a href="login.php" class="btn-primary-modern">
                    <i class="fas fa-sign-in-alt mr-2"></i>Acceder
                </a>
            </div>
            
            <!-- Mobile Menu Button -->
            <button class="md:hidden text-white/80 hover:text-emerald-400" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden hidden bg-black/95 backdrop-blur-lg border-t border-emerald-500/30 mt-4 py-4">
            <div class="flex flex-col space-y-4 px-4">
                <a href="#inicio" class="text-white/80 hover:text-emerald-400 font-medium">Inicio</a>
                <a href="#servicios" class="text-white/80 hover:text-emerald-400 font-medium">Servicios</a>
                <a href="#testimonios" class="text-white/80 hover:text-emerald-400 font-medium">Testimonios</a>
                <a href="#galeria" class="text-white/80 hover:text-emerald-400 font-medium">Galería</a>
                <a href="#estadisticas" class="text-white/80 hover:text-emerald-400 font-medium">Estadísticas</a>
                <a href="#precios" class="text-white/80 hover:text-emerald-400 font-medium">Precios</a>
                <a href="#noticias" class="text-white/80 hover:text-emerald-400 font-medium">Noticias</a>
                <a href="#contacto" class="text-white/80 hover:text-emerald-400 font-medium">Contacto</a>
                <a href="login.php" class="btn-primary-modern text-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>Acceder
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero-background min-h-screen flex items-center relative">
        <div id="particles-js"></div>
        
        <div class="hero-content w-full">
            <div class="max-w-7xl mx-auto px-4 lg:px-8 py-20">
                <div class="grid lg:grid-cols-2 gap-12 items-center">
                    <div data-aos="fade-right" data-aos-duration="1000">
                        <h1 class="text-5xl lg:text-7xl font-display font-bold text-white mb-6 leading-tight">
                            Bienvenido a tu
                            <span class="block gradient-text glitch-title bg-gradient-to-r from-cyan-300 to-blue-300 bg-clip-text text-transparent pb-2">
                                Hogar Ideal
                            </span>
                        </h1>
                        <p class="text-xl text-white/90 mb-8 leading-relaxed">
                            Descubre la comodidad y elegancia de vivir en Quintanares. 
                            Un espacio diseñado para tu bienestar y el de tu familia.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button class="btn-primary-modern text-lg">
                                <i class="fas fa-play mr-2"></i>Ver Tour Virtual
                            </button>
                            <button class="btn-secondary-modern text-lg">
                                <i class="fas fa-info-circle mr-2"></i>Más Información
                            </button>
                        </div>
                        
                        <!-- Statistics -->
                        <div class="grid grid-cols-3 gap-6 mt-12">
                            <div class="text-center" data-aos="fade-up" data-aos-delay="200">
                                <div class="stat-number">500+</div>
                                <p class="text-white/80 font-medium">Familias Felices</p>
                            </div>
                            <div class="text-center" data-aos="fade-up" data-aos-delay="400">
                                <div class="stat-number">15</div>
                                <p class="text-white/80 font-medium">Años de Experiencia</p>
                            </div>
                            <div class="text-center" data-aos="fade-up" data-aos-delay="600">
                                <div class="stat-number">24/7</div>
                                <p class="text-white/80 font-medium">Seguridad</p>
                            </div>
                        </div>
                        
                        <!-- App Store Badges -->
                        <div class="flex justify-start items-center gap-6 mt-12" data-aos="fade-up" data-aos-delay="800">
                            <a href="#" class="hover:scale-105 transition-transform duration-300">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" 
                                     alt="Get it on Google Play" 
                                     class="h-16 w-auto opacity-90 hover:opacity-100 transition-opacity brightness-110 contrast-110 saturate-120">
                            </a>
                            <a href="#" class="hover:scale-105 transition-transform duration-300">
                                <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" 
                                     alt="Download on the App Store" 
                                     class="h-16 w-auto opacity-90 hover:opacity-100 transition-opacity brightness-90 contrast-110 saturate-80">
                            </a>
                        </div>
                    </div>
                    
                    <div class="relative" data-aos="fade-left" data-aos-duration="1000">
                        <div class="glass-card p-8 rounded-3xl floating">
                            <lottie-player 
                                src="https://assets1.lottiefiles.com/packages/lf20_puciaact.json" 
                                background="transparent" 
                                speed="1" 
                                style="width: 100%; height: 400px;" 
                                loop 
                                autoplay>
                            </lottie-player>
                        </div>
                        
                        <!-- Floating Elements -->
                        <div class="absolute -top-4 -right-4 w-20 h-20 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center pulse-custom">
                            <i class="fas fa-home text-white text-2xl"></i>
                        </div>
                        <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center pulse-custom" style="animation-delay: 1s;">
                            <i class="fas fa-heart text-white text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="servicios" class="py-20 bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-blue-500 to-purple-500"></div>
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold gradient-text mb-6">
                    Servicios Exclusivos
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Disfruta de amenidades de primer nivel diseñadas para mejorar tu calidad de vida
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="modern-card p-8 text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-icon bg-gradient-to-br from-indigo-500 to-purple-600 text-white">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Seguridad 24/7</h3>
                    <p class="text-white/70 leading-relaxed">
                        Sistema de vigilancia avanzado con personal capacitado para garantizar tu tranquilidad.
                    </p>
                </div>
                
                <!-- Service 2 -->
                <div class="modern-card p-8 text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-icon bg-gradient-to-br from-cyan-500 to-blue-600 text-white">
                        <i class="fas fa-swimming-pool"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Piscina & Spa</h3>
                    <p class="text-white/70 leading-relaxed">
                        Relájate en nuestras instalaciones acuáticas con jacuzzi y área de descanso.
                    </p>
                </div>
                
                <!-- Service 3 -->
                <div class="modern-card p-8 text-center" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-icon bg-gradient-to-br from-green-500 to-emerald-600 text-white">
                        <i class="fas fa-dumbbell"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Gimnasio Moderno</h3>
                    <p class="text-white/70 leading-relaxed">
                        Mantente en forma con equipos de última generación y entrenadores profesionales.
                    </p>
                </div>
                
                <!-- Service 4 -->
                <div class="modern-card p-8 text-center" data-aos="fade-up" data-aos-delay="400">
                    <div class="feature-icon bg-gradient-to-br from-orange-500 to-red-600 text-white">
                        <i class="fas fa-child"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Zona Infantil</h3>
                    <p class="text-white/70 leading-relaxed">
                        Espacios seguros y divertidos para que los más pequeños puedan jugar y socializar.
                    </p>
                </div>
                
                <!-- Service 5 -->
                <div class="modern-card p-8 text-center" data-aos="fade-up" data-aos-delay="500">
                    <div class="feature-icon bg-gradient-to-br from-purple-500 to-pink-600 text-white">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Parqueadero</h3>
                    <p class="text-white/70 leading-relaxed">
                        Espacios amplios y seguros para tu vehículo con sistema de control de acceso.
                    </p>
                </div>
                
                <!-- Service 6 -->
                <div class="modern-card p-8 text-center" data-aos="fade-up" data-aos-delay="600">
                    <div class="feature-icon bg-gradient-to-br from-yellow-500 to-orange-600 text-white">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">WiFi Gratis</h3>
                    <p class="text-white/70 leading-relaxed">
                        Internet de alta velocidad en todas las áreas comunes para mantenerte conectado.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section id="testimonios" class="py-20 bg-gradient-to-br from-black via-gray-900 to-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/40"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-blue-500 to-purple-500"></div>
        
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold gradient-text mb-6">
                    Lo Que Dicen Nuestros Residentes
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Descubre por qué más de 500 familias han elegido Quintanares como su hogar
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($testimonios as $index => $testimonio): ?>
                <div class="testimonial-card" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="flex items-center mb-4">
                        <img src="<?php echo $testimonio['avatar']; ?>" alt="<?php echo $testimonio['nombre']; ?>" class="testimonial-avatar mr-4">
                        <div>
                            <h4 class="text-white font-semibold text-lg"><?php echo $testimonio['nombre']; ?></h4>
                            <p class="text-emerald-400 text-sm font-mono"><?php echo $testimonio['apartamento']; ?></p>
                        </div>
                    </div>
                    
                    <div class="flex mb-4">
                        <?php for ($i = 0; $i < $testimonio['calificacion']; $i++): ?>
                        <i class="fas fa-star stars"></i>
                        <?php endfor; ?>
                    </div>
                    
                    <p class="text-white/80 leading-relaxed italic">
                        "<?php echo $testimonio['comentario']; ?>"
                    </p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section id="galeria" class="py-20 bg-gradient-to-br from-slate-900 via-purple-900 to-black relative overflow-hidden">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-emerald-500"></div>
        
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold gradient-text mb-6">
                    Galería de Imágenes
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Explora los espacios y amenidades que hacen de Quintanares tu hogar ideal
                </p>
            </div>
            
            <!-- Filter Buttons -->
            <div class="flex justify-center mb-12" data-aos="fade-up" data-aos-delay="200">
                <div class="flex space-x-4">
                    <button class="filter-btn active bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-6 py-3 rounded-full font-semibold transition-all hover:bg-emerald-500/30" data-filter="all">
                        Todas
                    </button>
                    <button class="filter-btn bg-white/5 text-white/80 border border-white/20 px-6 py-3 rounded-full font-semibold transition-all hover:bg-white/10" data-filter="apartamento">
                        Apartamentos
                    </button>
                    <button class="filter-btn bg-white/5 text-white/80 border border-white/20 px-6 py-3 rounded-full font-semibold transition-all hover:bg-white/10" data-filter="amenidades">
                        Amenidades
                    </button>
                    <button class="filter-btn bg-white/5 text-white/80 border border-white/20 px-6 py-3 rounded-full font-semibold transition-all hover:bg-white/10" data-filter="exterior">
                        Exteriores
                    </button>
                </div>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($galeria as $index => $item): ?>
                <div class="gallery-item" data-category="<?php echo $item['categoria']; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <img src="<?php echo $item['imagen']; ?>" alt="<?php echo $item['titulo']; ?>" class="w-full h-64 object-cover">
                    <div class="gallery-overlay">
                        <h3 class="text-white font-semibold text-lg"><?php echo $item['titulo']; ?></h3>
                        <p class="text-white/80 text-sm capitalize"><?php echo $item['categoria']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section id="estadisticas" class="py-20 bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/20"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-blue-500 to-purple-500"></div>
        
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold gradient-text mb-6">
                    Números que Hablan
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Conoce las estadísticas que respaldan la excelencia de Quintanares
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="counter" data-target="500">0</div>
                    <h3 class="text-white font-semibold text-lg mb-2">Familias Felices</h3>
                    <p class="text-white/70 text-sm">Residentes que han elegido Quintanares como su hogar</p>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="counter" data-target="120">0</div>
                    <h3 class="text-white font-semibold text-lg mb-2">Apartamentos</h3>
                    <p class="text-white/70 text-sm">Unidades disponibles en dos torres modernas</p>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="counter" data-target="98">0</div>
                    <h3 class="text-white font-semibold text-lg mb-2">% Satisfacción</h3>
                    <p class="text-white/70 text-sm">Nivel de satisfacción de nuestros residentes</p>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="counter" data-target="15">0</div>
                    <h3 class="text-white font-semibold text-lg mb-2">Años de Experiencia</h3>
                    <p class="text-white/70 text-sm">Construyendo hogares de calidad desde 2009</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="precios" class="py-20 bg-gradient-to-br from-slate-900 via-purple-900 to-black relative overflow-hidden">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-emerald-500"></div>
        
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold gradient-text mb-6">
                    Planes y Precios
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Encuentra el apartamento perfecto para tu familia con nuestros planes flexibles
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach ($planes as $index => $plan): ?>
                <div class="pricing-card <?php echo $plan['featured'] ? 'featured' : ''; ?>" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <?php if ($plan['featured']): ?>
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-emerald-500 text-white px-4 py-2 rounded-full text-sm font-semibold">
                            Más Popular
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <h3 class="text-2xl font-bold text-white mb-4"><?php echo $plan['nombre']; ?></h3>
                    
                    <div class="mb-6">
                        <div class="price">$<?php echo $plan['precio']; ?></div>
                        <p class="text-white/60 text-sm">millones COP</p>
                    </div>
                    
                    <div class="mb-6">
                        <div class="flex justify-center space-x-6 text-white/80">
                            <div class="text-center">
                                <i class="fas fa-expand-arrows-alt text-emerald-400 mb-1"></i>
                                <p class="text-sm"><?php echo $plan['area']; ?></p>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-bed text-emerald-400 mb-1"></i>
                                <p class="text-sm"><?php echo $plan['habitaciones']; ?> hab</p>
                            </div>
                            <div class="text-center">
                                <i class="fas fa-bath text-emerald-400 mb-1"></i>
                                <p class="text-sm"><?php echo $plan['banos']; ?> baños</p>
                            </div>
                        </div>
                    </div>
                    
                    <ul class="feature-list">
                        <?php foreach ($plan['caracteristicas'] as $caracteristica): ?>
                        <li>
                            <i class="fas fa-check"></i>
                            <?php echo $caracteristica; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <button class="btn-primary-modern w-full mt-6">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Agendar Visita
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-12" data-aos="fade-up" data-aos-delay="400">
                <p class="text-white/80 mb-6">
                    ¿Necesitas financiación? Tenemos planes especiales para ti
                </p>
                <button class="btn-secondary-modern">
                    <i class="fas fa-calculator mr-2"></i>
                    Calculadora de Cuotas
                </button>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="noticias" class="py-20 bg-gradient-to-br from-slate-900 via-gray-900 to-black relative overflow-hidden">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-purple-500 via-pink-500 to-emerald-500"></div>
        <div class="max-w-7xl mx-auto px-4 lg:px-8 relative z-10">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold gradient-text mb-6">
                    Últimas Noticias
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    Mantente informado sobre las novedades y eventos de nuestra comunidad
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($noticias as $index => $noticia): ?>
                <div class="news-card" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                    <div class="news-image" style="background-image: url('<?php echo $noticia['imagen']; ?>');">
                        <div class="absolute top-4 left-4">
                            <span class="bg-emerald-500/90 text-white px-3 py-1 rounded-full text-sm font-semibold">
                                <?php echo date('M d', strtotime($noticia['fecha'])); ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-white mb-3 line-clamp-2">
                            <?php echo $noticia['titulo']; ?>
                        </h3>
                        <p class="text-white/70 text-sm leading-relaxed mb-4">
                            <?php echo substr($noticia['contenido'], 0, 120) . '...'; ?>
                        </p>
                        <button class="text-emerald-400 font-semibold hover:text-emerald-300 transition-colors">
                            Leer más <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contacto" class="py-20 bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-900 relative overflow-hidden">
        <div class="absolute inset-0 bg-black/20"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl lg:text-5xl font-display font-bold text-white mb-6">
                    Contáctanos
                </h2>
                <p class="text-xl text-white/80 max-w-3xl mx-auto">
                    ¿Tienes preguntas? Estamos aquí para ayudarte. Contáctanos y te responderemos pronto.
                </p>
            </div>
            
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right">
                    <div class="glass-card-dark p-8 rounded-2xl">
                        <h3 class="text-2xl font-bold text-white mb-6">Información de Contacto</h3>
                        
                        <div class="space-y-6">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-white"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold">Dirección</h4>
                                    <p class="text-white/70">Calle Principal #123, Ciudad</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-phone text-white"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold">Teléfono</h4>
                                    <p class="text-white/70">+57 (123) 456-7890</p>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-envelope text-white"></i>
                                </div>
                                <div>
                                    <h4 class="text-white font-semibold">Email</h4>
                                    <p class="text-white/70">info@quintanares.com</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8">
                            <h4 class="text-white font-semibold mb-4">Síguenos</h4>
                            <div class="flex space-x-4">
                                <a href="#" class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center hover:bg-blue-700 transition-colors">
                                    <i class="fab fa-facebook-f text-white"></i>
                                </a>
                                <a href="#" class="w-10 h-10 bg-pink-600 rounded-lg flex items-center justify-center hover:bg-pink-700 transition-colors">
                                    <i class="fab fa-instagram text-white"></i>
                                </a>
                                <a href="#" class="w-10 h-10 bg-blue-400 rounded-lg flex items-center justify-center hover:bg-blue-500 transition-colors">
                                    <i class="fab fa-twitter text-white"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div data-aos="fade-left">
                    <div class="glass-card-dark p-8 rounded-2xl">
                        <h3 class="text-2xl font-bold text-white mb-6">Envíanos un Mensaje</h3>
                        
                        <?php if (isset($mensaje_exito)): ?>
                        <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-4 mb-6">
                            <div class="flex items-center space-x-2">
                                <i class="fas fa-check-circle text-green-400"></i>
                                <span class="text-green-300"><?php echo $mensaje_exito; ?></span>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <form method="POST" class="space-y-6" id="contactForm">
                            <div>
                                <label class="block text-white/80 font-medium mb-2">Nombre Completo</label>
                                <input type="text" name="nombre" required class="form-input w-full" placeholder="Tu nombre">
                            </div>
                            
                            <div>
                                <label class="block text-white/80 font-medium mb-2">Teléfono</label>
                                <input type="tel" name="telefono" required class="form-input w-full" placeholder="Tu teléfono">
                            </div>
                            
                            <div>
                                <label class="block text-white/80 font-medium mb-2">Email</label>
                                <input type="email" name="email" required class="form-input w-full" placeholder="tu@email.com">
                            </div>
                            
                            <div>
                                <label class="block text-white/80 font-medium mb-2">Mensaje</label>
                                <textarea name="mensaje" required class="form-input w-full h-32 resize-none" placeholder="Escribe tu mensaje aquí..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-primary-modern w-full text-lg" id="submitBtn">
                                <i class="fas fa-paper-plane mr-2"></i>
                                <span id="submitText">Enviar Mensaje</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="col-span-2">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-cyan-500 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-white text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold gradient-text">Quintanares</h1>
                            <p class="text-emerald-400 font-mono">by Parkovisco</p>
                        </div>
                    </div>
                    <p class="text-gray-400 leading-relaxed mb-6">
                        Más que un lugar para vivir, Quintanares es una comunidad donde las familias 
                        crean recuerdos inolvidables en un ambiente seguro y acogedor.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-emerald-600 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-emerald-600 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 hover:bg-emerald-600 rounded-lg flex items-center justify-center transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Enlaces Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="#inicio" class="text-gray-400 hover:text-emerald-400 transition-colors">Inicio</a></li>
                        <li><a href="#servicios" class="text-gray-400 hover:text-emerald-400 transition-colors">Servicios</a></li>
                        <li><a href="#testimonios" class="text-gray-400 hover:text-emerald-400 transition-colors">Testimonios</a></li>
                        <li><a href="#galeria" class="text-gray-400 hover:text-emerald-400 transition-colors">Galería</a></li>
                        <li><a href="#estadisticas" class="text-gray-400 hover:text-emerald-400 transition-colors">Estadísticas</a></li>
                        <li><a href="#precios" class="text-gray-400 hover:text-emerald-400 transition-colors">Precios</a></li>
                        <li><a href="#noticias" class="text-gray-400 hover:text-emerald-400 transition-colors">Noticias</a></li>
                        <li><a href="#contacto" class="text-gray-400 hover:text-emerald-400 transition-colors">Contacto</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><i class="fas fa-map-marker-alt mr-2"></i>Calle Principal #123</li>
                        <li><i class="fas fa-phone mr-2"></i>+57 (123) 456-7890</li>
                        <li><i class="fas fa-envelope mr-2"></i>info@quintanares.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    © 2025 Quintanares by Parkovisco. Todos los derechos reservados. 
                    <a href="#" class="hover:text-emerald-400 transition-colors">Términos y Condiciones</a> | 
                    <a href="#" class="hover:text-emerald-400 transition-colors">Política de Privacidad</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Floating Social Media Icons -->
    <div class="floating-social">
        <a href="https://www.facebook.com/quintanares" target="_blank" class="social-icon social-facebook" title="Síguenos en Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://www.instagram.com/quintanares" target="_blank" class="social-icon social-instagram" title="Síguenos en Instagram">
            <i class="fab fa-instagram"></i>
        </a>
        <a href="https://twitter.com/quintanares" target="_blank" class="social-icon social-twitter" title="Síguenos en Twitter">
            <i class="fab fa-twitter"></i>
        </a>
        <a href="https://wa.me/573123456789" target="_blank" class="social-icon social-whatsapp pulse-animation" title="Escríbenos por WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
        <a href="https://www.linkedin.com/company/quintanares" target="_blank" class="social-icon social-linkedin" title="Conéctate en LinkedIn">
            <i class="fab fa-linkedin-in"></i>
        </a>
    </div>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Initialize Enhanced Cyberpunk Particles
        particlesJS('particles-js', {
            "particles": {
                "number": {
                    "value": 150,
                    "density": {
                        "enable": true,
                        "value_area": 500
                    }
                },
                "color": {
                    "value": ["#10b981", "#3b82f6", "#8b5cf6", "#34d399", "#60a5fa", "#a78bfa"]
                },
                "shape": {
                    "type": "circle",
                    "stroke": {
                        "width": 0,
                        "color": "#000000"
                    }
                },
                "opacity": {
                    "value": 0.8,
                    "random": true,
                    "anim": {
                        "enable": true,
                        "speed": 1.5,
                        "opacity_min": 0.2,
                        "sync": false
                    }
                },
                "size": {
                    "value": 3,
                    "random": true,
                    "anim": {
                        "enable": true,
                        "speed": 30,
                        "size_min": 0.5,
                        "sync": false
                    }
                },
                "line_linked": {
                    "enable": true,
                    "distance": 100,
                    "color": "#10b981",
                    "opacity": 0.4,
                    "width": 1.5
                },
                "move": {
                    "enable": true,
                    "speed": 2,
                    "direction": "none",
                    "random": true,
                    "straight": false,
                    "out_mode": "out",
                    "bounce": false,
                    "attract": {
                        "enable": true,
                        "rotateX": 800,
                        "rotateY": 1600
                    }
                }
            },
            "interactivity": {
                "detect_on": "canvas",
                "events": {
                    "onhover": {
                        "enable": true,
                        "mode": "repulse"
                    },
                    "onclick": {
                        "enable": true,
                        "mode": "push"
                    },
                    "resize": true
                },
                "modes": {
                    "grab": {
                        "distance": 400,
                        "line_linked": {
                            "opacity": 1
                        }
                    },
                    "bubble": {
                        "distance": 400,
                        "size": 40,
                        "duration": 2,
                        "opacity": 8,
                        "speed": 3
                    },
                    "repulse": {
                        "distance": 120,
                        "duration": 0.3
                    },
                    "push": {
                        "particles_nb": 4
                    },
                    "remove": {
                        "particles_nb": 2
                    }
                }
            },
            "retina_detect": true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });

        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Contact form submission
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            
            submitBtn.disabled = true;
            submitText.innerHTML = '<span class="loading-dots">Enviando</span>';
            
            // Re-enable button after form submission
            setTimeout(() => {
                submitBtn.disabled = false;
                submitText.innerHTML = 'Enviar Mensaje';
            }, 2000);
        });

        // Add loading animation to buttons
        document.querySelectorAll('.btn-primary-modern, .btn-secondary-modern').forEach(btn => {
            btn.addEventListener('click', function() {
                if (this.getAttribute('href') === 'login.php') {
                    this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Cargando...';
                }
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in');
                }
            });
        }, observerOptions);

        // Observe all cards and sections
        document.querySelectorAll('.modern-card, .news-card, .glass-card').forEach(el => {
            observer.observe(el);
        });

        // Floating social icons interactions
        document.querySelectorAll('.social-icon').forEach((icon, index) => {
            // Add stagger animation on page load
            icon.style.animationDelay = `${index * 0.1}s`;
            
            // Add click analytics (optional)
            icon.addEventListener('click', function() {
                const platform = this.classList[1].replace('social-', '');
                console.log(`Social click: ${platform}`);
                
                // Add click feedback
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Show/hide floating icons on scroll
        let lastScrollTop = 0;
        window.addEventListener('scroll', function() {
            const floatingSocial = document.querySelector('.floating-social');
            const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (currentScrollTop > 100) {
                floatingSocial.style.opacity = '1';
                floatingSocial.style.transform = 'translateX(0)';
            } else {
                floatingSocial.style.opacity = '0.7';
                floatingSocial.style.transform = 'translateX(10px)';
            }
            
            lastScrollTop = currentScrollTop;
        });

        // Initialize floating icons with entrance animation
        setTimeout(() => {
            document.querySelector('.floating-social').style.opacity = '0.7';
            document.querySelector('.floating-social').style.transform = 'translateX(0)';
        }, 1000);

        // Gallery Filter Functionality
        const filterButtons = document.querySelectorAll('.filter-btn');
        const galleryItems = document.querySelectorAll('.gallery-item');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons
                filterButtons.forEach(btn => {
                    btn.classList.remove('active', 'bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30');
                    btn.classList.add('bg-white/5', 'text-white/80', 'border-white/20');
                });

                // Add active class to clicked button
                button.classList.add('active', 'bg-emerald-500/20', 'text-emerald-400', 'border-emerald-500/30');
                button.classList.remove('bg-white/5', 'text-white/80', 'border-white/20');

                const filter = button.getAttribute('data-filter');

                galleryItems.forEach(item => {
                    if (filter === 'all' || item.getAttribute('data-category') === filter) {
                        item.style.display = 'block';
                        item.style.animation = 'fadeIn 0.5s ease';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Add fadeIn animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
        `;
        document.head.appendChild(style);

        // Counter Animation
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            
            function updateCounter() {
                start += increment;
                if (start < target) {
                    element.textContent = Math.floor(start);
                    requestAnimationFrame(updateCounter);
                } else {
                    element.textContent = target;
                }
            }
            
            updateCounter();
        }

        // Intersection Observer for counters
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target'));
                    animateCounter(counter, target);
                    counterObserver.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });

        // Observe all counters
        document.querySelectorAll('.counter').forEach(counter => {
            counterObserver.observe(counter);
        });
    </script>
</body>
</html>
