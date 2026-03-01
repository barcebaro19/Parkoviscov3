<?php
/**
 * Configuración del Sistema de Visitantes
 */

// Configuración de códigos QR
define('QR_DEFAULT_SIZE', 200);
define('QR_DEFAULT_VALIDITY_HOURS', 24);
define('QR_CODE_PREFIX', 'QR');

// Configuración de archivos
define('QR_IMAGES_DIR', __DIR__ . '/../../storage/qr_images/');
define('QR_LOGS_DIR', __DIR__ . '/../../storage/logs/');

// Configuración de base de datos
define('VISITANTES_TABLE', 'visitantes');
define('RESERVAS_TABLE', 'reservas');

// Estados de códigos QR
define('QR_STATUS_ACTIVE', 'activo');
define('QR_STATUS_USED', 'usado');
define('QR_STATUS_EXPIRED', 'expirado');

// Estados de visitantes
define('VISITANTE_STATUS_ACTIVE', 'activo');
define('VISITANTE_STATUS_INACTIVE', 'inactivo');

// Motivos de visita predefinidos
$MOTIVOS_VISITA = [
    'Visita familiar',
    'Visita social',
    'Técnico/Servicio',
    'Entrega',
    'Trabajo',
    'Otro'
];

// Configuración de notificaciones
define('NOTIFICATION_SUCCESS_DURATION', 3000); // 3 segundos
define('NOTIFICATION_ERROR_DURATION', 5000);   // 5 segundos

// Configuración de seguridad
define('QR_MAX_ATTEMPTS', 3); // Máximo intentos de validación
define('QR_LOCKOUT_TIME', 300); // 5 minutos de bloqueo

// Crear directorios si no existen
if (!is_dir(QR_IMAGES_DIR)) {
    mkdir(QR_IMAGES_DIR, 0755, true);
}

if (!is_dir(QR_LOGS_DIR)) {
    mkdir(QR_LOGS_DIR, 0755, true);
}

// Función para obtener configuración
function getVisitantesConfig($key = null) {
    global $MOTIVOS_VISITA;
    
    $config = [
        'qr_default_size' => QR_DEFAULT_SIZE,
        'qr_default_validity_hours' => QR_DEFAULT_VALIDITY_HOURS,
        'qr_code_prefix' => QR_CODE_PREFIX,
        'qr_images_dir' => QR_IMAGES_DIR,
        'qr_logs_dir' => QR_LOGS_DIR,
        'visitantes_table' => VISITANTES_TABLE,
        'reservas_table' => RESERVAS_TABLE,
        'qr_status_active' => QR_STATUS_ACTIVE,
        'qr_status_used' => QR_STATUS_USED,
        'qr_status_expired' => QR_STATUS_EXPIRED,
        'visitante_status_active' => VISITANTE_STATUS_ACTIVE,
        'visitante_status_inactive' => VISITANTE_STATUS_INACTIVE,
        'motivos_visita' => $MOTIVOS_VISITA,
        'notification_success_duration' => NOTIFICATION_SUCCESS_DURATION,
        'notification_error_duration' => NOTIFICATION_ERROR_DURATION,
        'qr_max_attempts' => QR_MAX_ATTEMPTS,
        'qr_lockout_time' => QR_LOCKOUT_TIME
    ];
    
    if ($key) {
        return isset($config[$key]) ? $config[$key] : null;
    }
    
    return $config;
}

// Función para log de eventos
function logVisitanteEvent($event, $data = []) {
    $log_file = QR_LOGS_DIR . 'visitantes_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] $event: " . json_encode($data) . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);
}
?>






