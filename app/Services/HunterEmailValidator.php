<?php
/**
 * Hunter.io Email Validator Service
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * Servicio para validar emails usando la API de Hunter.io
 */

class HunterEmailValidator {
    private $config;
    private $cache = [];
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/hunter_config.php';
    }
    
    /**
     * Validar email usando Hunter.io API
     */
    public function validateEmail($email) {
        // Verificar cache primero
        if ($this->config['cache_results']) {
            $cached = $this->getCachedResult($email);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        // Validar con Hunter.io
        $result = $this->validateWithHunter($email);
        
        // Cachear resultado si está habilitado
        if ($this->config['cache_results'] && $result) {
            $this->cacheResult($email, $result);
        }
        
        return $result;
    }
    
    /**
     * Validar email con Hunter.io API
     */
    private function validateWithHunter($email) {
        // Verificar si la API key está configurada
        if ($this->config['api_key'] === 'TU_API_KEY_AQUI') {
            $this->logRequest($email, 'API key not configured', 'error');
            return $this->getFallbackResult($email);
        }
        
        $url = $this->config['base_url'] . '?' . http_build_query([
            'email' => $email,
            'api_key' => $this->config['api_key']
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Quintanares-EmailValidator/1.0',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json'
            ]
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Log de la petición
        $this->logRequest($email, $response, $httpCode === 200 ? 'success' : 'error');
        
        if ($error) {
            return $this->getFallbackResult($email, "cURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            return $this->getFallbackResult($email, "HTTP Error: $httpCode");
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['data'])) {
            return $this->getFallbackResult($email, "Invalid API response");
        }
        
        return $this->formatHunterResult($data['data'], $email);
    }
    
    /**
     * Formatear resultado de Hunter.io
     */
    private function formatHunterResult($data, $email) {
        $result = [
            'email' => $email,
            'valido' => false,
            'formato_valido' => $data['regex'] ?? false,
            'dominio_valido' => $data['host_exists'] ?? false,
            'mx_record' => !empty($data['mx_records']),
            'disponible' => false,
            'mensaje' => '',
            'timestamp' => date('Y-m-d H:i:s'),
            'fuente' => 'hunter.io',
            'detalles' => [
                'result' => $data['result'] ?? 'unknown',
                'score' => $data['score'] ?? 0,
                'sources' => $data['sources'] ?? [],
                'gibberish' => $data['gibberish'] ?? false,
                'disposable' => $data['disposable'] ?? false,
                'webmail' => $data['webmail'] ?? false,
                'role_based' => $data['role_based'] ?? false,
                'free' => $data['free'] ?? false,
                'deliverable' => $data['deliverable'] ?? false,
                'full_inbox' => $data['full_inbox'] ?? false,
                'catch_all' => $data['catch_all'] ?? false,
                'common' => $data['common'] ?? false
            ]
        ];
        
        // Determinar si el email es válido
        switch ($data['result'] ?? 'unknown') {
            case 'deliverable':
                $result['valido'] = true;
                $result['disponible'] = true;
                $result['mensaje'] = 'Email válido y puede recibir mensajes';
                break;
                
            case 'undeliverable':
                $result['valido'] = false;
                $result['disponible'] = false;
                $result['mensaje'] = 'Email no válido o no puede recibir mensajes';
                break;
                
            case 'risky':
                $result['valido'] = false;
                $result['disponible'] = false;
                $result['mensaje'] = 'Email riesgoso (posiblemente temporal o spam)';
                break;
                
            case 'unknown':
            default:
                $result['valido'] = false;
                $result['disponible'] = false;
                $result['mensaje'] = 'No se pudo determinar la validez del email';
                break;
        }
        
        // Ajustar mensaje según detalles adicionales
        if ($data['disposable'] ?? false) {
            $result['mensaje'] .= ' (Email temporal detectado)';
        }
        
        if ($data['full_inbox'] ?? false) {
            $result['mensaje'] .= ' (Buzón lleno)';
        }
        
        if ($data['catch_all'] ?? false) {
            $result['mensaje'] .= ' (Dominio catch-all)';
        }
        
        return $result;
    }
    
    /**
     * Obtener resultado de fallback (validación local)
     */
    private function getFallbackResult($email, $error = null) {
        if (!$this->config['fallback_to_local']) {
            return [
                'email' => $email,
                'valido' => false,
                'formato_valido' => false,
                'dominio_valido' => false,
                'mx_record' => false,
                'disponible' => false,
                'mensaje' => 'Error en validación: ' . ($error ?? 'API no disponible'),
                'timestamp' => date('Y-m-d H:i:s'),
                'fuente' => 'fallback',
                'error' => $error
            ];
        }
        
        // Validación local como fallback
        $formato_valido = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        $dominio = substr(strrchr($email, "@"), 1);
        $mx_records = [];
        $mx_valid = getmxrr($dominio, $mx_records);
        
        return [
            'email' => $email,
            'valido' => $formato_valido && $mx_valid,
            'formato_valido' => $formato_valido,
            'dominio_valido' => !empty($dominio),
            'mx_record' => $mx_valid,
            'disponible' => $formato_valido && $mx_valid,
            'mensaje' => $formato_valido && $mx_valid ? 
                'Email válido (validación local)' : 
                'Email inválido (validación local)',
            'timestamp' => date('Y-m-d H:i:s'),
            'fuente' => 'local_fallback',
            'error' => $error
        ];
    }
    
    /**
     * Obtener resultado del cache
     */
    private function getCachedResult($email) {
        $cache_file = $this->getCacheFile($email);
        
        if (!file_exists($cache_file)) {
            return null;
        }
        
        $cached_data = json_decode(file_get_contents($cache_file), true);
        
        if (!$cached_data || !isset($cached_data['timestamp'])) {
            return null;
        }
        
        // Verificar si el cache ha expirado
        if (time() - $cached_data['timestamp'] > $this->config['cache_duration']) {
            unlink($cache_file);
            return null;
        }
        
        return $cached_data['result'];
    }
    
    /**
     * Cachear resultado
     */
    private function cacheResult($email, $result) {
        $cache_file = $this->getCacheFile($email);
        $cache_dir = dirname($cache_file);
        
        if (!file_exists($cache_dir)) {
            mkdir($cache_dir, 0755, true);
        }
        
        $cache_data = [
            'timestamp' => time(),
            'result' => $result
        ];
        
        file_put_contents($cache_file, json_encode($cache_data));
    }
    
    /**
     * Obtener archivo de cache
     */
    private function getCacheFile($email) {
        $cache_dir = __DIR__ . '/../../storage/cache/hunter';
        $email_hash = md5($email);
        return $cache_dir . '/' . $email_hash . '.json';
    }
    
    /**
     * Log de peticiones
     */
    private function logRequest($email, $response, $status) {
        if (!$this->config['log_requests']) {
            return;
        }
        
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'email' => $email,
            'status' => $status,
            'response' => $response,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $log_file = $this->config['log_file'];
        $log_dir = dirname($log_file);
        
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Obtener estadísticas de uso
     */
    public function getUsageStats() {
        if ($this->config['api_key'] === 'TU_API_KEY_AQUI') {
            return [
                'api_configured' => false,
                'message' => 'API key no configurada'
            ];
        }
        
        $url = 'https://api.hunter.io/v2/account?' . http_build_query([
            'api_key' => $this->config['api_key']
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return [
                'api_configured' => true,
                'data' => $data['data'] ?? []
            ];
        }
        
        return [
            'api_configured' => true,
            'error' => 'No se pudo obtener estadísticas'
        ];
    }
}
?>
