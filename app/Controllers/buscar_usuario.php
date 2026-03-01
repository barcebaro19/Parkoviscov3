<?php
require_once __DIR__ . "/../Models/conexion.php";

function buscarUsuarios($valor, $orden = 'reciente') {
    $conn = Conexion::getInstancia()->getConexion();
    
    $sql = "SELECT 
        u.id,
        u.nombre,
        u.apellido,
        u.email,
        u.celular,
        COALESCE(r.nombre_rol, 'usuario') AS nombre_rol
    FROM 
        usuarios u
    LEFT JOIN 
        usu_roles ur ON u.id = ur.usuarios_id
    LEFT JOIN 
        roles r ON ur.roles_idroles = r.idroles";
    
    if(!empty($valor)) {
        $sql .= " WHERE u.nombre LIKE ? OR u.id LIKE ?";
    }
    
    // Agregar ordenamiento simple
    if($orden === 'antiguo') {
        $sql .= " ORDER BY u.id ASC"; // Más antiguo primero
    } else {
        $sql .= " ORDER BY u.id DESC"; // Más reciente primero (por defecto)
    }
    
    if(!empty($valor)) {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación de la consulta: " . $conn->error);
        }
        $busqueda = "%$valor%";
        $stmt->bind_param("ss", $busqueda, $busqueda);
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