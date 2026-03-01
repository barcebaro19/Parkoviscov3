<?php
require_once '../Models/conexion.php';

class EstadisticasVisitantesControllerCorrected {
    private $conexion;
    
    public function __construct() {
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Obtener historial de visitantes con ID de reserva
     */
    public function obtenerHistorialVisitantes($usuario_id, $limite = 10) {
        try {
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
            if (!$stmt) {
                throw new Exception('Error preparando consulta: ' . $this->conexion->error);
            }
            
            $stmt->bind_param("ii", $usuario_id, $limite);
            if (!$stmt->execute()) {
                throw new Exception('Error ejecutando consulta: ' . $stmt->error);
            }
            
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
            
        } catch (Exception $e) {
            error_log("Error en obtenerHistorialVisitantes: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>
