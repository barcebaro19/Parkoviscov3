# 🔌 Documentación de API - Quintanares Residencial

Esta documentación describe todas las APIs disponibles en el sistema de gestión de Quintanares Residencial.

## 📋 **Información General**

### **Base URL**
```
http://tu-dominio.com/ci4-parkovisko/public/
```

### **Formato de Respuesta**
Todas las respuestas están en formato JSON con la siguiente estructura:

```json
{
    "success": true|false,
    "message": "Mensaje descriptivo",
    "data": {}, // Datos de respuesta (opcional)
    "error": "Detalle del error" // Solo en caso de error
}
```

### **Códigos de Estado HTTP**
- **200 OK** - Solicitud exitosa
- **201 Created** - Recurso creado exitosamente
- **400 Bad Request** - Solicitud malformada
- **401 Unauthorized** - No autenticado
- **403 Forbidden** - Sin permisos
- **404 Not Found** - Recurso no encontrado
- **500 Internal Server Error** - Error del servidor

## 🔐 **Autenticación**

### **POST /login**
Iniciar sesión en el sistema.

**Parámetros:**
```json
{
    "email": "usuario@ejemplo.com",
    "password": "contraseña123"
}
```

**Respuesta exitosa:**
```json
{
    "success": true,
    "message": "Login exitoso",
    "data": {
        "user_id": 1,
        "nombre": "Juan",
        "apellido": "Pérez",
        "email": "usuario@ejemplo.com",
        "rol": "administrador",
        "session_token": "abc123def456"
    }
}
```

**Respuesta de error:**
```json
{
    "success": false,
    "message": "Credenciales inválidas",
    "error": "Email o contraseña incorrectos"
}
```

### **POST /logout**
Cerrar sesión del usuario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Sesión cerrada exitosamente"
}
```

### **POST /register**
Registrar nuevo propietario (público).

**Parámetros:**
```json
{
    "nombre": "Juan",
    "apellido": "Pérez",
    "email": "juan@ejemplo.com",
    "celular": "3001234567",
    "password": "contraseña123",
    "confirm_password": "contraseña123"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Usuario registrado exitosamente",
    "data": {
        "user_id": 5,
        "email": "juan@ejemplo.com"
    }
}
```

## 👥 **Gestión de Usuarios**

### **GET /api/users**
Obtener lista de usuarios.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Parámetros de consulta:**
- `page` - Número de página (opcional, default: 1)
- `limit` - Elementos por página (opcional, default: 10)
- `role` - Filtrar por rol (opcional)
- `search` - Búsqueda por nombre/email (opcional)

**Ejemplo:**
```
GET /api/users?page=1&limit=20&role=propietario&search=Juan
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "users": [
            {
                "id": 1,
                "nombre": "Juan",
                "apellido": "Pérez",
                "email": "juan@ejemplo.com",
                "celular": "3001234567",
                "rol": "propietario",
                "estado": "activo",
                "fecha_registro": "2025-01-15 10:30:00"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 50,
            "items_per_page": 10
        }
    }
}
```

### **GET /api/users/{id}**
Obtener información de un usuario específico.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "nombre": "Juan",
        "apellido": "Pérez",
        "email": "juan@ejemplo.com",
        "celular": "3001234567",
        "rol": "propietario",
        "estado": "activo",
        "fecha_registro": "2025-01-15 10:30:00",
        "ultimo_acceso": "2025-01-20 15:45:00"
    }
}
```

### **POST /api/users**
Crear nuevo usuario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "nombre": "María",
    "apellido": "González",
    "email": "maria@ejemplo.com",
    "celular": "3007654321",
    "password": "contraseña123",
    "rol": "vigilante",
    "jornada": "mañana"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Usuario creado exitosamente",
    "data": {
        "user_id": 6,
        "email": "maria@ejemplo.com"
    }
}
```

### **PUT /api/users/{id}**
Actualizar información de usuario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "nombre": "María",
    "apellido": "González",
    "email": "maria.nueva@ejemplo.com",
    "celular": "3007654321",
    "password": "nueva_contraseña123"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Usuario actualizado exitosamente"
}
```

### **DELETE /api/users/{id}**
Eliminar usuario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Usuario eliminado exitosamente"
}
```

## 🅿️ **Gestión de Parqueaderos**

### **GET /api/parqueaderos**
Obtener lista de parqueaderos.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Parámetros de consulta:**
- `estado` - Filtrar por estado (disponible, ocupado, mantenimiento)
- `tipo` - Filtrar por tipo (cubierto, descubierto, moto)

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "parqueaderos": [
            {
                "id": 1,
                "numero": "P001",
                "tipo": "cubierto",
                "estado": "disponible",
                "propietario": null,
                "fecha_asignacion": null
            },
            {
                "id": 2,
                "numero": "P002",
                "tipo": "descubierto",
                "estado": "ocupado",
                "propietario": {
                    "id": 1,
                    "nombre": "Juan Pérez",
                    "apartamento": "101"
                },
                "fecha_asignacion": "2025-01-10 09:00:00"
            }
        ]
    }
}
```

### **POST /api/parqueaderos**
Crear nuevo parqueadero.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "numero": "P003",
    "tipo": "cubierto",
    "estado": "disponible"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Parqueadero creado exitosamente",
    "data": {
        "parqueadero_id": 3
    }
}
```

### **PUT /api/parqueaderos/{id}/asignar**
Asignar parqueadero a propietario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "propietario_id": 1
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Parqueadero asignado exitosamente"
}
```

## 🛡️ **Gestión de Vigilantes**

### **GET /api/vigilantes**
Obtener lista de vigilantes.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "vigilantes": [
            {
                "id": 1,
                "nombre": "Carlos",
                "apellido": "Rodríguez",
                "email": "carlos@ejemplo.com",
                "celular": "3009876543",
                "jornada": "mañana",
                "estado": "activo",
                "fecha_ingreso": "2025-01-01 08:00:00"
            }
        ]
    }
}
```

### **POST /api/vigilantes**
Crear nuevo vigilante.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "nombre": "Ana",
    "apellido": "Martínez",
    "email": "ana@ejemplo.com",
    "celular": "3005555555",
    "password": "contraseña123",
    "jornada": "tarde"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Vigilante creado exitosamente",
    "data": {
        "vigilante_id": 2
    }
}
```

## 🏠 **Gestión de Propietarios**

### **GET /api/propietarios**
Obtener lista de propietarios.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Parámetros de consulta:**
- `torre` - Filtrar por torre
- `piso` - Filtrar por piso
- `estado` - Filtrar por estado

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "propietarios": [
            {
                "id": 1,
                "nombre": "Juan",
                "apellido": "Pérez",
                "email": "juan@ejemplo.com",
                "celular": "3001234567",
                "torre": "A",
                "piso": 1,
                "apartamento": "101",
                "parqueadero": {
                    "id": 2,
                    "numero": "P002",
                    "tipo": "cubierto"
                },
                "estado": "activo"
            }
        ]
    }
}
```

### **GET /api/propietarios/{id}/perfil**
Obtener perfil completo de propietario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "propietario": {
            "id": 1,
            "nombre": "Juan",
            "apellido": "Pérez",
            "email": "juan@ejemplo.com",
            "celular": "3001234567",
            "torre": "A",
            "piso": 1,
            "apartamento": "101",
            "parqueadero": {
                "id": 2,
                "numero": "P002",
                "tipo": "cubierto",
                "estado": "ocupado"
            },
            "vehiculos": [
                {
                    "id": 1,
                    "placa": "ABC123",
                    "marca": "Toyota",
                    "modelo": "Corolla",
                    "color": "Blanco"
                }
            ],
            "visitas": [
                {
                    "id": 1,
                    "visitante": "María González",
                    "fecha": "2025-01-25 14:00:00",
                    "estado": "pendiente"
                }
            ],
            "reportes": [
                {
                    "id": 1,
                    "tipo": "daño",
                    "descripcion": "Fuga de agua en baño",
                    "fecha": "2025-01-20 10:30:00",
                    "estado": "en_proceso"
                }
            ]
        }
    }
}
```

## 💳 **Sistema de Pagos**

### **POST /api/pagos/procesar**
Procesar pago de propietario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "propietario_id": 1,
    "monto": 150000,
    "concepto": "Administración Enero 2025",
    "metodo_pago": "tarjeta",
    "datos_tarjeta": {
        "numero": "4111111111111111",
        "cvv": "123",
        "fecha_vencimiento": "12/25",
        "nombre_titular": "Juan Pérez"
    }
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Pago procesado exitosamente",
    "data": {
        "pago_id": 1,
        "referencia": "PAY_20250120_001",
        "estado": "aprobado",
        "fecha": "2025-01-20 15:30:00",
        "recibo_url": "/recibos/PAY_20250120_001.pdf"
    }
}
```

### **GET /api/pagos/{id}**
Obtener información de pago.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "propietario": {
            "id": 1,
            "nombre": "Juan Pérez"
        },
        "monto": 150000,
        "concepto": "Administración Enero 2025",
        "metodo_pago": "tarjeta",
        "estado": "aprobado",
        "fecha": "2025-01-20 15:30:00",
        "referencia": "PAY_20250120_001"
    }
}
```

### **POST /api/webhook/wompi**
Webhook de confirmación de Wompi.

**Headers requeridos:**
```
X-Wompi-Signature: {signature}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "event": "transaction.updated",
    "data": {
        "transaction": {
            "id": "12345",
            "reference": "PAY_20250120_001",
            "status": "APPROVED",
            "amount_in_cents": 15000000,
            "currency": "COP"
        }
    }
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Webhook procesado exitosamente"
}
```

## 📧 **Sistema de Correos**

### **POST /api/correos/enviar**
Enviar correo individual.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "destinatario": "usuario@ejemplo.com",
    "asunto": "Notificación importante",
    "mensaje": "Contenido del mensaje",
    "adjuntos": [
        {
            "nombre": "documento.pdf",
            "contenido": "base64_encoded_content"
        }
    ]
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Correo enviado exitosamente"
}
```

### **POST /api/correos/masivo**
Enviar correo masivo.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "destinatarios": [
        "usuario1@ejemplo.com",
        "usuario2@ejemplo.com",
        "usuario3@ejemplo.com"
    ],
    "asunto": "Comunicado general",
    "mensaje": "Mensaje para todos los usuarios"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Correos enviados: 3 de 3",
    "data": {
        "enviados": 3,
        "errores": 0,
        "detalles": [
            "✅ Enviado a: usuario1@ejemplo.com",
            "✅ Enviado a: usuario2@ejemplo.com",
            "✅ Enviado a: usuario3@ejemplo.com"
        ]
    }
}
```

## 🔔 **Sistema de Notificaciones**

### **POST /api/notificaciones/enviar**
Enviar notificación.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
Content-Type: application/json
```

**Parámetros:**
```json
{
    "destinatario_id": 1,
    "tipo": "informacion",
    "titulo": "Nueva notificación",
    "mensaje": "Contenido de la notificación",
    "prioridad": "media"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Notificación enviada exitosamente",
    "data": {
        "notificacion_id": 1
    }
}
```

### **GET /api/notificaciones/{usuario_id}**
Obtener notificaciones de usuario.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "notificaciones": [
            {
                "id": 1,
                "tipo": "informacion",
                "titulo": "Nueva notificación",
                "mensaje": "Contenido de la notificación",
                "prioridad": "media",
                "fecha": "2025-01-20 15:30:00",
                "leida": false
            }
        ]
    }
}
```

## 📊 **Reportes y Estadísticas**

### **GET /api/reportes/usuarios**
Generar reporte de usuarios.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Parámetros de consulta:**
- `formato` - pdf, excel, csv (default: pdf)
- `filtro` - rol, estado, fecha

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "reporte_url": "/reportes/usuarios_20250120.pdf",
        "fecha_generacion": "2025-01-20 15:30:00"
    }
}
```

### **GET /api/estadisticas/dashboard**
Obtener estadísticas del dashboard.

**Headers requeridos:**
```
Authorization: Bearer {session_token}
```

**Respuesta:**
```json
{
    "success": true,
    "data": {
        "usuarios": {
            "total": 150,
            "administradores": 2,
            "vigilantes": 5,
            "propietarios": 143
        },
        "parqueaderos": {
            "total": 200,
            "disponibles": 45,
            "ocupados": 150,
            "mantenimiento": 5
        },
        "pagos": {
            "pendientes": 25,
            "monto_pendiente": 3750000,
            "pagos_mes": 120
        }
    }
}
```

## 🚨 **Manejo de Errores**

### **Códigos de Error Comunes**

#### **400 Bad Request**
```json
{
    "success": false,
    "message": "Solicitud malformada",
    "error": "Parámetros requeridos faltantes",
    "details": {
        "campo": "email",
        "mensaje": "El campo email es requerido"
    }
}
```

#### **401 Unauthorized**
```json
{
    "success": false,
    "message": "No autorizado",
    "error": "Token de sesión inválido o expirado"
}
```

#### **403 Forbidden**
```json
{
    "success": false,
    "message": "Acceso denegado",
    "error": "No tienes permisos para realizar esta acción"
}
```

#### **404 Not Found**
```json
{
    "success": false,
    "message": "Recurso no encontrado",
    "error": "El usuario con ID 999 no existe"
}
```

#### **500 Internal Server Error**
```json
{
    "success": false,
    "message": "Error interno del servidor",
    "error": "Error de conexión a la base de datos"
}
```

## 🔒 **Seguridad**

### **Autenticación**
- **Tokens de sesión** únicos por usuario
- **Expiración automática** después de inactividad
- **Validación de permisos** en cada endpoint

### **Validación de Datos**
- **Sanitización** de entrada
- **Validación de tipos** de datos
- **Límites de longitud** en campos
- **Formato de email** válido

### **Rate Limiting**
- **Límite de requests** por IP
- **Protección contra** ataques de fuerza bruta
- **Throttling** en endpoints sensibles

### **Headers de Seguridad**
```
X-Content-Type-Options: nosniff
X-Frame-Options: DENY
X-XSS-Protection: 1; mode=block
Strict-Transport-Security: max-age=31536000
```

## 📝 **Ejemplos de Uso**

### **Flujo Completo de Registro**
```javascript
// 1. Registrar nuevo propietario
const registro = await fetch('/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        nombre: 'Juan',
        apellido: 'Pérez',
        email: 'juan@ejemplo.com',
        celular: '3001234567',
        password: 'contraseña123',
        confirm_password: 'contraseña123'
    })
});

// 2. Iniciar sesión
const login = await fetch('/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        email: 'juan@ejemplo.com',
        password: 'contraseña123'
    })
});

const { data } = await login.json();
const token = data.session_token;

// 3. Obtener perfil
const perfil = await fetch('/api/users/me', {
    headers: { 'Authorization': `Bearer ${token}` }
});
```

### **Gestión de Parqueaderos**
```javascript
// Obtener parqueaderos disponibles
const parqueaderos = await fetch('/api/parqueaderos?estado=disponible', {
    headers: { 'Authorization': `Bearer ${token}` }
});

// Asignar parqueadero
const asignacion = await fetch('/api/parqueaderos/1/asignar', {
    method: 'PUT',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({ propietario_id: 1 })
});
```

## 📞 **Soporte de API**

### **Contacto**
- **Email:** api@parkovisco.com
- **Documentación:** [docs/API.md](API.md)
- **Postman Collection:** [Descargar](postman_collection.json)

### **Versiones**
- **Versión actual:** v1.0
- **Compatibilidad:** Retrocompatible
- **Deprecaciones:** Notificadas con 6 meses de anticipación

---

**¡Gracias por usar la API de Quintanares Residencial!** 🔌✨
