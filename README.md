# 🏢 Quintanares Residencial - Sistema de Parqueaderos (CodeIgniter 4)

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange.svg)](https://mysql.com)
[![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.4.8-red.svg)](https://codeigniter.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Refactored%20%26%20Optimized-brightgreen.svg)]()

> **Sistema de gestión de parqueaderos migrado a CodeIgniter 4** con arquitectura MVC moderna, autenticación por roles y seguridad mejorada.

## 🎯 **Descripción del Proyecto**

**Quintanares Residencial** es un sistema web refactorizado y optimizado para la gestión de parqueaderos en conjuntos residenciales. Migrado desde PHP tradicional a CodeIgniter 4 para mejor mantenibilidad y seguridad.

### 🏗️ **Reestructurado por Parkovisko**
- **Empresa:** Parkovisko
- **Cliente:** Quintanares Residencial
- **Versión:** 2.0.0 (CodeIgniter 4)
- **Año:** 2025
- **Framework:** CodeIgniter 4.4.8

## ✨ **Características Principales**

### 🔐 **Sistema de Autenticación**
- **Tres roles principales**: Administrador, Vigilante, Propietario
- **Login seguro** con hash de contraseñas
- **Filtros de protección** de rutas por rol
- **Sesiones seguras** con timeout

### 🎨 **Interfaz Moderna**
- **Bootstrap 5** con diseño responsive
- **Sidebar dinámico** según rol
- **Dashboard con estadísticas** en tiempo real
- **Alertas y notificaciones** automáticas

### 🗄️ **Base de Datos Optimizada**
- **Estructura relacional** con foreign keys
- **Migraciones automáticas** con CodeIgniter
- **Script de inicialización** con datos de prueba
- **MySQL 5.7+** con charset UTF-8

### 🛡️ **Seguridad Mejorada**
- **CSRF Protection** habilitado
- **Input validation** automático
- **SQL injection prevention** con Query Builder
- **XSS protection** nativo

## 🚀 **Instalación Rápida**

### 1. **Requisitos Previos**
- PHP 8.0 o superior
- MySQL 5.7+
- Apache/Nginx
- Composer instalado

### 2. **Instalación del Sistema**
```bash
# Navegar al directorio del proyecto
cd c:\xampp\htdocs\ci4-parkovisko

# Instalar dependencias de Composer
composer install

# Inicializar base de datos
php database_seed.php

# Iniciar servidor de desarrollo
php spark serve
```

### 3. **Acceso al Sistema**
- **URL**: `http://localhost:8080`
- **Login**: Usar credenciales de prueba

## 👥 **Usuarios de Prueba**

| Rol | Cédula | Contraseña | Panel de Acceso |
|-----|---------|------------|-----------------|
| 🔧 Administrador | 1 | admin123 | Panel completo |
| 👮 Vigilante | 2 | vigilante123 | Control de acceso |
| 👤 Propietario | 3 | propietario123 | Gestión personal |

## 📁 **Estructura del Proyecto (CodeIgniter 4)**

### 👥 **Gestión de Usuarios**
- **Administradores:** Control total del sistema
- **Vigilantes:** Gestión de seguridad y acceso
- **Propietarios:** Autogestión y pagos
- **Registro público** para nuevos propietarios
- **Sistema de roles** y permisos

### 🅿️ **Administración de Parqueaderos**
- **Gestión completa** de espacios de parqueo
- **Asignación automática** a propietarios
- **Control de disponibilidad**
- **Reportes de ocupación**

### 🛡️ **Sistema de Vigilancia**
- **Dashboard especializado** para vigilantes
- **Control de acceso** y visitantes
- **Registro de incidentes**
- **Comunicación con administración**

### 💳 **Sistema de Pagos Integrado**
- **Integración con Wompi** (Gateway de pagos Colombia)
- **Múltiples métodos:** Tarjetas, PSE, Nequi, Daviplata
- **Pagos automáticos** y recordatorios
- **Recibos PDF** descargables
- **Webhooks seguros** para confirmación

### 📊 **Reportes y Estadísticas**
- **PDFs profesionales** con logo corporativo
- **Reportes por usuario** y generales
- **Estadísticas de actividad**
- **Exportación de datos**

### 🔔 **Sistema de Notificaciones**
- **Notificaciones en tiempo real** con diseño cyberpunk
- **Correos electrónicos** automáticos
- **Alertas del sistema**
- **Confirmaciones de acciones**

### 🎨 **Interfaz Moderna**
- **Diseño cyberpunk** profesional
- **Responsive design** para todos los dispositivos
- **Animaciones fluidas** y efectos visuales
- **Tema oscuro** con colores corporativos

## 🛠️ **Tecnologías Utilizadas**

### **Backend**
- **PHP 7.4+** - Lenguaje principal
- **MySQL/MariaDB** - Base de datos
- **PHPMailer** - Envío de correos
- **TCPDF** - Generación de PDFs

### **Frontend**
- **HTML5** - Estructura semántica
- **CSS3** - Estilos avanzados
- **JavaScript ES6+** - Interactividad
- **Tailwind CSS** - Framework de estilos
- **Alpine.js** - Reactividad
- **Font Awesome** - Iconografía

### **Integraciones**
- **Wompi API** - Pagos en línea
- **Gmail SMTP** - Correos electrónicos
- **Composer** - Gestión de dependencias

## 🚀 **Instalación Rápida**

### **Prerrequisitos**
- PHP 7.4 o superior
- MySQL 5.7 o MariaDB 10.3+
- Servidor web (Apache/Nginx)
- Composer
- Extensiones PHP: `mysqli`, `curl`, `openssl`

### **Pasos de Instalación**

1. **Clonar el proyecto**
   ```bash
   git clone [repository-url]
   cd parkovisko
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar base de datos**
   ```sql
   mysql -u root -p
   CREATE DATABASE sistema_vigilancia;
   USE sistema_vigilancia;
   SOURCE sistema\ vigilancia\ \(1\).sql;
   ```

4. **Configurar archivos**
   - Editar `config/gmail_config.php`
   - Editar `config/wompi_config.php`
   - Configurar permisos de archivos

5. **Configurar servidor web**
   - Apache: Habilitar mod_rewrite
   - Nginx: Configurar rewrite rules

6. **Probar instalación**
   - Acceder a `http://localhost/ci4-parkovisko/public/`
   - Verificar que todas las funcionalidades trabajen

> 📖 **Para instalación detallada:** Ver [docs/INSTALLATION.md](docs/INSTALLATION.md)

## 👥 **Roles del Sistema**

### 🔧 **Administrador**
- **Acceso:** Dashboard completo
- **Funciones:**
  - Gestión de usuarios (crear, editar, eliminar)
  - Administración de parqueaderos
  - Gestión de vigilantes y propietarios
  - Generación de reportes
  - Configuración del sistema
  - Envío de correos masivos

### 🛡️ **Vigilante**
- **Acceso:** Dashboard de vigilancia
- **Funciones:**
  - Control de acceso
  - Registro de visitantes
  - Reportes de seguridad
  - Comunicación con administración
  - Validación de códigos QR

### 🏠 **Propietario**
- **Acceso:** Dashboard personal
- **Funciones:**
  - Gestión de vehículos
  - Programación de visitas
  - Realización de pagos
  - Reportes de daños
  - Notificaciones del sistema

## 📱 **Capturas de Pantalla**

### **Dashboard Administrador**
![Dashboard Admin](docs/screenshots/admin-dashboard.png)

### **Sistema de Pagos**
![Sistema de Pagos](docs/screenshots/payment-system.png)

### **Gestión de Usuarios**
![Gestión de Usuarios](docs/screenshots/user-management.png)

## 🔧 **Configuración**

### **Variables de Entorno**
```php
// config/gmail_config.php
define('GMAIL_USERNAME', 'tu-email@gmail.com');
define('GMAIL_PASSWORD', 'tu-app-password');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);

// config/wompi_config.php
'public_key' => 'tu-public-key',
'private_key' => 'tu-private-key',
'environment' => 'sandbox', // o 'production'
```

### **Base de Datos**
- **Host:** localhost
- **Puerto:** 3306
- **Base de datos:** sistema_vigilancia
- **Charset:** utf8mb4

## 📚 **Documentación**

- 📖 [Guía de Instalación](docs/INSTALLATION.md)
- 👥 [Guía de Usuario](docs/USER_GUIDE.md)
- 🔧 [Guía de Administrador](docs/ADMIN_GUIDE.md)
- 🔌 [Documentación de API](docs/API.md)
- 🗄️ [Estructura de Base de Datos](docs/DATABASE.md)
- 🏗️ [Arquitectura del Sistema](docs/ARCHITECTURE.md)
- 💳 [Sistema de Pagos](SISTEMA_PAGOS_README.md)
- 🛠️ [Guía de Desarrollo](docs/DEVELOPMENT.md)

## 🚀 **Uso Rápido**

### **Acceso al Sistema**
1. **URL:** `http://localhost/ci4-parkovisko/public/`
2. **Credenciales por defecto:**
   - **Admin:** admin@quintanares.com / admin123
   - **Vigilante:** vigilante@quintanares.com / vigilante123
   - **Propietario:** propietario@quintanares.com / propietario123

### **Primeros Pasos**
1. **Iniciar sesión** como administrador
2. **Configurar parqueaderos** en la sección correspondiente
3. **Registrar vigilantes** desde el dashboard
4. **Configurar sistema de pagos** con credenciales de Wompi
5. **Probar funcionalidades** de cada rol

## 🔒 **Seguridad**

- **Autenticación** basada en sesiones
- **Validación** de entrada en todos los formularios
- **Prepared statements** para consultas SQL
- **Encriptación** de contraseñas
- **HTTPS** recomendado para producción
- **Webhooks seguros** con firmas de verificación

## 🐛 **Solución de Problemas**

### **Problemas Comunes**

**Error de conexión a base de datos:**
```bash
# Verificar que MySQL esté ejecutándose
sudo systemctl status mysql
# Verificar credenciales en models/conexion.php
```

**Correos no se envían:**
```bash
# Verificar configuración de Gmail
# Habilitar "Acceso de aplicaciones menos seguras"
# Usar contraseña de aplicación
```

**PDFs no se generan:**
```bash
# Verificar permisos de escritura
chmod 755 vendor/tecnickcom/tcpdf/
# Verificar que TCPDF esté instalado
composer show tecnickcom/tcpdf
```

## 🤝 **Contribución**

1. **Fork** el proyecto
2. **Crear** una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. **Abrir** un Pull Request

## 📄 **Licencia**

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 👨‍💻 **Desarrollado por**

**Parkovisco**
- **Email:** info@parkovisco.com
- **Website:** [www.parkovisco.com](https://www.parkovisco.com)
- **GitHub:** [@parkovisco](https://github.com/parkovisco)

## 📞 **Soporte**

- **Email:** soporte@parkovisco.com
- **Documentación:** [docs/](docs/)
- **Issues:** [GitHub Issues](https://github.com/parkovisco/quintanares/issues)

## 🎉 **Agradecimientos**

- **Quintanares Residencial** por la confianza
- **Comunidad PHP** por las librerías utilizadas
- **Wompi** por la integración de pagos
- **Tailwind CSS** por el framework de estilos

---

<div align="center">

**⭐ Si este proyecto te ha sido útil, ¡dale una estrella! ⭐**

[![GitHub stars](https://img.shields.io/github/stars/parkovisco/quintanares.svg?style=social&label=Star)](https://github.com/parkovisco/quintanares)
[![GitHub forks](https://img.shields.io/github/forks/parkovisco/quintanares.svg?style=social&label=Fork)](https://github.com/parkovisco/quintanares/fork)

</div>
