<?php
require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../app/Models/conexion.php';

use setasign\Fpdi\Tcpdf\Fpdf;

$conn = Conexion::getInstancia()->getConexion();

// Función para crear el header profesional
function crearHeaderProfesional($pdf, $titulo) {
    // Configurar colores corporativos
    $pdf->SetTextColor(0, 0, 0);
    
    // Logo de la empresa (si existe)
        $logo_path = __DIR__ . '/../resources/images/logofinal.png';
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 15, 10, 30, 0, 'PNG');
    }
    
    // Información de la empresa
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->SetXY(50, 15);
    $pdf->Cell(0, 8, 'QUINTANARES RESIDENCIAL', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetXY(50, 22);
    $pdf->Cell(0, 6, 'Sistema de Gestión de Parqueaderos', 0, 1, 'L');
    
    $pdf->SetXY(50, 28);
    $pdf->Cell(0, 6, 'Tel: (601) 123-4567 | Email: info@quintanares.com', 0, 1, 'L');
    
    // Línea separadora
    $pdf->SetDrawColor(0, 150, 136);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, 35, 195, 35);
    
    // Título del reporte
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(0, 150, 136);
    $pdf->SetXY(15, 45);
    $pdf->Cell(0, 10, $titulo, 0, 1, 'C');
    
    // Fecha de generación
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(15, 55);
    $pdf->Cell(0, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    return 65; // Retorna la posición Y para continuar el contenido
}

// Función para crear el footer profesional
function crearFooterProfesional($pdf) {
    $pdf->SetY(-25);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(100, 100, 100);
    
    // Línea separadora
    $pdf->SetDrawColor(0, 150, 136);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, $pdf->GetY() - 5, 195, $pdf->GetY() - 5);
    
    // Información del footer
    $pdf->SetXY(15, $pdf->GetY() - 2);
    $pdf->Cell(0, 4, 'Quintanares Residencial - Sistema de Gestión de Parqueaderos', 0, 0, 'L');
    
    $pdf->SetXY(15, $pdf->GetY() + 4);
    $pdf->Cell(0, 4, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 0, 'R');
}

if (isset($_GET['id'])) {
    // PDF individual
    $id = $_GET['id'];
    $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular, r.nombre_rol 
            FROM usuarios u
            INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
            INNER JOIN roles r ON ur.roles_idroles = r.idroles
            WHERE u.id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Error en prepare: ' . $conn->error . '<br>SQL: ' . htmlspecialchars($sql));
    }
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Crear PDF con diseño profesional
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        
        // Crear header
        $y_pos = crearHeaderProfesional($pdf, 'REPORTE DE USUARIO');
        
        // Contenido del reporte
        $pdf->SetY($y_pos);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(0, 8, 'INFORMACIÓN DEL USUARIO', 0, 1, 'L');
        
        // Tabla de información
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(245, 245, 245);
        
        $campos = [
            'ID' => $row['id'],
            'Nombre' => $row['nombre'],
            'Apellido' => $row['apellido'],
            'Email' => $row['email'],
            'Celular' => $row['celular'],
            'Rol' => $row['nombre_rol']
        ];
        
        foreach ($campos as $campo => $valor) {
            $pdf->SetFillColor(245, 245, 245);
            $pdf->Cell(40, 8, $campo . ':', 1, 0, 'L', true);
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Cell(0, 8, $valor, 1, 1, 'L', true);
        }
        
        // Crear footer
        crearFooterProfesional($pdf);
        
        $pdf->Output('reporte_usuario_' . $id . '.pdf', 'I');
    } else {
        die('Usuario no encontrado');
    }
} else {
    // PDF general
    $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular, r.nombre_rol 
            FROM usuarios u
            INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
            INNER JOIN roles r ON ur.roles_idroles = r.idroles
            ORDER BY u.nombre, u.apellido";
    $result = $conn->query($sql);
    if (!$result) {
        die('Error al obtener los usuarios');
    }
    
    // Crear PDF con diseño profesional
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    
    // Crear header
    $y_pos = crearHeaderProfesional($pdf, 'REPORTE GENERAL DE USUARIOS');
    
    // Contenido del reporte
    $pdf->SetY($y_pos);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'LISTADO DE USUARIOS REGISTRADOS', 0, 1, 'L');
    
    // Tabla de usuarios
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetFillColor(0, 150, 136);
    $pdf->SetTextColor(255, 255, 255);
    
    // Encabezados de la tabla
    $pdf->Cell(15, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(35, 8, 'Nombre', 1, 0, 'C', true);
    $pdf->Cell(35, 8, 'Apellido', 1, 0, 'C', true);
    $pdf->Cell(50, 8, 'Email', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Celular', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Rol', 1, 1, 'C', true);
    
    // Datos de la tabla
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    while ($row = $result->fetch_assoc()) {
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        $pdf->Cell(15, 6, $row['id'], 1, 0, 'C', $fill);
        $pdf->Cell(35, 6, $row['nombre'], 1, 0, 'L', $fill);
        $pdf->Cell(35, 6, $row['apellido'], 1, 0, 'L', $fill);
        $pdf->Cell(50, 6, $row['email'], 1, 0, 'L', $fill);
        $pdf->Cell(25, 6, $row['celular'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, $row['nombre_rol'], 1, 1, 'C', $fill);
        $fill = !$fill;
    }
    
    // Crear footer
    crearFooterProfesional($pdf);
    
    $pdf->Output('reporte_usuarios.pdf', 'I');
}
?>
