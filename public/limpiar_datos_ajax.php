<?php
session_start();
require_once '../app/Models/conexion.php';

header('Content-Type: application/json');

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'limpiar_corruptos') {
        // Eliminar propietarios con usuario_id = 0 o que no existen
        $result = $conn->query("
            DELETE p FROM propietarios p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id = 0 OR u.id IS NULL
        ");
        
        $eliminados = $conn->affected_rows;
        
        echo json_encode([
            'success' => true, 
            'message' => "Datos corruptos eliminados: $eliminados registros"
        ]);
        
    } elseif ($action === 'verificar_integridad') {
        // Verificar integridad de datos
        $result = $conn->query("
            SELECT COUNT(*) as corruptos
            FROM propietarios p
            LEFT JOIN usuarios u ON p.usuario_id = u.id
            WHERE p.usuario_id = 0 OR u.id IS NULL
        ");
        
        $row = $result->fetch_assoc();
        $corruptos = $row['corruptos'];
        
        if ($corruptos == 0) {
            echo json_encode([
                'success' => true,
                'message' => '✅ Integridad de datos: OK - No hay registros corruptos'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => "⚠️ Se encontraron $corruptos registros corruptos que necesitan limpieza"
            ]);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
