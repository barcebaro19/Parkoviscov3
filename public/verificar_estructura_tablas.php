<?php
/**
 * Verificar estructura de tablas
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

require_once '../app/Models/conexion.php';

echo "<h1>Verificación de Estructura de Tablas</h1>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    echo "<h2>1. Estructura de la tabla 'usuarios':</h2>";
    $sql = "DESCRIBE usuarios";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
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
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
    
    echo "<h2>2. Estructura de la tabla 'propietarios':</h2>";
    $sql = "DESCRIBE propietarios";
    $result = $conn->query($sql);
    
    if ($result) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Por defecto</th><th>Extra</th></tr>";
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
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
    
    echo "<h2>3. Muestra de datos de la tabla 'usuarios':</h2>";
    $sql = "SELECT * FROM usuarios LIMIT 5";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        $first_row = true;
        while ($row = $result->fetch_assoc()) {
            if ($first_row) {
                echo "<tr>";
                foreach (array_keys($row) as $column) {
                    echo "<th>$column</th>";
                }
                echo "</tr>";
                $first_row = false;
            }
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No se encontraron datos o error: " . ($conn->error ?: "Sin datos") . "</p>";
    }
    
    echo "<h2>4. Buscar usuarios propietarios (prueba diferentes columnas):</h2>";
    
    // Probar diferentes posibles nombres de columna
    $possible_columns = ['tipo_usuario', 'nombre_rol', 'rol', 'tipo', 'role', 'user_type'];
    
    foreach ($possible_columns as $column) {
        echo "<h3>Probando columna: '$column'</h3>";
        $sql = "SELECT COUNT(*) as count FROM usuarios WHERE $column = 'propietario'";
        $result = $conn->query($sql);
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Usuarios con $column = 'propietario': " . $row['count'] . "</p>";
        } else {
            echo "<p>Error con columna '$column': " . $conn->error . "</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Enlaces:</h2>";
echo "<a href='debug_propietarios.php'>Volver al debug de propietarios</a><br>";
echo "<a href='usuario.php'>Volver al dashboard</a><br>";
?>
