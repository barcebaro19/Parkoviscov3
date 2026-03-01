<?php
/**
 * API Dashboard
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

require_once __DIR__ . '/dashboard_controller.php';

$dashboardController = new DashboardController();
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$usuario_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_dashboard':
        $data = $dashboardController->obtenerDatosDashboard($usuario_id);
        echo json_encode($data);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>


