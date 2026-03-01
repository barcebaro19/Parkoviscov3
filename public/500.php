<?php
/**
 * Página de Error 500
 * Quintanares by Parkovisco
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error del Servidor - Quintanares</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="mb-8">
            <i class="fas fa-server text-6xl text-red-400 mb-4"></i>
            <h1 class="text-4xl font-bold text-white mb-2">500</h1>
            <h2 class="text-2xl text-red-300 mb-4">Error del Servidor</h2>
            <p class="text-gray-300 mb-8 max-w-md mx-auto">
                Ha ocurrido un error interno del servidor. Por favor, intenta más tarde.
            </p>
        </div>
        
        <div class="space-y-4">
            <a href="/" class="inline-block bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">
                <i class="fas fa-home mr-2"></i>
                Ir al Inicio
            </a>
            
            <div class="text-gray-400 text-sm">
                <p>Quintanares Residencial - Sistema de Gestión</p>
                <p>Si el problema persiste, contacta al administrador</p>
            </div>
        </div>
    </div>
</body>
</html>
