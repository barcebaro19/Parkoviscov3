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

$pagosController = new PagosController();
$wompiIntegration = new WompiIntegration();
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $concepto_id = intval($_POST['concepto_id'] ?? 0);
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
    
    if ($concepto_id && $metodo_pago && $fecha_vencimiento) {
        // Crear el pago
        $resultado = $pagosController->crearPago(
            $_SESSION['id'],
            $concepto_id,
            $metodo_pago,
            $fecha_vencimiento
        );
        
        if ($resultado['success']) {
            // Obtener información del usuario
            $usuario_info = [
                'email' => $_SESSION['email'] ?? 'usuario@ejemplo.com',
                'nombre' => $_SESSION['nombre'] ?? 'Usuario'
            ];
            
            // Crear transacción en Wompi
            $transaccion = $wompiIntegration->crearTransaccion(
                $resultado['pago_id'],
                $resultado['monto'],
                $resultado['referencia'],
                $metodo_pago,
                $usuario_info
            );
            
            if ($transaccion['success']) {
                // Redirigir a Wompi para completar el pago
                if ($transaccion['payment_url']) {
                    header('Location: ' . $transaccion['payment_url']);
                    exit();
                } else {
                    $mensaje = 'Pago creado exitosamente. ID de transacción: ' . $transaccion['transaction_id'];
                }
            } else {
                $error = 'Error al procesar el pago: ' . $transaccion['message'];
            }
        } else {
            $error = $resultado['message'];
        }
    } else {
        $error = 'Todos los campos son obligatorios';
    }
}

// Obtener conceptos de pago disponibles
$conceptos = $pagosController->obtenerConceptosPago();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago | Quintanares by Parkovisco</title>
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
        }
        
        .cyber-button:hover {
            background: linear-gradient(135deg, #059669, #047857);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            transform: translateY(-2px);
        }
        
        .form-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.3);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #10b981, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body>
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold gradient-text mb-2">
                    <i class="fas fa-credit-card mr-3"></i>
                    Procesar Pago
                </h1>
                <p class="text-white/70">Quintanares by Parkovisco</p>
            </div>
            
            <!-- Formulario de Pago -->
            <div class="glass-card p-8">
                <?php if ($mensaje): ?>
                    <div class="bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 p-4 rounded-lg mb-6">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="bg-red-500/20 border border-red-500/30 text-red-400 p-4 rounded-lg mb-6">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6">
                    <!-- Concepto de Pago -->
                    <div>
                        <label class="block text-white font-semibold mb-2">
                            <i class="fas fa-receipt mr-2 text-emerald-400"></i>
                            Concepto de Pago
                        </label>
                        <select name="concepto_id" class="form-input w-full" required>
                            <option value="">Selecciona un concepto</option>
                            <?php foreach ($conceptos as $concepto): ?>
                                <option value="<?php echo $concepto['id']; ?>">
                                    <?php echo htmlspecialchars($concepto['nombre']); ?> - 
                                    $<?php echo number_format($concepto['monto'], 0, ',', '.'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-white font-semibold mb-2">
                            <i class="fas fa-credit-card mr-2 text-emerald-400"></i>
                            Método de Pago
                        </label>
                        <select name="metodo_pago" class="form-input w-full" required>
                            <option value="">Selecciona un método</option>
                            <option value="tarjeta">💳 Tarjeta de Crédito/Débito</option>
                            <option value="pse">🏦 PSE (Transferencia Bancaria)</option>
                            <option value="nequi">📱 Nequi</option>
                            <option value="daviplata">📱 Daviplata</option>
                        </select>
                    </div>
                    
                    <!-- Fecha de Vencimiento -->
                    <div>
                        <label class="block text-white font-semibold mb-2">
                            <i class="fas fa-calendar mr-2 text-emerald-400"></i>
                            Fecha de Vencimiento
                        </label>
                        <input type="date" name="fecha_vencimiento" class="form-input w-full" required>
                    </div>
                    
                    <!-- Botones -->
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="cyber-button flex-1">
                            <i class="fas fa-credit-card mr-2"></i>
                            Procesar Pago
                        </button>
                        <a href="usuario.php" class="cyber-button bg-gray-600 hover:bg-gray-700 flex-1 text-center">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Información de Seguridad -->
            <div class="glass-card p-6 mt-6">
                <h3 class="text-lg font-bold text-white mb-4">
                    <i class="fas fa-shield-alt mr-2 text-emerald-400"></i>
                    Información de Seguridad
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-white/70">
                    <div class="flex items-center">
                        <i class="fas fa-lock mr-2 text-emerald-400"></i>
                        <span>Transacciones 100% seguras</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-encrypt mr-2 text-emerald-400"></i>
                        <span>Datos encriptados SSL</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-certificate mr-2 text-emerald-400"></i>
                        <span>Certificado Wompi</span>
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
        // Establecer fecha mínima (hoy)
        document.querySelector('input[name="fecha_vencimiento"]').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
