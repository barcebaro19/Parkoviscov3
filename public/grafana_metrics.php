<?php
/**
 * Grafana Metrics Endpoint
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * Endpoint para obtener métricas del sistema para Grafana
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir dependencias
require_once __DIR__ . '/../app/Models/conexion.php';
require_once __DIR__ . '/../app/Services/GrafanaMetricsService.php';

try {
    $metricsService = new GrafanaMetricsService();
    
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Obtener métricas para dashboard
        $data = $metricsService->getDashboardData();
        
        // Enviar métricas a Grafana
        $metricsService->sendToGrafana();
        
        echo json_encode($data);
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar alertas
        $alerts = $metricsService->checkAlerts();
        
        if (!empty($alerts)) {
            // Enviar alertas
            foreach ($alerts as $alert) {
                // Log de alerta
                $log_entry = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'alert' => $alert,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ];
                
                $log_file = __DIR__ . '/../storage/logs/grafana_alerts.log';
                $log_dir = dirname($log_file);
                
                if (!file_exists($log_dir)) {
                    mkdir($log_dir, 0755, true);
                }
                
                file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
            }
        }
        
        echo json_encode([
            'success' => true,
            'alerts' => $alerts,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>










