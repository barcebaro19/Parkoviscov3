<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

require_once __DIR__ . '/../Models/conexion.php';

try {
    // Conectar a la base de datos
    $conn = Conexion::getInstancia()->getConexion();

    if (!$conn) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Obtener parámetros de filtro
    $apartamento = $_GET['apartamento'] ?? '';
    $torre = $_GET['torre'] ?? '';
    $tipo = $_GET['tipo'] ?? '';
    $activo = $_GET['activo'] ?? '1';
    $limite = (int)($_GET['limite'] ?? 50);
    $pagina = (int)($_GET['pagina'] ?? 1);

    // Construir consulta con filtros
    $where = ["1=1"];
    $params = [];
    $types = "";

    if (!empty($apartamento)) {
        $where[] = "apartamento = ?";
        $params[] = $apartamento;
        $types .= "s";
    }

    if (!empty($torre)) {
        $where[] = "torre = ?";
        $params[] = $torre;
        $types .= "s";
    }

    if (!empty($tipo)) {
        $where[] = "tipo = ?";
        $params[] = $tipo;
        $types .= "s";
    }

    if ($activo !== '') {
        $where[] = "activo = ?";
        $params[] = $activo;
        $types .= "i";
    }

    // Agregar filtro de no expirados (opcional)
    if ($_GET['incluir_expirados'] !== '1') {
        $where[] = "fecha_expiracion > NOW()";
    }

    $whereClause = implode(" AND ", $where);
    
    // Consulta principal
    $offset = ($pagina - 1) * $limite;
    $sql = "SELECT 
                codigo, tipo, apartamento, torre, propietario,
                placa, visitante_nombre, visitante_documento, visitante_telefono, motivo,
                fecha_creacion, fecha_expiracion, usado, fecha_uso, activo
            FROM codigos_qr 
            WHERE $whereClause 
            ORDER BY fecha_creacion DESC 
            LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($sql);
    
    // Agregar parámetros de paginación
    $params[] = $limite;
    $params[] = $offset;
    $types .= "ii";

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $codigos = [];
    while ($fila = $resultado->fetch_assoc()) {
        // Calcular estado
        $ahora = new DateTime();
        $fechaExpiracion = new DateTime($fila['fecha_expiracion']);
        
        $estado = 'valido';
        if (!$fila['activo']) {
            $estado = 'inactivo';
        } elseif ($ahora > $fechaExpiracion) {
            $estado = 'expirado';
        } elseif ($fila['usado'] && $fila['tipo'] === 'visitante') {
            $estado = 'usado';
        }

        $codigo = [
            'codigo' => $fila['codigo'],
            'tipo' => $fila['tipo'],
            'apartamento' => $fila['apartamento'],
            'torre' => $fila['torre'],
            'propietario' => $fila['propietario'],
            'fecha_creacion' => $fila['fecha_creacion'],
            'fecha_expiracion' => $fila['fecha_expiracion'],
            'usado' => (bool)$fila['usado'],
            'fecha_uso' => $fila['fecha_uso'],
            'activo' => (bool)$fila['activo'],
            'estado' => $estado
        ];

        // Agregar información específica según el tipo
        if ($fila['tipo'] === 'vehiculo') {
            $codigo['placa'] = $fila['placa'];
        } elseif ($fila['tipo'] === 'visitante') {
            $codigo['visitante'] = [
                'nombre' => $fila['visitante_nombre'],
                'documento' => $fila['visitante_documento'],
                'telefono' => $fila['visitante_telefono'],
                'motivo' => $fila['motivo']
            ];
        }

        $codigos[] = $codigo;
    }

    $stmt->close();

    // Contar total para paginación
    $sqlCount = "SELECT COUNT(*) as total FROM codigos_qr WHERE $whereClause";
    $stmtCount = $conn->prepare($sqlCount);
    
    if (!empty($params)) {
        // Remover los últimos dos parámetros (limit y offset)
        $paramsCount = array_slice($params, 0, -2);
        $typesCount = substr($types, 0, -2);
        
        if (!empty($paramsCount)) {
            $stmtCount->bind_param($typesCount, ...$paramsCount);
        }
    }
    
    $stmtCount->execute();
    $resultadoCount = $stmtCount->get_result();
    $total = $resultadoCount->fetch_assoc()['total'];
    $stmtCount->close();

    // Estadísticas adicionales
    $stats = [
        'total' => (int)$total,
        'pagina_actual' => $pagina,
        'limite' => $limite,
        'total_paginas' => ceil($total / $limite),
        'activos' => 0,
        'expirados' => 0,
        'usados' => 0,
        'vehiculos' => 0,
        'visitantes' => 0
    ];

    foreach ($codigos as $codigo) {
        switch ($codigo['estado']) {
            case 'valido':
                $stats['activos']++;
                break;
            case 'expirado':
                $stats['expirados']++;
                break;
            case 'usado':
                $stats['usados']++;
                break;
        }

        if ($codigo['tipo'] === 'vehiculo') {
            $stats['vehiculos']++;
        } else {
            $stats['visitantes']++;
        }
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'codigos' => $codigos,
        'estadisticas' => $stats,
        'filtros_aplicados' => [
            'apartamento' => $apartamento,
            'torre' => $torre,
            'tipo' => $tipo,
            'activo' => $activo
        ]
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'QR_LIST_ERROR'
    ]);
    
    error_log("Error listando QR: " . $e->getMessage());
}
?>
