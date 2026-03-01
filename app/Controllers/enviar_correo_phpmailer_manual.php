<?php
// Controlador para envío real usando PHPMailer manual con Gmail SMTP
// Incluye las clases de PHPMailer directamente

// Incluir configuración y clases de PHPMailer
require_once __DIR__ . '/../../config/gmail_config.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';

    if (!$correo || !$asunto || !$mensaje) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    // Validar formato de email
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = GMAIL_USERNAME;
        $mail->Password   = GMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Remitente y destinatario
        $mail->setFrom(GMAIL_USERNAME, GMAIL_FROM_NAME);
        $mail->addAddress($correo);
        $mail->addReplyTo(GMAIL_USERNAME, GMAIL_FROM_NAME);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = nl2br(htmlspecialchars($mensaje));
        $mail->AltBody = strip_tags($mensaje);

        $mail->send();
        
        // Log del correo enviado
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $correo,
            'subject' => $asunto,
            'message' => $mensaje,
            'from' => GMAIL_USERNAME,
            'status' => 'sent_phpmailer',
            'service' => 'PHPMailer + Gmail'
        ];
        
        // Guardar en archivo de log
        $log_file = __DIR__ . '/../../storage/logs/email_log.txt';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
        
        echo json_encode(['success' => true, 'message' => 'Correo enviado correctamente a ' . $correo . ' via PHPMailer + Gmail']);
        
    } catch (Exception $e) {
        // Log del error
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'to' => $correo,
            'subject' => $asunto,
            'message' => $mensaje,
            'from' => GMAIL_USERNAME,
            'status' => 'failed_phpmailer',
            'error' => $e->getMessage(),
            'service' => 'PHPMailer + Gmail'
        ];
        
        // Guardar en archivo de log
        $log_file = __DIR__ . '/../../storage/logs/email_log.txt';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
        
        echo json_encode([
            'success' => false, 
            'message' => 'Error al enviar el correo via PHPMailer: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso no permitido.']);
}
?>
