<?php
// Desactivar completamente errores y warnings
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_end_clean();
}

// Iniciar buffer de salida limpio
ob_start();

// Headers para JSON puro
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Función para enviar respuesta JSON limpia
function sendJsonResponse($data, $httpCode = 200) {
    // Limpiar buffer completamente
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    // Establecer código de respuesta HTTP
    http_response_code($httpCode);
    
    // Enviar solo JSON
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJsonResponse(['status' => 'ok'], 200);
}

session_start();

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Verificar autenticación - Compatible con Google OAuth y login tradicional
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$user_id) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ], 401);
}

// Verificar que el usuario es propietario (existe en tabla propietarios)
require_once __DIR__ . '/../Models/conexion.php';
$conn = Conexion::getInstancia()->getConexion();

$sql = "SELECT COUNT(*) as count FROM propietarios WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    sendJsonResponse([
        'success' => false,
        'message' => 'Usuario no tiene permisos de propietario'
    ], 403);
}

// Normalizar variables de sesión para compatibilidad
$_SESSION['id'] = $user_id;
$_SESSION['nombre_rol'] = 'propietario';

try {
    // Obtener datos JSON del cuerpo de la petición
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Datos JSON inválidos'
        ], 400);
    }
    
    // Validar datos requeridos
    $required_fields = ['nombre', 'documento'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendJsonResponse([
                'success' => false,
                'message' => "El campo '$field' es requerido"
            ], 400);
        }
    }
    
    // Incluir el controlador de visitantes
    require_once __DIR__ . '/visitantes_controller.php';
    
    // Crear instancia del controlador
    $visitantesController = new VisitantesController();
    
    // Generar QR usando el controlador (esto SÍ guarda en la base de datos)
    error_log("Generando QR con datos: " . json_encode($data));
    $resultado = $visitantesController->generarQRVisitante($data);
    error_log("Resultado de generarQRVisitante: " . json_encode($resultado));
    
    if ($resultado['success']) {
        // Agregar URL de la imagen QR
        $resultado['qr_image_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($resultado['qr_code']);
        $resultado['valid_until'] = $resultado['qr_data']['valido_hasta'];
        error_log("QR generado exitosamente: " . $resultado['qr_code']);
    } else {
        error_log("Error generando QR: " . $resultado['message']);
    }
    
    // Agregar URL de imagen QR si el QR se generó exitosamente
    if ($resultado['success'] && isset($resultado['qr_code'])) {
        $resultado['qr_image_url'] = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($resultado['qr_code']);
        $resultado['valid_until'] = $resultado['qr_data']['valido_hasta'] ?? date('Y-m-d H:i:s', strtotime('+24 hours'));
    }
    
    // Enviar respuesta usando función limpia
    error_log("Enviando respuesta: " . json_encode($resultado));
    sendJsonResponse($resultado);
    
} catch (Exception $e) {
    error_log("Error en generar_qr_simple.php: " . $e->getMessage());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ], 500);
}
?>
