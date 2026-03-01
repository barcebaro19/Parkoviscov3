<?php
header('Content-Type: application/json');

session_start();

// Verificar autenticación
$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$tipo_usuario = $_SESSION['tipo_usuario'] ?? $_SESSION['nombre_rol'] ?? null;

if (!$user_id || $tipo_usuario !== 'propietario') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado o sin permisos'
    ]);
    exit();
}

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit();
}

try {
    // Obtener datos JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos JSON inválidos'
        ]);
        exit();
    }
    
    // Validar acción
    if (empty($data['action'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Acción requerida'
        ]);
        exit();
    }
    
    // Incluir conexión
    require_once __DIR__ . '/../Models/conexion.php';
    $conn = Conexion::getInstancia()->getConexion();
    
    $action = $data['action'];
    
    if ($action === 'eliminar') {
        if (empty($data['id_reserva'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de reserva requerido'
            ]);
            exit();
        }
        
        $id_reserva = intval($data['id_reserva']);
        
        // Verificar que la reserva pertenece al usuario
        $sql = "SELECT r.id_reser, v.nombre_visitante 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                WHERE r.id_reser = ? AND p.usuario_id = ?";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'message' => 'Error preparando consulta: ' . $conn->error
            ]);
            exit();
        }
        
        $stmt->bind_param("ii", $id_reserva, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Reserva no encontrada o sin permisos'
            ]);
            exit();
        }
        
        $reserva = $result->fetch_assoc();
        
        // Eliminar la reserva
        $sql_delete = "DELETE FROM reservas WHERE id_reser = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        
        if (!$stmt_delete) {
            echo json_encode([
                'success' => false,
                'message' => 'Error preparando consulta de eliminación: ' . $conn->error
            ]);
            exit();
        }
        
        $stmt_delete->bind_param("i", $id_reserva);
        
        if ($stmt_delete->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Reserva eliminada exitosamente',
                'data' => [
                    'id_reserva' => $id_reserva,
                    'visitante' => $reserva['nombre_visitante']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la reserva'
            ]);
        }
        
    } elseif ($action === 'editar') {
        // Implementar edición aquí
        echo json_encode([
            'success' => false,
            'message' => 'Función de edición en desarrollo'
        ]);
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
