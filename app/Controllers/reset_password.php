<?php
session_start();
require_once __DIR__ . "/../Models/conexion.php";

$conexion = Conexion::getInstancia()->getConexion();

if (isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    
    // Verificar si el email existe
    $stmt = $conexion->prepare("SELECT id, nombre, apellido FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Generar nueva contraseña temporal
        $new_password = substr(md5(uniqid()), 0, 8);
        $password_hash = substr(md5($new_password), 0, 8);
        
        // Actualizar contraseña en usu_roles
        $stmt = $conexion->prepare("UPDATE usu_roles SET contraseña = ? WHERE usuarios_id = ?");
        $stmt->bind_param("si", $password_hash, $user['id']);
        $stmt->execute();
        
        // Enviar email con nueva contraseña (simulado)
        $mensaje = "Hola " . $user['nombre'] . " " . $user['apellido'] . ",\n\n";
        $mensaje .= "Tu nueva contraseña temporal es: " . $new_password . "\n\n";
        $mensaje .= "Por favor, cambia esta contraseña después de iniciar sesión.\n\n";
        $mensaje .= "Saludos,\nEquipo de Quintanares";
        
        // Aquí se enviaría el email real
        // mail($email, "Recuperación de Contraseña - Quintanares", $mensaje);
        
        header('Location: ../public/login.php?success=password_reset');
        exit();
    } else {
        header('Location: ../public/login.php?error=email_not_found');
        exit();
    }
} else {
    header('Location: ../public/login.php');
    exit();
}
?>
