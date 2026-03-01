<?php
require_once __DIR__ . "/../app/Models/conexion.php";

$conexion = Conexion::getInstancia()->getConexion();

echo "<h1>🔧 Arreglar Contraseña de Administrador</h1>";

// Hash correcto para la contraseña 12345678
$contrasena_plana = "12345678";
$contrasena_hash = substr(md5($contrasena_plana), 0, 8);

echo "<h2>📝 Información:</h2>";
echo "Contraseña original: " . $contrasena_plana . "<br>";
echo "Hash correcto: " . $contrasena_hash . "<br>";

// Actualizar la contraseña en la base de datos
$stmt = $conexion->prepare("UPDATE usu_roles SET contraseña = ? WHERE usuarios_id = ?");
$usuario_id = 1031570517;
$stmt->bind_param("si", $contrasena_hash, $usuario_id);

if ($stmt->execute()) {
    echo "<h2>✅ Contraseña actualizada exitosamente</h2>";
    echo "Usuario ID: 1031570517 (Juan González - ADMINISTRADOR)<br>";
    echo "Nueva contraseña hash: " . $contrasena_hash . "<br>";
    
    echo "<h2>🧪 Probar Login:</h2>";
    echo '<a href="debug_login.php">🔗 Probar Login</a><br>';
    echo '<a href="login.php">🔗 Ir al Login Normal</a><br>';
} else {
    echo "<h2>❌ Error al actualizar la contraseña</h2>";
    echo "Error: " . $conexion->error;
}

?>
