# Sistema de Gestión de Estacionamiento

## Características Principales

- Autenticación y manejo dinámico de roles
- CRUD completo con validaciones
- Generación de reportes PDF con filtros multicriterio
- Interfaz API REST documentada
- Caché para optimización de rendimiento
- Internacionalización (i18n)
- Sistema de logging y monitoreo
- Manejo global de excepciones
- Rate limiting para seguridad

## Requisitos Previos

1. Java JDK 17 o superior
2. Maven 3.6 o superior
3. IDE compatible con Spring Boot (opcional)

## Instalación

1. Clonar el repositorio:
```bash
git clone <url-del-repositorio>
```

2. Navegar al directorio del proyecto:
```bash
cd parkovisko
```

3. Compilar el proyecto:
```bash
mvn clean install
```

## Ejecución

1. Ejecutar la aplicación:
```bash
mvn spring-boot:run
```

La aplicación estará disponible en: http://localhost:8080

## Documentación API

La documentación interactiva de la API está disponible en:
http://localhost:8080/swagger-ui.html

## Endpoints Principales

### Autenticación
- POST `/api/auth/registro` - Registrar nuevo usuario
- POST `/api/auth/login` - Iniciar sesión

### Reportes
- GET `/api/reportes/vehiculos` - Generar reporte de vehículos (requiere rol ADMIN o VIGILANTE)
  - Parámetros opcionales:
    - marca
    - modelo
    - estado

## Seguridad

- Autenticación basada en JWT
- Rate limiting: 10 peticiones por minuto por IP
- Validación robusta de contraseñas
- Control de acceso basado en roles

## Monitoreo

Los logs se encuentran en:
- Consola: Salida estándar
- Archivo: `./logs/parkovisko.log`
- Archivos rotados: `./logs/archived/`

## Base de Datos

La aplicación utiliza H2 como base de datos en memoria. La consola H2 está disponible en:
http://localhost:8080/h2-console

Credenciales por defecto:
- JDBC URL: jdbc:h2:file:./parkoviskodb
- Usuario: sa
- Contraseña: password

## Internacionalización

El sistema soporta múltiples idiomas:
- Español (por defecto)
- Otros idiomas pueden ser agregados en `src/main/resources/i18n/`

## Caché

El sistema implementa caché para:
- Consultas de vehículos
- Información de usuarios
- Generación de reportes

## Contribución

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request 