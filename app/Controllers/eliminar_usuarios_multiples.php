<?php
session_start();
require_once __DIR__ . "/../Models/conexion.php";

// Verificar que el usuario sea administrador
if (!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: ../../public/login.php');
    exit();
}

$conexion = Conexion::getInstancia()->getConexion();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuarios_ids'])) {
    $usuarios_ids = $_POST['usuarios_ids'];
    $usuario_actual = $_SESSION['id'];
    $eliminados = 0;
    $errores = [];
    $usuarios_no_eliminados = [];
    
    // Verificar que no se intente eliminar la cuenta propia
    if (in_array($usuario_actual, $usuarios_ids)) {
        $_SESSION['error'] = 'No puedes eliminar tu propia cuenta ya que hay una sesión activa con esta misma cuenta';
        header('Location: ../../public/tablausu.php');
        exit();
    }
    
    // Comenzar transacción
    $conexion->begin_transaction();
    
    try {
        foreach ($usuarios_ids as $usuario_id) {
            $usuario_id = intval($usuario_id);
            
            // Verificar que el usuario existe
            $sql_verificar = "SELECT u.nombre, u.apellido FROM usuarios u WHERE u.id = ?";
            $stmt_verificar = $conexion->prepare($sql_verificar);
            $stmt_verificar->bind_param("i", $usuario_id);
            $stmt_verificar->execute();
            $resultado_verificar = $stmt_verificar->get_result();
            
            if ($resultado_verificar->num_rows === 0) {
                $errores[] = "Usuario ID $usuario_id no encontrado";
                continue;
            }
            
            $usuario_info = $resultado_verificar->fetch_assoc();
            $nombre_usuario = $usuario_info['nombre'] . ' ' . $usuario_info['apellido'];
            
            // Eliminar de la tabla usu_roles primero
            $sql_roles = "DELETE FROM usu_roles WHERE usuarios_id = ?";
            $stmt_roles = $conexion->prepare($sql_roles);
            $stmt_roles->bind_param("i", $usuario_id);
            
            if (!$stmt_roles->execute()) {
                $errores[] = "Error al eliminar roles del usuario $nombre_usuario";
                $usuarios_no_eliminados[] = $nombre_usuario;
                continue;
            }
            
            // Eliminar de la tabla usuarios
            $sql_usuarios = "DELETE FROM usuarios WHERE id = ?";
            $stmt_usuarios = $conexion->prepare($sql_usuarios);
            $stmt_usuarios->bind_param("i", $usuario_id);
            
            if (!$stmt_usuarios->execute()) {
                $errores[] = "Error al eliminar usuario $nombre_usuario";
                $usuarios_no_eliminados[] = $nombre_usuario;
                continue;
            }
            
            $eliminados++;
        }
        
        // Si hay errores, hacer rollback
        if (!empty($errores)) {
            $conexion->rollback();
            $mensaje_error = "Error al eliminar usuarios:\n" . implode("\n", $errores);
            $_SESSION['error'] = $mensaje_error;
        } else {
            // Si todo está bien, confirmar los cambios
            $conexion->commit();
            $_SESSION['success'] = "Se eliminaron $eliminados usuarios correctamente";
        }
        
    } catch (Exception $e) {
        // Si hay cualquier error, deshacer los cambios
        $conexion->rollback();
        $_SESSION['error'] = "Error en la transacción: " . $e->getMessage();
    }
    
} else {
    $_SESSION['error'] = 'No se recibieron usuarios para eliminar';
}

// Redirigir de vuelta a la tabla de usuarios
header('Location: ../../public/tablausu.php');
exit();
?>

