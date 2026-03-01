<?php
/**
 * EJEMPLO de Configuración de Google OAuth
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo como 'google_config.php'
 * 2. Ve a https://console.cloud.google.com/
 * 3. Crea un proyecto o selecciona uno existente
 * 4. Habilita Google+ API
 * 5. Crea credenciales OAuth 2.0
 * 6. Reemplaza los valores de ejemplo con tus datos reales
 */

return [
    // Credenciales de Google OAuth
    'client_id' => 'TU_CLIENT_ID_AQUI',
    'client_secret' => 'TU_CLIENT_SECRET_AQUI',
    'redirect_uri' => 'http://localhost/ci4-parkovisko/public/google_callback.php',
    
    // Configuración de OAuth
    'oauth' => [
        'enabled' => true, // Activar/desactivar Google OAuth
        'scope' => 'email profile', // Permisos solicitados
        'access_type' => 'offline', // Para obtener refresh token
        'prompt' => 'consent', // Forzar consentimiento
    ],
    
    // URLs de Google
    'urls' => [
        'auth' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token' => 'https://oauth2.googleapis.com/token',
        'userinfo' => 'https://www.googleapis.com/oauth2/v2/userinfo',
    ],
    
    // Configuración de logs
    'logging' => [
        'enabled' => true,
        'log_file' => 'storage/logs/google_oauth.log',
    ]
];


