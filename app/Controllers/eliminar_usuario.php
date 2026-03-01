<?php
require_once __DIR__ . "/../Models/conexion.php";

$conexion = Conexion::getInstancia()->getConexion();

if (isset($_GET['id']) || isset($_POST['id'])) {
    // Obtener ID ya sea por GET o POST
    $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
    
    // Comenzar transacción
    $conexion->begin_transaction();
    
    try {
        // Primero eliminar de la tabla usu_roles
        $sql_roles = $conexion->query("DELETE FROM usu_roles WHERE usuarios_id = $id");
        
        // Luego eliminar de la tabla usuarios
        $sql_usuarios = $conexion->query("DELETE FROM usuarios WHERE id = $id");
        
        if ($sql_roles && $sql_usuarios) {
            // Si todo está bien, confirmar los cambios
            $conexion->commit();
            header("Location: ../../public/tablausu.php?mensaje=eliminado");
            exit();
        } else {
            // Si hay error, deshacer los cambios
            throw new Exception("Error en la eliminación");
        }
    } catch (Exception $e) {
        // Si hay cualquier error, deshacer los cambios
        $conexion->rollback();
        header("Location: ../../public/tablausu.php?mensaje=error");
        exit();
    }
} else {
    header("Location: ../../public/tablausu.php?mensaje=error");
    exit();
}

// Cerrar la conexión
$conexion->close();
?> 