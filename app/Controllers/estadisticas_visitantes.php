<?php
require_once __DIR__ . '/../Models/conexion.php';

class EstadisticasVisitantes {
    private $conexion;
    
    public function __construct() {
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Obtener estadísticas de visitantes
     */
    public function obtenerEstadisticas($usuario_id) {
        try {
            $estadisticas = [
                'hoy' => $this->contarVisitantesHoy($usuario_id),
                'esta_semana' => $this->contarVisitantesEstaSemana($usuario_id),
                'este_mes' => $this->contarVisitantesEsteMes($usuario_id)
            ];
            
            return [
                'success' => true,
                'data' => $estadisticas
            ];
            
        } catch (Exception $e) {
            error_log("Error en obtenerEstadisticas: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ];
        }
    }
    
    /**
     * Contar visitantes de hoy
     */
    private function contarVisitantesHoy($usuario_id) {
        // Usar la estructura real de la base de datos
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                WHERE DATE(r.fecha_generacion) = CURDATE() 
                AND p.usuario_id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                return $row['total'] ?? 0;
            }
        }
        
        // Si falla, usar consulta simplificada sin filtro de usuario
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                WHERE DATE(r.fecha_generacion) = CURDATE()";
        
        $result = $this->conexion->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        
        return 0;
    }
    
    /**
     * Contar visitantes de esta semana
     */
    private function contarVisitantesEstaSemana($usuario_id) {
        // Primero intentar con la estructura actual
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                WHERE YEARWEEK(r.fecha_generacion) = YEARWEEK(CURDATE()) 
                AND p.usuario_id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        
        // Si falla, intentar con estructura alternativa
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                WHERE YEARWEEK(r.fecha_generacion) = YEARWEEK(CURDATE())";
        
        $result = $this->conexion->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        
        return 0;
    }
    
    /**
     * Contar visitantes de este mes
     */
    private function contarVisitantesEsteMes($usuario_id) {
        // Primero intentar con la estructura actual
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                WHERE YEAR(r.fecha_generacion) = YEAR(CURDATE()) 
                AND MONTH(r.fecha_generacion) = MONTH(CURDATE()) 
                AND p.usuario_id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        
        // Si falla, intentar con estructura alternativa
        $sql = "SELECT COUNT(*) as total 
                FROM reservas r 
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit 
                WHERE YEAR(r.fecha_generacion) = YEAR(CURDATE()) 
                AND MONTH(r.fecha_generacion) = MONTH(CURDATE())";
        
        $result = $this->conexion->query($sql);
        if ($result) {
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        
        return 0;
    }
    
    /**
     * Obtener historial de visitantes
     */
    public function obtenerHistorialVisitantes($usuario_id, $limite = 10) {
        try {
            // Primero intentar con la estructura actual
            $sql = "SELECT 
                        r.id_reser,
                        v.nombre_visitante,
                        v.documento,
                        v.telefono,
                        r.motivo_visita,
                        r.fecha_inicial as fecha_generacion,
                        r.estado_qr,
                        r.observaciones,
                        r.codigo_qr
                    FROM visitantes v
                    INNER JOIN reservas r ON v.id_visit = r.visitante_id_visit
                    INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                    WHERE p.usuario_id = ?
                    ORDER BY r.fecha_inicial DESC
                    LIMIT ?";
            
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ii", $usuario_id, $limite);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $historial = [];
                    while ($row = $result->fetch_assoc()) {
                        $historial[] = [
                            'id_reser' => $row['id_reser'],
                            'nombre_visitante' => $row['nombre_visitante'],
                            'documento' => $row['documento'],
                            'telefono' => $row['telefono'],
                            'motivo_visita' => $row['motivo_visita'],
                            'fecha_inicial' => $row['fecha_generacion'],
                            'estado' => $row['estado_qr'],
                            'observaciones' => $row['observaciones'],
                            'codigo_qr' => $row['codigo_qr']
                        ];
                    }
                    return [
                        'success' => true,
                        'data' => $historial
                    ];
                }
            }
            
            // Si falla, intentar con estructura alternativa
            $sql = "SELECT 
                        v.nombre_visitante,
                        v.documento,
                        r.motivo_visita,
                        r.fecha_inicial as fecha_generacion,
                        r.estado_qr,
                        r.observaciones,
                        r.codigo_qr
                    FROM visitantes v
                    INNER JOIN reservas r ON v.id_visit = r.visitante_id_visit
                    ORDER BY r.fecha_inicial DESC
                    LIMIT ?";
            
            $stmt = $this->conexion->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $limite);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $historial = [];
                    while ($row = $result->fetch_assoc()) {
                        $historial[] = [
                            'id_reser' => $row['id_reser'],
                            'nombre_visitante' => $row['nombre_visitante'],
                            'documento' => $row['documento'],
                            'telefono' => $row['telefono'],
                            'motivo_visita' => $row['motivo_visita'],
                            'fecha_inicial' => $row['fecha_generacion'],
                            'estado' => $row['estado_qr'],
                            'observaciones' => $row['observaciones'],
                            'codigo_qr' => $row['codigo_qr']
                        ];
                    }
                    return [
                        'success' => true,
                        'data' => $historial
                    ];
                }
            }
            
            return [
                'success' => true,
                'data' => []
            ];
            
        } catch (Exception $e) {
            error_log("Error en obtenerHistorialVisitantes: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener historial'
            ];
        }
    }
    
    /**
     * Obtener visitantes frecuentes
     */
    public function obtenerVisitantesFrecuentes($usuario_id, $limite = 5) {
        try {
            $sql = "SELECT 
                        v.nombre_visitante,
                        v.documento,
                        COUNT(r.id_reser) as total_visitas,
                        MAX(r.fecha_generacion) as ultima_visita
                    FROM visitantes v
                    INNER JOIN reservas r ON v.id_visit = r.visitante_id_visit
                    INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                    WHERE p.usuario_id = ?
                    GROUP BY v.id_visit, v.nombre_visitante, v.documento
                    HAVING total_visitas > 1
                    ORDER BY total_visitas DESC
                    LIMIT ?";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("ii", $usuario_id, $limite);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $frecuentes = [];
            while ($row = $result->fetch_assoc()) {
                $frecuentes[] = [
                    'nombre_visitante' => $row['nombre_visitante'],
                    'documento' => $row['documento'],
                    'total_visitas' => $row['total_visitas'],
                    'ultima_visita' => $row['ultima_visita']
                ];
            }
            
            return [
                'success' => true,
                'data' => $frecuentes
            ];
            
        } catch (Exception $e) {
            error_log("Error en obtenerVisitantesFrecuentes: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener visitantes frecuentes'
            ];
        }
    }
    
    /**
     * Obtener pre-autorizaciones activas
     */
    public function obtenerPreautorizaciones($usuario_id) {
        try {
            $sql = "SELECT 
                        v.nombre_visitante,
                        v.documento,
                        r.motivo_visita,
                        r.fecha_generacion,
                        r.observaciones,
                        r.codigo_qr
                    FROM visitantes v
                    INNER JOIN reservas r ON v.id_visit = r.visitante_id_visit
                    INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                    WHERE p.usuario_id = ?
                    AND r.estado_qr = 'activo'
                    AND r.fecha_generacion >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    ORDER BY r.fecha_generacion DESC";
            
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $preautorizaciones = [];
            while ($row = $result->fetch_assoc()) {
                $preautorizaciones[] = [
                    'nombre_visitante' => $row['nombre_visitante'],
                    'documento' => $row['documento'],
                    'motivo_visita' => $row['motivo_visita'],
                    'fecha_autorizada' => $row['fecha_generacion'],
                    'observaciones' => $row['observaciones'],
                    'codigo_qr' => $row['codigo_qr']
                ];
            }
            
            return [
                'success' => true,
                'data' => $preautorizaciones
            ];
            
        } catch (Exception $e) {
            error_log("Error en obtenerPreautorizaciones: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al obtener pre-autorizaciones'
            ];
        }
    }
}
?>
