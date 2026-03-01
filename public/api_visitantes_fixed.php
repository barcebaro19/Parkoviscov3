<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Verificar que el usuario esté autenticado (simplificado para pruebas)
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1; // Simular usuario para pruebas
}

try {
    require_once '../app/Controllers/estadisticas_visitantes_fixed.php';
    $estadisticasController = new EstadisticasVisitantesFixed();
    $usuario_id = $_SESSION['id'];
    
    // Obtener el tipo de datos solicitado
    $tipo = $_GET['tipo'] ?? 'estadisticas';
    
    switch ($tipo) {
        case 'estadisticas':
            $resultado = $estadisticasController->obtenerEstadisticas($usuario_id);
            if ($resultado['success']) {
                $resultado['estadisticas'] = [
                    'total_visitantes' => $resultado['data']['este_mes'],
                    'visitantes_mes' => $resultado['data']['este_mes'],
                    'visitantes_hoy' => $resultado['data']['hoy'],
                    'visitantes_pendientes' => 0
                ];
                unset($resultado['data']);
            }
            break;
            
        case 'historial':
            $limite = $_GET['limite'] ?? 10;
            $resultado = $estadisticasController->obtenerHistorialVisitantes($usuario_id, $limite);
            if ($resultado['success']) {
                $resultado['historial'] = $resultado['data'];
                unset($resultado['data']);
            }
            break;
            
        case 'frecuentes':
            $limite = $_GET['limite'] ?? 5;
            $resultado = $estadisticasController->obtenerVisitantesFrecuentes($usuario_id, $limite);
            if ($resultado['success']) {
                $resultado['frecuentes'] = $resultado['data'];
                unset($resultado['data']);
            }
            break;
            
        case 'preautorizaciones':
            $resultado = $estadisticasController->obtenerPreautorizaciones($usuario_id);
            if ($resultado['success']) {
                $resultado['preautorizaciones'] = $resultado['data'];
                unset($resultado['data']);
            }
            break;
            
        default:
            throw new Exception('Tipo de datos no válido');
    }
    
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en api_visitantes_fixed.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
