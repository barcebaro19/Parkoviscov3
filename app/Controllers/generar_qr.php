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

session_start();

require_once __DIR__ . '/../Models/conexion.php';
require_once __DIR__ . '/visitantes_controller.php';
require_once __DIR__ . '/QRCode.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Verificar autenticación
if (!isset($_SESSION['id']) || !isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'propietario') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado o sin permisos'
    ]);
    exit();
}

try {
    // Log de depuración
    error_log("=== GENERAR QR - INICIO ===");
    error_log("Método: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'No definido'));
    
    // Obtener datos JSON del cuerpo de la petición
    $input = file_get_contents('php://input');
    error_log("Input recibido: " . $input);
    
    $data = json_decode($input, true);
    error_log("Datos decodificados: " . print_r($data, true));
    
    if (!$data) {
        throw new Exception('Datos JSON inválidos');
    }
    
    // Validar datos requeridos
    $required_fields = ['nombre', 'documento'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("El campo '$field' es requerido");
        }
    }
    
    // Crear instancia del controlador
    $visitantesController = new VisitantesController();
    
    // Procesar según el tipo de QR
    if (isset($data['tipo']) && $data['tipo'] === 'visitante') {
        // Generar QR para visitante
        $resultado = $visitantesController->generarQRVisitante($data);
        
        if ($resultado['success']) {
            // Generar URL de la imagen QR (versión simplificada)
            $qr_data = $resultado['qr_data'];
            $qr_image_url = QRCode::generate($qr_data['codigo']);
            
            $resultado['qr_image_url'] = $qr_image_url;
            $resultado['valid_until'] = $data['validez'];
        }
        
    } else {
        // Generar QR para vehículo (lógica existente)
        $resultado = generarQRVehiculo($data);
    }
    
    // Log de respuesta
    error_log("Respuesta enviada: " . json_encode($resultado));
    
    // Enviar respuesta
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en generar_qr.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}


/**
 * Generar QR para vehículo (función existente adaptada)
 */
function generarQRVehiculo($data) {
    // Aquí puedes adaptar la lógica existente para vehículos
    // Por ahora retorno un placeholder
    return [
        'success' => true,
        'message' => 'QR de vehículo generado',
        'qr_data' => [
            'codigo' => 'VEH_' . time(),
            'tipo' => 'vehiculo',
            'placa' => $data['placa'] ?? 'N/A'
        ]
    ];
}
?>