<?php
session_start();
require_once __DIR__ . "/../Models/conexion.php";

// Verificar que el usuario sea administrador
if(!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

// Verificar que sea una petición POST
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener datos del JSON
$input = json_decode(file_get_contents('php://input'), true);

if(!isset($input['id']) || !is_numeric($input['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de propietario inválido']);
    exit();
}

$propietario_id = (int)$input['id'];

try {
    $conexion = Conexion::getInstancia()->getConexion();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    // Verificar que el propietario existe
    $sql_check = "SELECT u.id, u.nombre, u.apellido 
                  FROM usuarios u 
                  JOIN usu_roles ur ON u.id = ur.usuarios_id 
                  JOIN roles r ON ur.roles_idroles = r.idroles 
                  WHERE u.id = ? AND r.nombre_rol = 'propietario'";
    
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param("i", $propietario_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if($result_check->num_rows === 0) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'message' => 'Propietario no encontrado']);
        exit();
    }
    
    $propietario = $result_check->fetch_assoc();
    
    // Eliminar registros relacionados en orden (por restricciones de clave foránea)
    
    // 1. Eliminar notificaciones
    $sql_notif = "DELETE FROM notificaciones WHERE id_usuario = ?";
    $stmt_notif = $conexion->prepare($sql_notif);
    $stmt_notif->bind_param("i", $propietario_id);
    $stmt_notif->execute();
    
    // 2. Eliminar reportes de daños
    $sql_danos = "DELETE FROM danos WHERE id_usuario = ?";
    $stmt_danos = $conexion->prepare($sql_danos);
    $stmt_danos->bind_param("i", $propietario_id);
    $stmt_danos->execute();
    
    // 3. Eliminar visitas
    $sql_visitas = "DELETE FROM visitas WHERE id_usuario = ?";
    $stmt_visitas = $conexion->prepare($sql_visitas);
    $stmt_visitas->bind_param("i", $propietario_id);
    $stmt_visitas->execute();
    
    // 4. Eliminar vehículos
    $sql_vehiculos = "DELETE FROM vehiculos WHERE id_usuario = ?";
    $stmt_vehiculos = $conexion->prepare($sql_vehiculos);
    $stmt_vehiculos->bind_param("i", $propietario_id);
    $stmt_vehiculos->execute();
    
    // 5. Eliminar de la tabla propietarios
    $sql_propietario = "DELETE FROM propietarios WHERE usuarios_id = ?";
    $stmt_propietario = $conexion->prepare($sql_propietario);
    $stmt_propietario->bind_param("i", $propietario_id);
    $stmt_propietario->execute();
    
    // 6. Eliminar de usu_roles
    $sql_usu_roles = "DELETE FROM usu_roles WHERE usuarios_id = ?";
    $stmt_usu_roles = $conexion->prepare($sql_usu_roles);
    $stmt_usu_roles->bind_param("i", $propietario_id);
    $stmt_usu_roles->execute();
    
    // 7. Finalmente, eliminar de usuarios
    $sql_usuario = "DELETE FROM usuarios WHERE id = ?";
    $stmt_usuario = $conexion->prepare($sql_usuario);
    $stmt_usuario->bind_param("i", $propietario_id);
    $stmt_usuario->execute();
    
    // Confirmar transacción
    $conexion->commit();
    
    // Log de la acción (opcional)
    error_log("Propietario eliminado: ID {$propietario_id}, Nombre: {$propietario['nombre']} {$propietario['apellido']}");
    
        echo json_encode([
            'success' => true,
            'message' => 'Propietario eliminado exitosamente'
        ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conexion->rollback();
    
    error_log("Error al eliminar propietario: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor al eliminar el propietario'
    ]);
}
?>
