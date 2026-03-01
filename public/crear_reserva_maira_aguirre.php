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

echo "<h1>Crear Reserva para Maira Aguirre</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    // 1. Obtener el ID del propietario
    echo "<h2>1. Obtener ID del propietario:</h2>";
    $sql_propietario = "SELECT id FROM propietarios WHERE usuario_id = ?";
    $stmt_propietario = $conn->prepare($sql_propietario);
    $stmt_propietario->bind_param("i", $user_id);
    $stmt_propietario->execute();
    $result_propietario = $stmt_propietario->get_result();
    
    if ($result_propietario->num_rows > 0) {
        $propietario = $result_propietario->fetch_assoc();
        $propietario_id = $propietario['id'];
        echo "<p>✅ Propietario ID: $propietario_id</p>";
    } else {
        echo "<p>❌ No se encontró el propietario</p>";
        exit();
    }
    
    // 2. Obtener el ID del visitante "maira aguirre"
    echo "<h2>2. Obtener ID del visitante 'maira aguirre':</h2>";
    $sql_visitante = "SELECT id_visit FROM visitantes WHERE nombre_visitante = 'maira aguirre'";
    $result_visitante = $conn->query($sql_visitante);
    
    if ($result_visitante && $result_visitante->num_rows > 0) {
        $visitante = $result_visitante->fetch_assoc();
        $visitante_id = $visitante['id_visit'];
        echo "<p>✅ Visitante ID: $visitante_id</p>";
    } else {
        echo "<p>❌ No se encontró 'maira aguirre'</p>";
        exit();
    }
    
    // 3. Crear la reserva
    echo "<h2>3. Crear reserva:</h2>";
    $conn->begin_transaction();
    
    $fecha_actual = date('Y-m-d H:i:s');
    $codigo_qr = 'QR' . time() . rand(1000, 9999);
    
    $sql_reserva = "INSERT INTO reservas (
                        fecha_inicial, 
                        fecha_final, 
                        propietarios_id_pro, 
                        visitante_id_visit, 
                        parqueadero_id_parq, 
                        motivo_visita, 
                        codigo_qr, 
                        estado_qr, 
                        fecha_generacion, 
                        observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $fecha_final = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $parqueadero_id = 1; // ID por defecto
    $motivo = 'Visita familiar';
    $estado = 'activo';
    $observaciones = 'Reserva creada automáticamente';
    
    $stmt_reserva = $conn->prepare($sql_reserva);
    $stmt_reserva->bind_param("ssiiisssss", 
        $fecha_actual, 
        $fecha_final, 
        $propietario_id, 
        $visitante_id, 
        $parqueadero_id, 
        $motivo, 
        $codigo_qr, 
        $estado, 
        $fecha_actual, 
        $observaciones
    );
    
    if ($stmt_reserva->execute()) {
        $reserva_id = $conn->insert_id;
        $conn->commit();
        
        echo "<p>✅ Reserva creada exitosamente</p>";
        echo "<p><strong>ID de Reserva:</strong> $reserva_id</p>";
        echo "<p><strong>Código QR:</strong> $codigo_qr</p>";
        echo "<p><strong>Fecha Inicial:</strong> $fecha_actual</p>";
        echo "<p><strong>Fecha Final:</strong> $fecha_final</p>";
        
        // 4. Verificar que la reserva se creó
        echo "<h2>4. Verificar reserva creada:</h2>";
        $sql_verificar = "SELECT r.id_reser, v.nombre_visitante, r.fecha_inicial, r.codigo_qr
                          FROM reservas r 
                          INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                          WHERE r.id_reser = ?";
        
        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("i", $reserva_id);
        $stmt_verificar->execute();
        $result_verificar = $stmt_verificar->get_result();
        
        if ($result_verificar->num_rows > 0) {
            $reserva_creada = $result_verificar->fetch_assoc();
            echo "<p>✅ Reserva verificada:</p>";
            echo "<ul>";
            echo "<li>ID: " . $reserva_creada['id_reser'] . "</li>";
            echo "<li>Visitante: " . $reserva_creada['nombre_visitante'] . "</li>";
            echo "<li>Fecha: " . $reserva_creada['fecha_inicial'] . "</li>";
            echo "<li>QR: " . $reserva_creada['codigo_qr'] . "</li>";
            echo "</ul>";
        }
        
    } else {
        $conn->rollback();
        echo "<p>❌ Error creando reserva: " . $stmt_reserva->error . "</p>";
    }
    
    echo "<p><a href='verificar_reservas_existentes.php'>Verificar reservas actualizadas</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    if (isset($conn)) {
        $conn->rollback();
    }
}
?>
