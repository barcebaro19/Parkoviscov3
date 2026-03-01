<?php
// =====================================================
// VERIFICADOR DE COMPATIBILIDAD PHP
// =====================================================

echo "<h2>🔍 Verificación de Compatibilidad PHP</h2>";
echo "<p><strong>Versión actual:</strong> " . PHP_VERSION . "</p>";

echo "<p><strong>SAPI:</strong> " . php_sapi_name() . "</p>";
echo "<p><strong>php.ini cargado:</strong> " . (php_ini_loaded_file() ?: 'N/A') . "</p>";

// Verificar extensiones necesarias
$required_extensions = [
    'mysqli',
    'json',
    'session',
    'curl',
    'openssl',
    'mbstring',
    'intl'
];

echo "<h3>📦 Extensiones requeridas:</h3>";
echo "<ul>";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "<li>{$status} {$ext}</li>";
}
echo "</ul>";

echo "<h3>🌍 Intl/Locale:</h3>";
echo "<ul>";
echo "<li><strong>extension_loaded('intl'):</strong> " . (extension_loaded('intl') ? '✅' : '❌') . "</li>";
echo "<li><strong>class_exists('Locale'):</strong> " . (class_exists('Locale') ? '✅' : '❌') . "</li>";
echo "</ul>";

// Verificar funciones críticas
echo "<h3>🔧 Funciones críticas:</h3>";
$critical_functions = [
    'mysqli_connect',
    'json_encode',
    'json_decode',
    'session_start',
    'curl_init',
    'password_hash'
];

echo "<ul>";
foreach ($critical_functions as $func) {
    $status = function_exists($func) ? "✅" : "❌";
    echo "<li>{$status} {$func}</li>";
}
echo "</ul>";

// Verificar configuración
echo "<h3>⚙️ Configuración:</h3>";
echo "<ul>";
echo "<li><strong>display_errors:</strong> " . ini_get('display_errors') . "</li>";
echo "<li><strong>error_reporting:</strong> " . ini_get('error_reporting') . "</li>";
echo "<li><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . "</li>";
echo "<li><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</li>";
echo "<li><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "</ul>";

// Test de conexión a base de datos
echo "<h3>🗄️ Test de conexión:</h3>";
try {
    require_once '../app/Models/conexion.php';
    $conn = Conexion::getInstancia()->getConexion();
    if ($conn) {
        echo "<p>✅ Conexión a base de datos exitosa</p>";
    } else {
        echo "<p>❌ Error en conexión a base de datos</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>📋 Recomendaciones:</h3>";
if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    echo "<p>⚠️ <strong>Recomendación:</strong> Actualizar a PHP 8.1+ para mejor compatibilidad con Azure</p>";
} else {
    echo "<p>✅ <strong>Versión compatible</strong> con Azure App Service</p>";
}
?>
