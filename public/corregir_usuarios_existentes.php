<?php
session_start();
require_once '../app/Models/conexion.php';

echo "<h1>🔧 Corregir Usuarios Existentes</h1>";
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
    echo "<h2>1. Usuarios que NO tienen registro en Propietarios</h2>";
    
    // Buscar usuarios que deberían ser propietarios pero no están en la tabla propietarios
    $result = $conn->query("
        SELECT u.id, u.nombre, u.apellido, u.email, u.tipo_usuario
        FROM usuarios u
        LEFT JOIN propietarios p ON u.id = p.usuario_id
        WHERE (u.tipo_usuario = 'propietario' OR u.tipo_usuario = 'Propietario')
        AND p.usuario_id IS NULL
        ORDER BY u.id
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<p class='warning'>⚠️ Se encontraron " . $result->num_rows . " usuarios propietarios sin registro en la tabla propietarios:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Tipo Usuario</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['tipo_usuario']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='success'>✅ Todos los usuarios propietarios ya tienen registro en la tabla propietarios</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>2. Usuarios con rol de propietario en usu_roles</h2>";
    
    // Buscar usuarios con rol de propietario (ID 3) en usu_roles
    $result = $conn->query("
        SELECT u.id, u.nombre, u.apellido, u.email, ur.roles_idroles
        FROM usuarios u
        INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
        LEFT JOIN propietarios p ON u.id = p.usuario_id
        WHERE ur.roles_idroles = 3 AND p.usuario_id IS NULL
        ORDER BY u.id
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<p class='warning'>⚠️ Se encontraron " . $result->num_rows . " usuarios con rol de propietario sin registro en la tabla propietarios:</p>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Rol ID</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
            echo "<td>" . htmlspecialchars($row['apellido']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . $row['roles_idroles'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='success'>✅ Todos los usuarios con rol de propietario ya tienen registro en la tabla propietarios</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>3. Acciones de Corrección</h2>";
    echo "<p class='info'>Estas acciones crearán registros en la tabla propietarios para usuarios que deberían tenerlos:</p>";
    echo "<button onclick='corregirUsuariosPropietarios()' class='danger'>🔧 Corregir Usuarios Propietarios</button>";
    echo "<button onclick='corregirUsuariosConRol()' class='danger'>🔧 Corregir Usuarios con Rol</button>";
    echo "<button onclick='corregirTodos()' class='danger'>🔧 Corregir Todos</button>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>4. Estado Actual de Propietarios</h2>";
    
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
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>5. Enlaces de Prueba</h2>";
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
function corregirUsuariosPropietarios() {
    if (confirm('¿Crear registros en propietarios para usuarios con tipo_usuario = propietario?')) {
        fetch('corregir_usuarios_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'corregir_por_tipo'
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        })
        .catch(error => {
            alert('❌ Error de conexión: ' + error);
        });
    }
}

function corregirUsuariosConRol() {
    if (confirm('¿Crear registros en propietarios para usuarios con rol de propietario?')) {
        fetch('corregir_usuarios_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'corregir_por_rol'
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        })
        .catch(error => {
            alert('❌ Error de conexión: ' + error);
        });
    }
}

function corregirTodos() {
    if (confirm('¿Corregir todos los usuarios que deberían ser propietarios?')) {
        fetch('corregir_usuarios_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'corregir_todos'
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        })
        .catch(error => {
            alert('❌ Error de conexión: ' + error);
        });
    }
}
</script>
