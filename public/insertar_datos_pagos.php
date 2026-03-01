<?php
/**
 * Insertar datos de prueba para pagos
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

require_once '../app/Models/conexion.php';

try {
    $conn = Conexion::getInstancia()->getConexion();
    
    echo "<h1>Insertando datos de prueba para pagos</h1>";
    
    // 1. Insertar métodos de pago
    echo "<h2>1. Insertando métodos de pago...</h2>";
    $metodos = [
        [1, 'tarjeta', '**** 1234', 'Juan Pérez', 'tok_1234_5678'],
        [1, 'pse', '**** 5678', 'Juan Pérez', 'tok_5678_9012'],
        [1, 'nequi', '**** 9012', 'Juan Pérez', 'tok_9012_3456'],
        [1, 'daviplata', '**** 3456', 'Juan Pérez', 'tok_3456_7890'],
        [1, 'tarjeta', '**** 7890', 'Juan Pérez', 'tok_7890_1234']
    ];
    
    foreach ($metodos as $metodo) {
        $sql = "INSERT IGNORE INTO metodos_pago_usuario (usuario_id, tipo, numero_tarjeta, nombre_titular, token_wompi) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $metodo[0], $metodo[1], $metodo[2], $metodo[3], $metodo[4]);
        $stmt->execute();
        echo "Método insertado: " . $metodo[1] . "<br>";
    }
    
    // 2. Insertar pagos
    echo "<h2>2. Insertando pagos...</h2>";
    $pagos = [
        // Pagos aprobados (historial)
        [1, 1, 450000.00, 'aprobado', 'tarjeta', 'QNT17040672001234', '2025-01-05 10:30:00', '2025-01-05 10:35:00', '2025-01-15'],
        [1, 3, 180000.00, 'aprobado', 'pse', 'QNT17040672005678', '2025-01-10 14:20:00', '2025-01-10 14:25:00', '2025-01-20'],
        [1, 4, 120000.00, 'aprobado', 'nequi', 'QNT17040672009012', '2025-01-12 09:15:00', '2025-01-12 09:20:00', '2025-01-22'],
        
        // Pagos pendientes (urgentes)
        [1, 2, 450000.00, 'pendiente', 'tarjeta', 'QNT17040672003456', '2025-01-15 08:00:00', NULL, '2025-01-31'],
        [1, 5, 50000.00, 'pendiente', 'daviplata', 'QNT17040672007890', '2025-01-20 16:45:00', NULL, '2025-02-05'],
        
        // Pagos pendientes adicionales (para la presentación)
        [1, 1, 450000.00, 'pendiente', 'pse', 'QNT17040672001111', '2025-02-01 09:00:00', NULL, '2025-02-15'],
        [1, 3, 180000.00, 'pendiente', 'nequi', 'QNT17040672002222', '2025-02-01 10:30:00', NULL, '2025-02-20'],
        [1, 4, 120000.00, 'pendiente', 'tarjeta', 'QNT17040672003333', '2025-02-01 11:15:00', NULL, '2025-02-25'],
        
        // Pagos vencidos (para mostrar urgencia)
        [1, 5, 75000.00, 'pendiente', 'pse', 'QNT17040672004444', '2025-01-10 14:00:00', NULL, '2025-01-25'],
        [1, 1, 450000.00, 'pendiente', 'tarjeta', 'QNT17040672005555', '2025-01-05 08:00:00', NULL, '2025-01-20']
    ];
    
    foreach ($pagos as $pago) {
        $sql = "INSERT IGNORE INTO pagos (usuario_id, concepto_id, monto, estado, metodo_pago, referencia_wompi, fecha_creacion, fecha_pago, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidssssss", $pago[0], $pago[1], $pago[2], $pago[3], $pago[4], $pago[5], $pago[6], $pago[7], $pago[8]);
        $stmt->execute();
        echo "Pago insertado: " . $pago[2] . " - " . $pago[3] . "<br>";
    }
    
    echo "<h2>✅ Datos insertados correctamente</h2>";
    echo "<h3>Resumen de datos insertados:</h3>";
    echo "<ul>";
    echo "<li><strong>5 métodos de pago:</strong> Tarjeta, PSE, Nequi, Daviplata</li>";
    echo "<li><strong>3 pagos aprobados:</strong> Historial de pagos exitosos</li>";
    echo "<li><strong>7 pagos pendientes:</strong> Incluyendo algunos vencidos</li>";
    echo "<li><strong>Total pendiente:</strong> $1,820,000 COP</li>";
    echo "<li><strong>Total pagado:</strong> $750,000 COP</li>";
    echo "</ul>";
    
    echo "<h3>Próximos pasos:</h3>";
    echo "<p><a href='test_api_direct.php' style='background: #10B981; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Probar API nuevamente</a></p>";
    echo "<p><a href='usuario.php' style='background: #3B82F6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>💳 Ir al sistema de pagos</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
