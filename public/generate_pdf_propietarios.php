<?php
require_once __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../app/Models/conexion.php';

use setasign\Fpdi\Tcpdf\Fpdf;

$conn = Conexion::getInstancia()->getConexion();

// Función para crear el header profesional con logo
function crearHeaderProfesional($pdf, $titulo) {
    // Logo de la empresa
        $logo_path = __DIR__ . '/../resources/images/logofinal.png';
    if (file_exists($logo_path)) {
        $pdf->Image($logo_path, 15, 10, 35, 0, 'PNG');
    }
    
    // Información de la empresa
    $pdf->SetFont('helvetica', 'B', 18);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(55, 12);
    $pdf->Cell(0, 8, 'QUINTANARES RESIDENCIAL', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 150, 136);
    $pdf->SetXY(55, 20);
    $pdf->Cell(0, 6, 'Sistema de Gestión de Parqueaderos', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(55, 26);
    $pdf->Cell(0, 5, 'Tel: (601) 123-4567 | Email: info@quintanares.com', 0, 1, 'L');
    
    $pdf->SetXY(55, 31);
    $pdf->Cell(0, 5, 'Dirección: Calle 123 #45-67, Bogotá, Colombia', 0, 1, 'L');
    
    // Línea separadora con gradiente
    $pdf->SetDrawColor(0, 150, 136);
    $pdf->SetLineWidth(1);
    $pdf->Line(15, 38, 195, 38);
    
    // Título del reporte
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor(0, 150, 136);
    $pdf->SetXY(15, 48);
    $pdf->Cell(0, 12, $titulo, 0, 1, 'C');
    
    // Fecha de generación
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->SetXY(15, 58);
    $pdf->Cell(0, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
    
    return 68; // Retorna la posición Y para continuar el contenido
}

// Función para crear el footer profesional
function crearFooterProfesional($pdf) {
    $pdf->SetY(-30);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(100, 100, 100);
    
    // Línea separadora
    $pdf->SetDrawColor(0, 150, 136);
    $pdf->SetLineWidth(0.3);
    $pdf->Line(15, $pdf->GetY() - 8, 195, $pdf->GetY() - 8);
    
    // Información del footer
    $pdf->SetXY(15, $pdf->GetY() - 5);
    $pdf->Cell(0, 4, 'Quintanares Residencial - Sistema de Gestión de Parqueaderos', 0, 0, 'L');
    
    $pdf->SetXY(15, $pdf->GetY() + 4);
    $pdf->Cell(0, 4, 'Página ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages(), 0, 0, 'R');
    
    $pdf->SetXY(15, $pdf->GetY() + 4);
    $pdf->Cell(0, 4, '© 2025 Quintanares Residencial. Todos los derechos reservados.', 0, 0, 'C');
}

// Función para crear una tabla de información personal
function crearTablaInformacion($pdf, $datos, $titulo) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, $titulo, 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetFillColor(245, 245, 245);
    
    foreach ($datos as $campo => $valor) {
        $pdf->SetFillColor(245, 245, 245);
        $pdf->Cell(45, 8, $campo . ':', 1, 0, 'L', true);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Cell(0, 8, $valor, 1, 1, 'L', true);
    }
    $pdf->Ln(5);
}

// Verificar si es un reporte de vehículos
if (isset($_POST['action']) && $_POST['action'] === 'reporte_vehiculos') {
    // Obtener el ID del usuario de la sesión
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        die('Usuario no autenticado');
    }
    
    $usuario_id = $_SESSION['usuario_id'];
    
    // Consulta para obtener los vehículos del usuario
    $sql = "SELECT v.id, v.placa, v.marca, v.modelo, v.color, v.año, v.tipo_vehiculo, v.estado, v.observaciones,
                   u.nombre, u.apellido, u.email, u.celular,
                   p.torre, p.piso, p.apartamento
            FROM vehiculos v
            INNER JOIN usuarios u ON v.propietario_id = u.id
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE v.propietario_id = ?
            ORDER BY v.placa";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Error en prepare: ' . $conn->error);
    }
    $stmt->bind_param('i', $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        die('Error al obtener los vehículos');
    }
    
    // Crear PDF con diseño profesional
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    
    // Crear header
    $y_pos = crearHeaderProfesional($pdf, 'REPORTE DE VEHÍCULOS REGISTRADOS');
    
    // Contenido del reporte
    $pdf->SetY($y_pos);
    
    // Información del propietario
    if ($result->num_rows > 0) {
        $first_row = $result->fetch_assoc();
        $result->data_seek(0); // Resetear el puntero
        
        $info_propietario = [
            'Propietario' => $first_row['nombre'] . ' ' . $first_row['apellido'],
            'Email' => $first_row['email'],
            'Celular' => $first_row['celular'],
            'Apartamento' => $first_row['torre'] . '-' . $first_row['piso'] . '-' . $first_row['apartamento']
        ];
        crearTablaInformacion($pdf, $info_propietario, 'INFORMACIÓN DEL PROPIETARIO');
    }
    
    // Estadísticas
    $total_vehiculos = $result->num_rows;
    $vehiculos_activos = 0;
    $total_carros = 0;
    $total_motos = 0;
    
    $result->data_seek(0); // Resetear el puntero
    while ($row = $result->fetch_assoc()) {
        if ($row['estado'] === 'activo') $vehiculos_activos++;
        if ($row['tipo_vehiculo'] === 'carro') $total_carros++;
        if ($row['tipo_vehiculo'] === 'moto') $total_motos++;
    }
    
    $estadisticas = [
        'Total de Vehículos' => $total_vehiculos,
        'Vehículos Activos' => $vehiculos_activos,
        'Carros' => $total_carros,
        'Motocicletas' => $total_motos
    ];
    crearTablaInformacion($pdf, $estadisticas, 'ESTADÍSTICAS');
    
    // Tabla de vehículos
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'DETALLE DE VEHÍCULOS', 0, 1, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetFillColor(0, 150, 136);
    $pdf->SetTextColor(255, 255, 255);
    
    // Encabezados de la tabla
    $pdf->Cell(20, 8, 'Placa', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Marca/Modelo', 1, 0, 'C', true);
    $pdf->Cell(15, 8, 'Año', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Color', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Tipo', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Estado', 1, 0, 'C', true);
    $pdf->Cell(0, 8, 'Observaciones', 1, 1, 'C', true);
    
    // Datos de la tabla
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    $result->data_seek(0); // Resetear el puntero
    while ($row = $result->fetch_assoc()) {
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        $pdf->Cell(20, 6, $row['placa'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, $row['marca'] . ' ' . $row['modelo'], 1, 0, 'L', $fill);
        $pdf->Cell(15, 6, $row['año'], 1, 0, 'C', $fill);
        $pdf->Cell(20, 6, $row['color'], 1, 0, 'C', $fill);
        $pdf->Cell(20, 6, ucfirst($row['tipo_vehiculo']), 1, 0, 'C', $fill);
        $pdf->Cell(20, 6, ucfirst(str_replace('_', ' ', $row['estado'])), 1, 0, 'C', $fill);
        $pdf->Cell(0, 6, $row['observaciones'] ?: 'N/A', 1, 1, 'L', $fill);
        $fill = !$fill;
    }
    
    // Crear footer
    crearFooterProfesional($pdf);
    
    $pdf->Output('reporte_vehiculos_' . date('Y-m-d') . '.pdf', 'I');
    
} elseif (isset($_GET['id'])) {
    // PDF individual de propietario
    $id = $_GET['id'];
    $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular, r.nombre_rol,
                   p.torre, p.piso, p.apartamento, p.parqueadero_asignado
            FROM usuarios u
            INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
            INNER JOIN roles r ON ur.roles_idroles = r.idroles
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE u.id = ? AND r.nombre_rol = 'propietario'";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Error en prepare: ' . $conn->error);
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
        $y_pos = crearHeaderProfesional($pdf, 'REPORTE DE PROPIETARIO');
        
        // Contenido del reporte
        $pdf->SetY($y_pos);
        
        // Información personal
        $info_personal = [
            'ID' => $row['id'],
            'Nombre' => $row['nombre'],
            'Apellido' => $row['apellido'],
            'Email' => $row['email'],
            'Celular' => $row['celular'],
            'Rol' => $row['nombre_rol']
        ];
        crearTablaInformacion($pdf, $info_personal, 'INFORMACIÓN PERSONAL');
        
        // Información de la propiedad
        $info_propiedad = [
            'Torre' => $row['torre'] ?? 'No asignada',
            'Piso' => $row['piso'] ?? 'No asignado',
            'Apartamento' => $row['apartamento'] ?? 'No asignado',
            'Parqueadero' => $row['parqueadero_asignado'] ?? 'No asignado'
        ];
        crearTablaInformacion($pdf, $info_propiedad, 'INFORMACIÓN DE LA PROPIEDAD');
        
        // Crear footer
        crearFooterProfesional($pdf);
        
        $pdf->Output('reporte_propietario_' . $id . '.pdf', 'I');
    } else {
        die('Propietario no encontrado');
    }
} else {
    // PDF general de propietarios
    $sql = "SELECT u.id, u.nombre, u.apellido, u.email, u.celular, r.nombre_rol,
                   p.torre, p.piso, p.apartamento, p.parqueadero_asignado
            FROM usuarios u
            INNER JOIN usu_roles ur ON u.id = ur.usuarios_id
            INNER JOIN roles r ON ur.roles_idroles = r.idroles
            LEFT JOIN propietarios p ON u.id = p.usuario_id
            WHERE r.nombre_rol = 'propietario'
            ORDER BY u.nombre, u.apellido";
    $result = $conn->query($sql);
    if (!$result) {
        die('Error al obtener los propietarios');
    }
    
    // Crear PDF con diseño profesional
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->SetMargins(15, 15, 15);
    $pdf->AddPage();
    
    // Crear header
    $y_pos = crearHeaderProfesional($pdf, 'REPORTE GENERAL DE PROPIETARIOS');
    
    // Contenido del reporte
    $pdf->SetY($y_pos);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell(0, 8, 'LISTADO DE PROPIETARIOS REGISTRADOS', 0, 1, 'L');
    $pdf->Ln(2);
    
    // Tabla de propietarios
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetFillColor(0, 150, 136);
    $pdf->SetTextColor(255, 255, 255);
    
    // Encabezados de la tabla
    $pdf->Cell(12, 8, 'ID', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Nombre', 1, 0, 'C', true);
    $pdf->Cell(25, 8, 'Apellido', 1, 0, 'C', true);
    $pdf->Cell(40, 8, 'Email', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Celular', 1, 0, 'C', true);
    $pdf->Cell(15, 8, 'Torre', 1, 0, 'C', true);
    $pdf->Cell(15, 8, 'Piso', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Apto', 1, 0, 'C', true);
    $pdf->Cell(20, 8, 'Parqueadero', 1, 1, 'C', true);
    
    // Datos de la tabla
    $pdf->SetTextColor(0, 0, 0);
    $fill = false;
    while ($row = $result->fetch_assoc()) {
        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        $pdf->Cell(12, 6, $row['id'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 6, $row['nombre'], 1, 0, 'L', $fill);
        $pdf->Cell(25, 6, $row['apellido'], 1, 0, 'L', $fill);
        $pdf->Cell(40, 6, $row['email'], 1, 0, 'L', $fill);
        $pdf->Cell(20, 6, $row['celular'], 1, 0, 'C', $fill);
        $pdf->Cell(15, 6, $row['torre'] ?? 'N/A', 1, 0, 'C', $fill);
        $pdf->Cell(15, 6, $row['piso'] ?? 'N/A', 1, 0, 'C', $fill);
        $pdf->Cell(20, 6, $row['apartamento'] ?? 'N/A', 1, 0, 'C', $fill);
        $pdf->Cell(20, 6, $row['parqueadero_asignado'] ?? 'N/A', 1, 1, 'C', $fill);
        $fill = !$fill;
    }
    
    // Crear footer
    crearFooterProfesional($pdf);
    
    $pdf->Output('reporte_propietarios.pdf', 'I');
}
?>
