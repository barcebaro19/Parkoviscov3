<?php
session_start();
require_once '../app/Models/conexion.php';

echo "<h1>🧹 Limpiar Datos Corruptos</h1>";
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
</style>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    echo "<div class='section'>";
    echo "<h2>1. Datos Corruptos Identificados</h2>";
    
    // Buscar propietarios con usuario_id = 0 o que no existen
    $result = $conn->query("
        SELECT p.id, p.usuario_id, u.nombre, u.apellido
        FROM propietarios p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        WHERE p.usuario_id = 0 OR u.id IS NULL
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<p class='warning'>⚠️ Se encontraron " . $result->num_rows . " registros corruptos:</p>";
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>Propietario ID: " . $row['id'] . " - Usuario ID: " . $row['usuario_id'] . " - Nombre: " . ($row['nombre'] ?? 'N/A') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='success'>✅ No se encontraron datos corruptos</p>";
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>2. Acciones de Limpieza</h2>";
    echo "<p class='info'>Estas acciones corregirán los datos corruptos:</p>";
    echo "<button onclick='limpiarDatosCorruptos()' class='danger'>🧹 Limpiar Datos Corruptos</button>";
    echo "<button onclick='verificarIntegridad()'>🔍 Verificar Integridad</button>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>3. Estado Actual de Propietarios</h2>";
    
    $result = $conn->query("
        SELECT p.id, p.usuario_id, u.nombre, u.apellido, u.email,
               CASE 
                   WHEN p.usuario_id = 0 THEN 'CORRUPTO'
                   WHEN u.id IS NULL THEN 'USUARIO_NO_EXISTE'
                   ELSE 'OK'
               END as estado
        FROM propietarios p
        LEFT JOIN usuarios u ON p.usuario_id = u.id
        ORDER BY p.id
    ");
    
    if ($result && $result->num_rows > 0) {
        echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr style='background: #333;'><th style='border: 1px solid #444; padding: 8px;'>ID Propietario</th><th style='border: 1px solid #444; padding: 8px;'>Usuario ID</th><th style='border: 1px solid #444; padding: 8px;'>Nombre</th><th style='border: 1px solid #444; padding: 8px;'>Email</th><th style='border: 1px solid #444; padding: 8px;'>Estado</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $color = $row['estado'] === 'OK' ? '#10B981' : '#EF4444';
            echo "<tr>";
            echo "<td style='border: 1px solid #444; padding: 8px;'>" . $row['id'] . "</td>";
            echo "<td style='border: 1px solid #444; padding: 8px;'>" . $row['usuario_id'] . "</td>";
            echo "<td style='border: 1px solid #444; padding: 8px;'>" . htmlspecialchars($row['nombre'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #444; padding: 8px;'>" . htmlspecialchars($row['email'] ?? 'N/A') . "</td>";
            echo "<td style='border: 1px solid #444; padding: 8px; color: $color;'>" . $row['estado'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
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
function limpiarDatosCorruptos() {
    if (confirm('¿Estás seguro de que quieres limpiar los datos corruptos? Esta acción no se puede deshacer.')) {
        fetch('limpiar_datos_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'limpiar_corruptos'
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

function verificarIntegridad() {
    fetch('limpiar_datos_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'verificar_integridad'
        })
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => {
        alert('❌ Error de conexión: ' + error);
    });
}
</script>
