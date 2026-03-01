<?php
/**
 * Página de Preferencias de Usuario
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

session_start();
require_once '../app/Services/UserPreferencesService.php';
require_once '../app/Services/SessionManagerService.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$preferencesService = new UserPreferencesService();
$sessionManager = new SessionManagerService();

// Obtener preferencias actuales
$preferencias = $preferencesService->obtenerPreferencias($_SESSION['user_id']);

// Procesar actualización de preferencias
if ($_POST['action'] === 'update_preferences') {
    $nuevasPreferencias = [
        'notificaciones_email' => isset($_POST['notificaciones_email']) ? 1 : 0,
        'notificaciones_whatsapp' => isset($_POST['notificaciones_whatsapp']) ? 1 : 0,
        'notificaciones_push' => isset($_POST['notificaciones_push']) ? 1 : 0,
        'tema_oscuro' => isset($_POST['tema_oscuro']) ? 1 : 0,
        'idioma' => $_POST['idioma'] ?? 'es',
        'zona_horaria' => $_POST['zona_horaria'] ?? 'America/Bogota',
        'configuracion_notificaciones' => json_encode($_POST['config_notificaciones'] ?? []),
        'configuracion_privacidad' => json_encode($_POST['config_privacidad'] ?? [])
    ];
    
    if ($preferencesService->actualizarPreferencias($_SESSION['user_id'], $nuevasPreferencias)) {
        $mensaje = "Preferencias actualizadas exitosamente";
        $tipoMensaje = "success";
        $preferencias = $preferencesService->obtenerPreferencias($_SESSION['user_id']);
    } else {
        $mensaje = "Error al actualizar las preferencias";
        $tipoMensaje = "error";
    }
}

// Obtener sesiones activas
$sesionesActivas = $sessionManager->obtenerSesionesActivas($_SESSION['user_id']);
$estadisticasSesiones = $sessionManager->obtenerEstadisticasSesiones($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: <?php echo $preferencias['tema_oscuro'] ? 'true' : 'false'; ?> }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferencias | Quintanares by Parkovisco</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            <i class="fas fa-cog mr-2"></i>
                            Preferencias de Usuario
                        </h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Configura tus preferencias y configuraciones personales
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button onclick="history.back()" 
                                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensaje de estado -->
            <?php if (isset($mensaje)): ?>
                <div class="bg-<?php echo $tipoMensaje === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $tipoMensaje === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $tipoMensaje === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Preferencias de Notificaciones -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-bell mr-2"></i>
                        Notificaciones
                    </h2>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_preferences">
                        
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="font-medium">Notificaciones por Email</label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Recibir notificaciones importantes por correo electrónico</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notificaciones_email" 
                                           <?php echo $preferencias['notificaciones_email'] ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="font-medium">Notificaciones por WhatsApp</label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Recibir notificaciones por WhatsApp</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notificaciones_whatsapp" 
                                           <?php echo $preferencias['notificaciones_whatsapp'] ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="font-medium">Notificaciones Push</label>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Recibir notificaciones en el navegador</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notificaciones_push" 
                                           <?php echo $preferencias['notificaciones_push'] ? 'checked' : ''; ?>
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" 
                                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Guardar Preferencias
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Preferencias de Apariencia -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-palette mr-2"></i>
                        Apariencia
                    </h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="font-medium">Tema Oscuro</label>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Activar el tema oscuro</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="tema_oscuro" 
                                       <?php echo $preferencias['tema_oscuro'] ? 'checked' : ''; ?>
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                        
                        <div>
                            <label class="font-medium block mb-2">Idioma</label>
                            <select name="idioma" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                                <option value="es" <?php echo $preferencias['idioma'] === 'es' ? 'selected' : ''; ?>>Español</option>
                                <option value="en" <?php echo $preferencias['idioma'] === 'en' ? 'selected' : ''; ?>>English</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="font-medium block mb-2">Zona Horaria</label>
                            <select name="zona_horaria" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                                <option value="America/Bogota" <?php echo $preferencias['zona_horaria'] === 'America/Bogota' ? 'selected' : ''; ?>>Bogotá (GMT-5)</option>
                                <option value="America/New_York" <?php echo $preferencias['zona_horaria'] === 'America/New_York' ? 'selected' : ''; ?>>New York (GMT-5)</option>
                                <option value="Europe/Madrid" <?php echo $preferencias['zona_horaria'] === 'Europe/Madrid' ? 'selected' : ''; ?>>Madrid (GMT+1)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Sesiones Activas -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-desktop mr-2"></i>
                        Sesiones Activas
                    </h2>
                    
                    <div class="space-y-3">
                        <?php foreach ($sesionesActivas as $sesion): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <div class="font-medium">
                                        <?php echo $sesion['metodo_login'] === 'google' ? 'Google OAuth' : 'Login Manual'; ?>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        IP: <?php echo htmlspecialchars($sesion['ip_address']); ?>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">
                                        Última actividad: <?php echo date('d/m/Y H:i', strtotime($sesion['fecha_ultima_actividad'])); ?>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="w-3 h-3 bg-green-500 rounded-full"></span>
                                    <span class="text-sm text-green-600 dark:text-green-400">Activa</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-xl font-semibold mb-4 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Estadísticas de Sesiones
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                                <?php echo $estadisticasSesiones['sesiones_activas']; ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Activas</div>
                        </div>
                        
                        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                <?php echo $estadisticasSesiones['total_sesiones']; ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Total</div>
                        </div>
                        
                        <div class="text-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                <?php echo $estadisticasSesiones['sesiones_google']; ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Google</div>
                        </div>
                        
                        <div class="text-center p-4 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600 dark:text-orange-400">
                                <?php echo $estadisticasSesiones['sesiones_manual']; ?>
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Manual</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>



