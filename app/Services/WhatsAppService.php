<?php
/**
 * Servicio de WhatsApp para envío de notificaciones
 * Integración con WhatsApp Business API
 */
class WhatsAppService {
    
    private $config;
    private $apiUrl;
    private $phoneNumberId;
    private $accessToken;
    
    public function __construct() {
        // Cargar configuración
        $this->config = require_once __DIR__ . '/../../config/whatsapp_config.php';
        $this->apiUrl = $this->config['api_url'];
        $this->phoneNumberId = $this->config['phone_number_id'];
        $this->accessToken = $this->config['access_token'];
    }
    
    /**
     * Enviar mensaje de WhatsApp
     */
    public function enviarMensaje($numero, $mensaje) {
        try {
            // Verificar si WhatsApp está habilitado
            if (!$this->config['messaging']['enabled']) {
                return [
                    'success' => false,
                    'message' => 'WhatsApp está deshabilitado en la configuración'
                ];
            }
            
            // Formatear número de teléfono
            $numeroFormateado = $this->formatearNumero($numero);
            
            // Log del mensaje
            $this->logMensaje($numeroFormateado, $mensaje);
            
            // Si las credenciales no están configuradas, usar modo simulación
            if ($this->phoneNumberId === 'TU_PHONE_NUMBER_ID_AQUI' || $this->accessToken === 'TU_ACCESS_TOKEN_AQUI') {
                return $this->simularEnvio($numeroFormateado, $mensaje);
            }
            
            // Enviar mensaje real a WhatsApp Business API
            return $this->enviarMensajeReal($numeroFormateado, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando WhatsApp: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error enviando mensaje: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar mensaje real a WhatsApp Business API
     */
    private function enviarMensajeReal($numero, $mensaje) {
        $url = "{$this->apiUrl}/{$this->phoneNumberId}/messages";
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $numero,
            'type' => 'text',
            'text' => [
                'body' => $mensaje
            ]
        ];
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error cURL: " . $error);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode === 200 && isset($responseData['messages'][0]['id'])) {
            $this->logMensajeExitoso($numero, $responseData['messages'][0]['id']);
            return [
                'success' => true,
                'message' => 'Mensaje enviado exitosamente',
                'numero' => $numero,
                'message_id' => $responseData['messages'][0]['id']
            ];
        } else {
            $errorMsg = $responseData['error']['message'] ?? 'Error desconocido';
            throw new Exception("Error API WhatsApp: " . $errorMsg);
        }
    }
    
    /**
     * Simular envío de mensaje (para desarrollo)
     */
    private function simularEnvio($numero, $mensaje) {
        // Simular delay de red
        usleep(500000); // 0.5 segundos
        
        $this->logMensajeExitoso($numero, 'SIM_' . uniqid());
        
        return [
            'success' => true,
            'message' => 'Mensaje enviado exitosamente (modo simulación)',
            'numero' => $numero,
            'message_id' => 'SIM_' . uniqid(),
            'simulado' => true
        ];
    }
    
    /**
     * Formatear número de teléfono
     */
    private function formatearNumero($numero) {
        // Remover caracteres no numéricos
        $numero = preg_replace('/[^0-9]/', '', $numero);
        
        // Si no tiene código de país, agregarlo
        if (substr($numero, 0, 2) !== '57') {
            $numero = '57' . $numero;
        }
        
        return $numero;
    }
    
    /**
     * Log de mensaje
     */
    private function logMensaje($numero, $mensaje) {
        if ($this->config['logging']['enabled']) {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'numero' => $numero,
                'mensaje' => substr($mensaje, 0, 100) . (strlen($mensaje) > 100 ? '...' : ''),
                'accion' => 'enviando'
            ];
            
            $logFile = __DIR__ . '/../../' . $this->config['logging']['log_file'];
            file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Log de mensaje exitoso
     */
    private function logMensajeExitoso($numero, $messageId) {
        if ($this->config['logging']['enabled']) {
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'numero' => $numero,
                'message_id' => $messageId,
                'accion' => 'enviado_exitoso'
            ];
            
            $logFile = __DIR__ . '/../../' . $this->config['logging']['log_file'];
            file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
    
    /**
     * Enviar confirmación de QR usando plantilla
     */
    public function enviarConfirmacionQR($datos_visitante, $codigo_qr, $datos_propietario) {
        try {
            $numero = $datos_visitante['telefono'] ?? '';
            $nombre_visitante = $datos_visitante['nombre'] ?? 'Visitante';
            $nombre_propietario = $datos_propietario['nombre'] ?? 'Propietario';
            
            // Usar plantilla de mensaje
            $template = $this->config['templates']['reserva_confirmada'];
            
            $mensaje = $template['header'] . "\n\n";
            $mensaje .= str_replace([
                '{nombre}',
                '{fecha}',
                '{hora}',
                '{codigo_qr}',
                '{motivo}',
                '{fecha_final}'
            ], [
                $nombre_visitante,
                date('d/m/Y'),
                date('H:i'),
                $codigo_qr,
                $datos_visitante['motivo'] ?? 'Visita',
                date('d/m/Y H:i', strtotime('+24 hours'))
            ], $template['body']);
            
            $mensaje .= "\n\n" . $template['footer'];
            
            return $this->enviarMensaje($numero, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando confirmación QR: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error enviando confirmación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar notificación de reserva cancelada
     */
    public function enviarReservaCancelada($numero, $datos_reserva) {
        try {
            $template = $this->config['templates']['reserva_cancelada'];
            
            $mensaje = $template['header'] . "\n\n";
            $mensaje .= str_replace([
                '{nombre}',
                '{fecha}'
            ], [
                $datos_reserva['nombre'] ?? 'Usuario',
                $datos_reserva['fecha'] ?? date('d/m/Y')
            ], $template['body']);
            
            $mensaje .= "\n\n" . $template['footer'];
            
            return $this->enviarMensaje($numero, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando cancelación: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error enviando cancelación: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar recordatorio de reserva
     */
    public function enviarRecordatorio($numero, $datos_reserva) {
        try {
            $template = $this->config['templates']['recordatorio'];
            
            $mensaje = $template['header'] . "\n\n";
            $mensaje .= str_replace([
                '{fecha}',
                '{hora}',
                '{codigo_qr}'
            ], [
                $datos_reserva['fecha'] ?? date('d/m/Y'),
                $datos_reserva['hora'] ?? date('H:i'),
                $datos_reserva['codigo_qr'] ?? ''
            ], $template['body']);
            
            $mensaje .= "\n\n" . $template['footer'];
            
            return $this->enviarMensaje($numero, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando recordatorio: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error enviando recordatorio: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar mensaje personalizado
     */
    public function enviarMensajePersonalizado($numero, $mensaje, $tipo = 'texto') {
        try {
            // Agregar header y footer si es necesario
            if ($tipo === 'notificacion') {
                $mensaje = "🏠 *Quintanares Residencial*\n\n" . $mensaje . "\n\nQuintanares Residencial - Sistema de Gestión";
            }
            
            return $this->enviarMensaje($numero, $mensaje);
            
        } catch (Exception $e) {
            error_log("Error enviando mensaje personalizado: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error enviando mensaje: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Enviar confirmación de reserva (alias para compatibilidad)
     */
    public function enviarReservaConfirmada($datos_visitante, $codigo_qr, $datos_propietario) {
        return $this->enviarConfirmacionQR($datos_visitante, $codigo_qr, $datos_propietario);
    }
    
    /**
     * Verificar si el servicio está disponible
     */
    public function verificarDisponibilidad() {
        return [
            'disponible' => true,
            'mensaje' => 'Servicio de WhatsApp disponible (modo simulación)'
        ];
    }
}
?>