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
               p.torre, p.piso, p.apartamento,
               r.nombre_rol
        FROM usuarios u 
        JOIN usu_roles ur ON u.id = ur.usuarios_id 
        JOIN roles r ON ur.roles_idroles = r.idroles 
        LEFT JOIN propietarios p ON u.id = p.usuarios_id
        WHERE u.id = ? AND r.nombre_rol = 'propietario'";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $propietario_id);
$stmt->execute();
$result = $stmt->get_result();
$propietario = $result->fetch_assoc();

if(!$propietario) {
    header('Location: gestion_propietarios.php');
    exit();
}

// Obtener vehículos del propietario
$sql_vehiculos = "SELECT v.*, e.nombre as estado_nombre, p.numero as espacio_numero, p.nivel as espacio_nivel 
                  FROM vehiculos v 
                  LEFT JOIN estados e ON v.id_estado = e.id_estado 
                  LEFT JOIN espacios p ON v.id_espacio = p.id_espacio 
                  WHERE v.id_usuario = ?";
$stmt_vehiculos = $conexion->prepare($sql_vehiculos);
$stmt_vehiculos->bind_param("i", $propietario_id);
$stmt_vehiculos->execute();
$vehiculos = $stmt_vehiculos->get_result();

// Obtener visitas del propietario
$sql_visitas = "SELECT * FROM visitas WHERE id_usuario = ? ORDER BY fecha_visita DESC, hora_inicio DESC LIMIT 10";
$stmt_visitas = $conexion->prepare($sql_visitas);
$stmt_visitas->bind_param("i", $propietario_id);
$stmt_visitas->execute();
$visitas = $stmt_visitas->get_result();

// Obtener reportes de daños
$sql_danos = "SELECT d.*, v.placa 
              FROM danos d 
              JOIN vehiculos v ON d.id_vehiculo = v.id_vehiculo 
              WHERE d.id_usuario = ? 
              ORDER BY d.fecha_reporte DESC LIMIT 10";
$stmt_danos = $conexion->prepare($sql_danos);
$stmt_danos->bind_param("i", $propietario_id);
$stmt_danos->execute();
$danos = $stmt_danos->get_result();

// Obtener notificaciones (simuladas por ahora)
$notificaciones = [
    ['titulo' => 'Bienvenido al sistema', 'mensaje' => 'Su cuenta ha sido activada correctamente', 'fecha' => '2024-01-15', 'leida' => true],
    ['titulo' => 'Recordatorio de pago', 'mensaje' => 'Recuerde realizar el pago de administración', 'fecha' => '2024-01-10', 'leida' => false],
    ['titulo' => 'Mantenimiento programado', 'mensaje' => 'Se realizará mantenimiento en el parqueadero', 'fecha' => '2024-01-08', 'leida' => true]
];
?>

<!DOCTYPE html>
<html lang="es" x-data="{ activeTab: 'perfil' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Propietario | Quintanares by Parkovisco</title>
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
        
        .tab-button {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tab-button.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border-color: #10b981;
        }
        
        .tab-button:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
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
                        <i class="fas fa-user mr-2"></i>
                        Perfil del Propietario
                    </h1>
                    <p class="text-white/60"><?php echo htmlspecialchars($propietario['nombre'] . ' ' . $propietario['apellido']); ?></p>
                </div>
            </div>
            <div class="flex gap-2">
                <button onclick="editarPropietario(<?php echo $propietario['id']; ?>)" class="cyber-button">
                    <i class="fas fa-edit mr-2"></i>
                    Editar
                </button>
                <button onclick="eliminarPropietario(<?php echo $propietario['id']; ?>)" class="cyber-button" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-trash mr-2"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </header>

    <!-- Tabs Navigation -->
    <div class="mx-4 mb-6">
        <div class="flex gap-2 flex-wrap">
            <button @click="activeTab = 'perfil'" :class="activeTab === 'perfil' ? 'tab-button active' : 'tab-button'">
                <i class="fas fa-user mr-2"></i>Perfil
            </button>
            <button @click="activeTab = 'vehiculos'" :class="activeTab === 'vehiculos' ? 'tab-button active' : 'tab-button'">
                <i class="fas fa-car mr-2"></i>Vehículos
            </button>
            <button @click="activeTab = 'visitas'" :class="activeTab === 'visitas' ? 'tab-button active' : 'tab-button'">
                <i class="fas fa-calendar-alt mr-2"></i>Visitas
            </button>
            <button @click="activeTab = 'reportes'" :class="activeTab === 'reportes' ? 'tab-button active' : 'tab-button'">
                <i class="fas fa-exclamation-triangle mr-2"></i>Reportes
            </button>
            <button @click="activeTab = 'notificaciones'" :class="activeTab === 'notificaciones' ? 'tab-button active' : 'tab-button'">
                <i class="fas fa-bell mr-2"></i>Notificaciones
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="mx-4">
        <!-- Tab: Perfil -->
        <div x-show="activeTab === 'perfil'" class="space-y-6">
            <!-- Información Personal -->
            <div class="glass-card p-6">
                <h2 class="text-xl font-bold text-emerald-400 mb-4">
                    <i class="fas fa-user mr-2"></i>Información Personal
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="text-white/60 text-sm">Nombre Completo</label>
                        <p class="text-white font-semibold"><?php echo htmlspecialchars($propietario['nombre'] . ' ' . $propietario['apellido']); ?></p>
                    </div>
                    <div>
                        <label class="text-white/60 text-sm">ID</label>
                        <p class="text-white font-mono"><?php echo htmlspecialchars($propietario['id']); ?></p>
                    </div>
                    <div>
                        <label class="text-white/60 text-sm">Email</label>
                        <p class="text-white font-mono"><?php echo htmlspecialchars($propietario['email']); ?></p>
                    </div>
                    <div>
                        <label class="text-white/60 text-sm">Celular</label>
                        <p class="text-white font-mono"><?php echo htmlspecialchars($propietario['celular']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Información del Apartamento -->
            <div class="glass-card p-6">
                <h2 class="text-xl font-bold text-emerald-400 mb-4">
                    <i class="fas fa-home mr-2"></i>Información del Apartamento
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="text-white/60 text-sm">Torre</label>
                        <p class="text-white font-semibold"><?php echo htmlspecialchars($propietario['torre'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-white/60 text-sm">Piso</label>
                        <p class="text-white font-semibold"><?php echo htmlspecialchars($propietario['piso'] ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <label class="text-white/60 text-sm">Apartamento</label>
                        <p class="text-white font-semibold"><?php echo htmlspecialchars($propietario['apartamento'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Vehículos -->
        <div x-show="activeTab === 'vehiculos'" class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="text-xl font-bold text-emerald-400 mb-4">
                    <i class="fas fa-car mr-2"></i>Vehículos Registrados
                </h2>
                <?php if($vehiculos && $vehiculos->num_rows > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php while($vehiculo = $vehiculos->fetch_assoc()): ?>
                            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-emerald-400"><?php echo htmlspecialchars($vehiculo['placa']); ?></h3>
                                    <span class="px-2 py-1 rounded text-xs <?php echo $vehiculo['estado_nombre'] === 'Activo' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400'; ?>">
                                        <?php echo htmlspecialchars($vehiculo['estado_nombre'] ?? 'N/A'); ?>
                                    </span>
                                </div>
                                <p class="text-white/60 text-sm">Marca: <?php echo htmlspecialchars($vehiculo['marca'] ?? 'N/A'); ?></p>
                                <p class="text-white/60 text-sm">Modelo: <?php echo htmlspecialchars($vehiculo['modelo'] ?? 'N/A'); ?></p>
                                <?php if($vehiculo['espacio_numero']): ?>
                                    <p class="text-white/60 text-sm">Espacio: <?php echo htmlspecialchars($vehiculo['espacio_nivel'] . '-' . $vehiculo['espacio_numero']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center text-white/60 py-8">
                        <i class="fas fa-car text-4xl mb-4 block"></i>
                        <p>No hay vehículos registrados</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab: Visitas -->
        <div x-show="activeTab === 'visitas'" class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="text-xl font-bold text-emerald-400 mb-4">
                    <i class="fas fa-calendar-alt mr-2"></i>Visitas Programadas
                </h2>
                <?php if($visitas && $visitas->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($visita = $visitas->fetch_assoc()): ?>
                            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-white"><?php echo htmlspecialchars($visita['nombre_visitante']); ?></h3>
                                    <span class="px-2 py-1 rounded text-xs <?php echo $visita['estado'] === 'Aprobada' ? 'bg-green-500/20 text-green-400' : ($visita['estado'] === 'Pendiente' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400'); ?>">
                                        <?php echo htmlspecialchars($visita['estado']); ?>
                                    </span>
                                </div>
                                <p class="text-white/60 text-sm">Documento: <?php echo htmlspecialchars($visita['documento_visitante']); ?></p>
                                <p class="text-white/60 text-sm">Fecha: <?php echo date('d/m/Y', strtotime($visita['fecha_visita'])); ?></p>
                                <p class="text-white/60 text-sm">Hora: <?php echo htmlspecialchars($visita['hora_inicio'] . ' - ' . $visita['hora_fin']); ?></p>
                                <p class="text-white/60 text-sm">Motivo: <?php echo htmlspecialchars($visita['motivo']); ?></p>
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

        <!-- Tab: Reportes -->
        <div x-show="activeTab === 'reportes'" class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="text-xl font-bold text-emerald-400 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Reportes de Daños
                </h2>
                <?php if($danos && $danos->num_rows > 0): ?>
                    <div class="space-y-4">
                        <?php while($dano = $danos->fetch_assoc()): ?>
                            <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="font-semibold text-orange-400"><?php echo htmlspecialchars($dano['tipo_dano']); ?></h3>
                                    <span class="px-2 py-1 rounded text-xs <?php echo $dano['estado'] === 'Resuelto' ? 'bg-green-500/20 text-green-400' : ($dano['estado'] === 'En proceso' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400'); ?>">
                                        <?php echo htmlspecialchars($dano['estado']); ?>
                                    </span>
                                </div>
                                <p class="text-white/60 text-sm">Vehículo: <?php echo htmlspecialchars($dano['placa']); ?></p>
                                <p class="text-white/60 text-sm">Fecha: <?php echo date('d/m/Y', strtotime($dano['fecha_reporte'])); ?></p>
                                <p class="text-white/60 text-sm">Descripción: <?php echo htmlspecialchars($dano['descripcion']); ?></p>
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

        <!-- Tab: Notificaciones -->
        <div x-show="activeTab === 'notificaciones'" class="space-y-6">
            <div class="glass-card p-6">
                <h2 class="text-xl font-bold text-emerald-400 mb-4">
                    <i class="fas fa-bell mr-2"></i>Notificaciones
                </h2>
                <div class="space-y-4">
                    <?php foreach($notificaciones as $notif): ?>
                        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 <?php echo !$notif['leida'] ? 'border-emerald-500/50' : ''; ?>">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="font-semibold text-white"><?php echo htmlspecialchars($notif['titulo']); ?></h3>
                                <?php if(!$notif['leida']): ?>
                                    <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-white/60 text-sm"><?php echo htmlspecialchars($notif['mensaje']); ?></p>
                            <p class="text-white/40 text-xs mt-2"><?php echo date('d/m/Y', strtotime($notif['fecha'])); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editarPropietario(id) {
            window.location.href = `modificarusu.php?id=${id}`;
        }

        function eliminarPropietario(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este propietario?')) {
                // Implementar eliminación
                alert('Funcionalidad de eliminación pendiente de implementar');
            }
        }
    </script>
</body>
</html>
