# Sistema de Gestión de Visitantes

## 📋 Descripción General

El sistema de visitantes permite a los propietarios generar códigos QR para autorizar el acceso de visitantes al conjunto residencial. Los datos se almacenan en dos tablas principales: `visitantes` (datos personales) y `reservas` (información de la visita).

## 🗃️ Estructura de Base de Datos

### Tabla `visitantes`
- `id_visit` (PK) - ID único del visitante
- `nombre_visitante` - Nombre completo
- `documento` (UNIQUE) - Documento de identidad
- `telefono` - Número de contacto
- `fecha_registro` - Cuándo se registró por primera vez
- `estado` - Activo/Inactivo

### Tabla `reservas`
- `id_reser` (PK) - ID único de la reserva/visita
- `fecha_inicial` - Cuándo inicia la visita
- `fecha_final` - Cuándo termina la visita
- `propietarios_id_pro` (FK) - Propietario que autoriza
- `visitante_id_visit` (FK) - Visitante autorizado
- `parqueadero_id_parq` (FK) - Parqueadero asignado
- `motivo_visita` - Razón de la visita
- `codigo_qr` (UNIQUE) - Código QR único
- `estado_qr` - Estado del código (activo/usado/expirado)
- `fecha_generacion` - Cuándo se generó el QR
- `observaciones` - Notas adicionales

## 🔄 Flujo de Trabajo

### 1. Generación de QR para Visitante
1. **Usuario llena formulario** con datos del visitante
2. **Sistema verifica** si el visitante ya existe por documento
3. **Si no existe**: Crea nuevo registro en `visitantes`
4. **Si existe**: Actualiza datos del visitante
5. **Genera código QR único** y lo guarda en `reservas`
6. **Crea imagen QR** y la guarda localmente
7. **Muestra QR** al usuario para compartir

### 2. Validación de QR
1. **Vigilante escanea QR** en el sistema de control
2. **Sistema valida** el código en la base de datos
3. **Verifica estado** (activo/usado/expirado)
4. **Verifica vigencia** (fecha_final)
5. **Muestra información** del visitante y propietario
6. **Marca como usado** si es necesario

## 📁 Archivos del Sistema

### Controladores
- `visitantes_controller.php` - Lógica principal del sistema
- `generar_qr.php` - Endpoint para generar códigos QR
- `validar_qr.php` - Endpoint para validar códigos QR

### Utilidades
- `QRCode.php` - Clase para generar imágenes QR
- `test_visitantes_system.php` - Script de pruebas

### Base de Datos
- `update_visitantes_reservas_tables.sql` - Modificaciones de tablas
- `verify_visitantes_reservas_structure.sql` - Verificación de estructura
- `rollback_visitantes_reservas_tables.sql` - Reversión de cambios

## 🚀 API Endpoints

### POST `/app/Controllers/generar_qr.php`
Genera código QR para visitante o vehículo.

**Parámetros:**
```json
{
    "tipo": "visitante",
    "nombre": "Juan Pérez",
    "documento": "12345678",
    "telefono": "3123456789",
    "motivo": "Visita familiar",
    "validez": "2025-01-30 18:00:00",
    "observaciones": "Notas adicionales"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Código QR generado exitosamente",
    "qr_data": {...},
    "qr_code": "QR123456789",
    "qr_image_url": "/path/to/qr_image.png",
    "valid_until": "2025-01-30 18:00:00"
}
```

### POST `/app/Controllers/validar_qr.php`
Valida código QR escaneado.

**Parámetros:**
```json
{
    "codigo": "QR123456789",
    "action": "validar" // o "usar" o "desactivar"
}
```

**Respuesta:**
```json
{
    "valido": true,
    "datos": {
        "nombre_visitante": "Juan Pérez",
        "documento": "12345678",
        "propietario_nombre": "Carlos Propietario",
        "motivo_visita": "Visita familiar",
        "fecha_final": "2025-01-30 18:00:00"
    }
}
```

## 🔧 Configuración

### 1. Base de Datos
Ejecutar los scripts SQL en orden:
1. `update_visitantes_reservas_tables.sql`
2. `verify_visitantes_reservas_structure.sql`

### 2. Permisos de Archivos
```bash
chmod 755 storage/qr_images/
chmod 644 storage/qr_images/.htaccess
```

### 3. Pruebas
```bash
php test_visitantes_system.php
```

## 🛡️ Seguridad

- **Validación de sesión**: Solo usuarios autenticados pueden generar QR
- **Códigos únicos**: Cada QR tiene un código único no predecible
- **Vigencia temporal**: Los QR expiran automáticamente
- **Protección de archivos**: Directorio de imágenes protegido
- **Validación de datos**: Todos los inputs son validados

## 📊 Monitoreo

### Logs
- Errores de generación de QR
- Intentos de validación fallidos
- Accesos a archivos protegidos

### Métricas
- Número de visitantes registrados
- Códigos QR generados por día
- Tasa de uso de códigos QR
- Visitantes más frecuentes

## 🔄 Mantenimiento

### Limpieza Automática
- Códigos QR expirados (marcar como expirados)
- Imágenes QR antiguas (limpiar archivos)
- Logs antiguos (rotar archivos)

### Backup
- Respaldar tablas `visitantes` y `reservas`
- Respaldar imágenes QR generadas
- Respaldar logs del sistema

## 🐛 Solución de Problemas

### Error: "Usuario no autenticado"
- Verificar que la sesión esté activa
- Comprobar configuración de sesiones PHP

### Error: "Código QR no válido"
- Verificar que el código existe en la base de datos
- Comprobar que no ha expirado
- Verificar estado del código

### Error: "No se puede generar imagen QR"
- Verificar permisos del directorio `storage/qr_images/`
- Comprobar conexión a internet (para servicio online)
- Verificar espacio en disco

## 📞 Soporte

Para problemas técnicos:
1. Revisar logs del sistema
2. Ejecutar script de pruebas
3. Verificar configuración de base de datos
4. Contactar al administrador del sistema






