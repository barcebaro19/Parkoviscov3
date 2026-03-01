<?php
session_start();
require_once '../app/Models/conexion.php';

header('Content-Type: application/json');

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'crear_propietario') {
        $usuario_id = $input['usuario_id'] ?? null;
        
        if (!$usuario_id) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
            exit;
        }
        
        // Verificar si ya existe
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM propietarios WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'El usuario ya es propietario']);
            exit;
        }
        
        // Crear propietario
        $stmt = $conn->prepare("INSERT INTO propietarios (usuario_id) VALUES (?)");
        $stmt->bind_param("i", $usuario_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Propietario creado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear propietario: ' . $conn->error]);
        }
        
    } elseif ($action === 'crear_todos_propietarios') {
        // Buscar usuarios que no son propietarios
        $result = $conn->query("
            SELECT u.id
            FROM usuarios u
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE p.usuario_id IS NULL
        ");
        
        $creados = 0;
        $errores = 0;
        
        while ($row = $result->fetch_assoc()) {
            $usuario_id = $row['id'];
            
            // Crear propietario
            $stmt = $conn->prepare("INSERT INTO propietarios (usuario_id) VALUES (?)");
            $stmt->bind_param("i", $usuario_id);
            
            if ($stmt->execute()) {
                $creados++;
            } else {
                $errores++;
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => "Propietarios creados: $creados, Errores: $errores"
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
