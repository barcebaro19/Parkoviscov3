<?php
/**
 * WebService Demo - Validación de Correos Electrónicos
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * 
 * Este archivo proporciona un servicio web para validar correos electrónicos
 * utilizando Hunter.io API con fallback a validación local.
 */

// Incluir el validador de Hunter.io
require_once __DIR__ . '/../app/Services/HunterEmailValidator.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Función para validar formato de email
 */
function validarFormatoEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Función para validar dominio de email
 */
function validarDominioEmail($email) {
    $dominio = substr(strrchr($email, "@"), 1);
    
    // Lista de dominios comunes válidos
    $dominiosValidos = [
        'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com',
        'live.com', 'msn.com', 'aol.com', 'icloud.com',
        'protonmail.com', 'yandex.com', 'mail.com', 'zoho.com',
        'fastmail.com', 'tutanota.com', 'gmx.com', 'web.de',
        'terra.com', 'uol.com.br', 'bol.com.br', 'ig.com.br',
        'globo.com', 'r7.com', 'yahoo.com.br', 'hotmail.com.br',
        'outlook.com.br', 'live.com.br', 'msn.com.br'
    ];
    
    return in_array(strtolower($dominio), $dominiosValidos);
}

/**
 * Función para verificar si el dominio tiene MX record
 */
function verificarMXRecord($email) {
    $dominio = substr(strrchr($email, "@"), 1);
    
    // Verificar si el dominio tiene registros MX
    $mx_records = [];
    $result = getmxrr($dominio, $mx_records);
    
    return $result && count($mx_records) > 0;
}

/**
 * Función para validar email usando API externa (simulada)
 */
function validarEmailExterno($email) {
    // Simulación de validación externa
    // En un entorno real, aquí se haría una llamada a una API como:
    // - Hunter.io
    // - ZeroBounce
    // - EmailValidator
    // - etc.
    
    $dominio = substr(strrchr($email, "@"), 1);
    
    // Simular diferentes resultados basados en el dominio
    $dominiosDisponibles = [
        'gmail.com' => true,
        'yahoo.com' => true,
        'hotmail.com' => true,
        'outlook.com' => true,
        'test.com' => false,
        'invalid.com' => false,
        'example.com' => false
    ];
    
    return $dominiosDisponibles[$dominio] ?? true;
}

/**
 * Función principal de validación usando Hunter.io
 */
function validarEmail($email) {
    try {
        $validator = new HunterEmailValidator();
        return $validator->validateEmail($email);
    } catch (Exception $e) {
        // Fallback a validación local en caso de error
        return validarEmailLocal($email, $e->getMessage());
    }
}

/**
 * Función de validación local como fallback
 */
function validarEmailLocal($email, $error = null) {
    $resultado = [
        'email' => $email,
        'valido' => false,
        'formato_valido' => false,
        'dominio_valido' => false,
        'mx_record' => false,
        'disponible' => false,
        'mensaje' => '',
        'timestamp' => date('Y-m-d H:i:s'),
        'fuente' => 'local_fallback',
        'error' => $error
    ];
    
    // 1. Validar formato
    $resultado['formato_valido'] = validarFormatoEmail($email);
    if (!$resultado['formato_valido']) {
        $resultado['mensaje'] = 'Formato de email inválido';
        return $resultado;
    }
    
    // 2. Validar dominio
    $resultado['dominio_valido'] = validarDominioEmail($email);
    if (!$resultado['dominio_valido']) {
        $resultado['mensaje'] = 'Dominio de email no reconocido';
        return $resultado;
    }
    
    // 3. Verificar MX record
    $resultado['mx_record'] = verificarMXRecord($email);
    if (!$resultado['mx_record']) {
        $resultado['mensaje'] = 'Dominio no tiene registros MX válidos';
        return $resultado;
    }
    
    // 4. Validar disponibilidad (simulada)
    $resultado['disponible'] = validarEmailExterno($email);
    if (!$resultado['disponible']) {
        $resultado['mensaje'] = 'Email no disponible o no existe';
        return $resultado;
    }
    
    // Si llegamos aquí, el email es válido
    $resultado['valido'] = true;
    $resultado['mensaje'] = 'Email válido y disponible (validación local)';
    
    return $resultado;
}

// Procesar la petición
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? $_POST['email'] ?? '';
        
        if (empty($email)) {
            throw new Exception('Email no proporcionado');
        }
        
        $resultado = validarEmail($email);
        
        // Log de la validación
        $log_entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'email' => $email,
            'resultado' => $resultado,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        // Guardar log
        $log_file = __DIR__ . '/../storage/logs/email_validation.log';
        if (!file_exists(dirname($log_file))) {
            mkdir(dirname($log_file), 0755, true);
        }
        file_put_contents($log_file, json_encode($log_entry) . "\n", FILE_APPEND | LOCK_EX);
        
        // Si es una petición AJAX (JSON), devolver JSON
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            echo json_encode([
                'success' => true,
                'data' => $resultado
            ]);
        } else {
            // Si es un formulario normal, redirigir con los resultados
            session_start();
            $_SESSION['email_validation_result'] = $resultado;
            header('Location: tablausu.php?email_validated=1');
            exit();
        }
        
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Página de demostración
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>WebService Demo - Validación de Emails | Quintanares</title>
            <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
            <style>
                body { font-family: 'Inter', system-ui, sans-serif; }
                .cyber-card { background: rgba(255, 255, 255, 0.03); backdrop-filter: blur(25px); border: 1px solid rgba(255, 255, 255, 0.1); }
            </style>
        </head>
        <body class="bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 min-h-screen">
            <div class="container mx-auto px-4 py-8">
                <div class="max-w-4xl mx-auto">
                    <div class="cyber-card rounded-2xl p-8 mb-8">
                        <h1 class="text-4xl font-bold text-white mb-4">
                            <i class="fas fa-envelope text-emerald-400 mr-3"></i>
                            WebService Demo - Validación de Emails
                        </h1>
                        <p class="text-gray-300 mb-6">
                            Servicio web para validar correos electrónicos en tiempo real.
                        </p>
                        
                        <div class="bg-gray-800 rounded-lg p-6 mb-6">
                            <h2 class="text-xl font-semibold text-white mb-4">Probar Validación</h2>
                            <form id="validationForm" class="space-y-4">
                                <div>
                                    <label class="block text-white font-medium mb-2">Email a validar:</label>
                                    <input type="email" id="emailInput" class="w-full px-4 py-2 bg-gray-700 text-white rounded-lg border border-gray-600 focus:border-emerald-400 focus:outline-none" placeholder="ejemplo@dominio.com" required>
                                </div>
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-check mr-2"></i>Validar Email
                                </button>
                            </form>
                        </div>
                        
                        <div id="resultado" class="hidden bg-gray-800 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-white mb-4">Resultado de la Validación</h3>
                            <div id="resultadoContent"></div>
                        </div>
                    </div>
                    
                    <div class="cyber-card rounded-2xl p-8">
                        <h2 class="text-2xl font-bold text-white mb-4">
                            <i class="fas fa-code text-blue-400 mr-3"></i>
                            Documentación de la API
                        </h2>
                        
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-2">Endpoint</h3>
                                <code class="bg-gray-800 text-emerald-400 px-3 py-1 rounded">POST /webservice_demo.php</code>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-2">Parámetros</h3>
                                <ul class="text-gray-300 space-y-1">
                                    <li><code class="bg-gray-800 px-2 py-1 rounded">email</code> - Email a validar (requerido)</li>
                                </ul>
                            </div>
                            
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-2">Respuesta</h3>
                                <pre class="bg-gray-800 text-gray-300 p-4 rounded-lg overflow-x-auto"><code>{
  "success": true,
  "data": {
    "email": "ejemplo@dominio.com",
    "valido": true,
    "formato_valido": true,
    "dominio_valido": true,
    "mx_record": true,
    "disponible": true,
    "mensaje": "Email válido y disponible",
    "timestamp": "2024-01-01 12:00:00"
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                document.getElementById('validationForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const email = document.getElementById('emailInput').value;
                    const resultadoDiv = document.getElementById('resultado');
                    const resultadoContent = document.getElementById('resultadoContent');
                    
                    try {
                        const response = await fetch('webservice_demo.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ email: email })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            const result = data.data;
                            resultadoContent.innerHTML = `
                                <div class="space-y-3">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-envelope text-blue-400"></i>
                                        <span class="text-white">Email: <strong>${result.email}</strong></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="fas ${result.valido ? 'fa-check-circle text-green-400' : 'fa-times-circle text-red-400'}"></i>
                                        <span class="text-white">Válido: <strong>${result.valido ? 'Sí' : 'No'}</strong></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="fas ${result.formato_valido ? 'fa-check text-green-400' : 'fa-times text-red-400'}"></i>
                                        <span class="text-white">Formato: <strong>${result.formato_valido ? 'Válido' : 'Inválido'}</strong></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="fas ${result.dominio_valido ? 'fa-check text-green-400' : 'fa-times text-red-400'}"></i>
                                        <span class="text-white">Dominio: <strong>${result.dominio_valido ? 'Válido' : 'Inválido'}</strong></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="fas ${result.mx_record ? 'fa-check text-green-400' : 'fa-times text-red-400'}"></i>
                                        <span class="text-white">MX Record: <strong>${result.mx_record ? 'Válido' : 'Inválido'}</strong></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="fas ${result.disponible ? 'fa-check text-green-400' : 'fa-times text-red-400'}"></i>
                                        <span class="text-white">Disponible: <strong>${result.disponible ? 'Sí' : 'No'}</strong></span>
                                    </div>
                                    <div class="mt-4 p-3 rounded-lg ${result.valido ? 'bg-green-900/30 border border-green-500/30' : 'bg-red-900/30 border border-red-500/30'}">
                                        <p class="text-white"><strong>Mensaje:</strong> ${result.mensaje}</p>
                                    </div>
                                </div>
                            `;
                        } else {
                            resultadoContent.innerHTML = `
                                <div class="bg-red-900/30 border border-red-500/30 p-4 rounded-lg">
                                    <p class="text-red-300">Error: ${data.message || 'Error desconocido'}</p>
                                </div>
                            `;
                        }
                        
                        resultadoDiv.classList.remove('hidden');
                        
                    } catch (error) {
                        resultadoContent.innerHTML = `
                            <div class="bg-red-900/30 border border-red-500/30 p-4 rounded-lg">
                                <p class="text-red-300">Error de conexión: ${error.message}</p>
                            </div>
                        `;
                        resultadoDiv.classList.remove('hidden');
                    }
                });
            </script>
        </body>
        </html>
        <?php
    } else {
        throw new Exception('Método no permitido');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
