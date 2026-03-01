<?php
session_start();
require_once __DIR__ . "/../app/Models/conexion.php";
require_once __DIR__ . "/../app/Controllers/pagos_controller.php";
require_once __DIR__ . "/../app/Controllers/wompi_integration.php";

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id']) || $_SESSION['nombre_rol'] !== 'propietario') {
    header('Location: login.php');
    exit();
}

$pago_id = intval($_GET['pago_id'] ?? 0);
$mensaje = '';
$tipo_mensaje = '';

if ($pago_id) {
    $pagosController = new PagosController();
    $wompiIntegration = new WompiIntegration();
    
    // Obtener información del pago
    $conexion = Conexion::getInstancia()->getConexion();
    $sql = "SELECT p.*, cp.nombre as concepto_nombre, cp.descripcion 
            FROM pagos p 
            INNER JOIN conceptos_pago cp ON p.concepto_id = cp.id 
            WHERE p.id = ? AND p.usuario_id = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $pago_id, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $pago = $result->fetch_assoc();
    
    if ($pago) {
        // Verificar estado en Wompi si hay transacción
        if ($pago['transaccion_id']) {
            $estado_wompi = $wompiIntegration->verificarTransaccion($pago['transaccion_id']);
            
            if ($estado_wompi['success']) {
                // Mapear estado de Wompi a nuestro sistema
                $mapeo_estados = [
                    'PENDING' => 'procesando',
                    'APPROVED' => 'aprobado',
                    'DECLINED' => 'rechazado',
                    'VOIDED' => 'cancelado'
                ];
                
                $nuevo_estado = $mapeo_estados[$estado_wompi['data']['status']] ?? 'pendiente';
                
                if ($nuevo_estado !== $pago['estado']) {
                    $pagosController->actualizarEstadoPago($pago_id, $nuevo_estado, $pago['transaccion_id']);
                    $pago['estado'] = $nuevo_estado;
                }
            }
        }
        
        // Determinar mensaje según estado
        switch ($pago['estado']) {
            case 'aprobado':
                $mensaje = '¡Pago procesado exitosamente!';
                $tipo_mensaje = 'success';
                break;
            case 'procesando':
                $mensaje = 'Su pago está siendo procesado. Recibirá una confirmación por email.';
                $tipo_mensaje = 'info';
                break;
            case 'rechazado':
                $mensaje = 'Su pago fue rechazado. Por favor, intente con otro método de pago.';
                $tipo_mensaje = 'error';
                break;
            case 'cancelado':
                $mensaje = 'El pago fue cancelado.';
                $tipo_mensaje = 'warning';
                break;
            default:
                $mensaje = 'Estado del pago: ' . ucfirst($pago['estado']);
                $tipo_mensaje = 'info';
        }
    } else {
        $mensaje = 'Pago no encontrado';
        $tipo_mensaje = 'error';
    }
} else {
    $mensaje = 'ID de pago no válido';
    $tipo_mensaje = 'error';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pago | Quintanares by Parkovisco</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        body {
            background: linear-gradient(135deg, 
                #0a0a0a 0%, 
                #1a1a2e 25%, 
                #16213e 50%, 
                #0f3460 75%, 
                #533483 100%
            );
            min-height: 100vh;
            color: #ffffff;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .cyber-button {
            background: linear-gradient(135deg, #10b981, #059669);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .cyber-button:hover {
            background: linear-gradient(135deg, #059669, #047857);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .status-success { color: #10b981; }
        .status-error { color: #ef4444; }
        .status-warning { color: #f59e0b; }
        .status-info { color: #3b82f6; }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold gradient-text mb-2">
                    <i class="fas fa-receipt mr-3"></i>
                    Confirmación de Pago
                </h1>
                <p class="text-white/70">Quintanares by Parkovisco</p>
            </div>
            
            <!-- Tarjeta de Estado -->
            <div class="glass-card p-8 text-center">
                <!-- Icono de Estado -->
                <div class="status-icon status-<?php echo $tipo_mensaje; ?>">
                    <?php
                    switch ($tipo_mensaje) {
                        case 'success':
                            echo '<i class="fas fa-check-circle"></i>';
                            break;
                        case 'error':
                            echo '<i class="fas fa-times-circle"></i>';
                            break;
                        case 'warning':
                            echo '<i class="fas fa-exclamation-triangle"></i>';
                            break;
                        default:
                            echo '<i class="fas fa-info-circle"></i>';
                    }
                    ?>
                </div>
                
                <!-- Mensaje -->
                <h2 class="text-2xl font-bold text-white mb-4">
                    <?php echo htmlspecialchars($mensaje); ?>
                </h2>
                
                <?php if (isset($pago) && $pago): ?>
                    <!-- Detalles del Pago -->
                    <div class="bg-black/30 rounded-lg p-6 mb-6 text-left">
                        <h3 class="text-lg font-bold text-white mb-4">
                            <i class="fas fa-info-circle mr-2 text-emerald-400"></i>
                            Detalles del Pago
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-white/70">Concepto:</span>
                                <span class="text-white font-semibold"><?php echo htmlspecialchars($pago['concepto_nombre']); ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-white/70">Monto:</span>
                                <span class="text-emerald-400 font-bold">$<?php echo number_format($pago['monto'], 0, ',', '.'); ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-white/70">Referencia:</span>
                                <span class="text-white font-mono text-sm"><?php echo htmlspecialchars($pago['referencia_wompi']); ?></span>
                            </div>
                            
                            <div class="flex justify-between">
                                <span class="text-white/70">Estado:</span>
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    <?php
                                    switch ($pago['estado']) {
                                        case 'aprobado':
                                            echo 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30';
                                            break;
                                        case 'rechazado':
                                            echo 'bg-red-500/20 text-red-400 border border-red-500/30';
                                            break;
                                        case 'procesando':
                                            echo 'bg-blue-500/20 text-blue-400 border border-blue-500/30';
                                            break;
                                        default:
                                            echo 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30';
                                    }
                                    ?>">
                                    <?php echo strtoupper($pago['estado']); ?>
                                </span>
                            </div>
                            
                            <?php if ($pago['fecha_pago']): ?>
                                <div class="flex justify-between">
                                    <span class="text-white/70">Fecha de Pago:</span>
                                    <span class="text-white"><?php echo date('d/m/Y H:i:s', strtotime($pago['fecha_pago'])); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Botones de Acción -->
                    <div class="flex gap-4 justify-center">
                        <?php if ($pago['estado'] === 'aprobado'): ?>
                            <button onclick="imprimirRecibo()" class="cyber-button">
                                <i class="fas fa-print mr-2"></i>
                                Imprimir Recibo
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($pago['estado'] === 'rechazado'): ?>
                            <a href="procesar_pago.php" class="cyber-button">
                                <i class="fas fa-redo mr-2"></i>
                                Intentar Nuevamente
                            </a>
                        <?php endif; ?>
                        
                        <a href="usuario.php" class="cyber-button bg-gray-600 hover:bg-gray-700">
                            <i class="fas fa-home mr-2"></i>
                            Volver al Dashboard
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Información Adicional -->
            <div class="glass-card p-6 mt-6">
                <h3 class="text-lg font-bold text-white mb-4">
                    <i class="fas fa-question-circle mr-2 text-emerald-400"></i>
                    ¿Necesitas Ayuda?
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-white/70">
                    <div class="flex items-center">
                        <i class="fas fa-envelope mr-2 text-emerald-400"></i>
                        <span>parkovisco@gmail.com</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-phone mr-2 text-emerald-400"></i>
                        <span>+57 (1) 234-5678</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2 text-emerald-400"></i>
                        <span>Lunes a Viernes 8:00 - 18:00</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-headset mr-2 text-emerald-400"></i>
                        <span>Soporte 24/7</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function imprimirRecibo() {
            // Crear ventana de impresión
            const ventana = window.open('', '_blank');
            const contenido = `
                <html>
                <head>
                    <title>Recibo de Pago - Quintanares by Parkovisco</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .logo { font-size: 24px; font-weight: bold; color: #10b981; }
                        .details { margin: 20px 0; }
                        .detail-row { display: flex; justify-content: space-between; margin: 10px 0; }
                        .total { font-weight: bold; font-size: 18px; border-top: 2px solid #10b981; padding-top: 10px; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <div class="logo">Quintanares by Parkovisco</div>
                        <p>Recibo de Pago</p>
                    </div>
                    
                    <div class="details">
                        <div class="detail-row">
                            <span>Concepto:</span>
                            <span><?php echo htmlspecialchars($pago['concepto_nombre']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Referencia:</span>
                            <span><?php echo htmlspecialchars($pago['referencia_wompi']); ?></span>
                        </div>
                        <div class="detail-row">
                            <span>Fecha:</span>
                            <span><?php echo date('d/m/Y H:i:s'); ?></span>
                        </div>
                        <div class="detail-row total">
                            <span>Total Pagado:</span>
                            <span>$<?php echo number_format($pago['monto'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 30px; font-size: 12px; color: #666;">
                        <p>Gracias por su pago puntual</p>
                        <p>Quintanares by Parkovisco</p>
                    </div>
                </body>
                </html>
            `;
            
            ventana.document.write(contenido);
            ventana.document.close();
            ventana.print();
        }
    </script>
</body>
</html>
