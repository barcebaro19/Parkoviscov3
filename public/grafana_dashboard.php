<?php
/**
 * Grafana Dashboard Personalizado
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * Dashboard web personalizado que simula Grafana
 */

session_start();
require_once __DIR__ . "/../app/Models/conexion.php";
require_once __DIR__ . "/../app/Services/GrafanaMetricsService.php";

// Verificar que el usuario sea administrador
if(!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

// Obtener métricas
$metricsService = new GrafanaMetricsService();
$metrics = $metricsService->getDashboardData();
$alerts = $metricsService->checkAlerts();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Grafana | Quintanares Residencial</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .cyber-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .metric-card { background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(147, 51, 234, 0.1)); }
        .alert-critical { background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(220, 38, 38, 0.2)); }
        .alert-warning { background: linear-gradient(135deg, rgba(245, 158, 11, 0.2), rgba(217, 119, 6, 0.2)); }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 min-h-screen">
    <!-- Header -->
    <div class="cyber-card rounded-2xl p-6 mb-6 mx-4 mt-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    <i class="fas fa-chart-line text-emerald-400 mr-3"></i>
                    Dashboard Grafana - Quintanares
                </h1>
                <p class="text-gray-300">Monitoreo en tiempo real del sistema de parqueaderos</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="text-white font-semibold"><?php echo $_SESSION['nombre'] ?? 'Admin'; ?></p>
                    <p class="text-gray-400 text-sm">Administrador</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-white text-lg"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas -->
    <?php if (!empty($alerts)): ?>
    <div class="mx-4 mb-6">
        <div class="cyber-card rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
                Alertas Activas
            </h2>
            <div class="space-y-3">
                <?php foreach ($alerts as $alert): ?>
                <div class="p-4 rounded-lg <?php echo $alert['severity'] === 'critical' ? 'alert-critical' : 'alert-warning'; ?> border border-red-500/30">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-white font-semibold"><?php echo htmlspecialchars($alert['message']); ?></h3>
                            <p class="text-gray-300 text-sm"><?php echo htmlspecialchars($alert['timestamp']); ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold <?php echo $alert['severity'] === 'critical' ? 'bg-red-600 text-white' : 'bg-yellow-600 text-white'; ?>">
                                <?php echo ucfirst($alert['severity']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Métricas Principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mx-4 mb-6">
        <!-- Ocupación de Parqueaderos -->
        <div class="cyber-card metric-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-white font-semibold">Ocupación</h3>
                    <p class="text-gray-400 text-sm">Parqueaderos</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-parking text-white text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-white mb-2">
                <?php echo $metrics['data']['parking']['occupancy_percentage']; ?>%
            </div>
            <div class="text-sm text-gray-400">
                <?php echo $metrics['data']['parking']['occupied']; ?> / <?php echo $metrics['data']['parking']['total']; ?> ocupados
            </div>
        </div>

        <!-- Ingresos Diarios -->
        <div class="cyber-card metric-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-white font-semibold">Ingresos</h3>
                    <p class="text-gray-400 text-sm">Hoy</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-white text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-white mb-2">
                $<?php echo number_format($metrics['data']['financial']['daily_revenue']); ?>
            </div>
            <div class="text-sm text-gray-400">
                Mensual: $<?php echo number_format($metrics['data']['financial']['monthly_revenue']); ?>
            </div>
        </div>

        <!-- Usuarios Activos -->
        <div class="cyber-card metric-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-white font-semibold">Usuarios</h3>
                    <p class="text-gray-400 text-sm">Activos (24h)</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-white mb-2">
                <?php echo $metrics['data']['users']['active_24h']; ?>
            </div>
            <div class="text-sm text-gray-400">
                Total: <?php echo $metrics['data']['users']['total']; ?> usuarios
            </div>
        </div>

        <!-- Alertas de Seguridad -->
        <div class="cyber-card metric-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-white font-semibold">Seguridad</h3>
                    <p class="text-gray-400 text-sm">Alertas (24h)</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-lg"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-white mb-2">
                <?php echo $metrics['data']['security']['unauthorized_vehicles_24h']; ?>
            </div>
            <div class="text-sm text-gray-400">
                Vehículos no autorizados
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mx-4 mb-6">
        <!-- Ocupación por Torre -->
        <div class="cyber-card rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4">Ocupación por Torre</h3>
            <canvas id="towerChart" width="400" height="200"></canvas>
        </div>

        <!-- Métodos de Pago -->
        <div class="cyber-card rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4">Métodos de Pago (30 días)</h3>
            <canvas id="paymentChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Tabla de Usuarios por Rol -->
    <div class="mx-4 mb-6">
        <div class="cyber-card rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4">Usuarios por Rol</h3>
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="text-gray-400">
                            <th>Rol</th>
                            <th>Usuarios</th>
                            <th>Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metrics['data']['users']['by_role'] as $role => $count): ?>
                        <tr class="text-white">
                            <td class="capitalize"><?php echo htmlspecialchars($role); ?></td>
                            <td><?php echo $count; ?></td>
                            <td><?php echo round(($count / $metrics['data']['users']['total']) * 100, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="mx-4 mb-6">
        <div class="cyber-card rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div class="text-gray-400 text-sm">
                    Última actualización: <?php echo $metrics['timestamp']; ?>
                </div>
                <div class="flex items-center gap-4">
                    <button onclick="refreshDashboard()" class="cyber-button">
                        <i class="fas fa-sync-alt"></i>
                        Actualizar
                    </button>
                    <a href="Administrador1.php" class="cyber-button secondary">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Datos para gráficos
        const towerData = <?php echo json_encode($metrics['data']['parking']['by_tower']); ?>;
        const paymentData = <?php echo json_encode($metrics['data']['financial']['payment_methods']); ?>;

        // Gráfico de Ocupación por Torre
        const towerCtx = document.getElementById('towerChart').getContext('2d');
        new Chart(towerCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(towerData),
                datasets: [{
                    label: 'Ocupación %',
                    data: Object.values(towerData).map(tower => tower.percentage),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'white'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        }
                    }
                }
            }
        });

        // Gráfico de Métodos de Pago
        const paymentCtx = document.getElementById('paymentChart').getContext('2d');
        new Chart(paymentCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(paymentData),
                datasets: [{
                    data: Object.values(paymentData),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(147, 51, 234, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    }
                }
            }
        });

        // Función para actualizar dashboard
        function refreshDashboard() {
            location.reload();
        }

        // Auto-refresh cada 30 segundos
        setInterval(refreshDashboard, 30000);
    </script>
</body>
</html>










