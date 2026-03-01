<?php
require_once __DIR__ . "/../Models/conexion.php";

function buscarParqueaderos($valor) {
    $conn = Conexion::getInstancia()->getConexion();
    
    // Crear tabla de parqueaderos si no existe
    $createTable = "CREATE TABLE IF NOT EXISTS parqueaderos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numero_espacio INT NOT NULL,
        estado ENUM('disponible', 'ocupado', 'reservado') DEFAULT 'disponible',
        tipo ENUM('propietario', 'visitante', 'reservado') DEFAULT 'visitante',
        ocupante_nombre VARCHAR(100),
        vehiculo_placa VARCHAR(20),
        hora_entrada DATETIME,
        hora_salida DATETIME,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        die("Error creando tabla parqueaderos: " . $conn->error);
    }
    
    // Insertar datos de ejemplo si la tabla está vacía
    $checkCount = "SELECT COUNT(*) as total FROM parqueaderos";
    $result = $conn->query($checkCount);
    $count = $result->fetch_assoc()['total'];
    
    if ($count == 0) {
        // Insertar 100 espacios de ejemplo
        for ($i = 1; $i <= 100; $i++) {
            $estados = ['disponible', 'ocupado', 'reservado'];
            $tipos = ['propietario', 'visitante', 'reservado'];
            $nombres = ['Juan Pérez', 'María García', 'Carlos López', 'Ana Martínez', 'Luis Rodríguez', 'Carmen Silva', 'Roberto Díaz', 'Laura Vega'];
            $placas = ['ABC-123', 'XYZ-789', 'DEF-456', 'GHI-012', 'JKL-345', 'MNO-678', 'PQR-901', 'STU-234'];
            
            $estado = $estados[array_rand($estados)];
            $tipo = $tipos[array_rand($tipos)];
            $nombre = $nombres[array_rand($nombres)];
            $placa = $placas[array_rand($placas)];
            
            $hora_entrada = null;
            if ($estado != 'disponible') {
                $hora_entrada = date('Y-m-d H:i:s', strtotime('-' . rand(1, 8) . ' hours'));
            }
            
            $insert = "INSERT INTO parqueaderos (numero_espacio, estado, tipo, ocupante_nombre, vehiculo_placa, hora_entrada) 
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert);
            $stmt->bind_param("isssss", $i, $estado, $tipo, $nombre, $placa, $hora_entrada);
            $stmt->execute();
        }
    }
    
    // Consulta principal
    $sql = "SELECT 
        id,
        numero_espacio,
        estado,
        tipo,
        ocupante_nombre,
        vehiculo_placa,
        hora_entrada,
        hora_salida,
        fecha_creacion,
        fecha_actualizacion
    FROM parqueaderos";
    
    if(!empty($valor)) {
        $sql .= " WHERE numero_espacio LIKE ? OR estado LIKE ? OR tipo LIKE ? OR ocupante_nombre LIKE ? OR vehiculo_placa LIKE ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $busqueda = "%$valor%";
        $stmt->bind_param("sssss", $busqueda, $busqueda, $busqueda, $busqueda, $busqueda);
    } else {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
    }
    
    if (!$stmt->execute()) {
        die("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    return $stmt->get_result();
}
?>
