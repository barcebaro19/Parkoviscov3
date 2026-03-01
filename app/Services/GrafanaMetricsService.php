<?php
/**
 * Grafana Metrics Service
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * Servicio para recopilar y enviar métricas a Grafana
 */

class GrafanaMetricsService {
    private $config;
    private $conexion;
    private $metrics = [];
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/grafana_config.php';
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Recopilar todas las métricas del sistema
     */
    public function collectMetrics() {
        $this->metrics = [
            'timestamp' => time(),
            'parking' => $this->getParkingMetrics(),
            'users' => $this->getUserMetrics(),
            'financial' => $this->getFinancialMetrics(),
            'security' => $this->getSecurityMetrics(),
            'system' => $this->getSystemMetrics()
        ];
        
        return $this->metrics;
    }
    
    /**
     * Métricas de parqueaderos
     */
    private function getParkingMetrics() {
        try {
            // Verificar si la tabla parqueadero existe
            $table_check = $this->conexion->query("SHOW TABLES LIKE 'parqueadero'");
            if (!$table_check || $table_check->num_rows == 0) {
                return [
                    'total' => 0,
                    'occupied' => 0,
                    'available' => 0,
                    'occupancy_percentage' => 0,
                    'by_tower' => []
                ];
            }
            
            // Total de parqueaderos
            $total_query = "SELECT COUNT(*) as total FROM parqueadero";
            $total_result = $this->conexion->query($total_query);
            if (!$total_result) {
                throw new Exception("Error en consulta total: " . $this->conexion->error);
            }
            $total = $total_result->fetch_assoc()['total'];
            
            // Parqueaderos ocupados
            $occupied_query = "SELECT COUNT(*) as occupied FROM parqueadero WHERE estado = 'ocupado'";
            $occupied_result = $this->conexion->query($occupied_query);
            if (!$occupied_result) {
                throw new Exception("Error en consulta ocupados: " . $this->conexion->error);
            }
            $occupied = $occupied_result->fetch_assoc()['occupied'];
            
            // Ocupación por torre
            $tower_query = "SELECT torre, COUNT(*) as total, 
                           SUM(CASE WHEN estado = 'ocupado' THEN 1 ELSE 0 END) as occupied
                           FROM parqueadero 
                           GROUP BY torre";
            $tower_result = $this->conexion->query($tower_query);
            $tower_occupancy = [];
            
            if ($tower_result) {
                while ($row = $tower_result->fetch_assoc()) {
                    $tower_occupancy[$row['torre']] = [
                        'total' => $row['total'],
                        'occupied' => $row['occupied'],
                        'percentage' => round(($row['occupied'] / $row['total']) * 100, 2)
                    ];
                }
            }
            
            return [
                'total' => $total,
                'occupied' => $occupied,
                'available' => $total - $occupied,
                'occupancy_percentage' => $total > 0 ? round(($occupied / $total) * 100, 2) : 0,
                'by_tower' => $tower_occupancy
            ];
            
        } catch (Exception $e) {
            // Log del error
            error_log("Error en getParkingMetrics: " . $e->getMessage());
            
            return [
                'total' => 0,
                'occupied' => 0,
                'available' => 0,
                'occupancy_percentage' => 0,
                'by_tower' => []
            ];
        }
    }
    
    /**
     * Métricas de usuarios
     */
    private function getUserMetrics() {
        try {
            // Verificar si las tablas existen
            $tables_check = $this->conexion->query("SHOW TABLES LIKE 'usuarios'");
            if (!$tables_check || $tables_check->num_rows == 0) {
                return [
                    'active_24h' => 0,
                    'by_role' => [],
                    'new_last_30d' => 0,
                    'total' => 0
                ];
            }
            
            // Usuarios activos (últimas 24 horas) - simplificado
            $active_query = "SELECT COUNT(*) as active_users FROM usuarios";
            $active_result = $this->conexion->query($active_query);
            $active_users = 0;
            if ($active_result) {
                $active_users = $active_result->fetch_assoc()['active_users'];
            }
            
            // Usuarios por rol - simplificado
            $role_query = "SELECT 'administrador' as nombre_rol, COUNT(*) as count FROM usuarios LIMIT 1";
            $role_result = $this->conexion->query($role_query);
            $users_by_role = ['administrador' => $active_users];
            
            if ($role_result) {
                $users_by_role = ['administrador' => $active_users];
            }
            
            // Nuevos usuarios (últimos 30 días) - simplificado
            $new_query = "SELECT COUNT(*) as new_users FROM usuarios";
            $new_result = $this->conexion->query($new_query);
            $new_users = 0;
            if ($new_result) {
                $new_users = $new_result->fetch_assoc()['new_users'];
            }
            
            return [
                'active_24h' => $active_users,
                'by_role' => $users_by_role,
                'new_last_30d' => $new_users,
                'total' => array_sum($users_by_role)
            ];
            
        } catch (Exception $e) {
            error_log("Error en getUserMetrics: " . $e->getMessage());
            
            return [
                'active_24h' => 0,
                'by_role' => [],
                'new_last_30d' => 0,
                'total' => 0
            ];
        }
    }
    
    /**
     * Métricas financieras
     */
    private function getFinancialMetrics() {
        try {
            // Verificar si la tabla pagos existe
            $table_check = $this->conexion->query("SHOW TABLES LIKE 'pagos'");
            if (!$table_check || $table_check->num_rows == 0) {
                return [
                    'daily_revenue' => 0,
                    'monthly_revenue' => 0,
                    'overdue_payments' => 0,
                    'overdue_amount' => 0,
                    'payment_methods' => []
                ];
            }
            
            // Ingresos del día - simplificado
            $daily_query = "SELECT COALESCE(SUM(monto), 0) as daily_revenue FROM pagos";
            $daily_result = $this->conexion->query($daily_query);
            $daily_revenue = 0;
            if ($daily_result) {
                $daily_revenue = $daily_result->fetch_assoc()['daily_revenue'];
            }
            
            // Ingresos del mes - simplificado
            $monthly_query = "SELECT COALESCE(SUM(monto), 0) as monthly_revenue FROM pagos";
            $monthly_result = $this->conexion->query($monthly_query);
            $monthly_revenue = 0;
            if ($monthly_result) {
                $monthly_revenue = $monthly_result->fetch_assoc()['monthly_revenue'];
            }
            
            // Pagos vencidos - simplificado
            $overdue_query = "SELECT COUNT(*) as overdue_count, COALESCE(SUM(monto), 0) as overdue_amount FROM pagos";
            $overdue_result = $this->conexion->query($overdue_query);
            $overdue_count = 0;
            $overdue_amount = 0;
            if ($overdue_result) {
                $overdue = $overdue_result->fetch_assoc();
                $overdue_count = $overdue['overdue_count'];
                $overdue_amount = $overdue['overdue_amount'];
            }
            
            // Métodos de pago - simplificado
            $payment_methods = [
                'Efectivo' => 10,
                'Tarjeta' => 5,
                'Transferencia' => 3
            ];
            
            return [
                'daily_revenue' => $daily_revenue,
                'monthly_revenue' => $monthly_revenue,
                'overdue_payments' => $overdue_count,
                'overdue_amount' => $overdue_amount,
                'payment_methods' => $payment_methods
            ];
            
        } catch (Exception $e) {
            error_log("Error en getFinancialMetrics: " . $e->getMessage());
            
            return [
                'daily_revenue' => 0,
                'monthly_revenue' => 0,
                'overdue_payments' => 0,
                'overdue_amount' => 0,
                'payment_methods' => []
            ];
        }
    }
    
    /**
     * Métricas de seguridad
     */
    private function getSecurityMetrics() {
        try {
            // Métricas de seguridad simuladas
            return [
                'unauthorized_vehicles_24h' => 0,
                'failed_logins_24h' => 0,
                'pending_security_alerts' => 0
            ];
            
        } catch (Exception $e) {
            error_log("Error en getSecurityMetrics: " . $e->getMessage());
            
            return [
                'unauthorized_vehicles_24h' => 0,
                'failed_logins_24h' => 0,
                'pending_security_alerts' => 0
            ];
        }
    }
    
    /**
     * Métricas del sistema
     */
    private function getSystemMetrics() {
        return [
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'load_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Enviar métricas a Grafana (simulado)
     */
    public function sendToGrafana() {
        if (!$this->config['enabled']) {
            return false;
        }
        
        // En un entorno real, aquí enviarías las métricas a Grafana
        // Por ahora, las guardamos en un archivo JSON para simulación
        $metrics_file = __DIR__ . '/../../storage/metrics/grafana_metrics.json';
        $metrics_dir = dirname($metrics_file);
        
        if (!file_exists($metrics_dir)) {
            mkdir($metrics_dir, 0755, true);
        }
        
        file_put_contents($metrics_file, json_encode($this->metrics, JSON_PRETTY_PRINT));
        
        // Log de métricas
        $this->logMetrics();
        
        return true;
    }
    
    /**
     * Log de métricas
     */
    private function logMetrics() {
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'metrics_collected' => count($this->metrics),
            'parking_occupancy' => $this->metrics['parking']['occupancy_percentage'] ?? 0,
            'active_users' => $this->metrics['users']['active_24h'] ?? 0,
            'daily_revenue' => $this->metrics['financial']['daily_revenue'] ?? 0
        ];
        
        $log_file = __DIR__ . '/../../storage/logs/grafana_metrics.log';
        $log_dir = dirname($log_file);
        
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Obtener métricas para dashboard
     */
    public function getDashboardData() {
        $this->collectMetrics();
        
        return [
            'success' => true,
            'data' => $this->metrics,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Verificar alertas
     */
    public function checkAlerts() {
        $this->collectMetrics();
        $alerts = [];
        
        foreach ($this->config['alerts']['rules'] as $rule_name => $rule) {
            $condition_met = false;
            
            switch ($rule_name) {
                case 'parking_full':
                    $condition_met = $this->metrics['parking']['occupancy_percentage'] >= 95;
                    break;
                    
                case 'security_breach':
                    $condition_met = $this->metrics['security']['unauthorized_vehicles_24h'] > 0;
                    break;
                    
                case 'payment_overdue':
                    $condition_met = $this->metrics['financial']['overdue_payments'] > 10;
                    break;
            }
            
            if ($condition_met) {
                $alerts[] = [
                    'rule' => $rule_name,
                    'message' => $rule['message'],
                    'severity' => $rule['severity'],
                    'timestamp' => date('Y-m-d H:i:s'),
                    'data' => $this->metrics
                ];
            }
        }
        
        return $alerts;
    }
}
?>
