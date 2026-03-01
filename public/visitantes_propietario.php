<?php
session_start();
require_once __DIR__ . "/../app/Models/conexion.php";

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

// Procesar acciones
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'aprobar_visita':
                $visita_id = (int)$_POST['visita_id'];
                $stmt = $conexion->prepare("UPDATE visitas SET estado = 'Aprobada' WHERE id_visita = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $visita_id, $propietario_id);
                $stmt->execute();
                break;
                
            case 'rechazar_visita':
                $visita_id = (int)$_POST['visita_id'];
                $stmt = $conexion->prepare("UPDATE visitas SET estado = 'Rechazada' WHERE id_visita = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $visita_id, $propietario_id);
                $stmt->execute();
                break;
                
            case 'eliminar_visita':
                $visita_id = (int)$_POST['visita_id'];
                $stmt = $conexion->prepare("DELETE FROM visitas WHERE id_visita = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $visita_id, $propietario_id);
                $stmt->execute();
                break;
        }
        header('Location: visitantes_propietario.php?id=' . $propietario_id);
        exit();
    }
}

// Obtener todas las visitas del propietario
$sql_visitas = "SELECT * FROM visitas WHERE id_usuario = ? ORDER BY fecha_visita DESC, hora_inicio DESC";
$stmt_visitas = $conexion->prepare($sql_visitas);
$stmt_visitas->bind_param("i", $propietario_id);
$stmt_visitas->execute();
$visitas = $stmt_visitas->get_result();

// Estadísticas
$sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'Aprobada' THEN 1 ELSE 0 END) as aprobadas,
                SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'Rechazada' THEN 1 ELSE 0 END) as rechazadas
              FROM visitas WHERE id_usuario = ?";
$stmt_stats = $conexion->prepare($sql_stats);
$stmt_stats->bind_param("i", $propietario_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es" x-data="{ activeFilter: 'todas' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Visitantes | Quintanares by Parkovisco</title>
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
                    <h1 class="text-2xl font-bold text-emerald-400">
                        <i class="fas fa-users mr-2"></i>
                        Gestión de Visitantes
                    </h1>
                    <p class="text-white/60"><?php echo htmlspecialchars($propietario['nombre'] . ' ' . $propietario['apellido']); ?> - <?php echo htmlspecialchars($propietario['torre'] . ' ' . $propietario['apiso'] . '-' . $propietario['apartamento']); ?></p>
                </div>
            </div>
        </div>
    </header>

    <!-- Estadísticas -->
    <div class="mx-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Visitas</p>
                        <p class="text-2xl font-bold text-white"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Aprobadas</p>
                        <p class="text-2xl font-bold text-green-400"><?php echo $stats['aprobadas']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Pendientes</p>
                        <p class="text-2xl font-bold text-yellow-400"><?php echo $stats['pendientes']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Rechazadas</p>
                        <p class="text-2xl font-bold text-red-400"><?php echo $stats['rechazadas']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times text-white"></i>
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
                <button @click="activeFilter = 'pendientes'" :class="activeFilter === 'pendientes' ? 'filter-button active' : 'filter-button'">
                    Pendientes
                </button>
                <button @click="activeFilter = 'aprobadas'" :class="activeFilter === 'aprobadas' ? 'filter-button active' : 'filter-button'">
                    Aprobadas
                </button>
                <button @click="activeFilter = 'rechazadas'" :class="activeFilter === 'rechazadas' ? 'filter-button active' : 'filter-button'">
                    Rechazadas
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Visitas -->
    <div class="mx-4">
        <div class="glass-card p-6">
            <h2 class="text-xl font-bold text-emerald-400 mb-4">
                <i class="fas fa-calendar-alt mr-2"></i>Visitas Programadas
            </h2>
            
            <?php if($visitas && $visitas->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($visita = $visitas->fetch_assoc()): ?>
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700" 
                             x-show="activeFilter === 'todas' || activeFilter === '<?php echo strtolower($visita['estado']); ?>'">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-white"><?php echo htmlspecialchars($visita['nombre_visitante']); ?></h3>
                                        <p class="text-white/60 text-sm">Documento: <?php echo htmlspecialchars($visita['documento_visitante']); ?></p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php 
                                    switch($visita['estado']) {
                                        case 'Aprobada': echo 'bg-green-500/20 text-green-400'; break;
                                        case 'Pendiente': echo 'bg-yellow-500/20 text-yellow-400'; break;
                                        case 'Rechazada': echo 'bg-red-500/20 text-red-400'; break;
                                        default: echo 'bg-gray-500/20 text-gray-400';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($visita['estado']); ?>
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <label class="text-white/60 text-sm">Fecha</label>
                                    <p class="text-white font-semibold"><?php echo date('d/m/Y', strtotime($visita['fecha_visita'])); ?></p>
                                </div>
                                <div>
                                    <label class="text-white/60 text-sm">Hora</label>
                                    <p class="text-white font-semibold"><?php echo htmlspecialchars($visita['hora_inicio'] . ' - ' . $visita['hora_fin']); ?></p>
                                </div>
                                <div>
                                    <label class="text-white/60 text-sm">Vehículo</label>
                                    <p class="text-white font-semibold"><?php echo htmlspecialchars($visita['placa_vehiculo']); ?></p>
                                </div>
                                <div>
                                    <label class="text-white/60 text-sm">Motivo</label>
                                    <p class="text-white font-semibold"><?php echo htmlspecialchars($visita['motivo']); ?></p>
                                </div>
                            </div>
                            
                            <?php if($visita['estado'] === 'Pendiente'): ?>
                                <div class="flex gap-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="aprobar_visita">
                                        <input type="hidden" name="visita_id" value="<?php echo $visita['id_visita']; ?>">
                                        <button type="submit" class="cyber-button text-sm">
                                            <i class="fas fa-check mr-1"></i>Aprobar
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="rechazar_visita">
                                        <input type="hidden" name="visita_id" value="<?php echo $visita['id_visita']; ?>">
                                        <button type="submit" class="cyber-button text-sm" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                                            <i class="fas fa-times mr-1"></i>Rechazar
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex gap-2 mt-2">
                                <form method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta visita?')">
                                    <input type="hidden" name="action" value="eliminar_visita">
                                    <input type="hidden" name="visita_id" value="<?php echo $visita['id_visita']; ?>">
                                    <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm transition-colors">
                                        <i class="fas fa-trash mr-1"></i>Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-white/60 py-8">
                    <i class="fas fa-calendar-alt text-4xl mb-4 block"></i>
                    <p>No hay visitas programadas</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
