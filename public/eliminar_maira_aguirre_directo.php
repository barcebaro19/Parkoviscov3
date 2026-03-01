<?php
session_start();
require_once '../app/Models/conexion.php';

// Simular datos de sesión si no existen
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id'])) {
    $_SESSION['user_id'] = 39793688;
    $_SESSION['id'] = 39793688;
    $_SESSION['tipo_usuario'] = 'propietario';
    $_SESSION['nombre_rol'] = 'propietario';
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['id'];

echo "<h1>Eliminación Directa de Maira Aguirre</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    // 1. Buscar "maira aguirre" en visitantes
    echo "<h2>1. Buscar 'maira aguirre' en visitantes:</h2>";
    $sql_visitante = "SELECT * FROM visitantes WHERE nombre_visitante = 'maira aguirre'";
    $result_visitante = $conn->query($sql_visitante);
    
    if ($result_visitante && $result_visitante->num_rows > 0) {
        $visitante = $result_visitante->fetch_assoc();
        $visitante_id = $visitante['id_visit'];
        echo "<p>✅ Visitante encontrado: ID $visitante_id - " . $visitante['nombre_visitante'] . "</p>";
        
        // 2. Buscar reservas para este visitante
        echo "<h2>2. Buscar reservas para este visitante:</h2>";
        $sql_reservas = "SELECT * FROM reservas WHERE visitante_id_visit = $visitante_id";
        $result_reservas = $conn->query($sql_reservas);
        
        if ($result_reservas && $result_reservas->num_rows > 0) {
            echo "<p>✅ Se encontraron " . $result_reservas->num_rows . " reserva(s)</p>";
            
            // Mostrar reservas
            while ($row = $result_reservas->fetch_assoc()) {
                echo "<p>- Reserva ID: " . $row['id_reser'] . " - Fecha: " . $row['fecha_inicial'] . "</p>";
            }
            
            // 3. Eliminar reservas
            echo "<h2>3. Eliminando reservas:</h2>";
            $conn->begin_transaction();
            
            $sql_eliminar_reservas = "DELETE FROM reservas WHERE visitante_id_visit = $visitante_id";
            if ($conn->query($sql_eliminar_reservas)) {
                $filas_reservas = $conn->affected_rows;
                echo "<p>✅ Reservas eliminadas: $filas_reservas fila(s)</p>";
                
                // 4. Eliminar visitante
                echo "<h2>4. Eliminando visitante:</h2>";
                $sql_eliminar_visitante = "DELETE FROM visitantes WHERE id_visit = $visitante_id";
                if ($conn->query($sql_eliminar_visitante)) {
                    $filas_visitante = $conn->affected_rows;
                    echo "<p>✅ Visitante eliminado: $filas_visitante fila(s)</p>";
                    
                    $conn->commit();
                    echo "<p>✅ <strong>ELIMINACIÓN COMPLETA EXITOSA</strong></p>";
                } else {
                    $conn->rollback();
                    echo "<p>❌ Error eliminando visitante: " . $conn->error . "</p>";
                }
            } else {
                $conn->rollback();
                echo "<p>❌ Error eliminando reservas: " . $conn->error . "</p>";
            }
            
        } else {
            echo "<p>❌ No se encontraron reservas para este visitante</p>";
            
            // Eliminar solo el visitante
            echo "<h2>3. Eliminando solo el visitante:</h2>";
            $conn->begin_transaction();
            
            $sql_eliminar_visitante = "DELETE FROM visitantes WHERE id_visit = $visitante_id";
            if ($conn->query($sql_eliminar_visitante)) {
                $filas_visitante = $conn->affected_rows;
                echo "<p>✅ Visitante eliminado: $filas_visitante fila(s)</p>";
                $conn->commit();
                echo "<p>✅ <strong>ELIMINACIÓN EXITOSA</strong></p>";
            } else {
                $conn->rollback();
                echo "<p>❌ Error eliminando visitante: " . $conn->error . "</p>";
            }
        }
        
    } else {
        echo "<p>❌ No se encontró 'maira aguirre' en visitantes</p>";
    }
    
    // 5. Verificar eliminación
    echo "<h2>5. Verificar eliminación:</h2>";
    $sql_verificar = "SELECT * FROM visitantes WHERE nombre_visitante = 'maira aguirre'";
    $result_verificar = $conn->query($sql_verificar);
    
    if ($result_verificar && $result_verificar->num_rows > 0) {
        echo "<p>❌ 'maira aguirre' AÚN EXISTE en visitantes</p>";
    } else {
        echo "<p>✅ 'maira aguirre' fue eliminado correctamente de visitantes</p>";
    }
    
    // Verificar reservas
    $sql_verificar_reservas = "SELECT r.*, v.nombre_visitante
                               FROM reservas r 
                               INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                               WHERE v.nombre_visitante = 'maira aguirre'";
    
    $result_verificar_reservas = $conn->query($sql_verificar_reservas);
    
    if ($result_verificar_reservas && $result_verificar_reservas->num_rows > 0) {
        echo "<p>❌ Reservas de 'maira aguirre' AÚN EXISTEN</p>";
    } else {
        echo "<p>✅ Reservas de 'maira aguirre' fueron eliminadas correctamente</p>";
    }
    
    echo "<p><a href='debug_estado_real.php'>Verificar estado actual</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    if (isset($conn)) {
        $conn->rollback();
    }
}
?>
