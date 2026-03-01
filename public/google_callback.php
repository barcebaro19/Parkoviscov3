<?php
/**
 * Callback de Google OAuth
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

session_start();
require_once '../app/Services/GoogleOAuthService.php';

try {
    // Verificar que se recibió el código de autorización
    if (!isset($_GET['code'])) {
        throw new Exception('Código de autorización no recibido');
    }
    
    $code = $_GET['code'];
    error_log("Google OAuth callback recibido con código: " . substr($code, 0, 10) . "...");
    
    // Procesar login con Google
    $googleService = new GoogleOAuthService();
    $userData = $googleService->processGoogleLogin($code);
    error_log("Datos de usuario obtenidos: " . json_encode($userData));
    
    // Crear o actualizar usuario en la base de datos
    $userId = $googleService->createOrUpdateUser($userData);
    error_log("Usuario creado/actualizado con ID: " . $userId);
    
    // Registrar log de autenticación exitosa
    $googleService->registrarLogAuth($userId, 'google_oauth', 'google', [
        'email' => $userData['email'],
        'nombre' => $userData['nombre'],
        'google_id' => $userData['google_id']
    ]);
    
    // Obtener datos completos del usuario
    require_once '../app/Models/conexion.php';
    $conn = Conexion::getInstancia()->getConexion();
    
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . $conn->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Crear sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['nombre'] = $user['nombre'] ?? '';
        $_SESSION['apellido'] = $user['apellido'] ?? '';
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'] ?? 'propietario';
        $_SESSION['foto'] = $user['foto_url'] ?? null;
        $_SESSION['login_method'] = 'google';
        $_SESSION['google_id'] = $user['google_id'] ?? null;
        
        // Crear sesión en la base de datos
        $googleService->crearSesionUsuario($user['id'], session_id(), 'google');
        
        // Registrar log de login exitoso
        $googleService->registrarLogAuth($user['id'], 'login_exitoso', 'google', [
            'session_id' => session_id(),
            'metodo' => 'google_oauth'
        ]);
        
        // Redirigir al dashboard
        header('Location: usuario.php');
        exit();
    } else {
        throw new Exception('Error obteniendo datos del usuario');
    }
    
} catch (Exception $e) {
    // Error en el proceso
    error_log("Error en Google OAuth: " . $e->getMessage());
    
    // Redirigir al login con error
    header('Location: login.php?error=google_oauth_error');
    exit();
}
?>
