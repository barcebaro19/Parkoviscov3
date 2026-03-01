# 🏗️ Arquitectura del Sistema - Quintanares Residencial

Esta documentación describe la arquitectura completa del sistema de gestión de Quintanares Residencial, incluyendo patrones de diseño, flujos de datos y decisiones arquitectónicas.

## 📋 **Visión General de la Arquitectura**

### **🎯 Principios Arquitectónicos**
- **Separación de Responsabilidades:** Cada capa tiene una responsabilidad específica
- **Escalabilidad:** Diseño que permite crecimiento futuro
- **Mantenibilidad:** Código fácil de mantener y extender
- **Seguridad:** Protección en todas las capas
- **Performance:** Optimización para rendimiento

### **🏛️ Arquitectura en Capas**

```
┌─────────────────────────────────────────────────────────────┐
│                    🌐 CAPA DE PRESENTACIÓN                  │
│  HTML5, CSS3, JavaScript, Tailwind CSS, Alpine.js         │
├─────────────────────────────────────────────────────────────┤
│                    🎮 CAPA DE CONTROL                       │
│  PHP Controllers, Session Management, Authentication       │
├─────────────────────────────────────────────────────────────┤
│                    💼 CAPA DE LÓGICA DE NEGOCIO            │
│  Business Rules, Validation, Data Processing              │
├─────────────────────────────────────────────────────────────┤
│                    🗄️ CAPA DE ACCESO A DATOS               │
│  MySQLi, Prepared Statements, Connection Pooling          │
├─────────────────────────────────────────────────────────────┤
│                    💾 CAPA DE DATOS                         │
│  MySQL/MariaDB, File System, External APIs                │
└─────────────────────────────────────────────────────────────┘
```

## 🎨 **Arquitectura de Presentación**

### **🌐 Frontend Stack**

#### **Tecnologías Utilizadas**
- **HTML5:** Estructura semántica y accesible
- **CSS3:** Estilos avanzados y animaciones
- **JavaScript ES6+:** Interactividad moderna
- **Tailwind CSS:** Framework de utilidades CSS
- **Alpine.js:** Reactividad ligera
- **Font Awesome:** Iconografía
- **AOS:** Animaciones on scroll
- **Particles.js:** Efectos visuales

#### **Patrón de Componentes**
```html
<!-- Componente reutilizable -->
<div class="cyber-card" x-data="userCard()">
    <div class="card-header">
        <h3 x-text="user.name"></h3>
        <span class="badge" x-text="user.role"></span>
    </div>
    <div class="card-body">
        <p x-text="user.email"></p>
        <p x-text="user.phone"></p>
    </div>
    <div class="card-actions">
        <button @click="editUser()" class="btn-primary">Editar</button>
        <button @click="deleteUser()" class="btn-danger">Eliminar</button>
    </div>
</div>
```

#### **Sistema de Diseño**
```css
/* Variables CSS para consistencia */
:root {
    --primary-color: #10b981;
    --secondary-color: #3b82f6;
    --accent-color: #8b5cf6;
    --background-dark: #0a0a0a;
    --text-light: #e0e0e0;
    --border-color: rgba(255, 255, 255, 0.1);
}

/* Componentes base */
.cyber-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(25px);
    border: 1px solid var(--border-color);
    border-radius: 24px;
    padding: 1.5rem;
}
```

### **📱 Responsive Design**

#### **Breakpoints**
```css
/* Mobile First Approach */
@media (min-width: 640px) { /* sm */ }
@media (min-width: 768px) { /* md */ }
@media (min-width: 1024px) { /* lg */ }
@media (min-width: 1280px) { /* xl */ }
@media (min-width: 1536px) { /* 2xl */ }
```

#### **Grid System**
```html
<!-- Sistema de grid responsivo -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="col-span-1">Contenido 1</div>
    <div class="col-span-1">Contenido 2</div>
    <div class="col-span-1 md:col-span-2 lg:col-span-1">Contenido 3</div>
</div>
```

## 🎮 **Arquitectura de Control**

### **🔄 Patrón MVC**

#### **Modelo (Model)**
```php
// models/Usuario.php
class Usuario {
    private $id;
    private $nombre;
    private $email;
    private $conexion;
    
    public function __construct() {
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    public function crear($datos) {
        $sql = "INSERT INTO usuarios (nombre, email) VALUES (?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ss", $datos['nombre'], $datos['email']);
        return $stmt->execute();
    }
    
    public function obtenerPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
```

#### **Vista (View)**
```php
<!-- views/usuarios/lista.php -->
<div class="usuarios-container">
    <h1>Lista de Usuarios</h1>
    <div class="usuarios-grid">
        <?php foreach ($usuarios as $usuario): ?>
            <div class="usuario-card">
                <h3><?php echo htmlspecialchars($usuario['nombre']); ?></h3>
                <p><?php echo htmlspecialchars($usuario['email']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

#### **Controlador (Controller)**
```php
// controller/UsuarioController.php
class UsuarioController {
    private $usuarioModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
    }
    
    public function listar() {
        $usuarios = $this->usuarioModel->obtenerTodos();
        include 'views/usuarios/lista.php';
    }
    
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = $this->validarDatos($_POST);
            if ($this->usuarioModel->crear($datos)) {
                header('Location: /usuarios?mensaje=creado');
            } else {
                $error = 'Error al crear usuario';
            }
        }
        include 'views/usuarios/crear.php';
    }
}
```

### **🔐 Sistema de Autenticación**

#### **Gestión de Sesiones**
```php
// utils/SessionManager.php
class SessionManager {
    public static function iniciarSesion($usuario) {
        session_start();
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $usuario['id'];
        $_SESSION['user_role'] = $usuario['rol'];
        $_SESSION['last_activity'] = time();
        $_SESSION['timeout'] = 3600; // 1 hora
    }
    
    public static function verificarSesion() {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        if (time() - $_SESSION['last_activity'] > $_SESSION['timeout']) {
            self::cerrarSesion();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    public static function cerrarSesion() {
        session_start();
        session_destroy();
        setcookie(session_name(), '', time() - 3600);
    }
}
```

#### **Sistema de Permisos**
```php
// utils/PermissionManager.php
class PermissionManager {
    private static $permisos = [
        'administrador' => [
            'usuarios' => ['crear', 'leer', 'actualizar', 'eliminar'],
            'parqueaderos' => ['crear', 'leer', 'actualizar', 'eliminar'],
            'vigilantes' => ['crear', 'leer', 'actualizar', 'eliminar'],
            'propietarios' => ['crear', 'leer', 'actualizar', 'eliminar'],
            'reportes' => ['generar', 'exportar'],
            'configuracion' => ['modificar']
        ],
        'vigilante' => [
            'usuarios' => ['leer'],
            'parqueaderos' => ['leer'],
            'visitas' => ['crear', 'leer', 'actualizar'],
            'reportes' => ['crear', 'leer']
        ],
        'propietario' => [
            'perfil' => ['leer', 'actualizar'],
            'vehiculos' => ['crear', 'leer', 'actualizar', 'eliminar'],
            'visitas' => ['crear', 'leer'],
            'pagos' => ['crear', 'leer'],
            'reportes' => ['crear', 'leer']
        ]
    ];
    
    public static function tienePermiso($rol, $recurso, $accion) {
        return isset(self::$permisos[$rol][$recurso]) && 
               in_array($accion, self::$permisos[$rol][$recurso]);
    }
}
```

## 💼 **Arquitectura de Lógica de Negocio**

### **🏭 Patrón Factory**

#### **Factory de Usuarios**
```php
// factories/UsuarioFactory.php
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
                throw new Exception("Tipo de usuario no válido: $tipo");
        }
    }
}

// Uso del factory
$usuario = UsuarioFactory::crear('propietario', $datos);
```

### **🔧 Patrón Strategy**

#### **Estrategias de Pago**
```php
// strategies/PaymentStrategy.php
interface PaymentStrategy {
    public function procesar($monto, $datos);
    public function validar($datos);
}

class TarjetaStrategy implements PaymentStrategy {
    public function procesar($monto, $datos) {
        // Lógica específica para tarjeta
        return $this->procesarConWompi($monto, $datos);
    }
    
    public function validar($datos) {
        return isset($datos['numero']) && isset($datos['cvv']);
    }
}

class PSEStrategy implements PaymentStrategy {
    public function procesar($monto, $datos) {
        // Lógica específica para PSE
        return $this->procesarPSE($monto, $datos);
    }
    
    public function validar($datos) {
        return isset($datos['banco']) && isset($datos['cuenta']);
    }
}

// Contexto de pago
class PaymentProcessor {
    private $strategy;
    
    public function setStrategy(PaymentStrategy $strategy) {
        $this->strategy = $strategy;
    }
    
    public function procesarPago($monto, $datos) {
        if (!$this->strategy->validar($datos)) {
            throw new Exception('Datos de pago inválidos');
        }
        
        return $this->strategy->procesar($monto, $datos);
    }
}
```

### **📋 Patrón Observer**

#### **Sistema de Notificaciones**
```php
// observers/NotificationObserver.php
interface Observer {
    public function update($evento, $datos);
}

class EmailNotificationObserver implements Observer {
    public function update($evento, $datos) {
        switch ($evento) {
            case 'usuario_creado':
                $this->enviarEmailBienvenida($datos);
                break;
            case 'pago_procesado':
                $this->enviarEmailConfirmacion($datos);
                break;
        }
    }
    
    private function enviarEmailBienvenida($usuario) {
        // Lógica de envío de email
    }
}

class SMSNotificationObserver implements Observer {
    public function update($evento, $datos) {
        // Lógica de envío de SMS
    }
}

// Subject
class EventManager {
    private $observers = [];
    
    public function agregarObserver(Observer $observer) {
        $this->observers[] = $observer;
    }
    
    public function notificar($evento, $datos) {
        foreach ($this->observers as $observer) {
            $observer->update($evento, $datos);
        }
    }
}
```

## 🗄️ **Arquitectura de Acceso a Datos**

### **🔌 Patrón Singleton para Conexión**

#### **Conexión a Base de Datos**
```php
// models/Conexion.php
class Conexion {
    private static $instancia = null;
    private $conexion;
    
    private function __construct() {
        $this->conexion = new mysqli(
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_USER'] ?? 'root',
            $_ENV['DB_PASS'] ?? '',
            $_ENV['DB_NAME'] ?? 'sistema_vigilancia',
            $_ENV['DB_PORT'] ?? 3306
        );
        
        if ($this->conexion->connect_error) {
            throw new Exception('Error de conexión: ' . $this->conexion->connect_error);
        }
        
        $this->conexion->set_charset('utf8mb4');
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
    
    public function __destruct() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
```

### **📊 Patrón Repository**

#### **Repositorio de Usuarios**
```php
// repositories/UsuarioRepository.php
class UsuarioRepository {
    private $conexion;
    
    public function __construct() {
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    public function encontrarPorId($id) {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function encontrarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function crear($datos) {
        $sql = "INSERT INTO usuarios (nombre, apellido, email, celular) VALUES (?, ?, ?, ?)";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssss", 
            $datos['nombre'], 
            $datos['apellido'], 
            $datos['email'], 
            $datos['celular']
        );
        
        if ($stmt->execute()) {
            return $this->conexion->insert_id;
        }
        
        throw new Exception('Error al crear usuario: ' . $stmt->error);
    }
    
    public function actualizar($id, $datos) {
        $sql = "UPDATE usuarios SET nombre = ?, apellido = ?, email = ?, celular = ? WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ssssi", 
            $datos['nombre'], 
            $datos['apellido'], 
            $datos['email'], 
            $datos['celular'],
            $id
        );
        
        return $stmt->execute();
    }
    
    public function eliminar($id) {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function listar($filtros = []) {
        $sql = "SELECT * FROM usuarios WHERE 1=1";
        $params = [];
        $types = "";
        
        if (isset($filtros['rol'])) {
            $sql .= " AND id IN (SELECT usuarios_id FROM usu_roles ur JOIN roles r ON ur.roles_idroles = r.idroles WHERE r.nombre_rol = ?)";
            $params[] = $filtros['rol'];
            $types .= "s";
        }
        
        if (isset($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR email LIKE ?)";
            $busqueda = "%{$filtros['busqueda']}%";
            $params[] = $busqueda;
            $params[] = $busqueda;
            $params[] = $busqueda;
            $types .= "sss";
        }
        
        $sql .= " ORDER BY nombre ASC";
        
        if (isset($filtros['limite'])) {
            $sql .= " LIMIT ?";
            $params[] = $filtros['limite'];
            $types .= "i";
        }
        
        $stmt = $this->conexion->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
```

### **🔄 Patrón Unit of Work**

#### **Transacciones de Base de Datos**
```php
// utils/UnitOfWork.php
class UnitOfWork {
    private $conexion;
    private $transaccionActiva = false;
    
    public function __construct() {
        $this->conexion = Conexion::getInstancia()->getConexion();
    }
    
    public function iniciarTransaccion() {
        if (!$this->transaccionActiva) {
            $this->conexion->begin_transaction();
            $this->transaccionActiva = true;
        }
    }
    
    public function confirmarTransaccion() {
        if ($this->transaccionActiva) {
            $this->conexion->commit();
            $this->transaccionActiva = false;
        }
    }
    
    public function revertirTransaccion() {
        if ($this->transaccionActiva) {
            $this->conexion->rollback();
            $this->transaccionActiva = false;
        }
    }
    
    public function ejecutarEnTransaccion(callable $callback) {
        $this->iniciarTransaccion();
        
        try {
            $resultado = $callback();
            $this->confirmarTransaccion();
            return $resultado;
        } catch (Exception $e) {
            $this->revertirTransaccion();
            throw $e;
        }
    }
}
```

## 🌐 **Arquitectura de Integración**

### **🔌 Integración con APIs Externas**

#### **Cliente HTTP Genérico**
```php
// clients/HttpClient.php
class HttpClient {
    private $baseUrl;
    private $headers;
    
    public function __construct($baseUrl, $headers = []) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->headers = $headers;
    }
    
    public function get($endpoint, $params = []) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $this->request('GET', $url);
    }
    
    public function post($endpoint, $data = []) {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        return $this->request('POST', $url, $data);
    }
    
    private function request($method, $url, $data = null) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->headers,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $this->headers[] = 'Content-Type: application/json';
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Error cURL: $error");
        }
        
        if ($httpCode >= 400) {
            throw new Exception("HTTP Error: $httpCode");
        }
        
        return json_decode($response, true);
    }
}
```

#### **Cliente de Wompi**
```php
// clients/WompiClient.php
class WompiClient {
    private $httpClient;
    private $publicKey;
    private $privateKey;
    private $environment;
    
    public function __construct($publicKey, $privateKey, $environment = 'sandbox') {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
        $this->environment = $environment;
        
        $baseUrl = $environment === 'production' 
            ? 'https://production.wompi.co/v1'
            : 'https://sandbox.wompi.co/v1';
            
        $this->httpClient = new HttpClient($baseUrl, [
            'Authorization: Bearer ' . $privateKey
        ]);
    }
    
    public function crearTransaccion($datos) {
        return $this->httpClient->post('/transactions', $datos);
    }
    
    public function obtenerTransaccion($id) {
        return $this->httpClient->get("/transactions/$id");
    }
    
    public function validarWebhook($payload, $signature) {
        $expectedSignature = hash_hmac('sha256', $payload, $this->privateKey);
        return hash_equals($expectedSignature, $signature);
    }
}
```

### **📧 Sistema de Correos**

#### **Factory de Correos**
```php
// mail/MailFactory.php
class MailFactory {
    public static function crear($tipo, $datos) {
        switch ($tipo) {
            case 'bienvenida':
                return new BienvenidaMail($datos);
            case 'confirmacion_pago':
                return new ConfirmacionPagoMail($datos);
            case 'recordatorio_pago':
                return new RecordatorioPagoMail($datos);
            case 'notificacion':
                return new NotificacionMail($datos);
            default:
                throw new Exception("Tipo de correo no válido: $tipo");
        }
    }
}

// mail/BienvenidaMail.php
class BienvenidaMail extends BaseMail {
    public function __construct($datos) {
        parent::__construct();
        $this->asunto = 'Bienvenido a Quintanares Residencial';
        $this->destinatario = $datos['email'];
        $this->datos = $datos;
    }
    
    protected function generarContenido() {
        return "
            <h2>¡Bienvenido a Quintanares Residencial!</h2>
            <p>Estimado/a {$this->datos['nombre']},</p>
            <p>Su cuenta ha sido creada exitosamente.</p>
            <p>Credenciales de acceso:</p>
            <ul>
                <li>Email: {$this->datos['email']}</li>
                <li>Contraseña: {$this->datos['password']}</li>
            </ul>
            <p>Por favor, cambie su contraseña en el primer acceso.</p>
        ";
    }
}
```

## 📊 **Arquitectura de Monitoreo**

### **📈 Sistema de Métricas**

#### **Collector de Métricas**
```php
// monitoring/MetricsCollector.php
class MetricsCollector {
    private static $metrics = [];
    
    public static function increment($metric, $value = 1, $tags = []) {
        $key = self::buildKey($metric, $tags);
        self::$metrics[$key] = (self::$metrics[$key] ?? 0) + $value;
    }
    
    public static function gauge($metric, $value, $tags = []) {
        $key = self::buildKey($metric, $tags);
        self::$metrics[$key] = $value;
    }
    
    public static function timer($metric, $callback, $tags = []) {
        $start = microtime(true);
        $result = $callback();
        $duration = microtime(true) - $start;
        
        self::gauge($metric, $duration, $tags);
        return $result;
    }
    
    private static function buildKey($metric, $tags) {
        $key = $metric;
        if (!empty($tags)) {
            $key .= ':' . implode(':', $tags);
        }
        return $key;
    }
    
    public static function getMetrics() {
        return self::$metrics;
    }
    
    public static function exportToPrometheus() {
        $output = '';
        foreach (self::$metrics as $key => $value) {
            $output .= "$key $value\n";
        }
        return $output;
    }
}
```

#### **Middleware de Monitoreo**
```php
// middleware/MonitoringMiddleware.php
class MonitoringMiddleware {
    public static function handle($request, $next) {
        $startTime = microtime(true);
        
        try {
            $response = $next($request);
            
            // Métricas de éxito
            MetricsCollector::increment('http_requests_total', 1, [
                'method' => $request['method'],
                'endpoint' => $request['endpoint'],
                'status' => 'success'
            ]);
            
            return $response;
        } catch (Exception $e) {
            // Métricas de error
            MetricsCollector::increment('http_requests_total', 1, [
                'method' => $request['method'],
                'endpoint' => $request['endpoint'],
                'status' => 'error'
            ]);
            
            throw $e;
        } finally {
            // Métricas de duración
            $duration = microtime(true) - $startTime;
            MetricsCollector::gauge('http_request_duration_seconds', $duration, [
                'method' => $request['method'],
                'endpoint' => $request['endpoint']
            ]);
        }
    }
}
```

### **📝 Sistema de Logging**

#### **Logger Estructurado**
```php
// logging/StructuredLogger.php
class StructuredLogger {
    private $logFile;
    
    public function __construct($logFile = null) {
        $this->logFile = $logFile ?? __DIR__ . '/../logs/app.log';
    }
    
    public function log($level, $message, $context = []) {
        $entry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request_id' => $this->getRequestId(),
            'user_id' => $_SESSION['user_id'] ?? null
        ];
        
        $logLine = json_encode($entry) . "\n";
        file_put_contents($this->logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    public function warning($message, $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    private function getRequestId() {
        if (!isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            $_SERVER['HTTP_X_REQUEST_ID'] = uniqid('req_');
        }
        return $_SERVER['HTTP_X_REQUEST_ID'];
    }
}
```

## 🚀 **Arquitectura de Deployment**

### **🐳 Containerización**

#### **Dockerfile**
```dockerfile
FROM php:7.4-apache

# Instalar extensiones PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Copiar código
COPY . /var/www/html/
WORKDIR /var/www/html

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
```

#### **Docker Compose**
```yaml
version: '3.8'
services:
  app:
    build: .
    ports:
      - "80:80"
    environment:
      - DB_HOST=db
      - DB_USER=parkovisko
      - DB_PASS=password
      - DB_NAME=sistema_vigilancia
    depends_on:
      - db
    volumes:
      - ./logs:/var/www/html/logs

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=sistema_vigilancia
      - MYSQL_USER=parkovisko
      - MYSQL_PASSWORD=password
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"

volumes:
  mysql_data:
```

### **☁️ Arquitectura en la Nube**

#### **Diagrama de Infraestructura**
```
┌─────────────────────────────────────────────────────────────┐
│                        🌐 CDN                              │
│  CloudFlare / AWS CloudFront                               │
├─────────────────────────────────────────────────────────────┤
│                    🔒 Load Balancer                        │
│  Nginx / HAProxy / AWS ALB                                 │
├─────────────────────────────────────────────────────────────┤
│  🖥️ Web Server 1    🖥️ Web Server 2    🖥️ Web Server 3   │
│  Apache/PHP        Apache/PHP        Apache/PHP           │
├─────────────────────────────────────────────────────────────┤
│                    🗄️ Database Cluster                     │
│  MySQL Master/Slave / AWS RDS                             │
├─────────────────────────────────────────────────────────────┤
│                    📁 File Storage                         │
│  AWS S3 / Google Cloud Storage                            │
└─────────────────────────────────────────────────────────────┘
```

## 🔒 **Arquitectura de Seguridad**

### **🛡️ Capas de Seguridad**

#### **1. Capa de Red**
- **Firewall:** Reglas de entrada/salida
- **DDoS Protection:** CloudFlare, AWS Shield
- **VPN:** Acceso seguro a servidores

#### **2. Capa de Aplicación**
- **HTTPS:** Certificados SSL/TLS
- **Headers de Seguridad:** HSTS, CSP, X-Frame-Options
- **Validación de Entrada:** Sanitización y validación
- **Autenticación:** JWT, OAuth2, 2FA

#### **3. Capa de Datos**
- **Encriptación:** AES-256 para datos sensibles
- **Backup Encriptado:** Respaldo seguro
- **Auditoría:** Log de accesos y modificaciones

### **🔐 Implementación de Seguridad**

#### **Middleware de Seguridad**
```php
// middleware/SecurityMiddleware.php
class SecurityMiddleware {
    public static function handle($request, $next) {
        // Validar CSRF token
        if ($request['method'] === 'POST' && !self::validarCSRF($request)) {
            throw new Exception('Token CSRF inválido');
        }
        
        // Validar rate limiting
        if (!self::validarRateLimit($request)) {
            throw new Exception('Límite de requests excedido');
        }
        
        // Validar headers de seguridad
        self::agregarHeadersSeguridad();
        
        return $next($request);
    }
    
    private static function validarCSRF($request) {
        $token = $request['headers']['X-CSRF-Token'] ?? '';
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    private static function validarRateLimit($request) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:$ip";
        
        $requests = Redis::get($key) ?? 0;
        if ($requests > 100) { // 100 requests por minuto
            return false;
        }
        
        Redis::incr($key);
        Redis::expire($key, 60);
        return true;
    }
    
    private static function agregarHeadersSeguridad() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        header('Content-Security-Policy: default-src \'self\'');
    }
}
```

## 📊 **Métricas y KPIs**

### **📈 Métricas de Performance**
- **Tiempo de respuesta:** < 200ms para 95% de requests
- **Throughput:** > 1000 requests/segundo
- **Uptime:** > 99.9%
- **Error rate:** < 0.1%

### **📊 Métricas de Negocio**
- **Usuarios activos:** Diarios, semanales, mensuales
- **Conversión:** Registro a activación
- **Retención:** Usuarios que regresan
- **Satisfacción:** NPS, CSAT

### **🔍 Métricas Técnicas**
- **CPU usage:** < 70%
- **Memory usage:** < 80%
- **Disk I/O:** < 1000 IOPS
- **Database connections:** < 80% del pool

---

**¡Gracias por revisar la arquitectura de Quintanares Residencial!** 🏗️✨










