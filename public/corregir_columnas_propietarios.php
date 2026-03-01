<?php
session_start();
require_once '../app/Models/conexion.php';

echo "<h1>🔧 Corregir Columnas de Propietarios</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; background: #1a1a1a; color: white; }
    .section { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #444; }
    .success { color: #10B981; }
    .error { color: #EF4444; }
    .warning { color: #F59E0B; }
    .info { color: #3B82F6; }
    button { background: #3B82F6; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    button:hover { background: #2563EB; }
    .danger { background: #EF4444; }
    .danger:hover { background: #DC2626; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #444; padding: 8px; text-align: left; }
    th { background: #333; }
</style>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    echo "<div class='section'>";
    echo "<h2>1. Estado Actual de la Tabla Propietarios</h2>";
    
    $result = $conn->query("SELECT * FROM propietarios ORDER BY id");
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>usuario_id</th><th>usuarios_id</th><th>Nombre</th><th>Email</th><th>Estado</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $usuarioId = $row['usuario_id'] ?? 'NULL';
            $usuariosId = $row['usuarios_id'] ?? 'NULL';
            $nombre = $row['nombre'] ?? 'NULL';
            $email = $row['email'] ?? 'NULL';
            
            // Determinar estado
            if ($usuarioId === 'NULL' && $usuariosId === 'NULL') {
                $estado = '<span style="color: #EF4444;">CORRUPTO</span>';
            } elseif ($usuarioId !== 'NULL' && $usuariosId === 'NULL') {
                $estado = '<span style="color: #F59E0B;">INCONSISTENTE</span>';
            } elseif ($usuarioId === 'NULL' && $usuariosId !== 'NULL') {
                $estado = '<span style="color: #F59E0B;">INCONSISTENTE</span>';
            } else {
                $estado = '<span style="color: #10B981;">OK</span>';
            }
            
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $usuarioId . "</td>";
            echo "<td>" . $usuariosId . "</td>";
            echo "<td>" . htmlspecialchars($nombre) . "</td>";
            echo "<td>" . htmlspecialchars($email) . "</td>";
            echo "<td>" . $estado . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>2. Análisis del Problema</h2>";
    echo "<p class='info'>El problema es que la tabla tiene dos columnas:</p>";
    echo "<ul>";
    echo "<li><strong>usuario_id</strong> - Usado por el código de la aplicación</li>";
    echo "<li><strong>usuarios_id</strong> - Columna diferente que no se usa</li>";
    echo "</ul>";
    echo "<p class='warning'>Algunos registros tienen datos en una columna pero no en la otra.</p>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>3. Solución</h2>";
    echo "<p class='info'>Voy a:</p>";
    echo "<ol>";
    echo "<li>Actualizar registros que tienen <code>usuarios_id</code> pero no <code>usuario_id</code></li>";
    echo "<li>Eliminar registros completamente corruptos (todo NULL)</li>";
    echo "<li>Eliminar la columna <code>usuarios_id</code> que no se usa</li>";
    echo "</ol>";
    echo "<button onclick='ejecutarCorreccion()' class='danger'>🔧 Ejecutar Corrección</button>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>4. Enlaces de Prueba</h2>";
    echo "<p><a href='debug_propietarios_usuarios.php' style='color: #3B82F6;'>🔍 Ver Debug Completo</a></p>";
    echo "<p><a href='usuario.php' style='color: #3B82F6;'>🚀 Ir al Dashboard</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='section'>";
    echo "<h2>❌ Error</h2>";
    echo "<p class='error'>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>

<script>
function ejecutarCorreccion() {
    if (confirm('¿Estás seguro de que quieres ejecutar la corrección? Esta acción modificará la base de datos.')) {
        fetch('corregir_propietarios_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'corregir_propietarios'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ ' + data.message);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('❌ Error de conexión: ' + error);
        });
    }
}
</script>
