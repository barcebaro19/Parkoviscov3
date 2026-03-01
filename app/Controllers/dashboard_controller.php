<?php
/**
 * Controlador del Dashboard
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

require_once __DIR__ . '/../Models/conexion.php';

class DashboardController {
    private $conn;
    
    public function __construct() {
        $this->conn = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Obtener datos del dashboard para un usuario
     */
    public function obtenerDatosDashboard($usuario_id) {
        try {
            // Obtener datos del usuario
            $usuario = $this->obtenerDatosUsuario($usuario_id);
            
            // Obtener estadísticas
            $estadisticas = $this->obtenerEstadisticas($usuario_id);
            
            // Obtener notificaciones
            $notificaciones = $this->obtenerNotificaciones($usuario_id);
            
            // Obtener visitantes recientes
            $visitantesRecientes = $this->obtenerVisitantesRecientes($usuario_id);
            
            // Obtener pagos pendientes
            $pagosPendientes = $this->obtenerPagosPendientes($usuario_id);
            
            return [
                'success' => true,
                'data' => [
                    'usuario' => $usuario,
                    'estadisticas' => $estadisticas,
                    'notificaciones' => $notificaciones,
                    'visitantes_recientes' => $visitantesRecientes,
                    'pagos_pendientes' => $pagosPendientes
                ]
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo datos del dashboard: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error obteniendo datos del dashboard'
            ];
        }
    }
    
    /**
     * Obtener datos del usuario
     */
    private function obtenerDatosUsuario($usuario_id) {
        $sql = "SELECT id, nombre, apellido, email, celular, foto_url, ultimo_login 
                FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta de usuario: " . $this->conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            
            // Formatear nombre completo
            $usuario['nombre_completo'] = trim($usuario['nombre'] . ' ' . $usuario['apellido']);
            
            // Formatear último acceso
            if ($usuario['ultimo_login']) {
                $usuario['ultimo_acceso'] = $this->formatearFecha($usuario['ultimo_login']);
            } else {
                $usuario['ultimo_acceso'] = 'Primera vez';
            }
            
            return $usuario;
        }
        
        return null;
    }
    
    /**
     * Obtener estadísticas del usuario
     */
    private function obtenerEstadisticas($usuario_id) {
        $estadisticas = [
            'vehiculos' => 0,
            'pagos_pendientes' => 0,
            'notificaciones' => 0,
            'visitantes_mes' => 0
        ];
        
        // Contar vehículos
        $sql = "SELECT COUNT(*) as total FROM vehiculo WHERE propietario_id = ?";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $estadisticas['vehiculos'] = $result->fetch_assoc()['total'];
        }
        
        // Contar pagos pendientes
        $sql = "SELECT COUNT(*) as total FROM pagos WHERE usuario_id = ? AND estado = 'pendiente'";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $estadisticas['pagos_pendientes'] = $result->fetch_assoc()['total'];
        }
        
        // Contar notificaciones (simulado)
        $estadisticas['notificaciones'] = 3; // Por ahora fijo
        
        // Contar visitantes este mes
        $sql = "SELECT COUNT(*) as total FROM visitantes v 
                INNER JOIN reservas r ON v.id_visit = r.visitante_id_visit 
                WHERE r.propietarios_id_pro = ? AND MONTH(r.fecha_inicial) = MONTH(NOW())";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $estadisticas['visitantes_mes'] = $result->fetch_assoc()['total'];
        }
        
        return $estadisticas;
    }
    
    /**
     * Obtener notificaciones
     */
    private function obtenerNotificaciones($usuario_id) {
        // Por ahora retornamos notificaciones simuladas
        // En el futuro se pueden obtener de una tabla de notificaciones
        return [
            [
                'tipo' => 'info',
                'titulo' => 'Mantenimiento programado',
                'descripcion' => 'Ascensores - 20 Feb, 9:00 AM',
                'icono' => 'fas fa-info',
                'color' => 'blue'
            ],
            [
                'tipo' => 'success',
                'titulo' => 'Pago confirmado',
                'descripcion' => 'Administración Enero 2025',
                'icono' => 'fas fa-check',
                'color' => 'emerald'
            ],
            [
                'tipo' => 'warning',
                'titulo' => 'Recordatorio de pago',
                'descripcion' => 'Administración Febrero vence pronto',
                'icono' => 'fas fa-calendar',
                'color' => 'orange'
            ]
        ];
    }
    
    /**
     * Obtener visitantes recientes
     */
    private function obtenerVisitantesRecientes($usuario_id) {
        $sql = "SELECT v.nombre_visitante, v.telefono, r.fecha_inicial, r.motivo_visita
                FROM visitantes v 
                INNER JOIN reservas r ON v.id_visit = r.visitante_id_visit 
                WHERE r.propietarios_id_pro = ? 
                ORDER BY r.fecha_inicial DESC 
                LIMIT 5";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta de visitantes: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $visitantes = [];
        while ($row = $result->fetch_assoc()) {
            $visitantes[] = [
                'nombre' => $row['nombre_visitante'],
                'telefono' => $row['telefono'],
                'fecha' => $this->formatearFecha($row['fecha_inicial']),
                'motivo' => $row['motivo_visita']
            ];
        }
        
        return $visitantes;
    }
    
    /**
     * Obtener pagos pendientes
     */
    private function obtenerPagosPendientes($usuario_id) {
        $sql = "SELECT p.id, p.monto, p.fecha_vencimiento, cp.nombre as concepto_nombre
                FROM pagos p 
                INNER JOIN conceptos_pago cp ON p.concepto_id = cp.id 
                WHERE p.usuario_id = ? AND p.estado = 'pendiente' 
                ORDER BY p.fecha_vencimiento ASC 
                LIMIT 3";
        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta de pagos: " . $this->conn->error);
            return [];
        }
        
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $pagos = [];
        while ($row = $result->fetch_assoc()) {
            $pagos[] = [
                'id' => $row['id'],
                'concepto' => $row['concepto_nombre'],
                'monto' => number_format($row['monto'], 0, ',', '.'),
                'fecha_vencimiento' => $this->formatearFecha($row['fecha_vencimiento'])
            ];
        }
        
        return $pagos;
    }
    
    /**
     * Formatear fecha
     */
    private function formatearFecha($fecha) {
        if (!$fecha) return 'N/A';
        
        $fechaObj = new DateTime($fecha);
        return $fechaObj->format('d M Y, H:i');
    }
}
?>


