<?php
session_start();
require_once '../app/Models/conexion.php';

echo "<h1>🔧 Verificar y Crear Propietarios</h1>";
echo "<style>
    body { font-family: Arial; margin: 20px; background: #1a1a1a; color: white; }
    .section { background: #2a2a2a; padding: 20px; margin: 20px 0; border-radius: 8px; border: 1px solid #444; }
    .success { color: #10B981; }
    .error { color: #EF4444; }
    .warning { color: #F59E0B; }
    .info { color: #3B82F6; }
    button { background: #3B82F6; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
    button:hover { background: #2563EB; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #444; padding: 8px; text-align: left; }
    th { background: #333; }
</style>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    echo "<div class='section'>";
    echo "<h2>1. Usuarios que NO son propietarios</h2>";
    
    // Buscar usuarios que no están en la tabla propietarios
    $result = $conn->query("
        SELECT u.id, u.nombre, u.apellido, u.email, u.tipo_usuario
        FROM usuarios u
        LEFT JOIN propietarios p ON u.id = p.usuario_id
        WHERE p.usuario_id IS NULL
        ORDER BY u.id
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Tipo Usuario</th><th>Acción</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tipo_usuario']) . "</td>";
            echo "<td><button onclick='crearPropietario(" . $row['id'] . ")'>Crear Propietario</button></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='success'>✅ Todos los usuarios ya son propietarios</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>2. Propietarios Existentes</h2>";
    
    $result = $conn->query("
        SELECT p.id, p.usuario_id, u.nombre, u.apellido, u.email
        FROM propietarios p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.id
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID Propietario</th><th>Usuario ID</th><th>Nombre</th><th>Apellido</th><th>Email</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['usuario_id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['apellido'] ?? 'N/A') . "</td>";
            echo "<td>" . htmlspecialchars($row['email'] ?? 'N/A') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No hay propietarios registrados</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>3. Crear Propietarios Automáticamente</h2>";
    echo "<p class='info'>Esto creará entradas en la tabla 'propietarios' para todos los usuarios que no las tengan.</p>";
    echo "<button onclick='crearTodosPropietarios()'>Crear Todos los Propietarios</button>";
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
function crearPropietario(userId) {
    if (confirm('¿Crear propietario para el usuario ID ' + userId + '?')) {
        fetch('crear_propietario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'crear_propietario',
                usuario_id: userId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✅ Propietario creado exitosamente');
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

function crearTodosPropietarios() {
    if (confirm('¿Crear propietarios para todos los usuarios que no los tengan?')) {
        fetch('crear_propietario_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'crear_todos_propietarios'
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
