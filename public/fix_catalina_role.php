<?php
require_once __DIR__ . "/../app/Models/conexion.php";

$conexion = Conexion::getInstancia()->getConexion();

echo "<h1>🔧 Arreglar Rol de Catalina López</h1>";

// Verificar el rol actual
$stmt_check = $conexion->prepare("SELECT u.id, u.nombre, u.apellido, r.nombre_rol, r.idroles 
                                 FROM usuarios u 
                                 JOIN usu_roles ur ON u.id = ur.usuarios_id 
                                 JOIN roles r ON ur.roles_idroles = r.idroles 
                                 WHERE u.id = ?");
$usuario_id = 344444444;
$stmt_check->bind_param("i", $usuario_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "<h2>📋 Rol actual de Catalina López:</h2>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Nombre: " . $user['nombre'] . " " . $user['apellido'] . "<br>";
    echo "Rol actual: " . $user['nombre_rol'] . " (ID: " . $user['idroles'] . ")<br>";
    
    // Cambiar a propietario (ID del rol propietario es 3)
    $nuevo_rol_id = 3;
    $stmt_update = $conexion->prepare("UPDATE usu_roles SET roles_idroles = ? WHERE usuarios_id = ?");
    $stmt_update->bind_param("ii", $nuevo_rol_id, $usuario_id);
    
    if ($stmt_update->execute()) {
        echo "<h2>✅ Rol actualizado exitosamente</h2>";
        echo "Catalina López ahora es: <strong>PROPIETARIO</strong><br>";
        
        echo "<h2>🧪 Probar Login:</h2>";
        echo '<a href="debug_login.php">🔗 Probar Login</a><br>';
        echo '<a href="login.php">🔗 Ir al Login Normal</a><br>';
        echo '<a href="check_admins.php">🔗 Verificar Administradores</a><br>';
    } else {
        echo "<h2>❌ Error al actualizar el rol</h2>";
        echo "Error: " . $conexion->error;
    }
} else {
    echo "<h2>❌ Usuario no encontrado</h2>";
}

echo "<h2>📋 Roles disponibles:</h2>";
$roles_query = $conexion->query("SELECT * FROM roles");
if ($roles_query) {
    while ($row = $roles_query->fetch_assoc()) {
        echo "ID: " . $row['idroles'] . " - Rol: " . $row['nombre_rol'] . "<br>";
    }
}

?>
