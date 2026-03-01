# 🏢 Sistema de Gestión Residencial - Parkovisco

## 📋 Descripción
Sistema completo de gestión para conjuntos residenciales con funcionalidades de administración, vigilancia y gestión de propietarios.

## 🚀 Características Principales

### 🔐 Sistema de Autenticación
- **Login inteligente** que detecta automáticamente el tipo de usuario
- **Soporte para múltiples tipos de hash** (bcrypt, MD5, texto plano)
- **Búsqueda en múltiples tablas** (vigilantes, propietarios, usuarios legacy)
- **Redirección automática** según el rol del usuario

### 👥 Gestión de Usuarios
- **Administradores**: Acceso completo al sistema
- **Vigilantes**: Gestión de seguridad y acceso
- **Propietarios**: Dashboard personalizado con información de apartamento

### 🏠 Gestión de Propietarios
- **Registro completo** con datos de apartamento
- **Búsqueda y filtrado** avanzado
- **Estadísticas** de parqueaderos asignados
- **Gestión de estados** (activo/inactivo)

### 👮 Gestión de Vigilantes
- **Registro con jornada** (Diurna/Nocturna/Mixta)
- **Búsqueda y listado** completo
- **Dashboard específico** para vigilantes
- **Gestión de estados** (activo/inactivo)

### 🅿️ Gestión de Parqueaderos
- **Visualización en tiempo real** del estado de espacios
- **Métricas de ocupación** y disponibilidad
- **Búsqueda de espacios** por número o ocupante
- **Estados**: Disponible, Ocupado, Reservado

## 📁 Estructura del Proyecto

```
parkovisko/
├── app/
│   ├── Controllers/          # Controladores de lógica de negocio
│   │   ├── validar_login.php        # Sistema de autenticación mejorado
│   │   ├── buscar_propietarios.php  # Búsqueda de propietarios
│   │   ├── buscar_vigilantes.php    # Búsqueda de vigilantes
│   │   ├── buscar_usuario.php       # Búsqueda de usuarios
│   │   ├── registrar_vigilante.php  # Registro de vigilantes
│   │   └── ...
│   ├── Models/
│   │   └── conexion.php             # Conexión a base de datos
│   └── Services/
│       └── GrafanaMetricsService.php # Servicios de métricas
├── public/                   # Archivos públicos y páginas web
│   ├── login.php                    # Página de login
│   ├── Administrador1.php           # Dashboard de administrador
│   ├── vigilante.php                # Dashboard de vigilante
│   ├── propietario_dashboard.php    # Dashboard de propietario
│   ├── gestion_vigilantes.php       # Gestión de vigilantes
│   ├── gestion_propietarios.php     # Gestión de propietarios
│   ├── tablausu.php                 # Gestión de usuarios
│   ├── parqueaderos.php             # Gestión de parqueaderos
│   ├── registrarusu.php             # Registro de propietarios
│   └── components/
│       └── footer.php               # Componente de footer
├── database/
│   ├── migrations/                  # Scripts de migración
│   │   ├── clean_vigilantes_table.sql
│   │   └── remove_usuarios_id_propietarios.sql
│   └── sistema vigilancia (1).sql   # Estructura de base de datos
├── config/                   # Archivos de configuración
│   ├── grafana_config.php
│   ├── gmail_config.php
│   └── wompi_config.php
└── docs/                     # Documentación
    ├── FEATURES.md
    ├── ARCHITECTURE.md
    └── ...
```

## 🗃️ Base de Datos

### Tablas Principales
- **`usuarios`**: Información básica de usuarios
- **`roles`**: Roles del sistema (administrador, vigilante, propietario)
- **`usu_roles`**: Relación usuarios-roles
- **`vigilantes`**: Información específica de vigilantes
- **`propietarios`**: Información específica de propietarios
- **`parqueadero`**: Espacios de parqueo
- **`vehiculos`**: Información de vehículos
- **`reservas`**: Reservas de parqueaderos

### Características de Seguridad
- **Contraseñas hasheadas** con bcrypt para usuarios nuevos
- **Compatibilidad legacy** con MD5 y texto plano
- **Verificación de sesiones** en todas las páginas
- **Manejo de errores** robusto

## 🚀 Instalación y Configuración

### Requisitos
- PHP 7.4+
- MySQL/MariaDB
- Servidor web (Apache/Nginx)

### Pasos de Instalación
1. **Clonar el proyecto** en el directorio del servidor web
2. **Configurar la base de datos** ejecutando `sistema vigilancia (1).sql`
3. **Ejecutar migraciones** si es necesario:
   - `clean_vigilantes_table.sql`
   - `remove_usuarios_id_propietarios.sql`
4. **Configurar conexión** en `app/Models/conexion.php`
5. **Configurar servicios** en la carpeta `config/`

### Acceso al Sistema
- **URL**: `http://localhost/ci4-parkovisko/public/login.php`
- **Credenciales por defecto**: Verificar en la base de datos

## 🔧 Funcionalidades por Rol

### 👨‍💼 Administrador
- Dashboard completo con métricas
- Gestión de usuarios, vigilantes y propietarios
- Gestión de parqueaderos
- Reportes y estadísticas
- Integración con Grafana

### 👮 Vigilante
- Dashboard específico de vigilancia
- Gestión de accesos
- Reportes de seguridad
- Información de jornada

### 🏠 Propietario
- Dashboard personalizado
- Información de apartamento
- Gestión de parqueaderos asignados
- Historial de pagos

## 📊 Métricas y Reportes
- **Tiempo real**: Estado de parqueaderos y ocupación
- **Estadísticas**: Usuarios por rol, actividad, ingresos
- **Grafana**: Integración para métricas avanzadas
- **Exportación**: Datos en múltiples formatos

## 🛡️ Seguridad
- **Autenticación robusta** con múltiples métodos
- **Verificación de sesiones** en todas las páginas
- **Manejo seguro de contraseñas**
- **Validación de entrada** en todos los formularios
- **Prepared statements** para consultas SQL

## 🔄 Actualizaciones Recientes
- ✅ Sistema de login mejorado con detección automática de usuarios
- ✅ Tablas de vigilantes y propietarios optimizadas
- ✅ Controladores actualizados para nueva estructura
- ✅ Manejo de errores mejorado
- ✅ Compatibilidad con contraseñas hash seguras

## 📞 Soporte
Para soporte técnico o consultas sobre el sistema, contactar al equipo de desarrollo.

---
**Versión**: 2.0  
**Última actualización**: Septiembre 2025  
**Estado**: ✅ Producción
