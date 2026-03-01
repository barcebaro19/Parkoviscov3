<?php
/**
 * Limpiar reservas problemáticas - Versión simplificada
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

require_once '../app/Models/conexion.php';

echo "<h1>Limpieza de Reservas Problemáticas</h1>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    echo "<h2>1. Verificar reservas con problemas:</h2>";
    $sql = "SELECT COUNT(*) as total FROM reservas WHERE id_reser = 0 OR propietarios_id_pro = 0";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    
    echo "<p><strong>Reservas problemáticas encontradas: " . $row['total'] . "</strong></p>";
    
    if ($row['total'] > 0) {
        echo "<h2>2. Mostrar reservas problemáticas:</h2>";
        $sql = "SELECT id_reser, propietarios_id_pro, visitante_id_visit, codigo_qr, observaciones 
                FROM reservas 
                WHERE id_reser = 0 OR propietarios_id_pro = 0 
                ORDER BY id_reser";
        $result = $conn->query($sql);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Reserva</th><th>Propietario ID</th><th>Visitante ID</th><th>Código QR</th><th>Observaciones</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_reser'] . "</td>";
            echo "<td>" . $row['propietarios_id_pro'] . "</td>";
            echo "<td>" . $row['visitante_id_visit'] . "</td>";
            echo "<td>" . $row['codigo_qr'] . "</td>";
            echo "<td>" . $row['observaciones'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2>3. ¿Deseas limpiar estas reservas?</h2>";
        echo "<form method='post'>";
        echo "<button type='submit' name='limpiar' style='background: #dc3545; color: white; padding: 10px; border: none; border-radius: 5px;'>Limpiar Todas las Reservas Problemáticas</button>";
        echo "</form>";
        
        if (isset($_POST['limpiar'])) {
            echo "<h2>4. Limpiando reservas...</h2>";
            
            // Eliminar reservas con ID 0 o propietario ID 0
            $sql = "DELETE FROM reservas WHERE id_reser = 0 OR propietarios_id_pro = 0";
            if ($conn->query($sql)) {
                $affected = $conn->affected_rows;
                echo "<p>✅ Se eliminaron $affected reservas problemáticas</p>";
                
                // Verificar que se limpiaron
                $sql = "SELECT COUNT(*) as total FROM reservas WHERE id_reser = 0 OR propietarios_id_pro = 0";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                
                if ($row['total'] == 0) {
                    echo "<p>✅ Verificación: No quedan reservas problemáticas</p>";
                } else {
                    echo "<p>⚠️ Aún quedan " . $row['total'] . " reservas problemáticas</p>";
                }
                
            } else {
                echo "<p>❌ Error eliminando reservas: " . $conn->error . "</p>";
            }
        }
        
    } else {
        echo "<p>✅ No hay reservas problemáticas</p>";
    }
    
    echo "<h2>5. Verificar propietarios:</h2>";
    $sql = "SELECT p.id, p.usuario_id, u.nombre, u.apellido 
            FROM propietarios p 
            LEFT JOIN usuarios u ON p.usuario_id = u.id 
            ORDER BY p.id";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Propietario</th><th>Usuario ID</th><th>Nombre</th><th>Apellido</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $style = ($row['id'] == 0) ? "background-color: #ffebee;" : "";
            echo "<tr style='$style'>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['usuario_id'] . "</td>";
            echo "<td>" . $row['nombre'] . "</td>";
            echo "<td>" . $row['apellido'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar si hay propietario con ID 0
        $sql = "SELECT COUNT(*) as count FROM propietarios WHERE id = 0";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo "<p>⚠️ Hay un propietario con ID 0</p>";
            echo "<form method='post'>";
            echo "<button type='submit' name='corregir_propietario' style='background: #ff9800; color: white; padding: 10px; border: none; border-radius: 5px;'>Corregir Propietario con ID 0</button>";
            echo "</form>";
            
            if (isset($_POST['corregir_propietario'])) {
                echo "<h3>Corrigiendo propietario con ID 0...</h3>";
                
                // Obtener el siguiente ID disponible
                $sql = "SELECT MAX(id) + 1 as next_id FROM propietarios WHERE id > 0";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                $next_id = $row['next_id'] ?: 1;
                
                // Actualizar el propietario con ID 0
                $sql = "UPDATE propietarios SET id = $next_id WHERE id = 0";
                if ($conn->query($sql)) {
                    echo "<p>✅ Propietario con ID 0 corregido a ID $next_id</p>";
                } else {
                    echo "<p>❌ Error corrigiendo propietario: " . $conn->error . "</p>";
                }
            }
        } else {
            echo "<p>✅ No hay propietarios con ID 0</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Enlaces:</h2>";
echo "<a href='verificar_estructura_tablas.php'>Verificar estructura de tablas</a><br>";
echo "<a href='test_qr_fix.php'>Probar generación de QR</a><br>";
echo "<a href='usuario.php'>Volver al dashboard</a><br>";
?>
