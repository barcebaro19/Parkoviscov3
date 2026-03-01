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

echo "<h1>Verificación Solo Base de Datos</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    // 1. Buscar "maira aguirre" en visitantes
    echo "<h2>1. Estado de 'maira aguirre' en tabla visitantes:</h2>";
    $sql_visitante = "SELECT * FROM visitantes WHERE nombre_visitante LIKE '%maira%'";
    $result_visitante = $conn->query($sql_visitante);
    
    if ($result_visitante && $result_visitante->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Visitante</th><th>Nombre</th><th>Documento</th><th>Teléfono</th><th>Fecha Registro</th><th>Estado</th></tr>";
        
        while ($row = $result_visitante->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_visit'] . "</td>";
            echo "<td>" . $row['nombre_visitante'] . "</td>";
            echo "<td>" . $row['documento'] . "</td>";
            echo "<td>" . $row['telefono'] . "</td>";
            echo "<td>" . $row['fecha_registro'] . "</td>";
            echo "<td>" . $row['estado'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No se encontró 'maira aguirre' en visitantes</p>";
    }
    
    // 2. Buscar reservas para "maira aguirre"
    echo "<h2>2. Estado de reservas para 'maira aguirre':</h2>";
    $sql_reservas = "SELECT r.*, v.nombre_visitante, p.usuario_id
                     FROM reservas r 
                     INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                     INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                     WHERE v.nombre_visitante LIKE '%maira%'";
    
    $result_reservas = $conn->query($sql_reservas);
    
    if ($result_reservas && $result_reservas->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Reserva</th><th>Visitante</th><th>Fecha Inicial</th><th>Usuario ID</th><th>Propietario ID</th><th>Estado QR</th></tr>";
        
        while ($row = $result_reservas->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_reser'] . "</td>";
            echo "<td>" . $row['nombre_visitante'] . "</td>";
            echo "<td>" . $row['fecha_inicial'] . "</td>";
            echo "<td>" . $row['usuario_id'] . "</td>";
            echo "<td>" . $row['propietarios_id_pro'] . "</td>";
            echo "<td>" . $row['estado_qr'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No se encontraron reservas para 'maira aguirre'</p>";
    }
    
    // 3. Mostrar todas las reservas del usuario
    echo "<h2>3. Todas las reservas del usuario:</h2>";
    $sql_todas = "SELECT r.id_reser, v.nombre_visitante, r.fecha_inicial, r.estado_qr
                  FROM reservas r 
                  INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                  INNER JOIN propietarios p ON r.propietarios_id_pro = p.id 
                  WHERE p.usuario_id = ? 
                  ORDER BY r.id_reser DESC";
    
    $stmt_todas = $conn->prepare($sql_todas);
    $stmt_todas->bind_param("i", $user_id);
    $stmt_todas->execute();
    $result_todas = $stmt_todas->get_result();
    
    if ($result_todas->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID Reserva</th><th>Visitante</th><th>Fecha</th><th>Estado</th></tr>";
        
        while ($row = $result_todas->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id_reser'] . "</td>";
            echo "<td>" . $row['nombre_visitante'] . "</td>";
            echo "<td>" . $row['fecha_inicial'] . "</td>";
            echo "<td>" . $row['estado_qr'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No hay reservas para este usuario</p>";
    }
    
    echo "<p><a href='eliminar_maira_aguirre_directo.php'>Eliminar Maira Aguirre Directamente</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>
