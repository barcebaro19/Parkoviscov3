<?php
session_start();
require_once __DIR__ . '/pagos_controller.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id']) || $_SESSION['nombre_rol'] !== 'propietario') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

header('Content-Type: application/json');

$pagosController = new PagosController();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'historial':
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['id']);
            $limite = intval($_GET['limite'] ?? 10);
            
            $pagos = $pagosController->obtenerHistorialPagos($usuario_id, $limite);
            echo json_encode(['success' => true, 'pagos' => $pagos]);
            break;
            
        case 'pendientes':
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['id']);
            
            $pagos = $pagosController->obtenerPagosPendientes($usuario_id);
            echo json_encode(['success' => true, 'pagos' => $pagos]);
            break;
            
        case 'resumen':
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['id']);
            
            $resumen = $pagosController->obtenerResumenPagos($usuario_id);
            echo json_encode(['success' => true, ...$resumen]);
            break;
            
        case 'metodos':
            $usuario_id = intval($_GET['usuario_id'] ?? $_SESSION['id']);
            
            $metodos = $pagosController->obtenerMetodosPago($usuario_id);
            echo json_encode(['success' => true, 'metodos' => $metodos]);
            break;
            
        case 'crear_pago':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $concepto_id = intval($_POST['concepto_id'] ?? 0);
            $metodo_pago = $_POST['metodo_pago'] ?? '';
            $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
            
            if (!$concepto_id || !$metodo_pago || !$fecha_vencimiento) {
                throw new Exception('Todos los campos son obligatorios');
            }
            
            $resultado = $pagosController->crearPago(
                $_SESSION['id'],
                $concepto_id,
                $metodo_pago,
                $fecha_vencimiento
            );
            
            echo json_encode($resultado);
            break;
            
        case 'eliminar_metodo':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Método no permitido');
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $metodo_id = intval($input['metodo_id'] ?? 0);
            
            if (!$metodo_id) {
                throw new Exception('ID de método no válido');
            }
            
            $resultado = $pagosController->eliminarMetodoPago($metodo_id, $_SESSION['id']);
            echo json_encode($resultado);
            break;
            
        case 'verificar_estado':
            $pago_id = intval($_GET['pago_id'] ?? 0);
            
            if (!$pago_id) {
                throw new Exception('ID de pago no válido');
            }
            
            $pago = $pagosController->obtenerPagoPorId($pago_id, $_SESSION['id']);
            if (!$pago) {
                throw new Exception('Pago no encontrado');
            }
            
            echo json_encode(['success' => true, 'pago' => $pago]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>

