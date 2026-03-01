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

echo "<h1>Verificar Reservas Actuales</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    // 1. Mostrar TODAS las reservas del usuario
    echo "<h2>1. Todas las reservas del usuario:</h2>";
    $sql_reservas = "SELECT r.id_reser, v.nombre_visitante, r.fecha_inicial, r.estado_qr
                     FROM reservas r 
                     INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                     INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                     WHERE p.usuario_id = ? 
                     ORDER BY r.id_reser DESC";
    
    $stmt_reservas = $conn->prepare($sql_reservas);
    $stmt_reservas->bind_param("i", $user_id);
    $stmt_reservas->execute();
    $result_reservas = $stmt_reservas->get_result();
    
    if ($result_reservas->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID Reserva</th><th>Visitante</th><th>Fecha</th><th>Estado</th><th>Acción</th></tr>";
        
        while ($row = $result_reservas->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_reser'] . "</td>";
            echo "<td>" . $row['nombre_visitante'] . "</td>";
            echo "<td>" . $row['fecha_inicial'] . "</td>";
            echo "<td>" . $row['estado_qr'] . "</td>";
            echo "<td><a href='debug_eliminar_ambas_tablas.php?id=" . $row['id_reser'] . "'>Eliminar Ambas Tablas</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No hay reservas para este usuario</p>";
    }
    
    // 2. Mostrar visitantes huérfanos (sin reserva)
    echo "<h2>2. Visitantes sin reserva:</h2>";
    $sql_huérfanos = "SELECT v.id_visit, v.nombre_visitante, v.documento, v.fecha_registro
                      FROM visitantes v 
                      LEFT JOIN reservas r ON v.id_visit = r.visitante_id_visit 
                      WHERE r.visitante_id_visit IS NULL
                      ORDER BY v.id_visit DESC";
    
    $result_huérfanos = $conn->query($sql_huérfanos);
    
    if ($result_huérfanos && $result_huérfanos->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID Visitante</th><th>Nombre</th><th>Documento</th><th>Fecha Registro</th><th>Acción</th></tr>";
        
        while ($row = $result_huérfanos->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_visit'] . "</td>";
            echo "<td>" . $row['nombre_visitante'] . "</td>";
            echo "<td>" . $row['documento'] . "</td>";
            echo "<td>" . $row['fecha_registro'] . "</td>";
            echo "<td><a href='eliminar_visitante_huérfano.php?id=" . $row['id_visit'] . "'>Eliminar Visitante</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>✅ No hay visitantes huérfanos</p>";
    }
    
    // 3. Mostrar las últimas 10 reservas en la BD (todas)
    echo "<h2>3. Últimas 10 reservas en la BD (todas):</h2>";
    $sql_todas = "SELECT r.id_reser, v.nombre_visitante, r.fecha_inicial, p.usuario_id
                  FROM reservas r 
                  INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                  INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                  ORDER BY r.id_reser DESC 
                  LIMIT 10";
    
    $result_todas = $conn->query($sql_todas);
    
    if ($result_todas && $result_todas->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID Reserva</th><th>Visitante</th><th>Fecha</th><th>Usuario ID</th></tr>";
        
        while ($row = $result_todas->fetch_assoc()) {
            $highlight = ($row['usuario_id'] == $user_id) ? 'style="background-color: yellow;"' : '';
            echo "<tr $highlight>";
            echo "<td>" . $row['id_reser'] . "</td>";
            echo "<td>" . $row['nombre_visitante'] . "</td>";
            echo "<td>" . $row['fecha_inicial'] . "</td>";
            echo "<td>" . $row['usuario_id'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><em>Las filas amarillas son las reservas de tu usuario</em></p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
