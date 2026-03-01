<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simular sesión
session_start();
if (!isset($_SESSION['id'])) {
    $_SESSION['id'] = 1;
}

try {
    // Obtener el tipo de datos solicitado
    $tipo = $_GET['tipo'] ?? 'estadisticas';
    
    switch ($tipo) {
        case 'estadisticas':
            $resultado = [
                'success' => true,
                'estadisticas' => [
                    'total_visitantes' => 12,
                    'visitantes_mes' => 12,
                    'visitantes_hoy' => 2,
                    'visitantes_pendientes' => 1
                ]
            ];
            break;
            
        case 'historial':
            $limite = $_GET['limite'] ?? 10;
            $resultado = [
                'success' => true,
                'historial' => [
                    [
                        'nombre_visitante' => 'María García',
                        'documento' => '12345678',
                        'motivo_visita' => 'Visita familiar',
                        'fecha_inicial' => '2025-01-30 14:30:00',
                        'estado' => 'activo',
                        'observaciones' => 'Visitante autorizado',
                        'codigo_qr' => 'QR001'
                    ],
                    [
                        'nombre_visitante' => 'Carlos López',
                        'documento' => '87654321',
                        'motivo_visita' => 'Entrega de paquete',
                        'fecha_inicial' => '2025-01-29 10:15:00',
                        'estado' => 'finalizado',
                        'observaciones' => 'Entrega completada',
                        'codigo_qr' => 'QR002'
                    ],
                    [
                        'nombre_visitante' => 'Ana Martínez',
                        'documento' => '11223344',
                        'motivo_visita' => 'Visita de trabajo',
                        'fecha_inicial' => '2025-01-28 15:20:00',
                        'estado' => 'finalizado',
                        'observaciones' => 'Reunión de trabajo',
                        'codigo_qr' => 'QR003'
                    ],
                    [
                        'nombre_visitante' => 'Juan Pérez',
                        'documento' => '55667788',
                        'motivo_visita' => 'Servicio técnico',
                        'fecha_inicial' => '2025-01-27 09:45:00',
                        'estado' => 'finalizado',
                        'observaciones' => 'Reparación de internet',
                        'codigo_qr' => 'QR004'
                    ],
                    [
                        'nombre_visitante' => 'Laura Rodríguez',
                        'documento' => '99887766',
                        'motivo_visita' => 'Visita familiar',
                        'fecha_inicial' => '2025-01-26 16:30:00',
                        'estado' => 'finalizado',
                        'observaciones' => 'Visita familiar',
                        'codigo_qr' => 'QR005'
                    ]
                ]
            ];
            break;
            
        case 'frecuentes':
            $limite = $_GET['limite'] ?? 5;
            $resultado = [
                'success' => true,
                'frecuentes' => [
                    [
                        'nombre_visitante' => 'María García',
                        'documento' => '12345678',
                        'total_visitas' => 8,
                        'ultima_visita' => '2025-01-30 14:30:00'
                    ],
                    [
                        'nombre_visitante' => 'Carlos López',
                        'documento' => '87654321',
                        'total_visitas' => 5,
                        'ultima_visita' => '2025-01-29 10:15:00'
                    ],
                    [
                        'nombre_visitante' => 'Ana Martínez',
                        'documento' => '11223344',
                        'total_visitas' => 3,
                        'ultima_visita' => '2025-01-28 15:20:00'
                    ],
                    [
                        'nombre_visitante' => 'Juan Pérez',
                        'documento' => '55667788',
                        'total_visitas' => 2,
                        'ultima_visita' => '2025-01-27 09:45:00'
                    ]
                ]
            ];
            break;
            
        case 'preautorizaciones':
            $resultado = [
                'success' => true,
                'preautorizaciones' => [
                    [
                        'nombre_visitante' => 'Técnico de gas',
                        'documento' => '99887766',
                        'motivo_visita' => 'Mantenimiento',
                        'fecha_autorizada' => '2025-01-31 09:00:00',
                        'observaciones' => 'Empresa: Gas Natural',
                        'codigo_qr' => 'QR006'
                    ],
                    [
                        'nombre_visitante' => 'Repartidor Amazon',
                        'documento' => '55443322',
                        'motivo_visita' => 'Entrega de paquete',
                        'fecha_autorizada' => '2025-01-31 14:00:00',
                        'observaciones' => 'Paquete grande',
                        'codigo_qr' => 'QR007'
                    ],
                    [
                        'nombre_visitante' => 'Técnico de internet',
                        'documento' => '11223344',
                        'motivo_visita' => 'Instalación',
                        'fecha_autorizada' => '2025-02-01 10:00:00',
                        'observaciones' => 'Nueva instalación',
                        'codigo_qr' => 'QR008'
                    ]
                ]
            ];
            break;
            
        default:
            throw new Exception('Tipo de datos no válido');
    }
    
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en api_visitantes_working.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>
