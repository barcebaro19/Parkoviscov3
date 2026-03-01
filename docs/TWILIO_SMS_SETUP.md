# 📱 Configuración de Twilio SMS

## 🎯 Descripción
Sistema de notificaciones SMS para reservas de visitantes en Quintanares Residencial.

## 🚀 Características
- ✅ SMS automático al crear reserva
- ✅ SMS de cancelación de reserva
- ✅ SMS de recordatorio
- ✅ Plantillas personalizables
- ✅ Logs de envío
- ✅ Configuración flexible

## 📋 Requisitos
1. **Cuenta de Twilio** (gratuita para pruebas)
2. **Número de teléfono** para SMS
3. **Credenciales** de Twilio

## 🔧 Configuración Paso a Paso

### 1. Crear cuenta en Twilio
1. Ve a [console.twilio.com](https://console.twilio.com/)
2. Crea una cuenta gratuita
3. Verifica tu número de teléfono

### 2. Obtener credenciales
1. En el Dashboard de Twilio, encuentra:
   - **Account SID**: `ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`
   - **Auth Token**: `xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`

### 3. Comprar número de teléfono
1. Ve a **Phone Numbers** > **Manage** > **Buy a number**
2. Selecciona un número con capacidad de SMS
3. Anota el número (ej: `+1234567890`)

### 4. Configurar el proyecto
1. Copia `config/twilio_config.example.php` como `config/twilio_config.php`
2. Edita el archivo con tus credenciales:

```php
return [
    'account_sid' => 'TU_ACCOUNT_SID_REAL',
    'auth_token' => 'TU_AUTH_TOKEN_REAL',
    'from_number' => '+1234567890', // Tu número de Twilio
    // ... resto de configuración
];
```

### 5. Probar la configuración
1. Ve a `http://localhost/parkovisko/test_twilio_sms.php`
2. Verifica que la configuración sea válida
3. Envía un SMS de prueba

## 💰 Costos
- **Cuenta gratuita**: $15 USD de crédito
- **SMS**: ~$0.0075 USD por mensaje
- **Número de teléfono**: ~$1 USD/mes

## 📱 Plantillas de Mensajes

### Reserva Confirmada
```
¡Hola {nombre}! Tu reserva en Quintanares está confirmada para {fecha} a las {hora}. Código QR: {codigo_qr}. ¡Bienvenido!
```

### Reserva Cancelada
```
Hola {nombre}, tu reserva para {fecha} ha sido cancelada. Si tienes dudas, contacta administración.
```

### Recordatorio
```
Recordatorio: Tienes una reserva en Quintanares para {fecha} a las {hora}. Código QR: {codigo_qr}
```

## 🔧 Personalización

### Modificar plantillas
Edita `config/twilio_config.php`:

```php
'templates' => [
    'reserva_confirmada' => 'Tu mensaje personalizado aquí {nombre}',
    // ... otras plantillas
],
```

### Activar/Desactivar SMS
```php
'sms' => [
    'enabled' => false, // Cambiar a false para desactivar
    // ... resto de configuración
],
```

### Cambiar código de país
```php
'sms' => [
    'country_code' => '+1', // Para Estados Unidos
    // ... resto de configuración
],
```

## 📊 Logs
Los logs se guardan en `storage/logs/twilio_sms.log`:

```
[2025-09-29 18:00:00] SMS enviado exitosamente a: +573001234567: ¡Hola Juan! Tu reserva...
[2025-09-29 18:01:00] Error enviando SMS: HTTP 400: Invalid phone number
```

## 🚨 Solución de Problemas

### Error: "Invalid phone number"
- Verifica que el número incluya código de país
- Formato correcto: `+573001234567`

### Error: "Authentication failed"
- Verifica Account SID y Auth Token
- Asegúrate de que no tengan espacios extra

### Error: "From number not verified"
- Verifica que el número de Twilio esté activo
- Formato correcto: `+1234567890`

### SMS no se envían
- Verifica que `'enabled' => true` en la configuración
- Revisa los logs en `storage/logs/twilio_sms.log`
- Verifica el saldo de tu cuenta de Twilio

## 🔒 Seguridad
- **Nunca** subas `twilio_config.php` al repositorio
- Usa variables de entorno en producción
- Mantén tus credenciales seguras

## 📞 Soporte
- **Twilio**: [support.twilio.com](https://support.twilio.com/)
- **Documentación**: [twilio.com/docs](https://www.twilio.com/docs)
- **Comunidad**: [Stack Overflow](https://stackoverflow.com/questions/tagged/twilio)

## 🎉 ¡Listo!
Una vez configurado, cada vez que se cree una reserva de visitante, se enviará automáticamente un SMS con los detalles y el código QR.


