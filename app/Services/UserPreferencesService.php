<?php
/**
 * Servicio de Preferencias de Usuario
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

class UserPreferencesService {
    private $conn;
    
    public function __construct() {
        require_once __DIR__ . '/../Models/conexion.php';
        $this->conn = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Obtener preferencias de un usuario
     */
    public function obtenerPreferencias($usuarioId) {
        $sql = "SELECT * FROM user_preferences WHERE usuario_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        // Si no existen preferencias, crear por defecto
        return $this->crearPreferenciasPorDefecto($usuarioId);
    }
    
    /**
     * Actualizar preferencias de usuario
     */
    public function actualizarPreferencias($usuarioId, $preferencias) {
        $sql = "UPDATE user_preferences SET 
                notificaciones_email = ?,
                notificaciones_whatsapp = ?,
                notificaciones_push = ?,
                tema_oscuro = ?,
                idioma = ?,
                zona_horaria = ?,
                configuracion_notificaciones = ?,
                configuracion_privacidad = ?,
                fecha_actualizacion = NOW()
                WHERE usuario_id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiisssssi",
            $preferencias['notificaciones_email'],
            $preferencias['notificaciones_whatsapp'],
            $preferencias['notificaciones_push'],
            $preferencias['tema_oscuro'],
            $preferencias['idioma'],
            $preferencias['zona_horaria'],
            $preferencias['configuracion_notificaciones'],
            $preferencias['configuracion_privacidad'],
            $usuarioId
        );
        
        return $stmt->execute();
    }
    
    /**
     * Crear preferencias por defecto
     */
    public function crearPreferenciasPorDefecto($usuarioId) {
        $sql = "INSERT INTO user_preferences (usuario_id) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        
        // Retornar las preferencias recién creadas
        return $this->obtenerPreferencias($usuarioId);
    }
    
    /**
     * Obtener configuración de notificaciones
     */
    public function obtenerConfiguracionNotificaciones($usuarioId) {
        $preferencias = $this->obtenerPreferencias($usuarioId);
        
        return [
            'email' => (bool)$preferencias['notificaciones_email'],
            'whatsapp' => (bool)$preferencias['notificaciones_whatsapp'],
            'push' => (bool)$preferencias['notificaciones_push']
        ];
    }
    
    /**
     * Verificar si el usuario quiere notificaciones por email
     */
    public function notificacionesEmailHabilitadas($usuarioId) {
        $preferencias = $this->obtenerPreferencias($usuarioId);
        return (bool)$preferencias['notificaciones_email'];
    }
    
    /**
     * Verificar si el usuario quiere notificaciones por WhatsApp
     */
    public function notificacionesWhatsAppHabilitadas($usuarioId) {
        $preferencias = $this->obtenerPreferencias($usuarioId);
        return (bool)$preferencias['notificaciones_whatsapp'];
    }
    
    /**
     * Obtener tema preferido del usuario
     */
    public function obtenerTema($usuarioId) {
        $preferencias = $this->obtenerPreferencias($usuarioId);
        return $preferencias['tema_oscuro'] ? 'dark' : 'light';
    }
    
    /**
     * Obtener idioma preferido del usuario
     */
    public function obtenerIdioma($usuarioId) {
        $preferencias = $this->obtenerPreferencias($usuarioId);
        return $preferencias['idioma'] ?? 'es';
    }
}


