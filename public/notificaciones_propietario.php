<?php
session_start();
require_once "models/conexion.php";

// Verificar que el usuario sea administrador
if(!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

// Obtener ID del propietario
$propietario_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($propietario_id <= 0) {
    header('Location: gestion_propietarios.php');
    exit();
}

// Obtener información del propietario
$conexion = Conexion::getInstancia()->getConexion();

$sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular, 
               p.torre, p.piso, p.apartamento
        FROM usuarios u 
        LEFT JOIN propietarios p ON u.id = p.usuarios_id
        WHERE u.id = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$result = $stmt->get_result();
$propietario = $result->fetch_assoc();

if(!$propietario) {
    header('Location: gestion_propietarios.php');
    exit();
}

// Procesar envío de notificación
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enviar_notificacion') {
    $titulo = trim($_POST['titulo']);
    $mensaje = trim($_POST['mensaje']);
    $tipo = $_POST['tipo'];
    $prioridad = $_POST['prioridad'];
    
    if(!empty($titulo) && !empty($mensaje)) {
        // Insertar notificación en la base de datos
        $sql_notif = "INSERT INTO notificaciones (titulo, mensaje, tipo, prioridad, id_usuario, fecha_envio, estado) 
                      VALUES (?, ?, ?, ?, ?, NOW(), 'Enviada')";
        $stmt_notif = $conexion->prepare($sql_notif);
        $stmt_notif->bind_param("ssssi", $titulo, $mensaje, $tipo, $prioridad, $propietario_id);
        
        if($stmt_notif->execute()) {
            $mensaje_exito = "Notificación enviada correctamente";
        } else {
            $mensaje_error = "Error al enviar la notificación";
        }
    } else {
        $mensaje_error = "Todos los campos son obligatorios";
    }
}

// Obtener notificaciones del propietario (simuladas por ahora)
$notificaciones = [
    [
        'id' => 1,
        'titulo' => 'Bienvenido al sistema',
        'mensaje' => 'Su cuenta ha sido activada correctamente. Puede comenzar a usar todas las funcionalidades.',
        'tipo' => 'Sistema',
        'prioridad' => 'Normal',
        'fecha_envio' => '2024-01-15 10:30:00',
        'estado' => 'Enviada',
        'leida' => true
    ],
    [
        'id' => 2,
        'titulo' => 'Recordatorio de pago',
        'mensaje' => 'Recuerde realizar el pago de administración correspondiente al mes de enero.',
        'tipo' => 'Administración',
        'prioridad' => 'Alta',
        'fecha_envio' => '2024-01-10 14:20:00',
        'estado' => 'Enviada',
        'leida' => false
    ],
    [
        'id' => 3,
        'titulo' => 'Mantenimiento programado',
        'mensaje' => 'Se realizará mantenimiento en el parqueadero el próximo viernes de 8:00 AM a 12:00 PM.',
        'tipo' => 'Mantenimiento',
        'prioridad' => 'Normal',
        'fecha_envio' => '2024-01-08 16:45:00',
        'estado' => 'Enviada',
        'leida' => true
    ],
    [
        'id' => 4,
        'titulo' => 'Asamblea extraordinaria',
        'mensaje' => 'Se convoca a asamblea extraordinaria el próximo martes a las 7:00 PM en el salón comunal.',
        'tipo' => 'Eventos',
        'prioridad' => 'Alta',
        'fecha_envio' => '2024-01-05 09:15:00',
        'estado' => 'Enviada',
        'leida' => false
    ]
];

// Estadísticas
$total_notificaciones = count($notificaciones);
$no_leidas = count(array_filter($notificaciones, function($n) { return !$n['leida']; }));
$por_tipo = array_count_values(array_column($notificaciones, 'tipo'));
?>

<!DOCTYPE html>
<html lang="es" x-data="{ activeFilter: 'todas', showModal: false }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Notificaciones | Quintanares by Parkovisco</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }
        
        .cyber-button {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .cyber-button:hover {
            background: linear-gradient(135deg, #059669, #047857);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        .filter-button {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filter-button.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-color: #10b981;
        }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">
    <!-- Header -->
    <header class="glass-card m-4 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button onclick="window.close()" class="cyber-button">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver
                </button>
                <div>
                    <h1 class="text-2xl font-bold text-yellow-400">
                        <i class="fas fa-bell mr-2"></i>
                        Gestión de Notificaciones
                    </h1>
                    <p class="text-white/60"><?php echo htmlspecialchars($propietario['nombre'] . ' ' . $propietario['apellido']); ?> - <?php echo htmlspecialchars($propietario['torre'] . ' ' . $propietario['piso'] . '-' . $propietario['apartamento']); ?></p>
                </div>
            </div>
            <button @click="showModal = true" class="cyber-button">
                <i class="fas fa-plus mr-2"></i>
                Nueva Notificación
            </button>
        </div>
    </header>

    <!-- Estadísticas -->
    <div class="mx-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Notificaciones</p>
                        <p class="text-2xl font-bold text-white"><?php echo $total_notificaciones; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">No Leídas</p>
                        <p class="text-2xl font-bold text-red-400"><?php echo $no_leidas; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell-slash text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Administración</p>
                        <p class="text-2xl font-bold text-blue-400"><?php echo $por_tipo['Administración'] ?? 0; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-building text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Eventos</p>
                        <p class="text-2xl font-bold text-purple-400"><?php echo $por_tipo['Eventos'] ?? 0; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mx-4 mb-6">
        <div class="glass-card p-4">
            <div class="flex gap-2 flex-wrap">
                <button @click="activeFilter = 'todas'" :class="activeFilter === 'todas' ? 'filter-button active' : 'filter-button'">
                    Todas
                </button>
                <button @click="activeFilter = 'no leídas'" :class="activeFilter === 'no leídas' ? 'filter-button active' : 'filter-button'">
                    No Leídas
                </button>
                <button @click="activeFilter = 'administración'" :class="activeFilter === 'administración' ? 'filter-button active' : 'filter-button'">
                    Administración
                </button>
                <button @click="activeFilter = 'eventos'" :class="activeFilter === 'eventos' ? 'filter-button active' : 'filter-button'">
                    Eventos
                </button>
                <button @click="activeFilter = 'mantenimiento'" :class="activeFilter === 'mantenimiento' ? 'filter-button active' : 'filter-button'">
                    Mantenimiento
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Notificaciones -->
    <div class="mx-4">
        <div class="glass-card p-6">
            <h2 class="text-xl font-bold text-yellow-400 mb-4">
                <i class="fas fa-bell mr-2"></i>Historial de Notificaciones
            </h2>
            
            <?php if(!empty($notificaciones)): ?>
                <div class="space-y-4">
                    <?php foreach($notificaciones as $notif): ?>
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 <?php echo !$notif['leida'] ? 'border-yellow-500/50' : ''; ?>" 
                             x-show="activeFilter === 'todas' || activeFilter === '<?php echo strtolower($notif['tipo']); ?>' || (activeFilter === 'no leídas' && !<?php echo $notif['leida'] ? 'true' : 'false'; ?>)">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-bell text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="font-semibold text-white"><?php echo htmlspecialchars($notif['titulo']); ?></h3>
                                            <?php if(!$notif['leida']): ?>
                                                <span class="w-2 h-2 bg-yellow-400 rounded-full"></span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-white/60 text-sm mb-2"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                                        <div class="flex items-center gap-4 text-xs text-white/40">
                                            <span><i class="fas fa-tag mr-1"></i><?php echo htmlspecialchars($notif['tipo']); ?></span>
                                            <span><i class="fas fa-flag mr-1"></i><?php echo htmlspecialchars($notif['prioridad']); ?></span>
                                            <span><i class="fas fa-calendar mr-1"></i><?php echo date('d/m/Y H:i', strtotime($notif['fecha_envio'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    <?php 
                                    switch($notif['prioridad']) {
                                        case 'Alta': echo 'bg-red-500/20 text-red-400'; break;
                                        case 'Normal': echo 'bg-green-500/20 text-green-400'; break;
                                        case 'Baja': echo 'bg-gray-500/20 text-gray-400'; break;
                                        default: echo 'bg-gray-500/20 text-gray-400';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($notif['prioridad']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-white/60 py-8">
                    <i class="fas fa-bell text-4xl mb-4 block"></i>
                    <p>No hay notificaciones enviadas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para nueva notificación -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-cloak>
        <div class="glass-card p-6 w-full max-w-lg mx-4">
            <h3 class="text-xl font-bold text-yellow-400 mb-4">Nueva Notificación</h3>
            
            <?php if(isset($mensaje_exito)): ?>
                <div class="mb-4 p-3 bg-green-500/10 border border-green-500/20 rounded-lg">
                    <p class="text-green-400"><?php echo $mensaje_exito; ?></p>
                </div>
            <?php endif; ?>
            
            <?php if(isset($mensaje_error)): ?>
                <div class="mb-4 p-3 bg-red-500/10 border border-red-500/20 rounded-lg">
                    <p class="text-red-400"><?php echo $mensaje_error; ?></p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="enviar_notificacion">
                
                <div class="mb-4">
                    <label class="text-white/60 text-sm">Título</label>
                    <input type="text" name="titulo" required class="w-full p-3 bg-gray-800 border border-gray-600 rounded-lg text-white" 
                           placeholder="Título de la notificación">
                </div>
                
                <div class="mb-4">
                    <label class="text-white/60 text-sm">Mensaje</label>
                    <textarea name="mensaje" rows="4" required class="w-full p-3 bg-gray-800 border border-gray-600 rounded-lg text-white" 
                              placeholder="Contenido de la notificación..."></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-white/60 text-sm">Tipo</label>
                        <select name="tipo" class="w-full p-3 bg-gray-800 border border-gray-600 rounded-lg text-white">
                            <option value="Administración">Administración</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Eventos">Eventos</option>
                            <option value="Seguridad">Seguridad</option>
                            <option value="Sistema">Sistema</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="text-white/60 text-sm">Prioridad</label>
                        <select name="prioridad" class="w-full p-3 bg-gray-800 border border-gray-600 rounded-lg text-white">
                            <option value="Normal">Normal</option>
                            <option value="Alta">Alta</option>
                            <option value="Baja">Baja</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="cyber-button flex-1">
                        <i class="fas fa-paper-plane mr-2"></i>Enviar
                    </button>
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
