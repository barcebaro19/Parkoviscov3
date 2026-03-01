<?php
require_once __DIR__ . "/../app/Controllers/wompi_integration.php";

// Configurar headers para webhook
header('Content-Type: application/json');

try {
    // Obtener el payload del webhook
    $payload = file_get_contents('php://input');
    $signature = $_SERVER['HTTP_SIGNATURE'] ?? '';
    
    if (empty($payload)) {
        throw new Exception('Payload vacío');
    }
    
    // Inicializar integración con Wompi
    $wompiIntegration = new WompiIntegration();
    
    // Procesar el webhook
    $resultado = $wompiIntegration->procesarWebhook($payload, $signature);
    
    if ($resultado['success']) {
        // Log del webhook exitoso
        error_log("Webhook Wompi procesado exitosamente - Pago ID: " . $resultado['pago_id'] . " - Estado: " . $resultado['estado']);
        
        // Enviar notificación por email si es necesario
        if ($resultado['estado'] === 'aprobado') {
            enviarNotificacionPagoAprobado($resultado['pago_id']);
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Webhook procesado correctamente']);
    } else {
        error_log("Error en webhook Wompi: " . $resultado['message']);
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $resultado['message']]);
    }
    
} catch (Exception $e) {
    error_log("Excepción en webhook Wompi: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error interno del servidor']);
}

// Función para enviar notificación de pago aprobado
function enviarNotificacionPagoAprobado($pago_id) {
    require_once __DIR__ . "/../app/Models/conexion.php";
    
    try {
        $conexion = Conexion::getInstancia()->getConexion();
        
        // Obtener información del pago y usuario
        $sql = "SELECT p.*, u.nombre, u.apellido, u.email, cp.nombre as concepto_nombre 
                FROM pagos p 
                INNER JOIN usuarios u ON p.usuario_id = u.id 
                INNER JOIN conceptos_pago cp ON p.concepto_id = cp.id 
                WHERE p.id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $pago_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $pago = $result->fetch_assoc();
        
        if ($pago) {
            // Enviar email de confirmación
            $asunto = "Pago Aprobado - Quintanares by Parkovisco";
            $mensaje = "Estimado/a " . $pago['nombre'] . " " . $pago['apellido'] . ",\n\n";
            $mensaje .= "Su pago ha sido procesado exitosamente:\n\n";
            $mensaje .= "Concepto: " . $pago['concepto_nombre'] . "\n";
            $mensaje .= "Monto: $" . number_format($pago['monto'], 0, ',', '.') . "\n";
            $mensaje .= "Fecha: " . date('d/m/Y H:i:s') . "\n";
            $mensaje .= "Referencia: " . $pago['referencia_wompi'] . "\n\n";
            $mensaje .= "Gracias por su pago puntual.\n\n";
            $mensaje .= "Quintanares by Parkovisco";
            
            $headers = "From: Administrador Quintanares by Parkovisco <parkovisco@gmail.com>\r\n";
            $headers .= "Reply-To: parkovisco@gmail.com\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            mail($pago['email'], $asunto, $mensaje, $headers);
        }
        
    } catch (Exception $e) {
        error_log("Error enviando notificación de pago: " . $e->getMessage());
    }
}
?>

