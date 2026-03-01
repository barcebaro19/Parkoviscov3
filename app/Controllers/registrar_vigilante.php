<?php
session_start();
require_once __DIR__ . "/../Models/conexion.php";

// Verificar que el usuario sea administrador
if(!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: ../../public/login.php');
    exit();
}

// Mostrar errores si hay problemas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Procesar el formulario de registro de vigilantes
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!empty($_POST["id"]) && !empty($_POST["nombre"]) && !empty($_POST["apellido"]) && 
       !empty($_POST["email"]) && !empty($_POST["celular"]) && 
       !empty($_POST["jornada"]) && !empty($_POST["contrasena"]) && !empty($_POST["confirmar_contrasena"])) {
        
        $id = $_POST["id"];
        $nombre = $_POST["nombre"];
        $apellido = $_POST["apellido"];
        $email = $_POST["email"];
        $celular = $_POST["celular"];
        $jornada = $_POST["jornada"];
        $contrasena = $_POST["contrasena"];
        $confirmar_contrasena = $_POST["confirmar_contrasena"];
        
        // Asignar automáticamente el rol de vigilante (ID 2)
        $rol_id = 2;
        
        // Validar que las contraseñas coincidan
        if($contrasena !== $confirmar_contrasena) {
            $_SESSION['error_message'] = "Las contraseñas no coinciden";
        } elseif(strlen($contrasena) < 8) {
            $_SESSION['error_message'] = "La contraseña debe tener al menos 8 caracteres";
        } else {
            $conexion = Conexion::getInstancia()->getConexion();
            
            try {
                // Verificar si el vigilante ya existe (por ID o email)
                $check_id_sql = $conexion->prepare("SELECT id FROM vigilantes WHERE id = ?");
                $check_email_sql = $conexion->prepare("SELECT email FROM vigilantes WHERE email = ?");
                
                $check_id_sql->bind_param("i", $id);
                $check_id_sql->execute();
                $id_result = $check_id_sql->get_result();
                
                $check_email_sql->bind_param("s", $email);
                $check_email_sql->execute();
                $email_result = $check_email_sql->get_result();
                
                if($id_result->num_rows > 0) {
                    $_SESSION['error_message'] = "El vigilante con ID '$id' ya existe";
                } elseif($email_result->num_rows > 0) {
                    $_SESSION['error_message'] = "El email '$email' ya está registrado";
                } else {
                    // Iniciar transacción
                    $conexion->begin_transaction();
                    
                    try {
                        // Insertar nuevo vigilante directamente en la tabla vigilantes
                        $sql_vigilante = $conexion->prepare("INSERT INTO vigilantes (id, nombre, apellido, email, celular, jornada, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $sql_vigilante->bind_param("isssiss", $id, $nombre, $apellido, $email, $celular, $jornada, $contrasena_hash);
                        
                        if(!$sql_vigilante->execute()) {
                            throw new Exception("Error al insertar vigilante: " . $sql_vigilante->error);
                        }
                        
                        // También insertar en usuarios para mantener compatibilidad con el sistema
                        $sql_usuario = $conexion->prepare("INSERT INTO usuarios (id, nombre, apellido, email, celular) VALUES (?, ?, ?, ?, ?)");
                        $sql_usuario->bind_param("isssi", $id, $nombre, $apellido, $email, $celular);
                        
                        if(!$sql_usuario->execute()) {
                            throw new Exception("Error al insertar usuario: " . $sql_usuario->error);
                        }
                        
                        // Insertar rol del usuario
                        $sql_rol = $conexion->prepare("INSERT INTO usu_roles (usuarios_id, roles_idroles, contraseña) VALUES (?, ?, ?)");
                        $contrasena_legacy = substr(md5($contrasena), 0, 8);
                        $sql_rol->bind_param("iis", $id, $rol_id, $contrasena_legacy);
                        
                        if(!$sql_rol->execute()) {
                            throw new Exception("Error al insertar rol: " . $sql_rol->error);
                        }
                        
                        // Confirmar transacción
                        $conexion->commit();
                        
                        $_SESSION['success_message'] = "Vigilante registrado exitosamente";
                        
                    } catch (Exception $e) {
                        // Revertir transacción en caso de error
                        $conexion->rollback();
                        $_SESSION['error_message'] = "Error: " . $e->getMessage();
                    }
                }
            } catch(Exception $e) {
                $_SESSION['error_message'] = "Error: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['error_message'] = "Todos los campos son obligatorios";
    }
    
    // Redirigir de vuelta a la página de gestión
    header('Location: ../../public/gestion_vigilantes.php');
    exit();
}
?>
