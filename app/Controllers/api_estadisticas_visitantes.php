<?php
require_once __DIR__ . '/estadisticas_visitantes.php';

// Configurar headers para JSON
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Función para enviar respuesta JSON
function sendJsonResponse($data) {
    ob_clean();
    echo json_encode($data);
    exit;
}

try {
    // Verificar sesión
    session_start();
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Sesión no válida'
        ]);
    }

    // Obtener ID de usuario
    $usuario_id = $_SESSION['user_id'] ?? $_SESSION['id'];
    
    // Obtener acción
    $action = $_GET['action'] ?? 'estadisticas';
    
    // Crear instancia del controlador
    $estadisticas = new EstadisticasVisitantes();
    
    switch ($action) {
        case 'estadisticas':
            $resultado = $estadisticas->obtenerEstadisticas($usuario_id);
            break;
            
        case 'historial':
            $limite = $_GET['limite'] ?? 10;
            $resultado = $estadisticas->obtenerHistorialVisitantes($usuario_id, $limite);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
    }
    
    sendJsonResponse($resultado);
    
} catch (Exception $e) {
    error_log("Error en api_estadisticas_visitantes: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>