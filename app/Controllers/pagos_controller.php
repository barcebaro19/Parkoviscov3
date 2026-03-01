<?php
require_once __DIR__ . '/../Models/conexion.php';

class PagosController {
    private $conexion;
    
    public function __construct() {
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    // Obtener pagos pendientes del usuario
    public function obtenerPagosPendientes($usuario_id) {
        $sql = "SELECT p.*, cp.nombre as concepto_nombre, cp.descripcion 
                FROM pagos p 
                INNER JOIN conceptos_pago cp ON p.concepto_id = cp.id 
                WHERE p.usuario_id = ? AND p.estado IN ('pendiente', 'procesando')
                ORDER BY p.fecha_vencimiento ASC";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Obtener historial de pagos del usuario
    public function obtenerHistorialPagos($usuario_id, $limite = 10) {
        $sql = "SELECT p.*, cp.nombre as concepto_nombre, cp.descripcion 
                FROM pagos p 
                INNER JOIN conceptos_pago cp ON p.concepto_id = cp.id 
                WHERE p.usuario_id = ? 
                ORDER BY p.fecha_creacion DESC 
                LIMIT ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Crear nuevo pago
    public function crearPago($usuario_id, $concepto_id, $metodo_pago, $fecha_vencimiento) {
        // Obtener información del concepto
        $concepto = $this->obtenerConceptoPago($concepto_id);
        if (!$concepto) {
            return ['success' => false, 'message' => 'Concepto de pago no encontrado'];
        }
        
        // Generar referencia única
        $referencia = 'QNT' . time() . rand(1000, 9999);
        
        $sql = "INSERT INTO pagos (usuario_id, concepto_id, monto, metodo_pago, referencia_wompi, fecha_vencimiento) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iidss", $usuario_id, $concepto_id, $concepto['monto'], $metodo_pago, $referencia, $fecha_vencimiento);
        
        if ($stmt->execute()) {
            $pago_id = $this->conexion->insert_id;
            return [
                'success' => true, 
                'pago_id' => $pago_id,
                'referencia' => $referencia,
                'monto' => $concepto['monto']
            ];
        }
        
        return ['success' => false, 'message' => 'Error al crear el pago'];
    }
    
    // Obtener concepto de pago
    private function obtenerConceptoPago($concepto_id) {
        $sql = "SELECT * FROM conceptos_pago WHERE id = ? AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $concepto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    // Obtener todos los conceptos de pago activos
    public function obtenerConceptosPago() {
        $sql = "SELECT * FROM conceptos_pago WHERE activo = 1 ORDER BY tipo, nombre";
        $result = $this->conexion->query($sql);
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Actualizar estado del pago
    public function actualizarEstadoPago($pago_id, $estado, $transaccion_id = null) {
        $sql = "UPDATE pagos SET estado = ?, transaccion_id = ?, fecha_pago = NOW() WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssi", $estado, $transaccion_id, $pago_id);
        
        return $stmt->execute();
    }
    
    // Registrar transacción Wompi
    public function registrarTransaccionWompi($pago_id, $referencia_wompi, $transaccion_id, $estado_wompi, $respuesta_wompi) {
        $sql = "INSERT INTO transacciones_wompi (pago_id, referencia_wompi, transaccion_id, estado_wompi, respuesta_wompi) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("issss", $pago_id, $referencia_wompi, $transaccion_id, $estado_wompi, $respuesta_wompi);
        
        return $stmt->execute();
    }
    
    // Obtener métodos de pago del usuario
    public function obtenerMetodosPago($usuario_id) {
        $sql = "SELECT * FROM metodos_pago_usuario WHERE usuario_id = ? AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Agregar método de pago
    public function agregarMetodoPago($usuario_id, $tipo, $numero_tarjeta, $nombre_titular, $token_wompi) {
        $sql = "INSERT INTO metodos_pago_usuario (usuario_id, tipo, numero_tarjeta, nombre_titular, token_wompi) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("issss", $usuario_id, $tipo, $numero_tarjeta, $nombre_titular, $token_wompi);
        
        return $stmt->execute();
    }
    
    // Eliminar método de pago
    public function eliminarMetodoPago($metodo_id, $usuario_id) {
        $sql = "UPDATE metodos_pago_usuario SET activo = 0 WHERE id = ? AND usuario_id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $metodo_id, $usuario_id);
        
        return $stmt->execute();
    }
    
    // Obtener resumen de pagos
    public function obtenerResumenPagos($usuario_id) {
        $sql = "SELECT 
                    SUM(CASE WHEN estado = 'aprobado' THEN monto ELSE 0 END) as total_pagado,
                    SUM(CASE WHEN estado IN ('pendiente', 'procesando') THEN monto ELSE 0 END) as total_pendiente,
                    SUM(monto) as total_general
                FROM pagos 
                WHERE usuario_id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $resumen = $result->fetch_assoc();
        return [
            'total_pagado' => floatval($resumen['total_pagado'] ?? 0),
            'total_pendiente' => floatval($resumen['total_pendiente'] ?? 0),
            'total_general' => floatval($resumen['total_general'] ?? 0)
        ];
    }
    
    // Obtener pago por ID
    public function obtenerPagoPorId($pago_id, $usuario_id) {
        $sql = "SELECT p.*, cp.nombre as concepto_nombre, cp.descripcion 
                FROM pagos p 
                INNER JOIN conceptos_pago cp ON p.concepto_id = cp.id 
                WHERE p.id = ? AND p.usuario_id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $pago_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    // Crear pago automático mensual
    public function crearPagoAutomatico($usuario_id, $concepto_id, $mes, $año) {
        $fecha_vencimiento = date('Y-m-d', strtotime("$año-$mes-15"));
        
        // Verificar si ya existe un pago para este mes/año
        $sql = "SELECT id FROM pagos 
                WHERE usuario_id = ? AND concepto_id = ? 
                AND MONTH(fecha_creacion) = ? AND YEAR(fecha_creacion) = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("iiii", $usuario_id, $concepto_id, $mes, $año);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return ['success' => false, 'message' => 'Ya existe un pago para este período'];
        }
        
        return $this->crearPago($usuario_id, $concepto_id, 'pendiente', $fecha_vencimiento);
    }
}
?>
