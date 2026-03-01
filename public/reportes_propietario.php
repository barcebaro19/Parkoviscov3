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
            case 'cambiar_estado':
                $dano_id = (int)$_POST['dano_id'];
                $nuevo_estado = $_POST['nuevo_estado'];
                $comentario_admin = $_POST['comentario_admin'] ?? '';
                
                $stmt = $conexion->prepare("UPDATE danos SET estado = ?, comentario_admin = ?, fecha_respuesta = NOW() WHERE id_dano = ? AND id_usuario = ?");
                $stmt->bind_param("ssii", $nuevo_estado, $comentario_admin, $dano_id, $propietario_id);
                $stmt->execute();
                break;
                
            case 'eliminar_reporte':
                $dano_id = (int)$_POST['dano_id'];
                $stmt = $conexion->prepare("DELETE FROM danos WHERE id_dano = ? AND id_usuario = ?");
                $stmt->bind_param("ii", $dano_id, $propietario_id);
                $stmt->execute();
                break;
        }
        header('Location: reportes_propietario.php?id=' . $propietario_id);
        exit();
    }
}

// Obtener todos los reportes del propietario
$sql_danos = "SELECT d.*, v.placa, v.marca, v.modelo
              FROM danos d 
              JOIN vehiculos v ON d.id_vehiculo = v.id_vehiculo 
              WHERE d.id_usuario = ? 
              ORDER BY d.fecha_reporte DESC";
$stmt_danos = $conexion->prepare($sql_danos);
$stmt_danos->bind_param("i", $propietario_id);
$stmt_danos->execute();
$danos = $stmt_danos->get_result();

// Estadísticas
$sql_stats = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                SUM(CASE WHEN estado = 'En proceso' THEN 1 ELSE 0 END) as en_proceso,
                SUM(CASE WHEN estado = 'Resuelto' THEN 1 ELSE 0 END) as resueltos
              FROM danos WHERE id_usuario = ?";
$stmt_stats = $conexion->prepare($sql_stats);
$stmt_stats->bind_param("i", $propietario_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es" x-data="{ activeFilter: 'todos', showModal: false, selectedDano: null }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Reportes | Quintanares by Parkovisco</title>
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
                    <h1 class="text-2xl font-bold text-orange-400">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Gestión de Reportes
                    </h1>
                    <p class="text-white/60"><?php echo htmlspecialchars($propietario['nombre'] . ' ' . $propietario['apellido']); ?> - <?php echo htmlspecialchars($propietario['torre'] . ' ' . $propietario['piso'] . '-' . $propietario['apartamento']); ?></p>
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
                        <p class="text-white/60 text-sm">Total Reportes</p>
                        <p class="text-2xl font-bold text-white"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-white"></i>
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
                        <p class="text-white/60 text-sm">En Proceso</p>
                        <p class="text-2xl font-bold text-blue-400"><?php echo $stats['en_proceso']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-tools text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Resueltos</p>
                        <p class="text-2xl font-bold text-green-400"><?php echo $stats['resueltos']; ?></p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mx-4 mb-6">
        <div class="glass-card p-4">
            <div class="flex gap-2 flex-wrap">
                <button @click="activeFilter = 'todos'" :class="activeFilter === 'todos' ? 'filter-button active' : 'filter-button'">
                    Todos
                </button>
                <button @click="activeFilter = 'pendiente'" :class="activeFilter === 'pendiente' ? 'filter-button active' : 'filter-button'">
                    Pendientes
                </button>
                <button @click="activeFilter = 'en proceso'" :class="activeFilter === 'en proceso' ? 'filter-button active' : 'filter-button'">
                    En Proceso
                </button>
                <button @click="activeFilter = 'resuelto'" :class="activeFilter === 'resuelto' ? 'filter-button active' : 'filter-button'">
                    Resueltos
                </button>
            </div>
        </div>
    </div>

    <!-- Lista de Reportes -->
    <div class="mx-4">
        <div class="glass-card p-6">
            <h2 class="text-xl font-bold text-orange-400 mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>Reportes de Daños
            </h2>
            
            <?php if($danos && $danos->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($dano = $danos->fetch_assoc()): ?>
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700" 
                             x-show="activeFilter === 'todos' || activeFilter === '<?php echo strtolower($dano['estado']); ?>'">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-exclamation-triangle text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-orange-400"><?php echo htmlspecialchars($dano['tipo_dano']); ?></h3>
                                        <p class="text-white/60 text-sm">Vehículo: <?php echo htmlspecialchars($dano['placa'] . ' - ' . $dano['marca'] . ' ' . $dano['modelo']); ?></p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    <?php 
                                    switch($dano['estado']) {
                                        case 'Resuelto': echo 'bg-green-500/20 text-green-400'; break;
                                        case 'En proceso': echo 'bg-blue-500/20 text-blue-400'; break;
                                        case 'Pendiente': echo 'bg-yellow-500/20 text-yellow-400'; break;
                                        default: echo 'bg-gray-500/20 text-gray-400';
                                    }
                                    ?>">
                                    <?php echo htmlspecialchars($dano['estado']); ?>
                                </span>
                            </div>
                            
                            <div class="mb-4">
                                <label class="text-white/60 text-sm">Descripción del daño</label>
                                <p class="text-white"><?php echo htmlspecialchars($dano['descripcion']); ?></p>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="text-white/60 text-sm">Fecha de Reporte</label>
                                    <p class="text-white font-semibold"><?php echo date('d/m/Y H:i', strtotime($dano['fecha_reporte'])); ?></p>
                                </div>
                                <?php if($dano['fecha_respuesta']): ?>
                                    <div>
                                        <label class="text-white/60 text-sm">Fecha de Respuesta</label>
                                        <p class="text-white font-semibold"><?php echo date('d/m/Y H:i', strtotime($dano['fecha_respuesta'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if($dano['comentario_admin']): ?>
                                <div class="mb-4 p-3 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                                    <label class="text-blue-400 text-sm font-semibold">Comentario del Administrador</label>
                                    <p class="text-white"><?php echo htmlspecialchars($dano['comentario_admin']); ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="flex gap-2">
                                <button onclick="abrirModalEstado(<?php echo $dano['id_dano']; ?>, '<?php echo $dano['estado']; ?>')" 
                                        class="cyber-button text-sm">
                                    <i class="fas fa-edit mr-1"></i>Cambiar Estado
                                </button>
                                <form method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar este reporte?')">
                                    <input type="hidden" name="action" value="eliminar_reporte">
                                    <input type="hidden" name="dano_id" value="<?php echo $dano['id_dano']; ?>">
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
                    <i class="fas fa-exclamation-triangle text-4xl mb-4 block"></i>
                    <p>No hay reportes de daños</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div x-show="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" x-cloak>
        <div class="glass-card p-6 w-full max-w-md mx-4">
            <h3 class="text-xl font-bold text-orange-400 mb-4">Cambiar Estado del Reporte</h3>
            
            <form method="POST">
                <input type="hidden" name="action" value="cambiar_estado">
                <input type="hidden" name="dano_id" :value="selectedDano">
                
                <div class="mb-4">
                    <label class="text-white/60 text-sm">Nuevo Estado</label>
                    <select name="nuevo_estado" class="w-full p-3 bg-gray-800 border border-gray-600 rounded-lg text-white">
                        <option value="Pendiente">Pendiente</option>
                        <option value="En proceso">En proceso</option>
                        <option value="Resuelto">Resuelto</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="text-white/60 text-sm">Comentario (opcional)</label>
                    <textarea name="comentario_admin" rows="3" class="w-full p-3 bg-gray-800 border border-gray-600 rounded-lg text-white" 
                              placeholder="Agregar comentario sobre el estado del reporte..."></textarea>
                </div>
                
                <div class="flex gap-2">
                    <button type="submit" class="cyber-button flex-1">
                        <i class="fas fa-save mr-2"></i>Guardar
                    </button>
                    <button type="button" @click="showModal = false" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function abrirModalEstado(danoId, estadoActual) {
            Alpine.store('selectedDano', danoId);
            Alpine.store('showModal', true);
        }
    </script>
</body>
</html>
