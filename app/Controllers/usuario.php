<?php
require_once __DIR__ . '/../Models/conexion.php';
// Reglas de negocio implementadas:
// 1. Solo los usuarios con rol de administrador pueden eliminar usuarios del sistema (ver lógica en el controlador correspondiente).
// 2. El correo electrónico de cada usuario debe ser único y válido (validación en base de datos y en el formulario).
// 3. No se permite el registro de usuarios con contraseñas menores a 8 caracteres (validación en el formulario y/o aquí).
// 4. El sistema bloquea el acceso tras 5 intentos fallidos de inicio de sesión (ver controlador de login).
// Historia de usuario:
// Como administrador, quiero poder registrar nuevos usuarios con roles específicos, para que cada usuario tenga acceso solo a las funcionalidades permitidas según su rol.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug: Verificar si se está enviando el formulario
if(isset($_POST["registrar"])) {
    echo "<div class='alert alert-info'>✓ Formulario enviado - Botón registrar detectado</div>";
}

if(!empty($_POST["registrar"])) {
    if(!empty($_POST["id"]) && !empty($_POST["nombre"]) && !empty($_POST["apellido"]) && 
       !empty($_POST["email"]) && !empty($_POST["celular"]) && !empty($_POST["rol"]) &&
       !empty($_POST["contrasena"]) && !empty($_POST["confirmar_contrasena"])) {
        
        $id = $_POST["id"];
        $nombre = $_POST["nombre"];
        $apellido = $_POST["apellido"];
        $email = $_POST["email"];
        $celular = $_POST["celular"];
        $rol_id = $_POST["rol"];
        $contrasena = $_POST["contrasena"];
        $confirmar_contrasena = $_POST["confirmar_contrasena"];
        
        // Validar que las contraseñas coincidan
        if($contrasena !== $confirmar_contrasena) {
            echo "<div class='alert alert-warning'>Las contraseñas no coinciden</div>";
            return;
        }
        
        // Validar longitud de contraseña
        if(strlen($contrasena) < 8) {
            echo "<div class='alert alert-warning'>La contraseña debe tener al menos 8 caracteres</div>";
            return;
        }

        $conexion = Conexion::getInstancia()->getConexion();
        
        try {
            // Verificar si el usuario ya existe
            $check_id_sql = $conexion->prepare("SELECT id FROM usuarios WHERE id = ?");
            $check_email_sql = $conexion->prepare("SELECT email FROM usuarios WHERE email = ?");
            
            if (!$check_id_sql || !$check_email_sql) {
                echo "<div class='alert alert-danger'>Error en la preparación de la consulta de verificación: " . $conexion->error . "</div>";
                return;
            }
            
            // Verificar ID
            $check_id_sql->bind_param("s", $id);
            $check_id_sql->execute();
            $id_result = $check_id_sql->get_result();
            
            // Verificar Email
            $check_email_sql->bind_param("s", $email);
            $check_email_sql->execute();
            $email_result = $check_email_sql->get_result();
            
            if($id_result->num_rows > 0) {
                echo "<div class='alert alert-warning'>El usuario con ID '$id' ya existe</div>";
            } elseif($email_result->num_rows > 0) {
                echo "<div class='alert alert-warning'>El email '$email' ya está registrado</div>";
            } else {
                // Iniciar transacción
                $conexion->begin_transaction();
                
                try {
                    // Insertar nuevo usuario
                    $sql_usuario = $conexion->prepare("INSERT INTO usuarios (id, nombre, apellido, email, celular) VALUES (?, ?, ?, ?, ?)");
                    if (!$sql_usuario) {
                        throw new Exception("Error en la preparación de la consulta de usuario: " . $conexion->error);
                    }
                    
                    $sql_usuario->bind_param("isssi", $id, $nombre, $apellido, $email, $celular);
                    
                    if(!$sql_usuario->execute()) {
                        throw new Exception("Error al insertar usuario: " . $sql_usuario->error);
                    }
                    
                    // Insertar rol del usuario
                    $sql_rol = $conexion->prepare("INSERT INTO usu_roles (usuarios_id, roles_idroles, contraseña) VALUES (?, ?, ?)");
                    if (!$sql_rol) {
                        throw new Exception("Error en la preparación de la consulta de rol: " . $conexion->error);
                    }
                    
                    // Hash de la contraseña (limitado a 8 caracteres por la BD)
                    $contrasena_hash = substr(md5($contrasena), 0, 8);
                    $sql_rol->bind_param("iis", $id, $rol_id, $contrasena_hash);
                    
                    if(!$sql_rol->execute()) {
                        throw new Exception("Error al insertar rol: " . $sql_rol->error);
                    }
                    
                    // Si el rol es propietario (ID 3), crear registro en propietarios
                    if($rol_id == 3) {
                        $sql_propietario = $conexion->prepare("INSERT INTO propietarios (usuario_id) VALUES (?)");
                        if (!$sql_propietario) {
                            throw new Exception("Error en la preparación de la consulta de propietario: " . $conexion->error);
                        }
                        
                        $sql_propietario->bind_param("i", $id);
                        
                        if(!$sql_propietario->execute()) {
                            throw new Exception("Error al insertar propietario: " . $sql_propietario->error);
                        }
                    }
                    
                    // Confirmar transacción
                    $conexion->commit();
                    
                    echo "<div class='alert alert-success'>✅ Usuario registrado exitosamente en la base de datos</div>";
                    echo "<div class='alert alert-info'>🔄 Redirigiendo a tabla de usuarios...</div>";
                    
                    $_SESSION['mensaje'] = 'Usuario registrado exitosamente';
                    header("Location: tablausu.php");
                    exit();
                    
                } catch (Exception $e) {
                    // Revertir transacción en caso de error
                    $conexion->rollback();
                    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
                }
            }
        } catch(Exception $e) {
            echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>❌ Campos faltantes o vacíos</div>";
        echo "<div class='alert alert-info'>Debug: ID=" . (isset($_POST["id"]) ? "'" . $_POST["id"] . "'" : "NO") . 
             ", Nombre=" . (isset($_POST["nombre"]) ? "'" . $_POST["nombre"] . "'" : "NO") . 
             ", Apellido=" . (isset($_POST["apellido"]) ? "'" . $_POST["apellido"] . "'" : "NO") . 
             ", Email=" . (isset($_POST["email"]) ? "'" . $_POST["email"] . "'" : "NO") . 
             ", Celular=" . (isset($_POST["celular"]) ? "'" . $_POST["celular"] . "'" : "NO") . 
             ", Rol=" . (isset($_POST["rol"]) ? "'" . $_POST["rol"] . "'" : "NO") . 
             ", Contraseña=" . (isset($_POST["contrasena"]) ? "SÍ" : "NO") . 
             ", Confirmar=" . (isset($_POST["confirmar_contrasena"]) ? "SÍ" : "NO") . "</div>";
    }
}
?>