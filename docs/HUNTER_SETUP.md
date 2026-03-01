# 🚀 Configuración de Hunter.io para Validación de Emails

## 📋 Pasos para Configurar Hunter.io

### **1. Crear Cuenta en Hunter.io**
1. Ve a [https://hunter.io/](https://hunter.io/)
2. Haz clic en **"Sign Up"** (Registrarse)
3. Completa el formulario con tu información
4. Confirma tu email

### **2. Obtener tu API Key**
1. Inicia sesión en tu cuenta de Hunter.io
2. Ve a tu **Dashboard**
3. Busca la sección **"API Key"**
4. Copia tu API key (algo como: `abc123def456ghi789`)

### **3. Configurar la API Key en el Sistema**
1. Abre el archivo: `config/hunter_config.php`
2. Busca la línea: `'api_key' => 'TU_API_KEY_AQUI',`
3. Reemplaza `'TU_API_KEY_AQUI'` con tu API key real:
   ```php
   'api_key' => 'abc123def456ghi789', // Tu API key real
   ```

### **4. Verificar la Configuración**
1. Ve a `tablausu.php` en tu sistema
2. Usa el formulario de validación de emails
3. Prueba con un email como `test@gmail.com`
4. Deberías ver resultados detallados de Hunter.io

## 🎯 Características de Hunter.io

### **Plan Gratuito:**
- ✅ **100 validaciones gratis** por mes
- ✅ **Sin tarjeta de crédito** requerida
- ✅ **API completa** disponible
- ✅ **Resultados detallados**

### **Resultados que Obtienes:**
- 📧 **Formato válido** - Sintaxis correcta
- 🌐 **Dominio válido** - El dominio existe
- 📬 **MX Record** - Puede recibir emails
- ✅ **Disponible** - El email puede recibir mensajes
- 🎯 **Puntuación** - 0-100 de confianza
- 🚫 **Email temporal** - Detecta emails desechables
- 📱 **Webmail** - Identifica Gmail, Yahoo, etc.
- 👤 **Email de rol** - Detecta admin@, info@, etc.
- 🔄 **Catch-all** - Detecta dominios catch-all

## 🔧 Configuración Avanzada

### **Opciones Disponibles en `hunter_config.php`:**

```php
return [
    'api_key' => 'TU_API_KEY_AQUI',           // Tu API key
    'base_url' => 'https://api.hunter.io/v2/email-verifier',
    'timeout' => 30,                          // Timeout en segundos
    'fallback_to_local' => true,              // Usar validación local si falla
    'cache_results' => true,                  // Cachear resultados 24h
    'cache_duration' => 86400,                // 24 horas en segundos
    'log_requests' => true,                   // Loggear peticiones
    'log_file' => __DIR__ . '/../storage/logs/hunter_api.log'
];
```

### **Archivos de Log:**
- **API Log**: `storage/logs/hunter_api.log` - Todas las peticiones a Hunter.io
- **Validation Log**: `storage/logs/email_validation.log` - Resultados de validación

### **Cache:**
- **Ubicación**: `storage/cache/hunter/`
- **Duración**: 24 horas por defecto
- **Beneficio**: Evita peticiones repetidas a la API

## 🚨 Solución de Problemas

### **Error: "API key not configured"**
- ✅ Verifica que hayas reemplazado `'TU_API_KEY_AQUI'` con tu API key real
- ✅ Asegúrate de que no haya espacios extra en la API key

### **Error: "HTTP Error: 401"**
- ✅ Verifica que tu API key sea correcta
- ✅ Asegúrate de que tu cuenta de Hunter.io esté activa

### **Error: "HTTP Error: 429"**
- ✅ Has excedido el límite de 100 validaciones por mes
- ✅ Espera hasta el próximo mes o actualiza tu plan

### **Fallback a Validación Local**
- ✅ Si Hunter.io falla, el sistema usa validación local automáticamente
- ✅ Verás "Fuente: local_fallback" en los resultados

## 📊 Monitoreo de Uso

### **Ver Estadísticas de tu Cuenta:**
1. Ve a tu dashboard de Hunter.io
2. Busca la sección **"Usage"**
3. Verás cuántas validaciones has usado este mes

### **Logs del Sistema:**
- Revisa `storage/logs/hunter_api.log` para ver todas las peticiones
- Revisa `storage/logs/email_validation.log` para ver resultados

## 🎉 ¡Listo!

Una vez configurado, tu sistema tendrá:
- ✅ **Validación profesional** de emails
- ✅ **Resultados detallados** y confiables
- ✅ **Fallback automático** si hay problemas
- ✅ **Cache inteligente** para mejor rendimiento
- ✅ **Logs completos** para monitoreo

**¡Disfruta de la validación de emails de nivel empresarial!** 🚀

