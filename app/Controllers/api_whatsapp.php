<?php
require_once __DIR__ . '/../Services/WhatsAppService.php';

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
    // Verificar método HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse([
            'success' => false,
            'message' => 'Método no permitido'
        ]);
    }
    
    // Verificar sesión
    session_start();
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Sesión no válida'
        ]);
    }
    
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    // Crear instancia del servicio
    $whatsappService = new WhatsAppService();
    
    switch ($action) {
        case 'enviar_mensaje':
            $numero = $input['numero'] ?? '';
            $mensaje = $input['mensaje'] ?? '';
            $tipo = $input['tipo'] ?? 'texto';
            
            if (empty($numero) || empty($mensaje)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Número y mensaje son requeridos'
                ]);
            }
            
            $resultado = $whatsappService->enviarMensajePersonalizado($numero, $mensaje, $tipo);
            sendJsonResponse($resultado);
            break;
            
        case 'enviar_confirmacion_qr':
            $datos_visitante = $input['datos_visitante'] ?? [];
            $codigo_qr = $input['codigo_qr'] ?? '';
            $datos_propietario = $input['datos_propietario'] ?? [];
            
            if (empty($datos_visitante['telefono']) || empty($codigo_qr)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Datos de visitante y código QR son requeridos'
                ]);
            }
            
            $resultado = $whatsappService->enviarConfirmacionQR($datos_visitante, $codigo_qr, $datos_propietario);
            sendJsonResponse($resultado);
            break;
            
        case 'enviar_cancelacion':
            $numero = $input['numero'] ?? '';
            $datos_reserva = $input['datos_reserva'] ?? [];
            
            if (empty($numero)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Número de teléfono es requerido'
                ]);
            }
            
            $resultado = $whatsappService->enviarReservaCancelada($numero, $datos_reserva);
            sendJsonResponse($resultado);
            break;
            
        case 'enviar_recordatorio':
            $numero = $input['numero'] ?? '';
            $datos_reserva = $input['datos_reserva'] ?? [];
            
            if (empty($numero)) {
                sendJsonResponse([
                    'success' => false,
                    'message' => 'Número de teléfono es requerido'
                ]);
            }
            
            $resultado = $whatsappService->enviarRecordatorio($numero, $datos_reserva);
            sendJsonResponse($resultado);
            break;
            
        case 'verificar_estado':
            $resultado = $whatsappService->verificarDisponibilidad();
            sendJsonResponse($resultado);
            break;
            
        case 'obtener_logs':
            $logFile = __DIR__ . '/../../storage/logs/whatsapp_messages.log';
            $logs = [];
            
            if (file_exists($logFile)) {
                $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $logs = array_map('json_decode', array_slice($lines, -50)); // Últimos 50 logs
            }
            
            sendJsonResponse([
                'success' => true,
                'logs' => $logs
            ]);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
    }
    
} catch (Exception $e) {
    error_log("Error en api_whatsapp: " . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
?>
