<?php
require_once __DIR__ . "/../Models/conexion.php";

function buscarVigilantes($valor = '', $orden = 'reciente') {
    $conexion = Conexion::getInstancia()->getConexion();
    
    // Construir la consulta base - ahora usando directamente la tabla vigilantes
    $sql = "SELECT id, nombre, apellido, email, celular, jornada, fecha_registro, estado
            FROM vigilantes";
    
    // Agregar filtro de búsqueda si se proporciona
    if (!empty($valor)) {
        $sql .= " WHERE (nombre LIKE ? OR apellido LIKE ? OR email LIKE ? OR id LIKE ?)";
    }
    
    // Agregar ordenamiento
    switch ($orden) {
        case 'nombre':
            $sql .= " ORDER BY nombre ASC";
            break;
        case 'apellido':
            $sql .= " ORDER BY apellido ASC";
            break;
        case 'email':
            $sql .= " ORDER BY email ASC";
            break;
        case 'jornada':
            $sql .= " ORDER BY jornada ASC";
            break;
        case 'reciente':
        default:
            $sql .= " ORDER BY fecha_registro DESC";
            break;
    }
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    if (!empty($valor)) {
        $valor_like = "%$valor%";
        $stmt->bind_param("ssss", $valor_like, $valor_like, $valor_like, $valor_like);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result;
}

function obtenerVigilantePorId($id) {
    $conexion = Conexion::getInstancia()->getConexion();
    
    $sql = "SELECT id, nombre, apellido, email, celular, jornada, fecha_registro, estado
            FROM vigilantes 
            WHERE id = ?";
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}
?>
