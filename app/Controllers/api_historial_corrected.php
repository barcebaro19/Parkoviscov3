<?php
require_once '../Models/conexion.php';
require_once 'estadisticas_visitantes_corrected.php';

// Configurar headers para JSON
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Función para enviar respuesta JSON limpia
function sendJsonResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit();
}

try {
    // Verificar método
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Método no permitido'
        ], 405);
    }
    
    // Verificar autenticación
    session_start();
    $user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
    
    if (!$user_id) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Usuario no autenticado'
        ], 401);
    }
    
    // Obtener límite
    $limite = $_GET['limite'] ?? 10;
    
    // Crear instancia del controlador corregido
    $estadisticasController = new EstadisticasVisitantesControllerCorrected();
    
    // Obtener historial
    $resultado = $estadisticasController->obtenerHistorialVisitantes($user_id, $limite);
    
    if ($resultado['success']) {
        $resultado['historial'] = $resultado['data'];
        unset($resultado['data']);
    }
    
    sendJsonResponse($resultado);
    
} catch (Exception $e) {
    error_log("Error en api_historial_corrected.php: " . $e->getMessage());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ], 500);
}
?>
