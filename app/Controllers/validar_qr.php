<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../Models/conexion.php';
require_once __DIR__ . '/visitantes_controller.php';

try {
    // Obtener datos JSON del cuerpo de la petición
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Verificar que se proporcionó el código QR
    if (empty($data['codigo'])) {
        throw new Exception('Código QR requerido');
    }
    
    $codigo_qr = $data['codigo'];
    $accion = $data['action'] ?? 'validar';
    
    // Crear instancia del controlador
    $visitantesController = new VisitantesController();
    
    switch ($accion) {
        case 'validar':
            $resultado = $visitantesController->validarCodigoQR($codigo_qr);
            break;
            
        case 'usar':
            // Marcar QR como usado
            $validacion = $visitantesController->validarCodigoQR($codigo_qr);
            if ($validacion['valido']) {
                $visitantesController->marcarQRUsado($codigo_qr);
                $resultado = [
                    'success' => true,
                    'message' => 'Código QR usado exitosamente',
                    'datos' => $validacion['datos']
                ];
            } else {
                $resultado = [
                    'success' => false,
                    'message' => $validacion['mensaje']
                ];
            }
            break;
            
        case 'desactivar':
            // Desactivar QR
            $visitantesController->marcarQRExpirado($codigo_qr);
            $resultado = [
                'success' => true,
                'message' => 'Código QR desactivado exitosamente'
            ];
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
    // Enviar respuesta
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en validar_qr.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>