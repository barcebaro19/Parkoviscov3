<?php
/**
 * Grafana Webhook Handler
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * Maneja alertas enviadas desde Grafana
 */

header('Content-Type: application/json');

// Incluir dependencias
require_once __DIR__ . '/../app/Models/conexion.php';
require_once __DIR__ . '/../app/Services/GrafanaMetricsService.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('No se recibieron datos del webhook');
    }
    
    // Procesar alerta
    $alert_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'webhook_data' => $input,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Log de webhook
    $log_file = __DIR__ . '/../storage/logs/grafana_webhook.log';
    $log_dir = dirname($log_file);
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($alert_data) . "\n", FILE_APPEND | LOCK_EX);
    
    // Procesar según el tipo de alerta
    if (isset($input['alerts'])) {
        foreach ($input['alerts'] as $alert) {
            $this->processAlert($alert);
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Webhook procesado correctamente',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

/**
 * Procesar alerta individual
 */
function processAlert($alert) {
    $conexion = Conexion::getInstancia()->getConexion();
    
    // Crear notificación en la base de datos
    $stmt = $conexion->prepare("
        INSERT INTO notificaciones (titulo, mensaje, tipo, prioridad, estado, fecha_creacion) 
        VALUES (?, ?, 'sistema', 'alta', 'pendiente', NOW())
    ");
    
    $titulo = $alert['labels']['alertname'] ?? 'Alerta del Sistema';
    $mensaje = $alert['annotations']['description'] ?? 'Alerta generada por Grafana';
    
    $stmt->bind_param("ss", $titulo, $mensaje);
    $stmt->execute();
    
    // Enviar email de alerta (opcional)
    if (isset($alert['status']) && $alert['status'] === 'firing') {
        sendAlertEmail($alert);
    }
}

/**
 * Enviar email de alerta
 */
function sendAlertEmail($alert) {
    // Aquí podrías integrar con SendGrid o PHPMailer
    // Por ahora, solo log
    $email_log = [
        'timestamp' => date('Y-m-d H:i:s'),
        'alert' => $alert,
        'action' => 'email_sent'
    ];
    
    $log_file = __DIR__ . '/../storage/logs/grafana_email_alerts.log';
    $log_dir = dirname($log_file);
    
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents($log_file, json_encode($email_log) . "\n", FILE_APPEND | LOCK_EX);
}
?>










