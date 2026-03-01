<?php
require_once __DIR__ . "/../Models/conexion.php";

if (!empty($_POST["btnmodificar"])) {
    if (!empty($_POST["email"]) && !empty($_POST["celular"]) && !empty($_POST["contraseña"])) {
        
        // Obtener la conexión
        $conexion = Conexion::getInstancia()->getConexion();
        
        // Obtener los datos del formulario
        $id = $_POST["id"];
        $email = $_POST["email"];
        $celular = $_POST["celular"];
        $contraseña = $_POST["contraseña"];

        try {
            // Actualizar datos en la tabla usuarios
            $sql_usuarios = "UPDATE usuarios SET email = ?, celular = ? WHERE id = ?";
            $stmt_usuarios = $conexion->prepare($sql_usuarios);
            $stmt_usuarios->bind_param("ssi", $email, $celular, $id);
            
            if ($stmt_usuarios->execute()) {
                // Actualizar contraseña en la tabla usu_roles
                $sql_roles = "UPDATE usu_roles SET contraseña = ? WHERE usuarios_id = ?";
                $stmt_roles = $conexion->prepare($sql_roles);
                $stmt_roles->bind_param("si", $contraseña, $id);
                
                if ($stmt_roles->execute()) {
                    header("Location: ../../public/tablausu.php?mensaje=actualizado");
                    exit();
                } else {
                    throw new Exception("Error al actualizar la contraseña");
                }
            } else {
                throw new Exception("Error al actualizar los datos del usuario");
            }

        } catch (Exception $e) {
            header("Location: ../../public/tablausu.php?mensaje=error");
            exit();
        }

    } else {
        header("Location: ../../public/tablausu.php?mensaje=campos_vacios");
        exit();
    }
}
?>