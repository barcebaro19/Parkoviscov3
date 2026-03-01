<?php
/**
 * API Dashboard de Pagos
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

session_start();
header('Content-Type: application/json');

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once __DIR__ . '/../Services/PaymentService.php';

$paymentService = new PaymentService();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_dashboard':
            $dashboard = $paymentService->obtenerDashboardPagos($_SESSION['user_id']);
            echo json_encode(['success' => true, 'data' => $dashboard]);
            break;
            
        case 'process_payment':
            $concepto_id = intval($_POST['concepto_id'] ?? 0);
            $metodo_pago = $_POST['metodo_pago'] ?? '';
            $metodo_pago_id = intval($_POST['metodo_pago_id'] ?? 0) ?: null;
            
            if (!$concepto_id || !$metodo_pago) {
                throw new Exception('Datos de pago incompletos');
            }
            
            $resultado = $paymentService->procesarPago(
                $_SESSION['user_id'],
                $concepto_id,
                $metodo_pago,
                $metodo_pago_id
            );
            
            echo json_encode($resultado);
            break;
            
        case 'add_payment_method':
            $tipo = $_POST['tipo'] ?? '';
            $datosTarjeta = [
                'number' => $_POST['numero_tarjeta'] ?? '',
                'holder_name' => $_POST['nombre_titular'] ?? '',
                'exp_month' => $_POST['mes_expiracion'] ?? '',
                'exp_year' => $_POST['año_expiracion'] ?? '',
                'cvc' => $_POST['cvc'] ?? ''
            ];
            
            if (!$tipo || !$datosTarjeta['number']) {
                throw new Exception('Datos de tarjeta incompletos');
            }
            
            $resultado = $paymentService->agregarMetodoPago(
                $_SESSION['user_id'],
                $tipo,
                $datosTarjeta
            );
            
            echo json_encode($resultado);
            break;
            
        case 'generate_receipt':
            $pago_id = intval($_GET['pago_id'] ?? 0);
            
            if (!$pago_id) {
                throw new Exception('ID de pago requerido');
            }
            
            $resultado = $paymentService->generarRecibo($pago_id, $_SESSION['user_id']);
            echo json_encode($resultado);
            break;
            
        case 'create_automatic_payments':
            $resultado = $paymentService->crearPagosAutomaticos($_SESSION['user_id']);
            echo json_encode($resultado);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en API pagos: " . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>


