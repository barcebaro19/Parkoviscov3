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

// Verificar autenticación - Compatible con Google OAuth y login tradicional
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo_usuario'] ?? $_SESSION['nombre_rol'] ?? null;

if (!$user_id || $tipo_usuario !== 'propietario') {
    sendJsonResponse([
        'success' => false,
        'message' => 'Usuario no autenticado o sin permisos'
    ], 401);
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse([
        'success' => false,
        'message' => 'Método no permitido'
    ], 405);
}

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
    
    // Validar acción requerida
    if (empty($data['action'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'Acción requerida'
        ], 400);
    }
    
    // Incluir conexión a la base de datos
    require_once __DIR__ . '/../Models/conexion.php';
    $conn = Conexion::getInstancia()->getConexion();
    
    $action = $data['action'];
    
    switch ($action) {
        case 'eliminar':
            eliminarReserva($conn, $data, $user_id);
            break;
            
        case 'editar':
            editarReserva($conn, $data, $user_id);
            break;
            
        case 'listar':
            listarReservas($conn, $user_id);
            break;
            
        default:
            sendJsonResponse([
                'success' => false,
                'message' => 'Acción no válida'
            ], 400);
    }
    
} catch (Exception $e) {
    error_log("Error en api_reservas.php: " . $e->getMessage());
    
    sendJsonResponse([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ], 500);
}

/**
 * Eliminar una reserva
 */
function eliminarReserva($conn, $data, $user_id) {
    if (empty($data['id_reserva'])) {
        sendJsonResponse([
            'success' => false,
            'message' => 'ID de reserva requerido'
        ], 400);
    }
    
    $id_reserva = intval($data['id_reserva']);
    
    try {
    // Verificar que la reserva pertenece al usuario
    $sql_verificar = "SELECT r.id_reser, v.nombre_visitante 
                     FROM reservas r 
                     INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                     INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                     WHERE r.id_reser = ? AND p.usuario_id = ?";
        
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("ii", $id_reserva, $user_id);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows === 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Reserva no encontrada o sin permisos'
            ], 404);
        }
        
        $reserva = $result_verificar->fetch_assoc();
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        // Obtener el ID del visitante antes de eliminar
        $sql_visitante = "SELECT visitante_id_visit FROM reservas WHERE id_reser = ?";
        $stmt_visitante = $conn->prepare($sql_visitante);
        $stmt_visitante->bind_param("i", $id_reserva);
        $stmt_visitante->execute();
        $result_visitante = $stmt_visitante->get_result();
        
        if ($result_visitante->num_rows === 0) {
            throw new Exception('Reserva no encontrada');
        }
        
        $visitante_data = $result_visitante->fetch_assoc();
        $visitante_id = $visitante_data['visitante_id_visit'];
        
        error_log("Eliminando reserva ID: $id_reserva, Visitante ID: $visitante_id");
        
        // 1. Eliminar la reserva
        $sql_eliminar_reserva = "DELETE FROM reservas WHERE id_reser = ?";
        $stmt_eliminar_reserva = $conn->prepare($sql_eliminar_reserva);
        
        if (!$stmt_eliminar_reserva) {
            throw new Exception('Error preparando consulta de eliminación de reserva: ' . $conn->error);
        }
        
        $stmt_eliminar_reserva->bind_param("i", $id_reserva);
        
        if (!$stmt_eliminar_reserva->execute()) {
            throw new Exception('Error al eliminar la reserva: ' . $stmt_eliminar_reserva->error);
        }
        
        $filas_reserva = $stmt_eliminar_reserva->affected_rows;
        error_log("Filas afectadas en eliminación de reserva: $filas_reserva");
        
        if ($filas_reserva === 0) {
            throw new Exception('No se eliminó ninguna reserva. Verificar que el ID existe.');
        }
        
        // 2. Eliminar el visitante
        $sql_eliminar_visitante = "DELETE FROM visitantes WHERE id_visit = ?";
        $stmt_eliminar_visitante = $conn->prepare($sql_eliminar_visitante);
        
        if (!$stmt_eliminar_visitante) {
            throw new Exception('Error preparando consulta de eliminación de visitante: ' . $conn->error);
        }
        
        $stmt_eliminar_visitante->bind_param("i", $visitante_id);
        
        if (!$stmt_eliminar_visitante->execute()) {
            throw new Exception('Error al eliminar el visitante: ' . $stmt_eliminar_visitante->error);
        }
        
        $filas_visitante = $stmt_eliminar_visitante->affected_rows;
        error_log("Filas afectadas en eliminación de visitante: $filas_visitante");
        
        if ($filas_visitante === 0) {
            error_log("Advertencia: No se eliminó ningún visitante (puede que ya no exista)");
        }
        
        // Confirmar transacción
        $conn->commit();
        
        error_log("Reserva y visitante eliminados exitosamente: ID $id_reserva, Visitante: " . $reserva['nombre_visitante']);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Reserva y visitante eliminados exitosamente de ambas tablas',
            'data' => [
                'id_reserva' => $id_reserva,
                'id_visitante' => $visitante_id,
                'visitante' => $reserva['nombre_visitante']
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback en caso de error
        $conn->rollback();
        
        error_log("Error eliminando reserva: " . $e->getMessage());
        
        sendJsonResponse([
            'success' => false,
            'message' => 'Error al eliminar la reserva: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Editar una reserva
 */
function editarReserva($conn, $data, $user_id) {
    $required_fields = ['id_reserva', 'nombre', 'documento'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            sendJsonResponse([
                'success' => false,
                'message' => "El campo '$field' es requerido"
            ], 400);
        }
    }
    
    $id_reserva = intval($data['id_reserva']);
    $nombre = trim($data['nombre']);
    $documento = trim($data['documento']);
    $telefono = trim($data['telefono'] ?? '');
    $motivo = trim($data['motivo'] ?? 'Visita familiar');
    $fecha = $data['fecha'] ?? date('Y-m-d H:i:s');
    $observaciones = trim($data['observaciones'] ?? '');
    
    try {
    // Verificar que la reserva pertenece al usuario
    $sql_verificar = "SELECT r.id_reser, v.id_visit, v.nombre_visitante 
                     FROM reservas r 
                     INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                     INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                     WHERE r.id_reser = ? AND p.usuario_id = ?";
        
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("ii", $id_reserva, $user_id);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows === 0) {
            sendJsonResponse([
                'success' => false,
                'message' => 'Reserva no encontrada o sin permisos'
            ], 404);
        }
        
        $reserva_actual = $result_verificar->fetch_assoc();
        $id_visitante = $reserva_actual['id_visit'];
        
        // Iniciar transacción
        $conn->begin_transaction();
        
        // Actualizar datos del visitante
        $sql_visitante = "UPDATE visitantes 
                         SET nombre_visitante = ?, documento = ?, telefono = ?
                         WHERE id_visit = ?";
        
        $stmt_visitante = $conn->prepare($sql_visitante);
        $stmt_visitante->bind_param("sssi", $nombre, $documento, $telefono, $id_visitante);
        
        if (!$stmt_visitante->execute()) {
            throw new Exception('Error al actualizar datos del visitante');
        }
        
        // Actualizar datos de la reserva
        $sql_reserva = "UPDATE reservas SET fecha_inicial = ?, motivo_visita = ?, observaciones = ? WHERE id_reser = ?";
        $stmt_reserva = $conn->prepare($sql_reserva);
        $stmt_reserva->bind_param("sssi", $fecha, $motivo, $observaciones, $id_reserva);
        
        if (!$stmt_reserva->execute()) {
            throw new Exception('Error al actualizar datos de la reserva');
        }
        
        // Confirmar transacción
        $conn->commit();
        
        error_log("Reserva actualizada exitosamente: ID $id_reserva, Visitante: $nombre");
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Reserva actualizada exitosamente',
            'data' => [
                'id_reserva' => $id_reserva,
                'visitante' => $nombre,
                'documento' => $documento,
                'telefono' => $telefono,
                'motivo' => $motivo,
                'fecha' => $fecha,
                'observaciones' => $observaciones
            ]
        ]);
        
    } catch (Exception $e) {
        // Rollback en caso de error
        $conn->rollback();
        
        error_log("Error actualizando reserva: " . $e->getMessage());
        
        sendJsonResponse([
            'success' => false,
            'message' => 'Error al actualizar la reserva: ' . $e->getMessage()
        ], 500);
    }
}

function listarReservas($conn, $user_id) {
    try {
        // Obtener reservas del usuario
        $sql = "SELECT r.id_reser, v.nombre_visitante, r.fecha_inicial, r.codigo_qr, r.estado_qr, r.motivo_visita, r.observaciones
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                WHERE p.usuario_id = ? 
                ORDER BY r.fecha_inicial DESC 
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando consulta: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            $reservas[] = $row;
        }
        
        sendJsonResponse([
            'success' => true,
            'data' => $reservas,
            'total' => count($reservas)
        ]);
        
    } catch (Exception $e) {
        error_log("Error listando reservas: " . $e->getMessage());
        
        sendJsonResponse([
            'success' => false,
            'message' => 'Error al listar reservas: ' . $e->getMessage()
        ], 500);
    }
}
?>
