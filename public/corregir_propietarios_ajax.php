<?php
session_start();
require_once '../app/Models/conexion.php';

header('Content-Type: application/json');

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'corregir_propietarios') {
        $conn->begin_transaction();
        
        $mensajes = [];
        
        // 1. Actualizar registros que tienen usuarios_id pero no usuario_id
        $result = $conn->query("
            UPDATE propietarios 
            SET usuario_id = usuarios_id 
            WHERE usuarios_id IS NOT NULL AND usuario_id IS NULL
        ");
        $actualizados = $conn->affected_rows;
        if ($actualizados > 0) {
            $mensajes[] = "Actualizados $actualizados registros con usuarios_id";
        }
        
        // 2. Eliminar registros completamente corruptos (todo NULL)
        $result = $conn->query("
            DELETE FROM propietarios 
            WHERE usuario_id IS NULL AND usuarios_id IS NULL
        ");
        $eliminados = $conn->affected_rows;
        if ($eliminados > 0) {
            $mensajes[] = "Eliminados $eliminados registros corruptos";
        }
        
        // 3. Verificar si la columna usuarios_id existe y eliminarla si no se usa
        $result = $conn->query("SHOW COLUMNS FROM propietarios LIKE 'usuarios_id'");
        if ($result->num_rows > 0) {
            // Verificar si hay datos en usuarios_id que no estén en usuario_id
            $result = $conn->query("
                SELECT COUNT(*) as count 
                FROM propietarios 
                WHERE usuarios_id IS NOT NULL AND usuarios_id != usuario_id
            ");
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                // Es seguro eliminar la columna
                $conn->query("ALTER TABLE propietarios DROP COLUMN usuarios_id");
                $mensajes[] = "Columna usuarios_id eliminada";
            } else {
                $mensajes[] = "Columna usuarios_id mantenida (tiene datos únicos)";
            }
        }
        
        $conn->commit();
        
        $mensajeFinal = implode(', ', $mensajes);
        if (empty($mensajeFinal)) {
            $mensajeFinal = "No se encontraron problemas para corregir";
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Corrección completada: $mensajeFinal"
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
