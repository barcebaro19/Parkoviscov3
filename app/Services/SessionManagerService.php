<?php
/**
 * Servicio de Gestión de Sesiones
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

class SessionManagerService {
    private $conn;
    
    public function __construct() {
        require_once __DIR__ . '/../Models/conexion.php';
        $this->conn = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Verificar si una sesión es válida
     */
    public function verificarSesion($sessionId) {
        $sql = "SELECT us.*, u.nombre, u.apellido, u.email, u.tipo_usuario, u.foto_url 
                FROM user_sessions us 
                INNER JOIN usuarios u ON us.usuario_id = u.id 
                WHERE us.session_id = ? AND us.activa = TRUE 
                AND (us.fecha_expiracion IS NULL OR us.fecha_expiracion > NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            
            // Actualizar última actividad
            $this->actualizarUltimaActividad($sessionId);
            
            return $session;
        }
        
        return false;
    }
    
    /**
     * Actualizar última actividad de una sesión
     */
    public function actualizarUltimaActividad($sessionId) {
        $sql = "UPDATE user_sessions SET fecha_ultima_actividad = NOW() WHERE session_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
    }
    
    /**
     * Cerrar sesión
     */
    public function cerrarSesion($sessionId) {
        $sql = "UPDATE user_sessions SET activa = FALSE WHERE session_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        
        // Registrar log de logout
        $this->registrarLogLogout($sessionId);
    }
    
    /**
     * Cerrar todas las sesiones de un usuario
     */
    public function cerrarTodasLasSesiones($usuarioId) {
        $sql = "UPDATE user_sessions SET activa = FALSE WHERE usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
    }
    
    /**
     * Obtener sesiones activas de un usuario
     */
    public function obtenerSesionesActivas($usuarioId) {
        $sql = "SELECT * FROM user_sessions 
                WHERE usuario_id = ? AND activa = TRUE 
                AND (fecha_expiracion IS NULL OR fecha_expiracion > NOW())
                ORDER BY fecha_ultima_actividad DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sesiones = [];
        while ($row = $result->fetch_assoc()) {
            $sesiones[] = $row;
        }
        
        return $sesiones;
    }
    
    /**
     * Limpiar sesiones expiradas
     */
    public function limpiarSesionesExpiradas() {
        $sql = "UPDATE user_sessions SET activa = FALSE 
                WHERE activa = TRUE 
                AND fecha_expiracion IS NOT NULL 
                AND fecha_expiracion < NOW()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        
        return $stmt->affected_rows;
    }
    
    /**
     * Registrar log de logout
     */
    private function registrarLogLogout($sessionId) {
        // Obtener datos de la sesión antes de cerrarla
        $sql = "SELECT usuario_id FROM user_sessions WHERE session_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $sessionId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $session = $result->fetch_assoc();
            
            // Registrar en auth_logs
            $sql = "INSERT INTO auth_logs (usuario_id, tipo_evento, metodo_auth, ip_address, user_agent) 
                    VALUES (?, 'logout', 'manual', ?, ?)";
            $stmt = $this->conn->prepare($sql);
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->bind_param("iss", $session['usuario_id'], $ip, $userAgent);
            $stmt->execute();
        }
    }
    
    /**
     * Obtener estadísticas de sesiones
     */
    public function obtenerEstadisticasSesiones($usuarioId = null) {
        $whereClause = $usuarioId ? "WHERE usuario_id = ?" : "";
        $params = $usuarioId ? [$usuarioId] : [];
        
        $sql = "SELECT 
                    COUNT(*) as total_sesiones,
                    COUNT(CASE WHEN activa = TRUE THEN 1 END) as sesiones_activas,
                    COUNT(CASE WHEN metodo_login = 'google' THEN 1 END) as sesiones_google,
                    COUNT(CASE WHEN metodo_login = 'manual' THEN 1 END) as sesiones_manual
                FROM user_sessions $whereClause";
        
        $stmt = $this->conn->prepare($sql);
        if ($usuarioId) {
            $stmt->bind_param("i", $usuarioId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
}


