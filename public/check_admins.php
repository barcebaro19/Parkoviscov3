<?php
require_once __DIR__ . "/../app/Models/conexion.php";

$conexion = Conexion::getInstancia()->getConexion();

echo "<h1>🔍 Verificar Administradores</h1>";

// Verificar roles disponibles
echo "<h2>📋 Roles disponibles:</h2>";
$roles_query = $conexion->query("SELECT * FROM roles");
if ($roles_query) {
    while ($row = $roles_query->fetch_assoc()) {
        echo "ID: " . $row['idroles'] . " - Rol: " . $row['nombre_rol'] . "<br>";
    }
}

echo "<h2>👑 Usuarios Administradores:</h2>";
$admin_query = $conexion->query("SELECT u.id, u.nombre, u.apellido, r.nombre_rol 
                                FROM usuarios u 
                                JOIN usu_roles ur ON u.id = ur.usuarios_id 
                                JOIN roles r ON ur.roles_idroles = r.idroles 
                                WHERE r.nombre_rol = 'administrador'");

if ($admin_query && $admin_query->num_rows > 0) {
    while ($row = $admin_query->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Nombre: " . $row['nombre'] . " " . $row['apellido'] . " - Rol: " . $row['nombre_rol'] . "<br>";
    }
} else {
    echo "❌ No se encontraron administradores en la base de datos.<br>";
}

echo "<h2>🔐 Todos los usuarios con roles:</h2>";
$all_users_query = $conexion->query("SELECT u.id, u.nombre, u.apellido, r.nombre_rol 
                                    FROM usuarios u 
                                    JOIN usu_roles ur ON u.id = ur.usuarios_id 
                                    JOIN roles r ON ur.roles_idroles = r.idroles 
                                    ORDER BY r.nombre_rol, u.nombre");

if ($all_users_query) {
    while ($row = $all_users_query->fetch_assoc()) {
        echo "ID: " . $row['id'] . " - Nombre: " . $row['nombre'] . " " . $row['apellido'] . " - Rol: " . $row['nombre_rol'] . "<br>";
    }
}

?>
