<?php
// Verificar estructura de la tabla parqueadero
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once '../app/Models/conexion.php';
    $conn = Conexion::getInstancia()->getConexion();
    
    $info = [];
    
    // 1. Verificar estructura de la tabla parqueadero
    $sql = "DESCRIBE parqueadero";
    $result = $conn->query($sql);
    $info['estructura_parqueadero'] = [];
    while ($row = $result->fetch_assoc()) {
        $info['estructura_parqueadero'][] = $row;
    }
    
    // 2. Verificar datos existentes en parqueadero
    $sql = "SELECT * FROM parqueadero LIMIT 10";
    $result = $conn->query($sql);
    $info['datos_parqueadero'] = [];
    while ($row = $result->fetch_assoc()) {
        $info['datos_parqueadero'][] = $row;
    }
    
    // 3. Verificar tipos de vehículo únicos
    $sql = "SELECT DISTINCT tipo_de_vehiculo FROM parqueadero";
    $result = $conn->query($sql);
    $info['tipos_vehiculo'] = [];
    while ($row = $result->fetch_assoc()) {
        $info['tipos_vehiculo'][] = $row['tipo_de_vehiculo'];
    }
    
    // 4. Verificar disponibilidades únicas
    $sql = "SELECT DISTINCT disponibilidad FROM parqueadero";
    $result = $conn->query($sql);
    $info['disponibilidades'] = [];
    while ($row = $result->fetch_assoc()) {
        $info['disponibilidades'][] = $row['disponibilidad'];
    }
    
    echo json_encode($info, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
