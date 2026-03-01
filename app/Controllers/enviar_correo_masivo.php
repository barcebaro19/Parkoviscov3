<?php
// Script para envío de correos masivos usando PHPMailer con Gmail SMTP

// Incluir configuración y clases de PHPMailer
require_once __DIR__ . '/../../config/gmail_config.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/SMTP.php';
require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correos = $_POST['correos'] ?? '';
    $asunto = $_POST['asunto'] ?? '';
    $mensaje = $_POST['mensaje'] ?? '';

    if (!$correos || !$asunto || !$mensaje) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    $correosArray = array_filter(array_map('trim', explode(',', $correos)));
    $enviados = 0;
    $errores = [];
    $detalles = [];

    foreach ($correosArray as $correo) {
        // Validar formato de email
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = $correo . ' (formato inválido)';
            continue;
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
            $enviados++;
            $detalles[] = "✅ Enviado a: $correo";
            
        } catch (Exception $e) {
            $errores[] = $correo . ' (' . $e->getMessage() . ')';
            $detalles[] = "❌ Error en: $correo - " . $e->getMessage();
        }
    }

    // Log del envío masivo
    $log_entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => 'mass_email',
        'total_recipients' => count($correosArray),
        'sent' => $enviados,
        'errors' => count($errores),
        'subject' => $asunto,
        'message' => $mensaje,
        'from' => GMAIL_USERNAME,
        'status' => $enviados > 0 ? 'partial_success' : 'failed',
        'details' => $detalles
    ];
    
    // Guardar en archivo de log
    $log_file = __DIR__ . '/../../storage/logs/email_log.txt';
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0755, true);
    }
    file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);

    // Respuesta JSON
    $response = [
        'success' => $enviados > 0,
        'message' => "Correos enviados: $enviados de " . count($correosArray),
        'details' => $detalles
    ];

    if (!empty($errores)) {
        $response['errors'] = $errores;
    }

    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Acceso no permitido.']);
} 