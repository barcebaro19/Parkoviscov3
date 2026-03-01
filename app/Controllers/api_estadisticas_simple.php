<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id']) || !isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'propietario') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado o sin permisos'
    ]);
    exit();
}

try {
    require_once __DIR__ . '/../Models/conexion.php';
    
    $conexion = Conexion::getInstancia()->getConexion();
    $usuario_id = $_SESSION['id'];
    
    // Obtener el tipo de datos solicitado
    $tipo = $_GET['tipo'] ?? 'estadisticas';
    
    switch ($tipo) {
        case 'estadisticas':
            // Consulta simplificada para estadísticas
            $sql = "SELECT COUNT(*) as total FROM reservas r 
                    INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                    INNER JOIN propietarios p ON r.propietarios_id_pro = p.id_pro
                    WHERE p.usuario_id = ?";
            
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $total = $row['total'] ?? 0;
            
            $resultado = [
                'success' => true,
                'data' => [
                    'hoy' => $total,
                    'esta_semana' => $total,
                    'este_mes' => $total
                ]
            ];
            break;
            
        case 'historial':
            $resultado = [
                'success' => true,
                'data' => []
            ];
            break;
            
        case 'frecuentes':
            $resultado = [
                'success' => true,
                'data' => []
            ];
            break;
            
        case 'preautorizaciones':
            $resultado = [
                'success' => true,
                'data' => []
            ];
            break;
            
        default:
            throw new Exception('Tipo de datos no válido');
    }
    
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en api_estadisticas_simple.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>


