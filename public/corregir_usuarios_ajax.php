<?php
session_start();
require_once '../app/Models/conexion.php';

header('Content-Type: application/json');

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'corregir_por_tipo') {
        // Crear propietarios para usuarios con tipo_usuario = 'propietario'
        $result = $conn->query("
            INSERT INTO propietarios (usuario_id)
            SELECT u.id
            FROM usuarios u
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE (u.tipo_usuario = 'propietario' OR u.tipo_usuario = 'Propietario')
            AND p.usuario_id IS NULL
        ");
        
        $creados = $conn->affected_rows;
        echo json_encode([
            'success' => true, 
            'message' => "✅ Creados $creados registros en propietarios para usuarios con tipo_usuario = propietario"
        ]);
        
    } elseif ($action === 'corregir_por_rol') {
        // Crear propietarios para usuarios con rol de propietario (ID 3)
        $result = $conn->query("
            INSERT INTO propietarios (usuario_id)
            SELECT u.id
            FROM usuarios u
            INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE ur.roles_idroles = 3 AND p.usuario_id IS NULL
        ");
        
        $creados = $conn->affected_rows;
        echo json_encode([
            'success' => true, 
            'message' => "✅ Creados $creados registros en propietarios para usuarios con rol de propietario"
        ]);
        
    } elseif ($action === 'corregir_todos') {
        $conn->begin_transaction();
        
        $creados1 = 0;
        $creados2 = 0;
        
        // Crear por tipo_usuario
        $result = $conn->query("
            INSERT INTO propietarios (usuario_id)
            SELECT u.id
            FROM usuarios u
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE (u.tipo_usuario = 'propietario' OR u.tipo_usuario = 'Propietario')
            AND p.usuario_id IS NULL
        ");
        $creados1 = $conn->affected_rows;
        
        // Crear por rol
        $result = $conn->query("
            INSERT INTO propietarios (usuario_id)
            SELECT u.id
            FROM usuarios u
            INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE ur.roles_idroles = 3 AND p.usuario_id IS NULL
        ");
        $creados2 = $conn->affected_rows;
        
        $conn->commit();
        
        $total = $creados1 + $creados2;
        echo json_encode([
            'success' => true, 
            'message' => "✅ Corrección completada: $total registros creados ($creados1 por tipo, $creados2 por rol)"
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>