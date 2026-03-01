<?php
require_once __DIR__ . "/../Models/conexion.php";
$conexion = Conexion::getInstancia()->getConexion();


header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=usuarios_parkovisco_' . date('Y-m-d') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

echo pack("CCC",0xef,0xbb,0xbf);

// Obtener datos
$sql = "SELECT 
        u.id,
        u.nombre,
        u.apellido,
        u.email,
        u.celular,
        ur.contraseña,
        r.nombre_rol
    FROM 
        usu_roles ur 
    INNER JOIN 
        usuarios u ON ur.usuarios_id = u.id 
    INNER JOIN 
        roles r ON ur.roles_idroles = r.idroles";
$resultado = $conexion->query($sql);

// Crear tabla Excel con estilos básicos
echo "<table border='1' cellpadding='5' cellspacing='0'>";

// Encabezado con título
echo "<tr>";
echo "<th colspan='6' style='background-color: #4F46E5; color: white; font-size: 16px; text-align: center;'>";
echo "Reporte de Usuarios - Parkovisco - " . date('d/m/Y');
echo "</th>";
echo "</tr>";

// Encabezados de columnas
echo "<tr style='background-color: #6366F1; color: white;'>";
echo "<th>Cédula</th>";
echo "<th>Nombre</th>";
echo "<th>Apellido</th>";
echo "<th>Email</th>";
echo "<th>Celular</th>";
echo "<th>Rol</th>";
echo "</tr>";

// Agregar datos
if ($resultado && $resultado->num_rows > 0) {
    while ($row = $resultado->fetch_assoc()) {
        echo "<tr>";
        echo "<td style='text-align: center;'>" . $row['id'] . "</td>";
        echo "<td>" . $row['nombre'] . "</td>";
        echo "<td>" . $row['apellido'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['celular'] . "</td>";
        echo "<td style='text-align: center;'>" . $row['nombre_rol'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' style='text-align: center;'>No se encontraron registros</td></tr>";
}

echo "</table>";

$conexion->close();
?> 