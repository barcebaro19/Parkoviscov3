# 🛠️ Guía de Desarrollo - Quintanares Residencial

Esta guía está dirigida a desarrolladores que deseen contribuir, modificar o extender el sistema de gestión de Quintanares Residencial.

## 📋 **Información del Proyecto**

### **Tecnologías Utilizadas**
- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript ES6+
- **Base de Datos:** MySQL 5.7+ / MariaDB 10.3+
- **Frameworks:** Tailwind CSS, Alpine.js
- **Librerías:** PHPMailer, TCPDF, Particles.js, AOS

### **Estructura del Proyecto**
```
parkovisko/
├── 📁 components/          # Componentes reutilizables
│   ├── header.php
│   └── footer.php
├── 📁 config/             # Archivos de configuración
│   ├── gmail_config.php
│   └── wompi_config.php
├── 📁 controller/         # Controladores de lógica
│   ├── buscar_*.php
│   ├── eliminar_*.php
│   ├── enviar_correo_*.php
│   └── modificar_*.php
├── 📁 database/           # Scripts de base de datos
│   └── pagos_structure.sql
├── 📁 docs/               # Documentación
├── 📁 img/                # Imágenes y assets
│   ├── logofinal.png
│   └── cuarto.jpg
├── 📁 includes/           # Archivos incluidos
│   └── sidebar.php
├── 📁 js/                 # JavaScript
│   ├── notifications.js
│   └── pagos.js
├── 📁 logs/               # Archivos de log
│   └── email_log.txt
├── 📁 models/             # Modelos de datos
│   └── conexion.php
├── 📁 modals/             # Modales
│   └── modal_eliminar.php
├── 📁 vendor/             # Dependencias (Composer)
└── 📄 *.php              # Archivos principales
```

## 🚀 **Configuración del Entorno de Desarrollo**

### **Requisitos del Sistema**
- **PHP 7.4+** con extensiones: mysqli, curl, openssl, json, mbstring, zip, gd
- **MySQL 5.7+** o **MariaDB 10.3+**
- **Composer** para gestión de dependencias
- **Git** para control de versiones
- **Servidor web** (Apache/Nginx)

### **Instalación del Entorno**

#### **1. Clonar el Repositorio**
```bash
git clone [repository-url] quintanares-dev
cd quintanares-dev
```

#### **2. Instalar Dependencias**
```bash
composer install
```

#### **3. Configurar Base de Datos**
```bash
# Crear base de datos de desarrollo
mysql -u root -p
CREATE DATABASE sistema_vigilancia_dev;
USE sistema_vigilancia_dev;
SOURCE sistema\ vigilancia\ \(1\).sql;
```

#### **4. Configurar Variables de Entorno**
```bash
# Copiar archivos de configuración
cp config/gmail_config.php.example config/gmail_config.php
cp config/wompi_config.php.example config/wompi_config.php

# Editar configuraciones
nano config/gmail_config.php
nano config/wompi_config.php
```

#### **5. Configurar Servidor de Desarrollo**
```bash
# Usar servidor PHP integrado
php -S localhost:8000

# O configurar Apache/Nginx
# Ver docs/INSTALLATION.md para detalles
```

## 🏗️ **Arquitectura del Sistema**

### **Patrón MVC (Modelo-Vista-Controlador)**

#### **📁 Models (Modelos)**
```php
// models/conexion.php
class Conexion {
    private static $instancia = null;
    private $conexion;
    
    private function __construct() {
        $this->conexion = new mysqli(
            'localhost', 'usuario', 'password', 'base_datos'
        );
    }
    
    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    public function getConexion() {
        return $this->conexion;
    }
}
```

#### **📁 Controllers (Controladores)**
```php
// controller/ejemplo_controller.php
<?php
require_once "../models/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conexion = Conexion::getInstancia()->getConexion();
    
    // Validar datos
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    
    if (!$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
        exit;
    }
    
    // Procesar lógica
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Usuario encontrado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
}
?>
```

#### **📄 Views (Vistas)**
```php
<!-- ejemplo.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ejemplo</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Título</h1>
        <!-- Contenido de la vista -->
    </div>
</body>
</html>
```

### **🔧 Patrones de Diseño Utilizados**

#### **Singleton Pattern**
```php
// Para conexión a base de datos
class Conexion {
    private static $instancia = null;
    
    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
}
```

#### **Factory Pattern**
```php
// Para crear diferentes tipos de usuarios
class UsuarioFactory {
    public static function crear($tipo, $datos) {
        switch ($tipo) {
            case 'administrador':
                return new Administrador($datos);
            case 'vigilante':
                return new Vigilante($datos);
            case 'propietario':
                return new Propietario($datos);
            default:
                throw new Exception("Tipo de usuario no válido");
        }
    }
}
```

## 💻 **Estándares de Código**

### **📝 Convenciones de Nomenclatura**

#### **Variables y Funciones**
```php
// camelCase para variables
$nombreUsuario = "Juan";
$fechaCreacion = date('Y-m-d');

// camelCase para funciones
function obtenerUsuario($id) {
    // código
}

function validarEmail($email) {
    // código
}
```

#### **Clases y Constantes**
```php
// PascalCase para clases
class UsuarioController {
    // código
}

class ConexionBaseDatos {
    // código
}

// UPPER_SNAKE_CASE para constantes
define('GMAIL_USERNAME', 'email@gmail.com');
define('SMTP_HOST', 'smtp.gmail.com');
```

#### **Archivos y Carpetas**
```bash
# snake_case para archivos PHP
usuario_controller.php
buscar_propietarios.php
enviar_correo_masivo.php

# kebab-case para archivos CSS/JS
user-management.js
payment-system.css
```

### **📋 Estructura de Archivos PHP**

#### **Template Estándar**
```php
<?php
/**
 * Descripción del archivo
 * 
 * @author Tu Nombre
 * @version 1.0
 * @date 2025-01-20
 */

// Incluir dependencias
require_once "models/conexion.php";
require_once "config/gmail_config.php";

// Iniciar sesión si es necesario
session_start();

// Validar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Validar datos de entrada
$datos = validarDatos($_POST);

// Procesar lógica
try {
    $resultado = procesarSolicitud($datos);
    echo json_encode(['success' => true, 'data' => $resultado]);
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno']);
}

/**
 * Validar datos de entrada
 */
function validarDatos($datos) {
    // Validaciones
    return $datos;
}

/**
 * Procesar solicitud
 */
function procesarSolicitud($datos) {
    // Lógica de negocio
    return $resultado;
}
?>
```

### **🎨 Estándares de CSS/JavaScript**

#### **CSS con Tailwind**
```html
<!-- Usar clases de Tailwind -->
<div class="bg-gray-100 p-4 rounded-lg shadow-md">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Título</h2>
    <p class="text-gray-600">Contenido</p>
</div>

<!-- Clases personalizadas para elementos específicos -->
<style>
.cyber-button {
    @apply bg-gradient-to-r from-emerald-500 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-300 hover:shadow-lg;
}
</style>
```

#### **JavaScript con Alpine.js**
```html
<div x-data="userManagement()" x-init="loadUsers()">
    <button @click="showModal = true" class="cyber-button">
        Agregar Usuario
    </button>
    
    <div x-show="showModal" class="modal">
        <!-- Contenido del modal -->
    </div>
</div>

<script>
function userManagement() {
    return {
        showModal: false,
        users: [],
        
        async loadUsers() {
            try {
                const response = await fetch('/api/users');
                this.users = await response.json();
            } catch (error) {
                console.error('Error:', error);
            }
        }
    }
}
</script>
```

## 🔧 **Herramientas de Desarrollo**

### **📦 Gestión de Dependencias**

#### **Composer**
```json
// composer.json
{
    "require": {
        "tecnickcom/tcpdf": "6.7",
        "phpmailer/phpmailer": "^6.8"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0"
    }
}
```

#### **Instalación de Dependencias**
```bash
# Instalar dependencias de producción
composer install --no-dev

# Instalar dependencias de desarrollo
composer install

# Actualizar dependencias
composer update

# Verificar dependencias
composer validate
```

### **🧪 Testing**

#### **Configuración de PHPUnit**
```xml
<!-- phpunit.xml -->
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

#### **Ejemplo de Test**
```php
// tests/Unit/UsuarioTest.php
<?php
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase {
    public function testValidarEmail() {
        $usuario = new Usuario();
        $this->assertTrue($usuario->validarEmail('test@ejemplo.com'));
        $this->assertFalse($usuario->validarEmail('email-invalido'));
    }
    
    public function testCrearUsuario() {
        $datos = [
            'nombre' => 'Juan',
            'email' => 'juan@ejemplo.com'
        ];
        
        $usuario = Usuario::crear($datos);
        $this->assertInstanceOf(Usuario::class, $usuario);
        $this->assertEquals('Juan', $usuario->getNombre());
    }
}
?>
```

### **🔍 Debugging**

#### **Configuración de Debug**
```php
// config/debug.php
<?php
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}
?>
```

#### **Logging Personalizado**
```php
// utils/Logger.php
class Logger {
    public static function log($mensaje, $nivel = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$nivel] $mensaje" . PHP_EOL;
        file_put_contents(__DIR__ . '/../logs/app.log', $logEntry, FILE_APPEND);
    }
    
    public static function error($mensaje) {
        self::log($mensaje, 'ERROR');
    }
    
    public static function info($mensaje) {
        self::log($mensaje, 'INFO');
    }
}
```

## 🚀 **Proceso de Desarrollo**

### **🔄 Flujo de Trabajo Git**

#### **Ramas de Desarrollo**
```bash
# Rama principal
main                    # Código de producción
develop                 # Código de desarrollo
feature/nueva-funcionalidad  # Nuevas características
bugfix/corregir-error   # Corrección de errores
hotfix/error-critico    # Correcciones urgentes
```

#### **Comandos Git Básicos**
```bash
# Crear nueva rama
git checkout -b feature/nueva-funcionalidad

# Hacer commit
git add .
git commit -m "feat: agregar nueva funcionalidad de usuarios"

# Push a rama remota
git push origin feature/nueva-funcionalidad

# Merge a develop
git checkout develop
git merge feature/nueva-funcionalidad
```

#### **Convenciones de Commits**
```bash
# Formato: tipo(scope): descripción
feat(auth): agregar autenticación con JWT
fix(payments): corregir error en procesamiento de pagos
docs(api): actualizar documentación de endpoints
style(ui): mejorar diseño del dashboard
refactor(db): optimizar consultas de base de datos
test(users): agregar tests para gestión de usuarios
```

### **📋 Checklist de Desarrollo**

#### **Antes de Hacer Commit**
- [ ] ✅ Código sigue estándares del proyecto
- [ ] ✅ Funcionalidad probada localmente
- [ ] ✅ Tests pasan correctamente
- [ ] ✅ Documentación actualizada
- [ ] ✅ Sin errores de linting
- [ ] ✅ Commit message descriptivo

#### **Antes de Hacer Merge**
- [ ] ✅ Code review completado
- [ ] ✅ Tests de integración pasan
- [ ] ✅ Documentación actualizada
- [ ] ✅ Compatibilidad con versiones anteriores
- [ ] ✅ Performance no degradada

### **🧪 Testing y QA**

#### **Tipos de Tests**
```php
// Unit Tests - Pruebas unitarias
class UsuarioTest extends TestCase {
    public function testValidarEmail() {
        // Probar función específica
    }
}

// Integration Tests - Pruebas de integración
class PagosIntegrationTest extends TestCase {
    public function testProcesarPagoCompleto() {
        // Probar flujo completo
    }
}

// End-to-End Tests - Pruebas de extremo a extremo
class E2ETest extends TestCase {
    public function testFlujoCompletoUsuario() {
        // Probar desde registro hasta pago
    }
}
```

#### **Automatización de Tests**
```bash
# Ejecutar todos los tests
./vendor/bin/phpunit

# Ejecutar tests específicos
./vendor/bin/phpunit tests/Unit/UsuarioTest.php

# Generar reporte de cobertura
./vendor/bin/phpunit --coverage-html coverage/
```

## 🔒 **Seguridad en Desarrollo**

### **🛡️ Mejores Prácticas**

#### **Validación de Entrada**
```php
// Validar y sanitizar datos
function validarDatos($datos) {
    $validados = [];
    
    // Email
    if (isset($datos['email'])) {
        $validados['email'] = filter_var($datos['email'], FILTER_VALIDATE_EMAIL);
        if (!$validados['email']) {
            throw new Exception('Email inválido');
        }
    }
    
    // Número de teléfono
    if (isset($datos['celular'])) {
        $validados['celular'] = preg_replace('/[^0-9]/', '', $datos['celular']);
        if (strlen($validados['celular']) !== 10) {
            throw new Exception('Celular inválido');
        }
    }
    
    return $validados;
}
```

#### **Prepared Statements**
```php
// Siempre usar prepared statements
function obtenerUsuario($id) {
    $conexion = Conexion::getInstancia()->getConexion();
    
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_assoc();
}
```

#### **Manejo de Errores**
```php
// No exponer información sensible
try {
    $resultado = operacionCritica();
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error: " . $e->getMessage());
    
    // Respuesta genérica al usuario
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
}
```

### **🔐 Autenticación y Autorización**

#### **Sistema de Sesiones**
```php
// Iniciar sesión segura
function iniciarSesion($usuario) {
    session_start();
    
    // Regenerar ID de sesión
    session_regenerate_id(true);
    
    // Establecer variables de sesión
    $_SESSION['user_id'] = $usuario['id'];
    $_SESSION['user_role'] = $usuario['rol'];
    $_SESSION['last_activity'] = time();
    
    // Configurar timeout
    $_SESSION['timeout'] = 3600; // 1 hora
}
```

#### **Verificación de Permisos**
```php
// Verificar permisos por rol
function verificarPermiso($accion, $rol) {
    $permisos = [
        'administrador' => ['crear', 'leer', 'actualizar', 'eliminar'],
        'vigilante' => ['leer', 'actualizar'],
        'propietario' => ['leer']
    ];
    
    return in_array($accion, $permisos[$rol] ?? []);
}
```

## 📊 **Optimización y Performance**

### **⚡ Optimización de Base de Datos**

#### **Índices Estratégicos**
```sql
-- Índices para consultas frecuentes
CREATE INDEX idx_usuarios_email ON usuarios(email);
CREATE INDEX idx_propietarios_torre_piso ON propietarios(torre, piso);
CREATE INDEX idx_parqueadero_estado ON parqueadero(estado);
CREATE INDEX idx_pagos_fecha ON pagos(fecha_pago);
```

#### **Consultas Optimizadas**
```php
// Usar LIMIT para paginación
function obtenerUsuarios($pagina = 1, $limite = 10) {
    $offset = ($pagina - 1) * $limite;
    
    $sql = "SELECT * FROM usuarios LIMIT ? OFFSET ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ii", $limite, $offset);
    $stmt->execute();
    
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
```

### **🎨 Optimización Frontend**

#### **Minificación de Assets**
```bash
# Minificar CSS
npx tailwindcss -i ./src/input.css -o ./dist/output.css --minify

# Minificar JavaScript
npx terser js/app.js -o js/app.min.js
```

#### **Lazy Loading**
```html
<!-- Cargar imágenes de forma diferida -->
<img src="placeholder.jpg" data-src="imagen-real.jpg" loading="lazy" alt="Descripción">

<!-- Cargar scripts de forma diferida -->
<script src="script.js" defer></script>
```

## 🚀 **Deployment y Producción**

### **📦 Preparación para Producción**

#### **Configuración de Producción**
```php
// config/production.php
<?php
// Configuraciones específicas de producción
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php_errors.log');

// Configuración de base de datos de producción
define('DB_HOST', 'prod-db-host');
define('DB_USER', 'prod-db-user');
define('DB_PASS', 'prod-db-password');
define('DB_NAME', 'prod-db-name');
?>
```

#### **Script de Deployment**
```bash
#!/bin/bash
# deploy.sh

echo "🚀 Iniciando deployment..."

# Backup de base de datos
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > backup_$(date +%Y%m%d_%H%M%S).sql

# Actualizar código
git pull origin main

# Instalar dependencias
composer install --no-dev --optimize-autoloader

# Limpiar caché
php artisan cache:clear

# Optimizar autoloader
composer dump-autoload --optimize

# Verificar permisos
chmod -R 755 storage/
chmod -R 755 logs/

echo "✅ Deployment completado"
```

### **📊 Monitoreo de Producción**

#### **Logs de Aplicación**
```php
// utils/ProductionLogger.php
class ProductionLogger {
    public static function log($mensaje, $nivel = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$nivel] $mensaje" . PHP_EOL;
        
        // Log a archivo
        file_put_contents('/var/log/quintanares.log', $logEntry, FILE_APPEND);
        
        // Log a sistema (opcional)
        syslog(LOG_INFO, $mensaje);
    }
}
```

#### **Métricas de Performance**
```php
// utils/PerformanceMonitor.php
class PerformanceMonitor {
    private static $startTime;
    
    public static function start() {
        self::$startTime = microtime(true);
    }
    
    public static function end() {
        $endTime = microtime(true);
        $executionTime = $endTime - self::$startTime;
        
        if ($executionTime > 2.0) { // Más de 2 segundos
            self::log("Slow query detected: {$executionTime}s");
        }
        
        return $executionTime;
    }
}
```

## 📚 **Recursos y Referencias**

### **📖 Documentación Oficial**
- **PHP:** [php.net/manual](https://www.php.net/manual/)
- **MySQL:** [dev.mysql.com/doc](https://dev.mysql.com/doc/)
- **Tailwind CSS:** [tailwindcss.com/docs](https://tailwindcss.com/docs)
- **Alpine.js:** [alpinejs.dev](https://alpinejs.dev/)

### **🛠️ Herramientas Recomendadas**
- **IDE:** VS Code, PhpStorm
- **Git:** GitKraken, SourceTree
- **Base de Datos:** phpMyAdmin, MySQL Workbench
- **Testing:** PHPUnit, Selenium
- **Debugging:** Xdebug, Chrome DevTools

### **📧 Contacto y Soporte**
- **Email:** dev@parkovisco.com
- **Slack:** #quintanares-dev
- **GitHub:** [github.com/parkovisco/quintanares](https://github.com/parkovisco/quintanares)

---

**¡Gracias por contribuir al desarrollo de Quintanares Residencial!** 🛠️✨
