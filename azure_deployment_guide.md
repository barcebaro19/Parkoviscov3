# 🚀 Guía de Despliegue en Azure

## ✅ Compatibilidad Verificada
- **PHP 8.0.30** → Compatible con Azure PHP 8.1-8.3
- **Todas las extensiones** necesarias disponibles
- **Conexión a base de datos** funcionando
- **Código compatible** con versiones superiores

## 📋 Pasos para Desplegar

### 1. Preparar Archivos
- ✅ `.htaccess` - Configuración para Apache
- ✅ `web.config` - Configuración para IIS/Azure
- ✅ `check_php_compatibility.php` - Verificador (eliminar en producción)

### 2. Crear App Service en Azure
```bash
# Crear grupo de recursos
az group create --name parkovisko-rg --location "East US"

# Crear App Service Plan
az appservice plan create --name parkovisko-plan --resource-group parkovisko-rg --sku B1

# Crear App Service
az webapp create --resource-group parkovisko-rg --plan parkovisko-plan --name parkovisko-app --runtime "PHP|8.1"
```

### 3. Configurar Base de Datos
- Crear **Azure Database for MySQL**
- Configurar **connection string**
- Actualizar `app/Models/conexion.php`

### 4. Configurar Variables de Entorno
```bash
# Configurar en Azure Portal
DB_HOST=tu-servidor.mysql.database.azure.com
DB_NAME=sistema_vigilancia
DB_USER=tu-usuario
DB_PASS=tu-password
```

### 5. Subir Código
```bash
# Usar Azure CLI o Visual Studio Code
az webapp deployment source config-zip --resource-group parkovisko-rg --name parkovisko-app --src parkovisko.zip
```

## 🔧 Configuraciones Importantes

### PHP Settings en Azure
- `display_errors: Off` (producción)
- `error_reporting: E_ALL`
- `max_execution_time: 300`
- `memory_limit: 256M`

### Base de Datos
- Usar **Azure Database for MySQL**
- Configurar **SSL** obligatorio
- Usar **connection pooling**

## 🎯 Resultado Esperado
- ✅ Aplicación funcionando en Azure
- ✅ Base de datos conectada
- ✅ Google OAuth funcionando
- ✅ Sistema de pagos operativo
- ✅ QR generation funcionando

## 📞 Soporte
Si hay problemas, revisar:
1. Logs de Azure App Service
2. Configuración de base de datos
3. Variables de entorno
4. Permisos de archivos
