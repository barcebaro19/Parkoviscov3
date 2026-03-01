<?php
// Verificar estructura real de la base de datos
session_start();

echo "<h2>🔍 Verificación de Estructura de Base de Datos</h2>";

try {
    require_once '../app/Models/conexion.php';
    $conexion = Conexion::getInstancia()->getConexion();
    echo "✅ Conexión a base de datos: OK<br><br>";
    
    // Verificar estructura de tabla usuarios
    echo "<h3>📋 Estructura de tabla 'usuarios':</h3>";
    $sql = "DESCRIBE usuarios";
    $result = $conexion->query($sql);
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar estructura de tabla propietarios
    echo "<h3>📋 Estructura de tabla 'propietarios':</h3>";
    $sql = "DESCRIBE propietarios";
    $result = $conexion->query($sql);
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . $row['Default'] . "</td>";
            echo "<td>" . $row['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Verificar datos de ejemplo
    echo "<h3>📊 Datos de ejemplo en tabla 'usuarios':</h3>";
    $sql = "SELECT * FROM usuarios LIMIT 3";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        $first = true;
        while ($row = $result->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No hay datos en la tabla usuarios<br>";
    }
    
    // Verificar datos de ejemplo en propietarios
    echo "<h3>📊 Datos de ejemplo en tabla 'propietarios':</h3>";
    $sql = "SELECT * FROM propietarios LIMIT 3";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        $first = true;
        while ($row = $result->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No hay datos en la tabla propietarios<br>";
    }
    
    // Verificar relación entre tablas
    echo "<h3>🔗 Verificar relación usuarios-propietarios:</h3>";
    $sql = "SELECT u.*, p.* FROM usuarios u LEFT JOIN propietarios p ON u.id = p.usuario_id LIMIT 3";
    $result = $conexion->query($sql);
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        $first = true;
        while ($row = $result->fetch_assoc()) {
            if ($first) {
                echo "<tr>";
                foreach (array_keys($row) as $key) {
                    echo "<th>$key</th>";
                }
                echo "</tr>";
                $first = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ No hay relación entre usuarios y propietarios<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}

echo "<h3>🎯 Verificación completada</h3>";
?>
