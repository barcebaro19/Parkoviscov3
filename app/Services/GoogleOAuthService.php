<?php
/**
 * Servicio de Google OAuth
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

class GoogleOAuthService {
    private $config;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    
    public function __construct() {
        $this->config = require_once __DIR__ . '/../../config/google_config.php';
        $this->clientId = $this->config['client_id'];
        $this->clientSecret = $this->config['client_secret'];
        $this->redirectUri = $this->config['redirect_uri'];
    }
    
    /**
     * Generar URL de autorización de Google
     */
    public function getAuthUrl() {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $this->config['oauth']['scope'],
            'response_type' => 'code',
            'access_type' => $this->config['oauth']['access_type'],
            'prompt' => $this->config['oauth']['prompt'],
            'state' => $this->generateState()
        ];
        
        return $this->config['urls']['auth'] . '?' . http_build_query($params);
    }
    
    /**
     * Intercambiar código de autorización por token de acceso
     */
    public function getAccessToken($code) {
        $data = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'grant_type' => 'authorization_code',
            'code' => $code
        ];
        
        $response = $this->makeRequest($this->config['urls']['token'], $data);
        
        if (isset($response['access_token'])) {
            return $response;
        }
        
        throw new Exception('Error obteniendo token de acceso: ' . json_encode($response));
    }
    
    /**
     * Obtener información del usuario desde Google
     */
    public function getUserInfo($accessToken) {
        $url = $this->config['urls']['userinfo'] . '?access_token=' . $accessToken;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        throw new Exception('Error obteniendo información del usuario');
    }
    
    /**
     * Procesar login con Google
     */
    public function processGoogleLogin($code) {
        try {
            // Obtener token de acceso
            $tokenData = $this->getAccessToken($code);
            $accessToken = $tokenData['access_token'];
            
            // Obtener información del usuario
            $userInfo = $this->getUserInfo($accessToken);
            
            // Procesar datos del usuario
            $userData = [
                'google_id' => $userInfo['id'],
                'email' => $userInfo['email'],
                'nombre' => $userInfo['given_name'],
                'apellido' => $userInfo['family_name'],
                'foto' => $userInfo['picture'] ?? null,
                'verificado' => $userInfo['verified_email'] ?? false
            ];
            
            $this->log("Login exitoso con Google para: " . $userData['email']);
            
            return $userData;
            
        } catch (Exception $e) {
            $this->log("Error en login con Google: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Crear o actualizar usuario en la base de datos
     */
    public function createOrUpdateUser($userData) {
        require_once __DIR__ . '/../Models/conexion.php';
        
        $conn = Conexion::getInstancia()->getConexion();
        
        // Verificar si el usuario ya existe
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando consulta: " . $conn->error);
        }
        $stmt->bind_param("s", $userData['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Usuario existe, actualizar
            $user = $result->fetch_assoc();
            $sql = "UPDATE usuarios SET 
                    nombre = ?, 
                    apellido = ?,
                    ultimo_login = NOW()
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando consulta UPDATE: " . $conn->error);
            }
            $stmt->bind_param("ssi", 
                $userData['nombre'],
                $userData['apellido'],
                $user['id']
            );
            $stmt->execute();
            
            return $user['id'];
        } else {
            // Usuario nuevo, crear
            $sql = "INSERT INTO usuarios (google_id, email, nombre, apellido, foto_url, email_verificado, metodo_registro, tipo_usuario, estado, fecha_registro, ultimo_login, ip_ultimo_login) 
                    VALUES (?, ?, ?, ?, ?, ?, 'google', 'propietario', 'activo', NOW(), NOW(), ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando consulta INSERT: " . $conn->error);
            }
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->bind_param("sssssss", 
                $userData['google_id'],
                $userData['email'],
                $userData['nombre'],
                $userData['apellido'],
                $userData['foto'],
                $userData['verificado'] ? 1 : 0,
                $ip
            );
            $stmt->execute();
            
            $userId = $conn->insert_id;
            
            // Crear registro en propietarios para usuarios con tipo_usuario = 'propietario'
            if ($userData['tipo_usuario'] === 'propietario') {
                $this->crearPropietario($userId, $userData);
            }
            
            // Crear preferencias por defecto
            $this->crearPreferenciasPorDefecto($userId);
            
            return $userId;
        }
    }
    
    /**
     * Crear registro en propietarios
     */
    private function crearPropietario($userId, $userData) {
        require_once __DIR__ . '/../Models/conexion.php';
        
        $conn = Conexion::getInstancia()->getConexion();
        
        $sql = "INSERT INTO propietarios (usuario_id) VALUES (?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
    }
    
    /**
     * Crear preferencias por defecto para un usuario
     */
    private function crearPreferenciasPorDefecto($userId) {
        require_once __DIR__ . '/../Models/conexion.php';
        
        $conn = Conexion::getInstancia()->getConexion();
        
        $sql = "INSERT INTO user_preferences (usuario_id) VALUES (?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
        }
    }
    
    /**
     * Registrar log de autenticación
     */
    public function registrarLogAuth($usuarioId, $tipoEvento, $metodoAuth = 'google', $detalles = null) {
        require_once __DIR__ . '/../Models/conexion.php';
        
        $conn = Conexion::getInstancia()->getConexion();
        
        $sql = "INSERT INTO auth_logs (usuario_id, tipo_evento, metodo_auth, ip_address, user_agent, detalles) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $detallesJson = $detalles ? json_encode($detalles) : null;
            
            $stmt->bind_param("isssss", $usuarioId, $tipoEvento, $metodoAuth, $ip, $userAgent, $detallesJson);
            $stmt->execute();
        }
    }
    
    /**
     * Crear sesión de usuario
     */
    public function crearSesionUsuario($usuarioId, $sessionId, $metodoLogin = 'google') {
        require_once __DIR__ . '/../Models/conexion.php';
        
        $conn = Conexion::getInstancia()->getConexion();
        
        // Desactivar sesiones anteriores
        $sql = "UPDATE user_sessions SET activa = FALSE WHERE usuario_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
        }
        
        // Crear nueva sesión
        $sql = "INSERT INTO user_sessions (usuario_id, session_id, ip_address, user_agent, metodo_login, fecha_expiracion) 
                VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->bind_param("issss", $usuarioId, $sessionId, $ip, $userAgent, $metodoLogin);
            $stmt->execute();
        }
    }
    
    /**
     * Hacer petición HTTP
     */
    private function makeRequest($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        throw new Exception("Error en petición HTTP: {$httpCode}");
    }
    
    /**
     * Generar estado para CSRF protection
     */
    private function generateState() {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Log de mensajes
     */
    private function log($mensaje) {
        if ($this->config['logging']['enabled']) {
            $logFile = __DIR__ . '/../../' . $this->config['logging']['log_file'];
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] {$mensaje}" . PHP_EOL;
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Verificar configuración
     */
    public function verificarConfiguracion() {
        $errores = [];
        
        if (empty($this->clientId) || $this->clientId === 'TU_CLIENT_ID_AQUI') {
            $errores[] = 'Client ID no configurado';
        }
        
        if (empty($this->clientSecret) || $this->clientSecret === 'TU_CLIENT_SECRET_AQUI') {
            $errores[] = 'Client Secret no configurado';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'servicio' => 'Google OAuth'
        ];
    }
}
