/**
 * Sistema de Notificaciones Cyberpunk
 * Quintanares by Parkovisco
 */

class CyberNotification {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Crear contenedor de notificaciones si no existe
        if (!document.getElementById('cyber-notifications')) {
            this.createContainer();
        }
        this.container = document.getElementById('cyber-notifications');
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'cyber-notifications';
        container.className = 'fixed top-4 right-4 z-[9999] space-y-3';
        document.body.appendChild(container);
    }

    show(message, type = 'success', duration = 5000) {
        const notification = this.createNotification(message, type);
        this.container.appendChild(notification);

        // Animar entrada
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        // Auto-remover después de la duración
        setTimeout(() => {
            this.hide(notification);
        }, duration);

        return notification;
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `cyber-notification ${type} transform translate-x-full opacity-0 transition-all duration-500 ease-out`;
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle',
            download: 'fas fa-download',
            delete: 'fas fa-trash',
            edit: 'fas fa-edit',
            add: 'fas fa-plus'
        };

        const colors = {
            success: 'from-emerald-500 to-green-600',
            error: 'from-red-500 to-red-600',
            warning: 'from-yellow-500 to-orange-600',
            info: 'from-blue-500 to-blue-600',
            download: 'from-purple-500 to-purple-600',
            delete: 'from-red-500 to-red-600',
            edit: 'from-blue-500 to-blue-600',
            add: 'from-emerald-500 to-green-600'
        };

        notification.innerHTML = `
            <div class="bg-black/90 backdrop-blur-lg border border-emerald-500/30 rounded-xl p-4 shadow-2xl min-w-[320px] max-w-[400px]">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br ${colors[type]} rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="${icons[type]} text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-white font-semibold text-sm">${message}</p>
                    </div>
                    <button onclick="cyberNotification.hide(this.closest('.cyber-notification'))" 
                            class="text-white/60 hover:text-white transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="mt-3 bg-white/10 rounded-full h-1 overflow-hidden">
                    <div class="progress-bar bg-gradient-to-r ${colors[type]} h-full rounded-full transition-all duration-100 ease-linear"></div>
                </div>
            </div>
        `;

        // Agregar estilos CSS si no existen
        this.addStyles();

        return notification;
    }

    hide(notification) {
        if (notification && notification.parentNode) {
            notification.classList.remove('show');
            notification.classList.add('hide');
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 500);
        }
    }

    addStyles() {
        if (!document.getElementById('cyber-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'cyber-notification-styles';
            style.textContent = `
                .cyber-notification.show {
                    transform: translateX(0);
                    opacity: 1;
                }
                
                .cyber-notification.hide {
                    transform: translateX(100%);
                    opacity: 0;
                }
                
                .cyber-notification .progress-bar {
                    animation: progress-shrink 5s linear forwards;
                }
                
                @keyframes progress-shrink {
                    from { width: 100%; }
                    to { width: 0%; }
                }
                
                .cyber-notification:hover .progress-bar {
                    animation-play-state: paused;
                }
            `;
            document.head.appendChild(style);
        }
    }

    // Métodos de conveniencia
    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }

    download(message, duration) {
        return this.show(message, 'download', duration);
    }

    delete(message, duration) {
        return this.show(message, 'delete', duration);
    }

    edit(message, duration) {
        return this.show(message, 'edit', duration);
    }

    add(message, duration) {
        return this.show(message, 'add', duration);
    }
}

// Crear instancia global
const cyberNotification = new CyberNotification();

// Funciones de conveniencia globales
function showSuccess(message, duration = 5000) {
    return cyberNotification.success(message, duration);
}

function showError(message, duration = 7000) {
    return cyberNotification.error(message, duration);
}

function showWarning(message, duration = 6000) {
    return cyberNotification.warning(message, duration);
}

function showInfo(message, duration = 5000) {
    return cyberNotification.info(message, duration);
}

function showDownload(message, duration = 4000) {
    return cyberNotification.download(message, duration);
}

function showDelete(message, duration = 5000) {
    return cyberNotification.delete(message, duration);
}

function showEdit(message, duration = 4000) {
    return cyberNotification.edit(message, duration);
}

function showAdd(message, duration = 4000) {
    return cyberNotification.add(message, duration);
}

// Función para mostrar notificaciones basadas en parámetros URL
function showUrlNotification() {
    const urlParams = new URLSearchParams(window.location.search);
    const mensaje = urlParams.get('mensaje');
    
    if (mensaje) {
        switch (mensaje) {
            case 'actualizado':
                showSuccess('Usuario modificado exitosamente');
                break;
            case 'eliminado':
                showDelete('Usuario eliminado exitosamente');
                break;
            case 'creado':
                showAdd('Usuario creado exitosamente');
                break;
            case 'error':
                showError('Error al procesar la solicitud');
                break;
            case 'campos_vacios':
                showWarning('Por favor, complete todos los campos requeridos');
                break;
            case 'pdf_generado':
                showDownload('Reporte PDF generado exitosamente');
                break;
            case 'correo_enviado':
                showSuccess('Correo enviado exitosamente');
                break;
            case 'correo_masivo_enviado':
                showSuccess('Correos masivos enviados exitosamente');
                break;
            case 'vigilante_creado':
                showAdd('Vigilante registrado exitosamente');
                break;
            case 'propietario_eliminado':
                showDelete('Propietario eliminado exitosamente');
                break;
            case 'notificacion_enviada':
                showSuccess('Notificación enviada exitosamente');
                break;
            case 'reporte_generado':
                showDownload('Reporte generado exitosamente');
                break;
            default:
                showInfo('Operación completada');
        }
        
        // Limpiar URL
        const url = new URL(window.location);
        url.searchParams.delete('mensaje');
        window.history.replaceState({}, document.title, url);
    }
}

// Auto-ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', showUrlNotification);
