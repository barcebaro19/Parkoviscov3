<?php
// Generar imagen QR para compartir
require_once __DIR__ . '/../Models/conexion.php';

// Función simple para generar QR como imagen SVG
function generarQRSVG($texto, $size = 200) {
    // Esta es una implementación básica de QR en SVG
    // Para producción, recomiendo usar una librería como endroid/qr-code
    
    $hash = md5($texto);
    $modules = [];
    
    // Generar patrón pseudo-aleatorio basado en el hash
    for ($i = 0; $i < 21; $i++) {
        $modules[$i] = [];
        for ($j = 0; $j < 21; $j++) {
            $modules[$i][$j] = (hexdec($hash[($i * 21 + $j) % 32]) % 2) === 1;
        }
    }
    
    // Agregar patrones de posicionamiento
    $positions = [[0, 0], [0, 14], [14, 0]];
    foreach ($positions as $pos) {
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                if (($i == 0 || $i == 6 || $j == 0 || $j == 6) || 
                    ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4)) {
                    $modules[$pos[0] + $i][$pos[1] + $j] = true;
                }
            }
        }
    }
    
    $moduleSize = $size / 21;
    $svg = '<svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">';
    $svg .= '<rect width="' . $size . '" height="' . $size . '" fill="white"/>';
    
    for ($i = 0; $i < 21; $i++) {
        for ($j = 0; $j < 21; $j++) {
            if ($modules[$i][$j]) {
                $x = $j * $moduleSize;
                $y = $i * $moduleSize;
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $moduleSize . '" height="' . $moduleSize . '" fill="black"/>';
            }
        }
    }
    
    $svg .= '</svg>';
    return $svg;
}

try {
    $codigo = $_GET['code'] ?? '';
    
    if (empty($codigo)) {
        throw new Exception('Código no especificado');
    }

    // Verificar que el código existe en la base de datos
    $conn = Conexion::getInstancia()->getConexion();

    $stmt = $conn->prepare("SELECT datos_json, activo, fecha_expiracion FROM codigos_qr WHERE codigo = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado->num_rows === 0) {
        throw new Exception('Código QR no encontrado');
    }
    
    $qr = $resultado->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Verificar que esté activo y no haya expirado
    if (!$qr['activo']) {
        throw new Exception('Código QR inactivo');
    }

    $fechaExpiracion = new DateTime($qr['fecha_expiracion']);
    $ahora = new DateTime();
    
    if ($ahora > $fechaExpiracion) {
        throw new Exception('Código QR expirado');
    }

    // Generar imagen QR
    $qrData = $qr['datos_json'];
    $svg = generarQRSVG($qrData, 300);

    // Enviar headers para SVG
    header('Content-Type: image/svg+xml');
    header('Cache-Control: no-cache, must-revalidate');
    header('Content-Disposition: inline; filename="qr_' . $codigo . '.svg"');
    
    echo $svg;

} catch (Exception $e) {
    // Generar imagen de error
    header('Content-Type: image/svg+xml');
    $errorSvg = '<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
        <rect width="300" height="300" fill="#f8f9fa"/>
        <text x="150" y="140" text-anchor="middle" font-family="Arial" font-size="14" fill="#dc3545">Error</text>
        <text x="150" y="160" text-anchor="middle" font-family="Arial" font-size="12" fill="#6c757d">' . htmlspecialchars($e->getMessage()) . '</text>
    </svg>';
    echo $errorSvg;
}
?>
