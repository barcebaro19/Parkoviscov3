<?php
// importar_usuarios.php
// Evidencia: Implementación de carga masiva de usuarios desde CSV, solo para administradores.
// Reglas de negocio: Validación de rol, formato de email, unicidad de cédula/email, contraseña mínima, rol permitido.

session_start();
require_once __DIR__ . "/../app/Models/conexion.php";
require_once __DIR__ . '/../vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Solo administradores pueden acceder
if (!isset($_SESSION['nombre_rol']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: tablausu.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
    $archivo = $_FILES['archivo_csv']['tmp_name'];
    $handle = fopen($archivo, 'r');
    $errores = [];
    $exitos = 0;
    $linea = 0;
    
    // Leer cada línea del CSV
    while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $linea++;
        if ($linea == 1 && preg_match('/id|nombre|apellido|email|celular|contraseña|rol/i', implode(',', $data))) {
            // Saltar encabezado si existe
            continue;
        }
        // Asignar columnas
        list($id, $nombre, $apellido, $email, $celular, $contrasena, $rol) = array_map('trim', $data + array_fill(0,7,''));
        // Validaciones de reglas de negocio
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "Línea $linea: Email inválido ($email)";
            continue;
        }
        if (strlen($contrasena) < 6) {
            $errores[] = "Línea $linea: Contraseña muy corta";
            continue;
        }
        if (!in_array($rol, ['administrador', 'vigilante', 'usuario'])) {
            $errores[] = "Línea $linea: Rol no permitido ($rol)";
            continue;
        }
        // Verificar unicidad de cédula/email
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE id=? OR email=?");
        $stmt->bind_param('ss', $id, $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errores[] = "Línea $linea: Cédula o email ya existe ($id, $email)";
            $stmt->close();
            continue;
        }
        $stmt->close();
        // Insertar usuario
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (id, nombre, apellido, email, celular, contraseña, nombre_rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssss', $id, $nombre, $apellido, $email, $celular, $hash, $rol);
        if ($stmt->execute()) {
            $exitos++;
            // Enviar correo al usuario
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Cambia por tu servidor SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'TU_CORREO@gmail.com'; // Cambia por tu correo
                $mail->Password = 'TU_CONTRASENA'; // Cambia por tu contraseña o app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->setFrom('TU_CORREO@gmail.com', 'Parkovisko');
                $mail->addAddress($email, $nombre . ' ' . $apellido);
                $mail->isHTML(true);
                $mail->Subject = 'Bienvenido a Parkovisko';
                $mail->Body = "<b>Hola $nombre $apellido</b><br>Tu usuario ha sido creado.<br>Email: $email<br>Contraseña: $contrasena<br>Rol: $rol<br>";
                $mail->send();
            } catch (Exception $e) {
                $errores[] = "Línea $linea: Usuario creado pero error al enviar correo a $email: {$mail->ErrorInfo}";
            }
        } else {
            $errores[] = "Línea $linea: Error al insertar ($id, $email)";
        }
        $stmt->close();
    }
    fclose($handle);
    // Mostrar resultados y volver
    $_SESSION['import_result'] = [
        'exitos' => $exitos,
        'errores' => $errores
    ];
    header('Location: tablausu.php');
    exit();
} else {
    header('Location: tablausu.php');
    exit();
} 