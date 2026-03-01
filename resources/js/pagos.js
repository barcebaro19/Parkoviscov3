// Sistema de Pagos Dinámico - Quintanares by Parkovisco

class PagosManager {
    constructor() {
        this.usuarioId = null;
        this.init();
    }
    
    init() {
        // Obtener ID del usuario desde la sesión (se puede pasar desde PHP)
        this.usuarioId = window.usuarioId || 1; // Fallback para testing
        
        // Cargar datos cuando se muestre la sección de pagos
        document.addEventListener('DOMContentLoaded', () => {
            this.cargarDatosPagos();
        });
    }
    
    async cargarDatosPagos() {
        try {
            await Promise.all([
                this.cargarHistorialPagos(),
                this.cargarResumenPagos(),
                this.cargarMetodosPago(),
                this.cargarPagosPendientes()
            ]);
        } catch (error) {
            console.error('Error cargando datos de pagos:', error);
        }
    }
    
    async cargarHistorialPagos() {
        try {
            const response = await fetch('controller/pagos_api.php?action=historial&usuario_id=' + this.usuarioId);
            const data = await response.json();
            
            const container = document.getElementById('historialPagos');
            if (!container) return;
            
            if (data.success && data.pagos.length > 0) {
                container.innerHTML = data.pagos.map(pago => this.crearTarjetaPago(pago)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-receipt text-white/30 text-4xl mb-4"></i>
                        <p class="text-white/70">No hay pagos registrados</p>
                        <a href="procesar_pago.php" class="cyber-button mt-4 inline-block">
                            <i class="fas fa-plus mr-2"></i>Realizar Primer Pago
                        </a>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error cargando historial:', error);
        }
    }
    
    async cargarResumenPagos() {
        try {
            const response = await fetch('controller/pagos_api.php?action=resumen&usuario_id=' + this.usuarioId);
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('totalPagado').textContent = this.formatearMoneda(data.total_pagado);
                document.getElementById('totalPendiente').textContent = this.formatearMoneda(data.total_pendiente);
                document.getElementById('totalGeneral').textContent = this.formatearMoneda(data.total_general);
            }
        } catch (error) {
            console.error('Error cargando resumen:', error);
        }
    }
    
    async cargarMetodosPago() {
        try {
            const response = await fetch('controller/pagos_api.php?action=metodos&usuario_id=' + this.usuarioId);
            const data = await response.json();
            
            const container = document.getElementById('metodosPago');
            if (!container) return;
            
            if (data.success && data.metodos.length > 0) {
                container.innerHTML = data.metodos.map(metodo => this.crearTarjetaMetodo(metodo)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-credit-card text-white/30 text-2xl mb-2"></i>
                        <p class="text-white/70 text-sm">No hay métodos de pago</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error cargando métodos:', error);
        }
    }
    
    async cargarPagosPendientes() {
        try {
            const response = await fetch('controller/pagos_api.php?action=pendientes&usuario_id=' + this.usuarioId);
            const data = await response.json();
            
            const container = document.getElementById('pagosPendientes');
            if (!container) return;
            
            if (data.success && data.pagos.length > 0) {
                container.innerHTML = data.pagos.map(pago => this.crearTarjetaPendiente(pago)).join('');
            } else {
                container.innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-emerald-400 text-2xl mb-2"></i>
                        <p class="text-white/70 text-sm">No hay pagos pendientes</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error cargando pendientes:', error);
        }
    }
    
    crearTarjetaPago(pago) {
        const estadoClass = this.obtenerClaseEstado(pago.estado);
        const estadoTexto = this.obtenerTextoEstado(pago.estado);
        const fechaPago = pago.fecha_pago ? new Date(pago.fecha_pago).toLocaleDateString('es-CO') : 'N/A';
        
        return `
            <div class="payment-status ${pago.estado} p-6 rounded-xl">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-white">${pago.concepto_nombre}</h3>
                        <p class="text-sm ${estadoClass} font-mono">${new Date(pago.fecha_creacion).toLocaleDateString('es-CO', { month: 'long', year: 'numeric' })}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 ${estadoClass}/20 text-${estadoClass} text-xs rounded border border-${estadoClass}/30 mb-2">${estadoTexto}</span>
                        <p class="font-bold text-${estadoClass}">${this.formatearMoneda(pago.monto)}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="${estadoClass}/70 font-mono">${pago.estado === 'aprobado' ? 'Pagado el: ' + fechaPago : 'Vence: ' + new Date(pago.fecha_vencimiento).toLocaleDateString('es-CO')}</span>
                    ${pago.estado === 'aprobado' ? 
                        `<button class="cyber-button text-xs" onclick="pagosManager.descargarRecibo(${pago.id})">
                            <i class="fas fa-download mr-1"></i>Recibo
                        </button>` :
                        `<button class="cyber-button text-xs bg-yellow-600 hover:bg-yellow-700" onclick="pagosManager.pagarAhora(${pago.id})">
                            <i class="fas fa-credit-card mr-1"></i>Pagar
                        </button>`
                    }
                </div>
            </div>
        `;
    }
    
    crearTarjetaMetodo(metodo) {
        const icono = this.obtenerIconoMetodo(metodo.tipo);
        const numero = metodo.numero_tarjeta ? '**** ' + metodo.numero_tarjeta.slice(-4) : 'N/A';
        
        return `
            <div class="flex items-center gap-3 p-3 bg-black/30 rounded-lg border border-blue-500/20">
                <i class="${icono} text-blue-400"></i>
                <div class="flex-1">
                    <p class="font-semibold text-white">${numero}</p>
                    <p class="text-sm text-blue-400 font-mono">${this.obtenerNombreMetodo(metodo.tipo)}</p>
                </div>
                <button class="text-red-400 hover:text-red-300" onclick="pagosManager.eliminarMetodo(${metodo.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
    }
    
    crearTarjetaPendiente(pago) {
        const diasVencimiento = Math.ceil((new Date(pago.fecha_vencimiento) - new Date()) / (1000 * 60 * 60 * 24));
        const esVencido = diasVencimiento < 0;
        const claseUrgencia = esVencido ? 'text-red-400' : diasVencimiento <= 3 ? 'text-yellow-400' : 'text-white/70';
        
        return `
            <div class="flex items-center justify-between p-3 bg-black/30 rounded-lg border border-yellow-500/20">
                <div>
                    <p class="font-semibold text-white">${pago.concepto_nombre}</p>
                    <p class="text-sm ${claseUrgencia}">${esVencido ? 'Vencido' : diasVencimiento + ' días restantes'}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-yellow-400">${this.formatearMoneda(pago.monto)}</p>
                    <button class="cyber-button text-xs mt-1" onclick="pagosManager.pagarAhora(${pago.id})">
                        <i class="fas fa-credit-card mr-1"></i>Pagar
                    </button>
                </div>
            </div>
        `;
    }
    
    obtenerClaseEstado(estado) {
        const clases = {
            'aprobado': 'emerald',
            'pendiente': 'yellow',
            'procesando': 'blue',
            'rechazado': 'red',
            'cancelado': 'gray'
        };
        return clases[estado] || 'gray';
    }
    
    obtenerTextoEstado(estado) {
        const textos = {
            'aprobado': 'PAGADO',
            'pendiente': 'PENDIENTE',
            'procesando': 'PROCESANDO',
            'rechazado': 'RECHAZADO',
            'cancelado': 'CANCELADO'
        };
        return textos[estado] || estado.toUpperCase();
    }
    
    obtenerIconoMetodo(tipo) {
        const iconos = {
            'tarjeta': 'fas fa-credit-card',
            'pse': 'fas fa-university',
            'nequi': 'fas fa-mobile-alt',
            'daviplata': 'fas fa-mobile-alt'
        };
        return iconos[tipo] || 'fas fa-credit-card';
    }
    
    obtenerNombreMetodo(tipo) {
        const nombres = {
            'tarjeta': 'Tarjeta de Crédito/Débito',
            'pse': 'PSE - Transferencia Bancaria',
            'nequi': 'Nequi',
            'daviplata': 'Daviplata'
        };
        return nombres[tipo] || tipo;
    }
    
    formatearMoneda(monto) {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(monto);
    }
    
    async pagarAhora(pagoId) {
        // Redirigir a la página de procesamiento de pago
        window.location.href = `procesar_pago.php?pago_id=${pagoId}`;
    }
    
    async descargarRecibo(pagoId) {
        // Abrir recibo en nueva ventana
        window.open(`recibo_pago.php?id=${pagoId}`, '_blank');
    }
    
    async eliminarMetodo(metodoId) {
        if (confirm('¿Estás seguro de que quieres eliminar este método de pago?')) {
            try {
                const response = await fetch('controller/pagos_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'eliminar_metodo',
                        metodo_id: metodoId
                    })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.cargarMetodosPago();
                } else {
                    alert('Error al eliminar el método de pago');
                }
            } catch (error) {
                console.error('Error eliminando método:', error);
                alert('Error al eliminar el método de pago');
            }
        }
    }
    
    agregarMetodoPago() {
        // Redirigir a página de agregar método de pago
        window.location.href = 'agregar_metodo_pago.php';
    }
}

// Inicializar el manager de pagos
const pagosManager = new PagosManager();

// Función global para agregar método de pago
function agregarMetodoPago() {
    pagosManager.agregarMetodoPago();
}

