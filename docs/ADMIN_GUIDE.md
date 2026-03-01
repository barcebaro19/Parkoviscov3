# 🔧 Guía de Administrador - Quintanares Residencial

Esta guía está dirigida a administradores del sistema y cubre todas las funcionalidades avanzadas de gestión.

## 🎯 **Acceso y Permisos**

### **Credenciales de Administrador**
- **Nivel de acceso:** Máximo privilegio
- **Funciones:** Control total del sistema
- **Restricciones:** Ninguna

### **Seguridad del Administrador**
- **Cambiar contraseña** regularmente
- **Usar contraseñas seguras** (mínimo 8 caracteres, mayúsculas, minúsculas, números, símbolos)
- **Cerrar sesión** al terminar
- **No compartir** credenciales

## 🏠 **Dashboard Principal**

### **📊 Métricas del Sistema**

#### **Estadísticas en Tiempo Real**
- **Total de usuarios:** Contador de usuarios registrados
- **Parqueaderos:** Disponibles vs ocupados
- **Vigilantes activos:** Personal de seguridad en turno
- **Propietarios:** Registrados y activos
- **Pagos pendientes:** Montos por cobrar
- **Visitas programadas:** Para el día actual

#### **Gráficos de Actividad**
- **Usuarios por mes:** Crecimiento de registros
- **Ocupación de parqueaderos:** Porcentaje de uso
- **Pagos procesados:** Ingresos por período
- **Reportes de daños:** Incidencias reportadas

### **🚀 Accesos Rápidos**
- **Gestión de Usuarios** - Administrar todos los usuarios
- **Parqueaderos** - Gestionar espacios de parqueo
- **Vigilantes** - Administrar personal de seguridad
- **Propietarios** - Gestionar propietarios
- **Reportes** - Generar reportes del sistema
- **Configuración** - Ajustes del sistema

## 👥 **Gestión Avanzada de Usuarios**

### **📋 Lista de Usuarios**

#### **Filtros y Búsqueda**
- **Por tipo de usuario:** Administrador, Vigilante, Propietario
- **Por estado:** Activo, Inactivo, Suspendido
- **Por fecha de registro:** Rango de fechas
- **Búsqueda por texto:** Nombre, email, celular

#### **Acciones Masivas**
- **Selección múltiple:** Checkbox para seleccionar varios usuarios
- **Envío masivo de correos:** Comunicación a múltiples usuarios
- **Exportación masiva:** Descargar lista en Excel/CSV
- **Cambio de estado masivo:** Activar/desactivar usuarios

### **➕ Crear Usuario**

#### **Formulario Completo**
```php
// Campos obligatorios
- Nombre (mínimo 2 caracteres)
- Apellido (mínimo 2 caracteres)
- Email (formato válido, único)
- Celular (10 dígitos, único)
- Contraseña (mínimo 8 caracteres)
- Tipo de usuario (Administrador, Vigilante, Propietario)
```

#### **Validaciones del Sistema**
- **Email único:** No se permiten duplicados
- **Celular único:** Validación de formato
- **Contraseña segura:** Cumplir políticas de seguridad
- **Campos obligatorios:** Todos deben completarse

#### **Configuraciones Específicas por Rol**

**Administrador:**
- Acceso completo al sistema
- Permisos de gestión total
- Acceso a reportes financieros

**Vigilante:**
- Jornada de trabajo (mañana, tarde, noche)
- Permisos de control de acceso
- Acceso a reportes de seguridad

**Propietario:**
- Información de apartamento (torre, piso, número)
- Asignación de parqueadero
- Permisos de autogestión

### **✏️ Editar Usuario**

#### **Información Editable**
- **Datos personales:** Nombre, apellido, email, celular
- **Credenciales:** Contraseña (con confirmación)
- **Estado:** Activo, Inactivo, Suspendido
- **Configuraciones específicas:** Según el rol

#### **Historial de Cambios**
- **Registro de modificaciones:** Quién, cuándo, qué cambió
- **Backup de datos:** Respaldo antes de cambios
- **Auditoría:** Log de todas las acciones

### **🗑️ Eliminar Usuario**

#### **Proceso de Eliminación**
1. **Verificación de dependencias:** Revisar relaciones con otros datos
2. **Confirmación de eliminación:** Doble confirmación
3. **Eliminación en cascada:** Remover datos relacionados
4. **Registro de eliminación:** Log de la acción

#### **Datos que se Eliminan**
- **Usuario principal:** De la tabla usuarios
- **Relaciones:** De usu_roles
- **Datos específicos:** De propietarios/vigilantes
- **Actividad:** Historial de acciones

### **📧 Sistema de Correos**

#### **Envío Individual**
- **Destinatario:** Usuario específico
- **Asunto:** Título del mensaje
- **Mensaje:** Contenido del correo
- **Adjuntos:** Archivos opcionales
- **Formato:** HTML o texto plano

#### **Envío Masivo**
- **Selección de destinatarios:** Por rol, estado, o manual
- **Plantillas:** Mensajes predefinidos
- **Programación:** Envío diferido
- **Seguimiento:** Estado de entrega

#### **Plantillas de Correo**
```html
<!-- Plantilla de bienvenida -->
<h2>Bienvenido a Quintanares Residencial</h2>
<p>Estimado/a [NOMBRE],</p>
<p>Su cuenta ha sido creada exitosamente.</p>
<p>Credenciales de acceso:</p>
<ul>
    <li>Email: [EMAIL]</li>
    <li>Contraseña: [PASSWORD]</li>
</ul>
```

## 🅿️ **Gestión de Parqueaderos**

### **📊 Vista General**
- **Total de espacios:** Contador de parqueaderos
- **Disponibles:** Espacios libres
- **Ocupados:** Espacios en uso
- **Mantenimiento:** Espacios en reparación

### **➕ Agregar Parqueadero**

#### **Información Requerida**
- **Número:** Identificador único
- **Tipo:** Cubierto, Descubierto, Moto, Bicicleta
- **Estado:** Disponible, Ocupado, Mantenimiento
- **Ubicación:** Zona específica (opcional)
- **Características:** Carga eléctrica, techado, etc.

#### **Validaciones**
- **Número único:** No duplicados
- **Formato válido:** Según convención del conjunto
- **Estado inicial:** Siempre "Disponible"

### **🔗 Asignación de Parqueaderos**

#### **Proceso de Asignación**
1. **Seleccionar parqueadero:** De la lista disponible
2. **Seleccionar propietario:** De la lista de propietarios
3. **Verificar disponibilidad:** Confirmar que esté libre
4. **Confirmar asignación:** Guardar relación
5. **Notificar:** Enviar confirmación al propietario

#### **Reglas de Asignación**
- **Un propietario, un parqueadero:** Relación 1:1
- **Parqueadero único:** No puede estar asignado a múltiples propietarios
- **Validación de propietario:** Debe existir y estar activo

### **📈 Reportes de Parqueaderos**
- **Ocupación por tipo:** Estadísticas por categoría
- **Historial de asignaciones:** Cambios a lo largo del tiempo
- **Espacios disponibles:** Lista actualizada
- **Mantenimientos programados:** Calendario de reparaciones

## 🛡️ **Gestión de Vigilantes**

### **👥 Lista de Vigilantes**

#### **Información Mostrada**
- **Datos personales:** Nombre, apellido, email, celular
- **Información laboral:** Jornada, fecha de ingreso
- **Estado:** Activo, Inactivo, Vacaciones
- **Última actividad:** Fecha de último acceso

#### **Filtros Disponibles**
- **Por jornada:** Mañana, tarde, noche
- **Por estado:** Activo, inactivo
- **Por fecha:** Rango de fechas
- **Búsqueda:** Por nombre o email

### **➕ Registrar Vigilante**

#### **Formulario Completo**
```php
// Información personal
- Nombre (obligatorio)
- Apellido (obligatorio)
- Email (único, obligatorio)
- Celular (único, obligatorio)

// Información laboral
- Jornada (mañana, tarde, noche)
- Fecha de ingreso
- Salario (opcional)
- Observaciones

// Credenciales
- Contraseña temporal
- Confirmar contraseña
```

#### **Validaciones Específicas**
- **Email único:** No puede existir en el sistema
- **Celular único:** Validación de formato colombiano
- **Jornada válida:** Solo opciones predefinidas
- **Contraseña segura:** Mínimo 8 caracteres

### **📊 Gestión Avanzada**

#### **Ver Perfil Completo**
- **Información personal:** Datos completos
- **Historial laboral:** Actividades registradas
- **Reportes generados:** Documentos creados
- **Actividad reciente:** Últimas acciones

#### **Editar Datos**
- **Modificar información:** Personal y laboral
- **Cambiar jornada:** Con validaciones
- **Actualizar credenciales:** Contraseña
- **Cambiar estado:** Activo/inactivo

#### **Eliminar Vigilante**
- **Verificación de dependencias:** Reportes, actividades
- **Confirmación:** Doble confirmación
- **Eliminación en cascada:** Datos relacionados
- **Registro:** Log de eliminación

### **📄 Reportes de Vigilantes**
- **PDF Individual:** Información completa de un vigilante
- **PDF General:** Lista completa de vigilantes
- **Estadísticas:** Por jornada, estado, actividad
- **Exportación:** Excel, CSV

## 🏠 **Gestión de Propietarios**

### **👥 Lista de Propietarios**

#### **Información Detallada**
- **Datos personales:** Nombre, apellido, email, celular
- **Información de propiedad:** Torre, piso, apartamento
- **Parqueadero asignado:** Número y tipo
- **Estado:** Activo, inactivo, moroso
- **Última actividad:** Fecha de último acceso

#### **Filtros Avanzados**
- **Por torre:** Filtrar por edificio
- **Por piso:** Rango de pisos
- **Por estado de pago:** Al día, moroso
- **Por parqueadero:** Con/sin asignación

### **🔍 Funciones Avanzadas**

#### **Ver Perfil Completo**
- **Información personal:** Datos completos
- **Información de propiedad:** Apartamento y parqueadero
- **Vehículos registrados:** Lista de vehículos
- **Historial de pagos:** Transacciones realizadas
- **Visitas programadas:** Historial de visitas
- **Reportes de daños:** Incidencias reportadas
- **Notificaciones:** Mensajes del sistema

#### **Ver Visitantes**
- **Historial de visitas:** Todas las visitas programadas
- **Visitas pendientes:** Por confirmar
- **Visitas completadas:** Historial
- **Estadísticas:** Por período, tipo de visita

#### **Ver Reportes**
- **Reportes de daños:** Incidencias reportadas
- **Estado de reportes:** Pendiente, en proceso, resuelto
- **Seguimiento:** Comentarios y actualizaciones
- **Estadísticas:** Por tipo de daño, frecuencia

#### **Ver Notificaciones**
- **Mensajes enviados:** Historial de notificaciones
- **Estado de entrega:** Leídas, no leídas
- **Tipos de notificación:** Información, advertencia, error
- **Programación:** Notificaciones futuras

#### **Editar Propietario**
- **Modificar datos personales:** Nombre, email, celular
- **Cambiar información de propiedad:** Apartamento
- **Reasignar parqueadero:** Cambiar asignación
- **Actualizar estado:** Activo, inactivo, moroso

#### **Eliminar Propietario**
- **Verificación de dependencias:** Vehículos, pagos, reportes
- **Confirmación:** Doble confirmación
- **Eliminación en cascada:** Datos relacionados
- **Registro:** Log de eliminación

### **📊 Estadísticas de Propietarios**
- **Por torre:** Distribución por edificio
- **Por piso:** Distribución por nivel
- **Estado de pagos:** Al día vs morosos
- **Actividad:** Usuarios activos vs inactivos
- **Reportes:** Incidencias por propietario

## 📊 **Sistema de Reportes**

### **📄 Generación de PDFs**

#### **Reportes Disponibles**
- **Usuarios generales:** Lista completa de usuarios
- **Propietarios:** Información detallada de propietarios
- **Vigilantes:** Datos de personal de seguridad
- **Parqueaderos:** Estado y asignaciones

#### **Características de los PDFs**
- **Diseño profesional:** Logo corporativo, colores institucionales
- **Información completa:** Todos los datos relevantes
- **Formato estándar:** A4, márgenes apropiados
- **Numeración:** Páginas numeradas
- **Fecha de generación:** Timestamp automático

#### **Proceso de Generación**
1. **Seleccionar tipo:** Usuario, propietario, vigilante
2. **Elegir alcance:** Individual o general
3. **Configurar filtros:** Si aplica
4. **Generar PDF:** Proceso automático
5. **Descargar:** Archivo listo para uso

### **📈 Estadísticas del Sistema**

#### **Métricas de Usuarios**
- **Crecimiento mensual:** Nuevos registros
- **Actividad por rol:** Distribución de uso
- **Sesiones activas:** Usuarios conectados
- **Tiempo promedio:** Duración de sesiones

#### **Métricas de Parqueaderos**
- **Ocupación promedio:** Porcentaje de uso
- **Rotación:** Cambios de asignación
- **Mantenimientos:** Frecuencia de reparaciones
- **Satisfacción:** Reportes de problemas

#### **Métricas de Pagos**
- **Ingresos mensuales:** Montos recaudados
- **Métodos de pago:** Distribución por tipo
- **Pagos pendientes:** Montos por cobrar
- **Eficiencia:** Tiempo de procesamiento

## 🔔 **Sistema de Notificaciones**

### **📢 Envío de Notificaciones**

#### **Tipos de Notificación**
- **Información:** Mensajes informativos
- **Advertencia:** Situaciones que requieren atención
- **Error:** Problemas que impiden operación
- **Éxito:** Confirmaciones de acciones

#### **Destinatarios**
- **Individual:** Usuario específico
- **Por rol:** Todos los usuarios de un tipo
- **Masivo:** Múltiples usuarios seleccionados
- **Sistema:** Notificaciones automáticas

#### **Configuración de Notificaciones**
```php
// Estructura de notificación
{
    "tipo": "informacion|advertencia|error|exito",
    "titulo": "Título del mensaje",
    "mensaje": "Contenido detallado",
    "destinatario": "usuario_id|rol|masivo",
    "prioridad": "baja|media|alta",
    "programar": "fecha_hora_opcional"
}
```

### **📊 Gestión de Notificaciones**
- **Historial:** Todas las notificaciones enviadas
- **Estado de entrega:** Leídas, no leídas, fallidas
- **Estadísticas:** Por tipo, destinatario, período
- **Plantillas:** Mensajes predefinidos

## ⚙️ **Configuración del Sistema**

### **🔧 Configuraciones Generales**

#### **Información del Conjunto**
- **Nombre:** Quintanares Residencial
- **Dirección:** Dirección completa
- **Teléfono:** Número de contacto
- **Email:** Correo institucional
- **Logo:** Imagen corporativa

#### **Configuraciones de Seguridad**
- **Política de contraseñas:** Longitud, complejidad
- **Tiempo de sesión:** Duración automática
- **Intentos de login:** Límite de intentos
- **IPs permitidas:** Restricciones de acceso

### **📧 Configuración de Correos**

#### **Servidor SMTP**
```php
// Configuración Gmail
define('GMAIL_USERNAME', 'tu-email@gmail.com');
define('GMAIL_PASSWORD', 'tu-app-password');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('GMAIL_FROM_NAME', 'Quintanares Residencial');
```

#### **Plantillas de Correo**
- **Bienvenida:** Nuevos usuarios
- **Recordatorio de pago:** Pagos pendientes
- **Confirmación:** Acciones realizadas
- **Notificaciones:** Mensajes del sistema

### **💳 Configuración de Pagos**

#### **Integración Wompi**
```php
// Configuración Wompi
'public_key' => 'tu-public-key',
'private_key' => 'tu-private-key',
'environment' => 'sandbox|production',
'webhook_url' => 'https://tu-dominio.com/webhook_wompi.php'
```

#### **Configuraciones de Pago**
- **Métodos permitidos:** Tarjetas, PSE, Nequi, Daviplata
- **Montos:** Valores por defecto
- **Moneda:** COP (Peso colombiano)
- **Webhooks:** URLs de confirmación

## 🔒 **Seguridad y Auditoría**

### **🛡️ Medidas de Seguridad**

#### **Autenticación**
- **Sesiones seguras:** Tokens únicos
- **Contraseñas encriptadas:** Hash seguro
- **Validación de entrada:** Sanitización de datos
- **Protección CSRF:** Tokens de validación

#### **Autorización**
- **Control de acceso:** Por roles y permisos
- **Validación de permisos:** En cada acción
- **Restricciones de IP:** Si es necesario
- **Logs de acceso:** Registro de intentos

### **📋 Auditoría del Sistema**

#### **Logs de Actividad**
- **Acciones de usuarios:** Qué, cuándo, quién
- **Cambios de datos:** Modificaciones realizadas
- **Accesos al sistema:** Login/logout
- **Errores del sistema:** Fallos y excepciones

#### **Reportes de Auditoría**
- **Actividad por usuario:** Historial detallado
- **Cambios en datos:** Modificaciones realizadas
- **Accesos no autorizados:** Intentos fallidos
- **Rendimiento del sistema:** Métricas de uso

## 🚨 **Mantenimiento del Sistema**

### **🔄 Tareas de Mantenimiento**

#### **Diarias**
- **Verificar logs:** Revisar errores
- **Backup de base de datos:** Respaldo automático
- **Monitoreo de espacio:** Disco y memoria
- **Verificar servicios:** Apache, MySQL, PHP

#### **Semanales**
- **Limpieza de logs:** Archivos antiguos
- **Optimización de base de datos:** Consultas lentas
- **Actualización de estadísticas:** Métricas del sistema
- **Verificación de seguridad:** Vulnerabilidades

#### **Mensuales**
- **Actualización del sistema:** Parches de seguridad
- **Revisión de permisos:** Usuarios y archivos
- **Análisis de rendimiento:** Optimizaciones
- **Planificación de mejoras:** Nuevas funcionalidades

### **📊 Monitoreo del Sistema**

#### **Métricas de Rendimiento**
- **Tiempo de respuesta:** Páginas y APIs
- **Uso de memoria:** PHP y MySQL
- **Espacio en disco:** Archivos y base de datos
- **Conexiones activas:** Base de datos

#### **Alertas del Sistema**
- **Espacio en disco:** Menos del 20%
- **Memoria alta:** Más del 80%
- **Errores frecuentes:** Más de 10 por hora
- **Conexiones fallidas:** Base de datos

## 📞 **Soporte Técnico**

### **🆘 Escalación de Problemas**

#### **Nivel 1 - Soporte Básico**
- **Problemas de usuario:** Login, navegación
- **Consultas generales:** Funcionalidades básicas
- **Solución de problemas:** Guías y FAQ

#### **Nivel 2 - Soporte Técnico**
- **Problemas del sistema:** Errores técnicos
- **Configuraciones:** Ajustes avanzados
- **Integraciones:** APIs y servicios externos

#### **Nivel 3 - Soporte Especializado**
- **Problemas críticos:** Fallos del sistema
- **Desarrollo:** Nuevas funcionalidades
- **Arquitectura:** Cambios estructurales

### **📧 Contacto de Soporte**
- **Email:** soporte@parkovisco.com
- **Teléfono:** +57 300 123 4567
- **Horario:** Lunes a Viernes 8:00 AM - 6:00 PM
- **Emergencias:** 24/7 para problemas críticos

---

**¡Gracias por administrar el sistema de Quintanares Residencial!** 🏢🔧
