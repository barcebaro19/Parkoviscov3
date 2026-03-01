<?php
require_once __DIR__ . "/../Models/conexion.php";

function buscarPropietarios($valor = '', $orden = 'reciente') {
    $conexion = Conexion::getInstancia()->getConexion();
    
    try {
        // Construir la consulta base usando la tabla propietarios directamente
        $sql = "SELECT 
                    p.id, 
                    p.nombre, 
                    p.apellido, 
                    p.email, 
                    p.celular, 
                    p.torre, 
                    p.piso, 
                    p.apartamento,
                    p.fecha_registro,
                    p.estado,
                    par.identificacion_espacio as parqueadero_asignado,
                    par.disponibilidad as estado_parqueadero,
                    par.tipo_de_vehiculo as tipo_parqueadero
                FROM propietarios p 
                LEFT JOIN parqueadero par ON p.id = par.id_parq
                WHERE p.estado = 'activo'";
        
        // Agregar filtro de búsqueda si se proporciona
        if (!empty($valor)) {
            $sql .= " AND (p.nombre LIKE ? OR p.apellido LIKE ? OR p.email LIKE ? OR p.id LIKE ? OR p.torre LIKE ? OR p.apartamento LIKE ?)";
        }
        
        // Agregar ordenamiento
        switch ($orden) {
            case 'nombre':
                $sql .= " ORDER BY p.nombre ASC";
                break;
            case 'apellido':
                $sql .= " ORDER BY p.apellido ASC";
                break;
            case 'torre':
                $sql .= " ORDER BY p.torre ASC";
                break;
            case 'apartamento':
                $sql .= " ORDER BY p.apartamento ASC";
                break;
            case 'parqueadero':
                $sql .= " ORDER BY par.identificacion_espacio ASC";
                break;
            case 'reciente':
            default:
                $sql .= " ORDER BY p.fecha_registro DESC";
                break;
        }
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }
        
        if (!empty($valor)) {
            $valor_like = "%$valor%";
            $stmt->bind_param("ssssss", $valor_like, $valor_like, $valor_like, $valor_like, $valor_like, $valor_like);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Error en buscarPropietarios: " . $e->getMessage());
        // Devolver un resultado vacío en caso de error
        return false;
    }
}

function obtenerPropietarioPorId($id) {
    $conexion = Conexion::getInstancia()->getConexion();
    
    try {
        $sql = "SELECT 
                    p.id, 
                    p.nombre, 
                    p.apellido, 
                    p.email, 
                    p.celular, 
                    p.torre, 
                    p.piso, 
                    p.apartamento,
                    p.fecha_registro,
                    p.estado,
                    par.identificacion_espacio as parqueadero_asignado,
                    par.disponibilidad as estado_parqueadero,
                    par.tipo_de_vehiculo as tipo_parqueadero
                FROM propietarios p 
                LEFT JOIN parqueadero par ON p.id = par.id_parq
                WHERE p.id = ? AND p.estado = 'activo'";
        
        $stmt = $conexion->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
        
    } catch (Exception $e) {
        error_log("Error en obtenerPropietarioPorId: " . $e->getMessage());
        return false;
    }
}

function obtenerEstadisticasPropietarios() {
    $conexion = Conexion::getInstancia()->getConexion();
    
    try {
        // Total de propietarios (buscar en tabla propietarios directamente)
        $sql_total = "SELECT COUNT(*) as total FROM propietarios WHERE estado = 'activo'";
        $result_total = $conexion->query($sql_total);
        
        if (!$result_total) {
            throw new Exception("Error en consulta total: " . $conexion->error);
        }
        
        $total_row = $result_total->fetch_assoc();
        $total = $total_row ? $total_row['total'] : 0;
        
        // Propietarios con parqueadero asignado (simplificado)
        $sql_con_parqueadero = "SELECT COUNT(*) as con_parqueadero FROM propietarios p 
                               LEFT JOIN parqueadero par ON p.id = par.id_parq
                               WHERE p.estado = 'activo' AND par.id_parq IS NOT NULL";
        $result_con_parqueadero = $conexion->query($sql_con_parqueadero);
        
        if (!$result_con_parqueadero) {
            throw new Exception("Error en consulta con parqueadero: " . $conexion->error);
        }
        
        $con_parqueadero_row = $result_con_parqueadero->fetch_assoc();
        $con_parqueadero = $con_parqueadero_row ? $con_parqueadero_row['con_parqueadero'] : 0;
        
        // Propietarios sin parqueadero
        $sin_parqueadero = $total - $con_parqueadero;
        
        return [
            'total' => $total,
            'con_parqueadero' => $con_parqueadero,
            'sin_parqueadero' => $sin_parqueadero
        ];
        
    } catch (Exception $e) {
        // En caso de error, devolver valores por defecto
        error_log("Error en obtenerEstadisticasPropietarios: " . $e->getMessage());
        return [
            'total' => 0,
            'con_parqueadero' => 0,
            'sin_parqueadero' => 0
        ];
    }
}
?>
