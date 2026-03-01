<?php
session_start();
if(!isset($_SESSION['nombre']) || $_SESSION['nombre_rol'] !== 'administrador') {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../app/Services/WhatsAppService.php';

// Crear instancia del servicio
$whatsappService = new WhatsAppService();
$estado = $whatsappService->verificarDisponibilidad();
?>
<!DOCTYPE html>
<html lang="es" x-data="{ darkMode: false }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📱 Gestor de WhatsApp | Quintanares by Parkovisco</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'Consolas', 'monospace'],
                        'display': ['Orbitron', 'system-ui', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'bounce-slow': 'bounce 3s infinite',
                        'spin-slow': 'spin 3s linear infinite',
                        'gradient': 'gradient 15s ease infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        gradient: {
                            '0%, 100%': { backgroundPosition: '0% 50%' },
                            '50%': { backgroundPosition: '100% 50%' },
                        },
                        shimmer: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' },
                        },
                        glow: {
                            '0%': { boxShadow: '0 0 5px rgba(59, 130, 246, 0.5)' },
                            '100%': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.8), 0 0 30px rgba(59, 130, 246, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .text-cyber {
            background: linear-gradient(135deg, #00d4ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .bg-cyber-gradient {
            background: linear-gradient(135deg, #0f172a, #1e293b, #334155);
        }
        
        .border-cyber {
            border-color: rgba(0, 212, 255, 0.3);
        }
        
        .hover-glow:hover {
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.4);
        }
    </style>
</head>
<body class="bg-cyber-gradient min-h-screen text-white">
    <!-- Header -->
    <header class="glass-card border-b border-cyber">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-cyan-500 to-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-comments text-white text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-cyber">GESTOR DE WHATSAPP</h1>
                        <p class="text-white/60">Quintanares by Parkovisco</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 <?php echo $estado['disponible'] ? 'bg-green-400' : 'bg-red-400'; ?> rounded-full animate-pulse"></div>
                        <span class="text-sm"><?php echo $estado['disponible'] ? 'ONLINE' : 'OFFLINE'; ?></span>
                    </div>
                    <a href="Administrador1.php" class="btn btn-outline border-cyan-500 text-cyan-400 hover:bg-cyan-500 hover:text-white">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Panel de Envío -->
            <div class="glass-card p-8">
                <h2 class="text-2xl font-bold text-cyan-400 mb-6">
                    <i class="fas fa-paper-plane mr-3"></i>
                    Enviar Mensaje
                </h2>
                
                <form id="whatsappForm" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-white/80 mb-2">Número de Teléfono</label>
                        <input type="tel" id="numero" class="input input-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" placeholder="+57 300 123 4567" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-white/80 mb-2">Tipo de Mensaje</label>
                        <select id="tipo" class="select select-bordered w-full bg-white/10 border-cyan-500 text-white">
                            <option value="texto">Mensaje de Texto</option>
                            <option value="notificacion">Notificación Oficial</option>
                            <option value="recordatorio">Recordatorio</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-white/80 mb-2">Mensaje</label>
                        <textarea id="mensaje" class="textarea textarea-bordered w-full bg-white/10 border-cyan-500 text-white placeholder-white/50" rows="6" placeholder="Escribe tu mensaje aquí..." required></textarea>
                        <div class="text-right text-white/60 text-sm mt-1">
                            <span id="contador">0</span>/4096 caracteres
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full bg-cyan-600 hover:bg-cyan-700 border-cyan-500">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Mensaje
                    </button>
                </form>
            </div>
            
            <!-- Panel de Plantillas -->
            <div class="glass-card p-8">
                <h2 class="text-2xl font-bold text-cyan-400 mb-6">
                    <i class="fas fa-file-alt mr-3"></i>
                    Plantillas Rápidas
                </h2>
                
                <div class="space-y-4">
                    <button onclick="usarPlantilla('reserva_confirmada')" class="btn btn-outline w-full border-green-500 text-green-400 hover:bg-green-500 hover:text-white">
                        <i class="fas fa-check-circle mr-2"></i>
                        Confirmación de Reserva
                    </button>
                    
                    <button onclick="usarPlantilla('reserva_cancelada')" class="btn btn-outline w-full border-red-500 text-red-400 hover:bg-red-500 hover:text-white">
                        <i class="fas fa-times-circle mr-2"></i>
                        Cancelación de Reserva
                    </button>
                    
                    <button onclick="usarPlantilla('recordatorio')" class="btn btn-outline w-full border-yellow-500 text-yellow-400 hover:bg-yellow-500 hover:text-white">
                        <i class="fas fa-bell mr-2"></i>
                        Recordatorio
                    </button>
                    
                    <button onclick="usarPlantilla('mantenimiento')" class="btn btn-outline w-full border-blue-500 text-blue-400 hover:bg-blue-500 hover:text-white">
                        <i class="fas fa-tools mr-2"></i>
                        Aviso de Mantenimiento
                    </button>
                </div>
                
                <!-- Estadísticas -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-cyan-400 mb-4">Estadísticas</h3>
                    <div class="stats stats-vertical bg-white/5 border border-cyan-500/20">
                        <div class="stat">
                            <div class="stat-title text-white/60">Mensajes Hoy</div>
                            <div class="stat-value text-cyan-400" id="mensajesHoy">0</div>
                        </div>
                        <div class="stat">
                            <div class="stat-title text-white/60">Estado</div>
                            <div class="stat-value text-green-400" id="estadoServicio">Activo</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Logs de Mensajes -->
        <div class="glass-card p-8 mt-8">
            <h2 class="text-2xl font-bold text-cyan-400 mb-6">
                <i class="fas fa-history mr-3"></i>
                Historial de Mensajes
            </h2>
            
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr class="border-cyan-500/20">
                            <th class="text-cyan-400">Fecha</th>
                            <th class="text-cyan-400">Número</th>
                            <th class="text-cyan-400">Mensaje</th>
                            <th class="text-cyan-400">Estado</th>
                            <th class="text-cyan-400">ID</th>
                        </tr>
                    </thead>
                    <tbody id="logsTable">
                        <tr>
                            <td colspan="5" class="text-center text-white/60">Cargando logs...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Contador de caracteres
        document.getElementById('mensaje').addEventListener('input', function() {
            const contador = document.getElementById('contador');
            contador.textContent = this.value.length;
            
            if (this.value.length > 4000) {
                contador.classList.add('text-red-400');
            } else {
                contador.classList.remove('text-red-400');
            }
        });
        
        // Envío de formulario
        document.getElementById('whatsappForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const numero = document.getElementById('numero').value;
            const mensaje = document.getElementById('mensaje').value;
            const tipo = document.getElementById('tipo').value;
            
            if (!numero || !mensaje) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Por favor completa todos los campos'
                });
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: 'Enviando...',
                text: 'Enviando mensaje de WhatsApp',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            try {
                const response = await fetch('../app/Controllers/api_whatsapp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'enviar_mensaje',
                        numero: numero,
                        mensaje: mensaje,
                        tipo: tipo
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: data.message,
                        timer: 3000
                    });
                    
                    // Limpiar formulario
                    document.getElementById('whatsappForm').reset();
                    document.getElementById('contador').textContent = '0';
                    
                    // Recargar logs
                    cargarLogs();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión'
                });
            }
        });
        
        // Plantillas rápidas
        function usarPlantilla(tipo) {
            const plantillas = {
                'reserva_confirmada': `¡Hola! Tu reserva está confirmada:

📅 Fecha: ${new Date().toLocaleDateString()}
🕐 Hora: ${new Date().toLocaleTimeString()}
🎫 Código QR: QR-${Math.random().toString(36).substr(2, 8).toUpperCase()}
📍 Motivo: Visita

✅ Estado: Activo
⏰ Válido hasta: ${new Date(Date.now() + 24*60*60*1000).toLocaleString()}

¡Bienvenido a Quintanares! 🎉`,
                
                'reserva_cancelada': `Hola, tu reserva ha sido cancelada.

Si tienes dudas, contacta administración.`,
                
                'recordatorio': `Recordatorio: Tienes una reserva para ${new Date().toLocaleDateString()} a las ${new Date().toLocaleTimeString()}.

🎫 Código QR: QR-${Math.random().toString(36).substr(2, 8).toUpperCase()}

¡Te esperamos! 🎉`,
                
                'mantenimiento': `AVISO IMPORTANTE:

🔧 Se realizará mantenimiento programado
📅 Fecha: ${new Date().toLocaleDateString()}
🕐 Hora: 08:00 - 12:00
📍 Área: Parqueadero Principal

Por favor, retira tu vehículo antes de la hora indicada.

Gracias por tu comprensión.`
            };
            
            document.getElementById('mensaje').value = plantillas[tipo];
            document.getElementById('contador').textContent = plantillas[tipo].length;
        }
        
        // Cargar logs
        async function cargarLogs() {
            try {
                const response = await fetch('../app/Controllers/api_whatsapp.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'obtener_logs'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const tbody = document.getElementById('logsTable');
                    tbody.innerHTML = '';
                    
                    if (data.logs.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-white/60">No hay logs disponibles</td></tr>';
                        return;
                    }
                    
                    data.logs.reverse().forEach(log => {
                        const row = document.createElement('tr');
                        row.className = 'border-cyan-500/20';
                        row.innerHTML = `
                            <td class="text-white/80">${log.timestamp}</td>
                            <td class="text-white/80">${log.numero}</td>
                            <td class="text-white/80">${log.mensaje || 'N/A'}</td>
                            <td class="text-white/80">${log.accion}</td>
                            <td class="text-white/80">${log.message_id || 'N/A'}</td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            } catch (error) {
                console.error('Error cargando logs:', error);
            }
        }
        
        // Cargar logs al inicio
        cargarLogs();
        
        // Actualizar logs cada 30 segundos
        setInterval(cargarLogs, 30000);
    </script>
</body>
</html>
