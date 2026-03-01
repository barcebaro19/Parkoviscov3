<?php
require_once __DIR__ . '/pagos_controller.php';

class WompiIntegration {
    private $public_key;
    private $private_key;
    private $base_url;
    private $pagosController;
    
    public function __construct() {
        // Cargar configuración de Wompi
        $config = require __DIR__ . '/../../config/wompi_config.php';
        
        $environment = $config['environment'];
        $this->public_key = $config['credentials'][$environment]['public_key'];
        $this->private_key = $config['credentials'][$environment]['private_key'];
        $this->base_url = $config['urls'][$environment];
        
        $this->pagosController = new PagosController();
    }
    
    // Crear transacción en Wompi
    public function crearTransaccion($pago_id, $monto, $referencia, $metodo_pago, $usuario_info) {
        $data = [
            'amount_in_cents' => $monto * 100, // Wompi maneja centavos
            'currency' => 'COP',
            'customer_email' => $usuario_info['email'],
            'payment_method' => $this->mapearMetodoPago($metodo_pago),
            'reference' => $referencia,
            'payment_source_id' => null, // Se obtiene del frontend
            'redirect_url' => 'https://tu-dominio.com/pagos/confirmacion.php?pago_id=' . $pago_id
        ];
        
        $response = $this->hacerPeticion('POST', '/transactions', $data);
        
        if ($response && isset($response['data'])) {
            // Registrar la transacción en nuestra base de datos
            $this->pagosController->registrarTransaccionWompi(
                $pago_id,
                $referencia,
                $response['data']['id'],
                $response['data']['status'],
                json_encode($response)
            );
            
            return [
                'success' => true,
                'transaction_id' => $response['data']['id'],
                'status' => $response['data']['status'],
                'payment_url' => $response['data']['payment_url'] ?? null
            ];
        }
        
        return ['success' => false, 'message' => 'Error al crear transacción en Wompi'];
    }
    
    // Verificar estado de transacción
    public function verificarTransaccion($transaccion_id) {
        $response = $this->hacerPeticion('GET', '/transactions/' . $transaccion_id);
        
        if ($response && isset($response['data'])) {
            return [
                'success' => true,
                'status' => $response['data']['status'],
                'data' => $response['data']
            ];
        }
        
        return ['success' => false, 'message' => 'Error al verificar transacción'];
    }
    
    // Mapear método de pago a formato Wompi
    private function mapearMetodoPago($metodo) {
        $mapeo = [
            'tarjeta' => 'CARD',
            'pse' => 'PSE',
            'nequi' => 'NEQUI',
            'daviplata' => 'DAVIPLATA'
        ];
        
        return $mapeo[$metodo] ?? 'CARD';
    }
    
    // Hacer petición HTTP a Wompi
    private function hacerPeticion($method, $endpoint, $data = null) {
        $url = $this->base_url . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->private_key,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return json_decode($response, true);
        }
        
        return false;
    }
    
    // Generar firma para webhook
    public function generarFirma($payload) {
        return hash('sha256', $payload . $this->private_key);
    }
    
    // Verificar firma del webhook
    public function verificarFirma($payload, $signature) {
        $expected_signature = $this->generarFirma($payload);
        return hash_equals($expected_signature, $signature);
    }
    
    // Procesar webhook de Wompi
    public function procesarWebhook($payload, $signature) {
        if (!$this->verificarFirma($payload, $signature)) {
            return ['success' => false, 'message' => 'Firma inválida'];
        }
        
        $data = json_decode($payload, true);
        
        if (!$data || !isset($data['data'])) {
            return ['success' => false, 'message' => 'Payload inválido'];
        }
        
        $transaccion = $data['data'];
        $referencia = $transaccion['reference'];
        
        // Buscar el pago por referencia
        $pago = $this->buscarPagoPorReferencia($referencia);
        
        if (!$pago) {
            return ['success' => false, 'message' => 'Pago no encontrado'];
        }
        
        // Actualizar estado del pago
        $estado = $this->mapearEstadoWompi($transaccion['status']);
        $this->pagosController->actualizarEstadoPago($pago['id'], $estado, $transaccion['id']);
        
        return ['success' => true, 'pago_id' => $pago['id'], 'estado' => $estado];
    }
    
    // Buscar pago por referencia
    private function buscarPagoPorReferencia($referencia) {
        require_once __DIR__ . '/../Models/conexion.php';
        $conexion = Conexion::getInstancia()->getConexion();
        
        $sql = "SELECT * FROM pagos WHERE referencia_wompi = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $referencia);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    // Mapear estado de Wompi a nuestro sistema
    private function mapearEstadoWompi($estado_wompi) {
        $mapeo = [
            'PENDING' => 'procesando',
            'APPROVED' => 'aprobado',
            'DECLINED' => 'rechazado',
            'VOIDED' => 'cancelado'
        ];
        
        return $mapeo[$estado_wompi] ?? 'pendiente';
    }
}
?>
