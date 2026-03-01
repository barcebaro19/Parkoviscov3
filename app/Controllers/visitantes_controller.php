<?php
require_once __DIR__ . '/../Models/conexion.php';
// WhatsApp Service removido

class VisitantesController {
    private $conn;
    public function __construct() {
        $this->conn = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Generar código QR para visitante
     */
    public function generarQRVisitante($datos) {
        try {
            // Iniciar transacción
            $this->conn->begin_transaction();
            
            // 1. Verificar si el visitante ya existe
            $visitante_id = $this->verificarOCrearVisitante($datos);
            
            // 2. Generar código QR único
            $codigo_qr = $this->generarCodigoQRUnico();
            
            // 3. Obtener datos del propietario desde la sesión
            $propietario_data = $this->obtenerDatosPropietario();
            
            // 4. Crear la reserva/visita
            $reserva_id = $this->crearReserva($visitante_id, $codigo_qr, $datos, $propietario_data);
            
            // 5. Generar datos del QR
            $qr_data = $this->generarDatosQR($reserva_id, $datos, $propietario_data);
            
            // Confirmar transacción
            $this->conn->commit();
            
            // 6. WhatsApp removido - solo generar QR
            
            return [
                'success' => true,
                'message' => 'Código QR generado exitosamente',
                'qr_data' => $qr_data,
                'qr_code' => $codigo_qr,
                'reserva_id' => $reserva_id
            ];
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $this->conn->rollback();
            
            error_log("Error al generar QR visitante: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error al generar el código QR: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar si el visitante existe, si no, crearlo
     */
    private function verificarOCrearVisitante($datos) {
        $documento = $datos['documento'];
        $nombre = $datos['nombre'];
        $telefono = $datos['telefono'] ?? null;
        
        // Buscar visitante por documento
        $sql = "SELECT id_visit FROM visitantes WHERE documento = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando consulta visitante: ' . $this->conn->error);
        }
        $stmt->bind_param("s", $documento);
        if (!$stmt->execute()) {
            throw new Exception('Error ejecutando consulta visitante: ' . $stmt->error);
        }
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Visitante existe, actualizar datos si es necesario
            $row = $result->fetch_assoc();
            $visitante_id = $row['id_visit'];
            
            // Actualizar datos del visitante
            $update_sql = "UPDATE visitantes SET 
                          nombre_visitante = ?, 
                          telefono = ?,
                          estado = 'activo'
                          WHERE id_visit = ?";
            $update_stmt = $this->conn->prepare($update_sql);
            $update_stmt->bind_param("ssi", $nombre, $telefono, $visitante_id);
            $update_stmt->execute();
            
        } else {
            // Crear nuevo visitante
            $insert_sql = "INSERT INTO visitantes (nombre_visitante, documento, telefono, estado) 
                          VALUES (?, ?, ?, 'activo')";
            $insert_stmt = $this->conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $nombre, $documento, $telefono);
            $insert_stmt->execute();
            
            $visitante_id = $this->conn->insert_id;
        }
        
        return $visitante_id;
    }
    
    /**
     * Generar código QR único
     */
    private function generarCodigoQRUnico() {
        do {
            // Generar código único: QR + timestamp + random
            $timestamp = time();
            $random = mt_rand(1000, 9999);
            $codigo_qr = "QR" . $timestamp . $random;
            
            // Verificar que no existe
            $sql = "SELECT id_reser FROM reservas WHERE codigo_qr = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $codigo_qr);
            $stmt->execute();
            $result = $stmt->get_result();
            
        } while ($result->num_rows > 0);
        
        return $codigo_qr;
    }
    
    /**
     * Obtener datos del propietario desde la sesión
     */
    private function obtenerDatosPropietario() {
        // Compatible con Google OAuth y login tradicional
        $usuario_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
        
        if (!$usuario_id) {
            throw new Exception('Usuario no autenticado');
        }
        
        // Obtener datos del propietario
        $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular,
                       p.torre, p.piso, p.apartamento
                FROM usuarios u
                INNER JOIN propietarios p ON u.id = p.usuario_id
                WHERE u.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error preparando consulta: ' . $this->conn->error);
        }
        
        $stmt->bind_param("i", $usuario_id);
        if (!$stmt->execute()) {
            throw new Exception('Error ejecutando consulta: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Propietario no encontrado');
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Crear reserva/visita
     */
    private function crearReserva($visitante_id, $codigo_qr, $datos, $propietario_data) {
        try {
            $fecha_inicial = date('Y-m-d H:i:s');
            $fecha_final = $datos['validez'];
            $motivo_visita = $datos['motivo'] ?? 'Visita familiar';
            $observaciones = $datos['observaciones'] ?? '';
            
            // Obtener ID del propietario
            $propietario_id = $this->obtenerPropietarioId($propietario_data['id']);
            
            // Obtener parqueadero para visitantes
            $parqueadero_id = $this->obtenerParqueaderoVisitante();
            
            $sql = "INSERT INTO reservas (
                        fecha_inicial, 
                        fecha_final, 
                        propietarios_id_pro, 
                        visitante_id_visit, 
                        parqueadero_id_parq,
                        motivo_visita,
                        codigo_qr,
                        estado_qr,
                        fecha_generacion,
                        observaciones
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', NOW(), ?)";
            
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error preparando consulta de reserva: ' . $this->conn->error);
            }
            
            $stmt->bind_param("ssiiisss", 
                $fecha_inicial, 
                $fecha_final, 
                $propietario_id, 
                $visitante_id, 
                $parqueadero_id,
                $motivo_visita,
                $codigo_qr,
                $observaciones
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Error ejecutando consulta de reserva: ' . $stmt->error);
            }
            
            $reserva_id = $this->conn->insert_id;
            
            // Log para debugging
            error_log("Reserva creada exitosamente - ID: $reserva_id, Visitante: $visitante_id, Propietario: $propietario_id, Parqueadero: $parqueadero_id");
            
            return $reserva_id;
            
        } catch (Exception $e) {
            error_log("Error creando reserva: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener ID del propietario en la tabla propietarios
     */
    private function obtenerPropietarioId($usuario_id) {
        try {
            error_log("Buscando propietario para usuario_id: $usuario_id");
            
            $sql = "SELECT id FROM propietarios WHERE usuario_id = ?";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Error preparando consulta propietario: ' . $this->conn->error);
            }
            
            $stmt->bind_param("i", $usuario_id);
            if (!$stmt->execute()) {
                throw new Exception('Error ejecutando consulta propietario: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $propietario_id = $row['id'];
                error_log("Propietario encontrado con ID: $propietario_id");
                return $propietario_id;
            } else {
                // Si no existe en propietarios, crear registro
                error_log("Propietario no encontrado, creando nuevo registro para usuario_id: $usuario_id");
                
                $insert_sql = "INSERT INTO propietarios (usuario_id) VALUES (?)";
                $insert_stmt = $this->conn->prepare($insert_sql);
                if (!$insert_stmt) {
                    throw new Exception('Error preparando inserción propietario: ' . $this->conn->error);
                }
                
                $insert_stmt->bind_param("i", $usuario_id);
                if (!$insert_stmt->execute()) {
                    throw new Exception('Error ejecutando inserción propietario: ' . $insert_stmt->error);
                }
                
                $propietario_id = $this->conn->insert_id;
                error_log("Nuevo propietario creado con ID: $propietario_id");
                return $propietario_id;
            }
        } catch (Exception $e) {
            error_log("Error en obtenerPropietarioId: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Obtener parqueadero para visitantes
     */
    private function obtenerParqueaderoVisitante() {
        // Primero intentar buscar un parqueadero para visitantes
        $sql = "SELECT id_parq FROM parqueadero WHERE tipo_de_vehiculo = 'visitante' AND disponibilidad = 'disponible' LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['id_parq'];
            }
        }
        
        // Si no hay parqueadero para visitantes, buscar cualquier parqueadero disponible
        $sql = "SELECT id_parq FROM parqueadero WHERE disponibilidad = 'disponible' LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                return $row['id_parq'];
            }
        }
        
        // Si no hay ningún parqueadero disponible, usar el ID 1 por defecto
        return 1;
    }
    
    /**
     * Generar datos del QR para mostrar al usuario
     */
    private function generarDatosQR($reserva_id, $datos, $propietario_data) {
        return [
            'id' => $reserva_id,
            'codigo' => $this->generarCodigoQRUnico(),
            'tipo' => 'visitante',
            'apartamento' => $propietario_data['apartamento'] ?? 'N/A',
            'torre' => $propietario_data['torre'] ?? 'N/A',
            'propietario' => $propietario_data['nombre'] . ' ' . $propietario_data['apellido'],
            'visitante_nombre' => $datos['nombre'],
            'visitante_documento' => $datos['documento'],
            'motivo' => $datos['motivo'] ?? 'Visita familiar',
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'valido_hasta' => $datos['validez'],
            'estado' => 'activo'
        ];
    }
    
    /**
     * Obtener historial de visitantes de un propietario
     */
    public function obtenerHistorialVisitantes($propietario_id) {
        $sql = "SELECT r.*, v.nombre_visitante, v.documento, v.telefono,
                       u.nombre as propietario_nombre, u.apellido as propietario_apellido
                FROM reservas r
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE r.propietarios_id_pro = ?
                ORDER BY r.fecha_generacion DESC
                LIMIT 50";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $propietario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $visitantes = [];
        while ($row = $result->fetch_assoc()) {
            $visitantes[] = $row;
        }
        
        return $visitantes;
    }
    
    /**
     * Validar código QR
     */
    public function validarCodigoQR($codigo_qr) {
        $sql = "SELECT r.*, v.nombre_visitante, v.documento,
                       u.nombre as propietario_nombre, u.apellido as propietario_apellido
                FROM reservas r
                INNER JOIN visitantes v ON r.visitante_id_visit = v.id_visit
                INNER JOIN propietarios p ON r.propietarios_id_pro = p.id
                INNER JOIN usuarios u ON p.usuario_id = u.id
                WHERE r.codigo_qr = ? AND r.estado_qr = 'activo'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $codigo_qr);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            // Verificar si no ha expirado
            $fecha_final = new DateTime($row['fecha_final']);
            $ahora = new DateTime();
            
            if ($ahora > $fecha_final) {
                // Marcar como expirado
                $this->marcarQRExpirado($codigo_qr);
                return ['valido' => false, 'mensaje' => 'El código QR ha expirado'];
            }
            
            return ['valido' => true, 'datos' => $row];
        }
        
        return ['valido' => false, 'mensaje' => 'Código QR no válido'];
    }
    
    /**
     * Marcar código QR como usado o expirado
     */
    public function marcarQRExpirado($codigo_qr) {
        $sql = "UPDATE reservas SET estado_qr = 'expirado' WHERE codigo_qr = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $codigo_qr);
        return $stmt->execute();
    }
    
    public function marcarQRUsado($codigo_qr) {
        $sql = "UPDATE reservas SET estado_qr = 'usado' WHERE codigo_qr = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $codigo_qr);
        return $stmt->execute();
    }
    
    // Métodos de WhatsApp removidos
    
    /**
     * Generar imagen QR para WhatsApp
     */
    private function generarImagenQR($codigo_qr) {
        try {
            // Crear directorio si no existe
            $qrDir = __DIR__ . '/../../storage/qr_images/';
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0755, true);
            }
            
            // Generar imagen QR usando API online
            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($codigo_qr) . "&bgcolor=FFFFFF&color=10B981";
            $imagePath = $qrDir . 'qr_' . $codigo_qr . '.png';
            
            // Descargar imagen
            $imageData = file_get_contents($qrUrl);
            if ($imageData) {
                file_put_contents($imagePath, $imageData);
                return $imagePath;
            }
            
            return null;
            
        } catch (Exception $e) {
            error_log("Error generando imagen QR: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Formatear fecha para WhatsApp
     */
    private function formatearFecha($fecha) {
        $fechaObj = new DateTime($fecha);
        return $fechaObj->format('d/m/Y');
    }
    
    /**
     * Formatear hora para WhatsApp
     */
    private function formatearHora($fecha) {
        $fechaObj = new DateTime($fecha);
        return $fechaObj->format('H:i');
    }
    
    // Métodos de WhatsApp eliminados
}
?>
