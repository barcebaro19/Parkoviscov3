<?php
/**
 * Iniciar autenticación con Google
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

require_once 'GoogleOAuthService.php';

try {
    $googleService = new GoogleOAuthService();
    
    // Verificar configuración
    $config = $googleService->verificarConfiguracion();
    if (!$config['valido']) {
        throw new Exception('Configuración de Google OAuth incompleta');
    }
    
    // Obtener URL de autorización
    $authUrl = $googleService->getAuthUrl();
    
    // Redirigir a Google
    header('Location: ' . $authUrl);
    exit();
    
} catch (Exception $e) {
    error_log("Error iniciando Google OAuth: " . $e->getMessage());
    
    // Redirigir al login con error
    header('Location: ../public/login.php?error=google_config_error');
    exit();
}
?>


