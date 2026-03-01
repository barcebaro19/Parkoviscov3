# 📦 Guía de Instalación - Quintanares Residencial

Esta guía te llevará paso a paso para instalar y configurar el sistema de gestión de Quintanares Residencial.

## 📋 **Requisitos del Sistema**

### **Servidor Web**
- **Apache 2.4+** o **Nginx 1.18+**
- **PHP 7.4** o superior (recomendado PHP 8.0+)
- **MySQL 5.7+** o **MariaDB 10.3+**

### **Extensiones PHP Requeridas**
```bash
# Verificar extensiones instaladas
php -m | grep -E "(mysqli|curl|openssl|json|mbstring|zip)"
```

**Extensiones necesarias:**
- `mysqli` - Conexión a base de datos
- `curl` - Comunicación con APIs externas
- `openssl` - Encriptación y seguridad
- `json` - Manejo de datos JSON
- `mbstring` - Manejo de caracteres multibyte
- `zip` - Compresión de archivos
- `gd` - Manipulación de imágenes
- `fileinfo` - Detección de tipos de archivo

### **Herramientas Adicionales**
- **Composer** - Gestión de dependencias PHP
- **Git** - Control de versiones (opcional)

## 🚀 **Instalación Paso a Paso**

### **Paso 1: Preparar el Entorno**

#### **En Windows (XAMPP/WAMP)**
1. **Descargar XAMPP** desde [apachefriends.org](https://www.apachefriends.org/)
2. **Instalar XAMPP** con Apache, MySQL y PHP
3. **Iniciar servicios** desde el panel de control
4. **Verificar instalación:**
   ```bash
   # Abrir terminal en la carpeta del proyecto
   php -v
   mysql --version
   ```

#### **En Linux (Ubuntu/Debian)**
```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, PHP y MySQL
sudo apt install apache2 php7.4 php7.4-mysql php7.4-curl php7.4-json php7.4-mbstring php7.4-zip php7.4-gd php7.4-fileinfo mysql-server composer -y

# Habilitar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### **En macOS (Homebrew)**
```bash
# Instalar Homebrew si no está instalado
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Instalar dependencias
brew install php mysql composer
brew services start mysql
```

### **Paso 2: Clonar/Descargar el Proyecto**

#### **Opción A: Descarga Directa**
1. **Descargar** el archivo ZIP del proyecto
2. **Extraer** en la carpeta del servidor web:
   - **XAMPP:** `C:\xampp\htdocs\ci4-parkovisko`
   - **Linux:** `/var/www/html/ci4-parkovisko`
   - **macOS:** `/usr/local/var/www/ci4-parkovisko`

#### **Opción B: Git Clone**
```bash
# Clonar repositorio
git clone [repository-url] parkovisko
cd parkovisko
```

### **Paso 3: Instalar Dependencias**

```bash
# Navegar al directorio del proyecto
cd parkovisko

# Instalar dependencias con Composer
composer install

# Verificar instalación
composer show
```

**Dependencias que se instalarán:**
- `tecnickcom/tcpdf` - Generación de PDFs
- `phpmailer/phpmailer` - Envío de correos

### **Paso 4: Configurar Base de Datos**

#### **Crear Base de Datos**
```sql
-- Conectar a MySQL
mysql -u root -p

-- Crear base de datos
CREATE DATABASE sistema_vigilancia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Crear usuario específico (recomendado)
CREATE USER 'parkovisko_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON sistema_vigilancia.* TO 'parkovisko_user'@'localhost';
FLUSH PRIVILEGES;

-- Seleccionar base de datos
USE sistema_vigilancia;
```

#### **Importar Estructura**
```sql
-- Importar estructura de base de datos
SOURCE sistema\ vigilancia\ \(1\).sql;

-- Verificar tablas creadas
SHOW TABLES;
```

**Tablas que se crearán:**
- `usuarios` - Información de usuarios
- `roles` - Tipos de roles del sistema
- `usu_roles` - Relación usuarios-roles
- `propietarios` - Información de propietarios
- `vigilantes` - Información de vigilantes
- `parqueadero` - Espacios de parqueo
- `vehiculos` - Vehículos de propietarios
- `visitas` - Registro de visitas
- `danos` - Reportes de daños
- `notificaciones` - Sistema de notificaciones
- `pagos` - Registro de pagos

### **Paso 5: Configurar Archivos del Sistema**

#### **Configurar Conexión a Base de Datos**
Editar `models/conexion.php`:
```php
<?php
class Conexion {
    private static $instancia = null;
    private $conexion;
    
    private function __construct() {
        $this->conexion = new mysqli(
            'localhost',           // Host
            'parkovisko_user',     // Usuario
            'tu_password_seguro',  // Contraseña
            'sistema_vigilancia',  // Base de datos
            3306                   // Puerto
        );
        
        if ($this->conexion->connect_error) {
            die('Error de conexión: ' . $this->conexion->connect_error);
        }
        
        $this->conexion->set_charset('utf8mb4');
    }
    
    // ... resto del código
}
```

#### **Configurar Correo Electrónico**
Editar `config/gmail_config.php`:
```php
<?php
// Configuración de Gmail para PHPMailer
define('GMAIL_USERNAME', 'tu-email@gmail.com');
define('GMAIL_PASSWORD', 'tu-app-password'); // Contraseña de aplicación
define('GMAIL_FROM_NAME', 'Quintanares Residencial');

// Configuración SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
```

**Configurar Gmail:**
1. **Habilitar 2FA** en tu cuenta de Gmail
2. **Generar contraseña de aplicación:**
   - Ir a Configuración de Google
   - Seguridad → Contraseñas de aplicaciones
   - Generar nueva contraseña
   - Usar esta contraseña en `GMAIL_PASSWORD`

#### **Configurar Sistema de Pagos (Opcional)**
Editar `config/wompi_config.php`:
```php
<?php
return [
    'environment' => 'sandbox', // 'sandbox' para pruebas, 'production' para producción
    
    'public_key' => 'tu-public-key-de-wompi',
    'private_key' => 'tu-private-key-de-wompi',
    
    'urls' => [
        'sandbox' => 'https://sandbox.wompi.co/v1',
        'production' => 'https://production.wompi.co/v1'
    ]
];
```

### **Paso 6: Configurar Servidor Web**

#### **Apache (.htaccess)**
Crear archivo `.htaccess` en la raíz del proyecto:
```apache
RewriteEngine On

# Redirigir a HTTPS (opcional)
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Manejar rutas amigables
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Configuraciones de seguridad
<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>

# Configuraciones de PHP
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
```

#### **Nginx**
Configurar virtual host en Nginx:
```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/html/ci4-parkovisko/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Seguridad
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(sql|log)$ {
        deny all;
    }
}
```

### **Paso 7: Configurar Permisos**

#### **Linux/macOS**
```bash
# Configurar permisos de archivos
sudo chown -R www-data:www-data /var/www/html/ci4-parkovisko
sudo chmod -R 755 /var/www/html/ci4-parkovisko
sudo chmod -R 777 /var/www/html/ci4-parkovisko/writable/logs
sudo chmod -R 777 /var/www/html/ci4-parkovisko/vendor/tecnickcom/tcpdf/cache
```

#### **Windows**
- **Clic derecho** en la carpeta del proyecto
- **Propiedades** → **Seguridad**
- **Agregar** usuario `IIS_IUSRS` o `Everyone`
- **Permisos completos** para lectura/escritura

### **Paso 8: Verificar Instalación**

#### **Pruebas Básicas**
1. **Acceder al sistema:**
   ```
   http://localhost/ci4-parkovisko/public/
   ```

2. **Verificar página de inicio:**
   - Debe mostrar la página de login
   - Sin errores de PHP
   - Estilos CSS cargando correctamente

3. **Probar conexión a base de datos:**
   - Intentar hacer login
   - Verificar que no hay errores de conexión

#### **Credenciales por Defecto**
```sql
-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, apellido, email, celular) VALUES ('Admin', 'Sistema', 'admin@quintanares.com', '3001234567');
INSERT INTO usu_roles (usuarios_id, roles_idroles, contraseña) VALUES (1, 1, 'admin123');
```

**Credenciales de prueba:**
- **Email:** admin@quintanares.com
- **Contraseña:** admin123

### **Paso 9: Configuración de Producción**

#### **Optimizaciones de Seguridad**
1. **Cambiar credenciales por defecto**
2. **Configurar HTTPS**
3. **Ocultar información del servidor**
4. **Configurar firewall**
5. **Hacer backup regular de la base de datos**

#### **Optimizaciones de Rendimiento**
1. **Habilitar caché de PHP**
2. **Configurar compresión gzip**
3. **Optimizar consultas de base de datos**
4. **Configurar CDN para assets estáticos**

## 🔧 **Solución de Problemas**

### **Error: "Class 'mysqli' not found"**
```bash
# Instalar extensión mysqli
sudo apt install php7.4-mysqli  # Ubuntu/Debian
brew install php@7.4            # macOS
```

### **Error: "Composer not found"**
```bash
# Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### **Error: "Permission denied"**
```bash
# Configurar permisos correctos
sudo chown -R www-data:www-data /var/www/html/ci4-parkovisko
sudo chmod -R 755 /var/www/html/ci4-parkovisko
```

### **Error: "Database connection failed"**
1. **Verificar** que MySQL esté ejecutándose
2. **Verificar** credenciales en `models/conexion.php`
3. **Verificar** que la base de datos existe
4. **Verificar** permisos del usuario de base de datos

### **Error: "Email not sending"**
1. **Verificar** configuración de Gmail
2. **Verificar** contraseña de aplicación
3. **Verificar** que PHPMailer esté instalado
4. **Verificar** logs en `logs/email_log.txt`

### **Error: "PDF not generating"**
1. **Verificar** que TCPDF esté instalado
2. **Verificar** permisos de escritura
3. **Verificar** que la carpeta `vendor/tecnickcom/tcpdf/cache` existe

## 📞 **Soporte**

Si encuentras problemas durante la instalación:

1. **Revisar logs** del servidor web
2. **Revisar logs** de PHP
3. **Revisar logs** de MySQL
4. **Contactar soporte:** soporte@parkovisco.com

## ✅ **Verificación Final**

Después de completar la instalación, verifica que:

- [ ] ✅ Página de inicio carga sin errores
- [ ] ✅ Login funciona correctamente
- [ ] ✅ Dashboard de administrador es accesible
- [ ] ✅ Base de datos conecta correctamente
- [ ] ✅ Correos se envían (opcional)
- [ ] ✅ PDFs se generan correctamente
- [ ] ✅ Sistema de pagos funciona (opcional)
- [ ] ✅ Todas las funcionalidades principales operan

**¡Felicidades! El sistema está listo para usar.** 🎉
