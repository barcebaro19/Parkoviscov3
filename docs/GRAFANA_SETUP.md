# 🚀 Configuración de Grafana para Quintanares

## 📋 ¿Qué es Grafana?

**Grafana** es una plataforma de monitoreo y análisis que te permite:
- 📊 **Dashboards en tiempo real** con métricas del sistema
- 🚨 **Alertas automáticas** cuando algo va mal
- 📈 **Gráficos interactivos** de ocupación, ingresos, usuarios
- 🔍 **Análisis de tendencias** para tomar decisiones

## 🎯 Lo que Implementamos

### **1. Sistema de Métricas Completo:**
- ✅ **Ocupación de parqueaderos** en tiempo real
- ✅ **Ingresos diarios y mensuales**
- ✅ **Usuarios activos** por rol
- ✅ **Alertas de seguridad**
- ✅ **Métricas del sistema**

### **2. Dashboard Personalizado:**
- ✅ **Interfaz web** que simula Grafana
- ✅ **Gráficos interactivos** con Chart.js
- ✅ **Métricas en tiempo real**
- ✅ **Alertas visuales**
- ✅ **Auto-refresh** cada 30 segundos

### **3. Sistema de Alertas:**
- ✅ **Alertas automáticas** cuando:
  - Parqueadero 95% ocupado
  - Vehículo no autorizado detectado
  - Múltiples pagos vencidos
- ✅ **Logs de alertas** en archivos
- ✅ **Webhooks** para integración futura

## 🚀 Cómo Usar el Sistema

### **Acceso al Dashboard:**
1. **Inicia sesión** como administrador
2. **Ve al menú lateral** y haz clic en "Grafana"
3. **O accede directamente**: `grafana_dashboard.php`

### **Métricas Disponibles:**

#### **📊 Métricas Principales:**
- **Ocupación**: Porcentaje de parqueaderos ocupados
- **Ingresos**: Dinero recaudado hoy y este mes
- **Usuarios**: Usuarios activos en las últimas 24 horas
- **Seguridad**: Vehículos no autorizados detectados

#### **📈 Gráficos:**
- **Ocupación por Torre**: Barras mostrando % de ocupación
- **Métodos de Pago**: Gráfico circular de métodos más usados
- **Usuarios por Rol**: Tabla con distribución de usuarios

#### **🚨 Alertas:**
- **Críticas**: Vehículos no autorizados (rojo)
- **Advertencias**: Parqueadero 95% ocupado (amarillo)

## 🔧 Configuración Avanzada

### **Archivos de Configuración:**
- **`config/grafana_config.php`** - Configuración principal
- **`app/Services/GrafanaMetricsService.php`** - Servicio de métricas
- **`public/grafana_metrics.php`** - Endpoint de métricas
- **`public/grafana_webhook.php`** - Webhook para alertas

### **Personalizar Alertas:**
```php
// En config/grafana_config.php
'alerts' => [
    'rules' => [
        'parking_full' => [
            'condition' => 'occupancy >= 95',
            'message' => 'Parqueadero 95% ocupado',
            'severity' => 'warning'
        ],
        // Agregar más reglas aquí
    ]
]
```

### **Agregar Nuevas Métricas:**
```php
// En GrafanaMetricsService.php
private function getCustomMetrics() {
    // Tu lógica personalizada aquí
    return [
        'custom_metric' => $value
    ];
}
```

## 📁 Estructura de Archivos

```
parkovisko/
├── config/
│   └── grafana_config.php          # Configuración
├── app/Services/
│   └── GrafanaMetricsService.php   # Servicio de métricas
├── public/
│   ├── grafana_dashboard.php       # Dashboard web
│   ├── grafana_metrics.php         # Endpoint de métricas
│   └── grafana_webhook.php         # Webhook de alertas
├── storage/
│   ├── metrics/
│   │   └── grafana_metrics.json    # Métricas en JSON
│   └── logs/
│       ├── grafana_metrics.log     # Log de métricas
│       ├── grafana_alerts.log      # Log de alertas
│       └── grafana_webhook.log     # Log de webhooks
└── GRAFANA_SETUP.md                # Este archivo
```

## 🎯 Próximos Pasos

### **Para Implementar Grafana Real:**
1. **Instalar Grafana** en tu servidor
2. **Configurar base de datos** (InfluxDB o Prometheus)
3. **Importar dashboards** desde archivos JSON
4. **Configurar alertas** con canales de notificación

### **Mejoras Futuras:**
- 📱 **App móvil** con notificaciones push
- 🔔 **Integración con Slack** para alertas
- 📧 **Emails automáticos** de reportes
- 🤖 **IA para predicciones** de ocupación

## 🚨 Solución de Problemas

### **Dashboard no carga:**
- ✅ Verifica que seas administrador
- ✅ Revisa los logs en `storage/logs/`
- ✅ Verifica la conexión a la base de datos

### **Métricas no se actualizan:**
- ✅ Verifica `storage/metrics/grafana_metrics.json`
- ✅ Revisa `storage/logs/grafana_metrics.log`
- ✅ Verifica permisos de escritura en `storage/`

### **Alertas no funcionan:**
- ✅ Revisa `storage/logs/grafana_alerts.log`
- ✅ Verifica las reglas en `config/grafana_config.php`
- ✅ Comprueba que las condiciones se cumplan

## 📊 Ejemplo de Uso

### **Dashboard Principal:**
```
┌─────────────────────────────────────────────────────────┐
│  🏢 QUINTANARES - DASHBOARD GRAFANA                    │
│  ─────────────────────────────────────────────────────  │
│  📊 MÉTRICAS EN TIEMPO REAL:                           │
│  🅿️ Ocupación: 45/50 (90%)                            │
│  💰 Ingresos hoy: $2,450,000                          │
│  👥 Usuarios activos: 23                              │
│  🚨 Alertas: 2 pendientes                             │
│                                                         │
│  📈 GRÁFICOS:                                          │
│  - Ocupación por torre (barras)                       │
│  - Métodos de pago (circular)                         │
│  - Usuarios por rol (tabla)                           │
└─────────────────────────────────────────────────────────┘
```

## 🎉 ¡Listo!

**Tu sistema ahora tiene:**
- ✅ **Dashboard profesional** de monitoreo
- ✅ **Métricas en tiempo real** del sistema
- ✅ **Alertas automáticas** de problemas
- ✅ **Gráficos interactivos** para análisis
- ✅ **Logs completos** para auditoría

**¡Disfruta de tu nuevo centro de control!** 🚀

