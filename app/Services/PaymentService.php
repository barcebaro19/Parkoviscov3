<?php
/**
 * Servicio de Pagos Mejorado
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

require_once __DIR__ . '/../Controllers/pagos_controller.php';
require_once __DIR__ . '/../Controllers/wompi_integration.php';

class PaymentService {
    private $pagosController;
    private $wompiIntegration;
    private $conn;
    
    public function __construct() {
        $this->pagosController = new PagosController();
        $this->wompiIntegration = new WompiIntegration();
        require_once __DIR__ . '/../Models/conexion.php';
        $this->conn = Conexion::getInstancia()->getConexion();
    }
    
    /**
     * Obtener dashboard de pagos completo
     */
    public function obtenerDashboardPagos($usuario_id) {
        $pagosPendientes = $this->pagosController->obtenerPagosPendientes($usuario_id);
        $historialPagos = $this->pagosController->obtenerHistorialPagos($usuario_id, 5);
        $resumenPagos = $this->pagosController->obtenerResumenPagos($usuario_id);
        $metodosPago = $this->pagosController->obtenerMetodosPago($usuario_id);
        $conceptosPago = $this->pagosController->obtenerConceptosPago();
        
        return [
            'pagos_pendientes' => $pagosPendientes,
            'historial_pagos' => $historialPagos,
            'resumen' => $resumenPagos,
            'metodos_pago' => $metodosPago,
            'conceptos_pago' => $conceptosPago
        ];
    }
    
    /**
     * Procesar pago con Wompi
     */
    public function procesarPago($usuario_id, $concepto_id, $metodo_pago, $metodo_pago_id = null) {
        try {
            // Crear el pago
            $fecha_vencimiento = date('Y-m-d', strtotime('+30 days'));
            $resultado = $this->pagosController->crearPago(
                $usuario_id,
                $concepto_id,
                $metodo_pago,
                $fecha_vencimiento
            );
            
            if (!$resultado['success']) {
                return $resultado;
            }
            
            // Obtener información del pago creado
            $pago = $this->pagosController->obtenerPagoPorId($resultado['pago_id'], $usuario_id);
            
            // Crear transacción en Wompi
            $datosWompi = [
                'amount_in_cents' => intval($pago['monto'] * 100),
                'currency' => 'COP',
                'customer_email' => $this->obtenerEmailUsuario($usuario_id),
                'reference' => $pago['referencia_wompi'],
                'payment_method_type' => $this->mapearMetodoPago($metodo_pago),
                'redirect_url' => $this->obtenerRedirectUrl($resultado['pago_id'])
            ];
            
            // Si hay método de pago específico, agregar token
            if ($metodo_pago_id) {
                $metodo = $this->obtenerMetodoPagoPorId($metodo_pago_id, $usuario_id);
                if ($metodo && $metodo['token_wompi']) {
                    $datosWompi['payment_method'] = [
                        'type' => $metodo['tipo'],
                        'token' => $metodo['token_wompi']
                    ];
                }
            }
            
            $respuestaWompi = $this->wompiIntegration->crearTransaccion(
                $resultado['pago_id'],
                $pago['monto'],
                $pago['referencia_wompi'],
                $metodo_pago,
                ['email' => $this->obtenerEmailUsuario($usuario_id)]
            );
            
            if ($respuestaWompi['success']) {
                // Actualizar pago con transacción ID
                $this->pagosController->actualizarEstadoPago(
                    $resultado['pago_id'],
                    'procesando',
                    $respuestaWompi['data']['id']
                );
                
                // Registrar transacción Wompi
                $this->pagosController->registrarTransaccionWompi(
                    $resultado['pago_id'],
                    $pago['referencia_wompi'],
                    $respuestaWompi['data']['id'],
                    $respuestaWompi['data']['status'],
                    json_encode($respuestaWompi['data'])
                );
                
                return [
                    'success' => true,
                    'pago_id' => $resultado['pago_id'],
                    'transaccion_id' => $respuestaWompi['data']['id'],
                    'redirect_url' => $respuestaWompi['data']['redirect_url'] ?? null,
                    'message' => 'Pago procesado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al procesar pago: ' . $respuestaWompi['message']
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error procesando pago: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }
    
    /**
     * Agregar método de pago del usuario
     */
    public function agregarMetodoPago($usuario_id, $tipo, $datosTarjeta) {
        try {
            // Generar token simulado (en producción usarías la API de Wompi)
            $tokenWompi = 'tok_' . time() . '_' . rand(1000, 9999);
            
            // Guardar método de pago
            $resultado = $this->pagosController->agregarMetodoPago(
                $usuario_id,
                $tipo,
                '****' . substr($datosTarjeta['number'], -4),
                $datosTarjeta['holder_name'],
                $tokenWompi
            );
            
            if ($resultado) {
                return [
                    'success' => true,
                    'message' => 'Método de pago agregado exitosamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al guardar método de pago'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error agregando método de pago: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }
    
    /**
     * Generar recibo de pago
     */
    public function generarRecibo($pago_id, $usuario_id) {
        $pago = $this->pagosController->obtenerPagoPorId($pago_id, $usuario_id);
        
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        // Aquí puedes implementar la generación de PDF
        // Por ahora retornamos los datos del recibo
        return [
            'success' => true,
            'recibo' => [
                'numero' => $pago['referencia_wompi'],
                'fecha' => $pago['fecha_pago'] ?? $pago['fecha_creacion'],
                'concepto' => $pago['concepto_nombre'],
                'monto' => $pago['monto'],
                'estado' => $pago['estado'],
                'metodo_pago' => $pago['metodo_pago']
            ]
        ];
    }
    
    /**
     * Obtener email del usuario
     */
    private function obtenerEmailUsuario($usuario_id) {
        $sql = "SELECT email FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
            return $usuario['email'];
        }
        
        return null;
    }
    
    /**
     * Mapear método de pago a formato Wompi
     */
    private function mapearMetodoPago($metodo) {
        $mapeo = [
            'tarjeta' => 'CARD',
            'pse' => 'PSE',
            'nequi' => 'NEQUI',
            'daviplata' => 'DAVIPLATA'
        ];
        
        return $mapeo[$metodo] ?? 'CARD';
    }
    
    /**
     * Obtener URL de redirección
     */
    private function obtenerRedirectUrl($pago_id) {
        $baseUrl = 'http://localhost/ci4-parkovisko/public/confirmacion_pago.php';
        return $baseUrl . '?pago_id=' . $pago_id;
    }
    
    /**
     * Obtener método de pago por ID
     */
    private function obtenerMetodoPagoPorId($metodo_id, $usuario_id) {
        $sql = "SELECT * FROM metodos_pago_usuario WHERE id = ? AND usuario_id = ? AND activo = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $metodo_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Crear pagos automáticos mensuales
     */
    public function crearPagosAutomaticos($usuario_id) {
        $conceptos = $this->pagosController->obtenerConceptosPago();
        $mesActual = date('n');
        $añoActual = date('Y');
        
        $pagosCreados = [];
        
        foreach ($conceptos as $concepto) {
            if ($concepto['tipo'] === 'administracion') {
                $resultado = $this->pagosController->crearPagoAutomatico(
                    $usuario_id,
                    $concepto['id'],
                    $mesActual,
                    $añoActual
                );
                
                if ($resultado['success']) {
                    $pagosCreados[] = $concepto['nombre'];
                }
            }
        }
        
        return [
            'success' => true,
            'pagos_creados' => $pagosCreados,
            'message' => 'Pagos automáticos creados: ' . implode(', ', $pagosCreados)
        ];
    }
}
