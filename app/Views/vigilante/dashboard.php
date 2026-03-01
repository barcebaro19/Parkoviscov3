<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo) ?> - Quintanares</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <div class="flex h-screen">
        <div class="w-64 bg-gray-800 text-white">
            <div class="p-4">
                <h2 class="text-2xl font-bold">Quintanares</h2>
                <p class="text-sm text-gray-400">Panel Vigilante</p>
            </div>
            <nav class="mt-8">
                <a href="/vigilante" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="/vigilante/control-acceso" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-qrcode mr-2"></i> Control Acceso
                </a>
                <a href="/auth/logout" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <header class="bg-white shadow-sm p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-semibold"><?= esc($titulo) ?></h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600"><?= esc($nombre_usuario) ?></span>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Vigilante</span>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-car text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">Vehículos Hoy</p>
                                <p class="text-2xl font-semibold">45</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-users text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">Visitantes</p>
                                <p class="text-2xl font-semibold">12</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">Turno Actual</p>
                                <p class="text-2xl font-semibold">Mañana</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-xl font-semibold mb-4">Acciones Rápidas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <button class="bg-blue-600 text-white p-4 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-qrcode mr-2"></i> Escanear QR
                        </button>
                        <button class="bg-green-600 text-white p-4 rounded-lg hover:bg-green-700">
                            <i class="fas fa-user-plus mr-2"></i> Registrar Visitante
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white p-6 rounded-lg shadow mt-6">
                    <h2 class="text-xl font-semibold mb-4">Actividad Reciente</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-car text-blue-600 mr-3"></i>
                                <div>
                                    <p class="font-medium">ABC-123</p>
                                    <p class="text-sm text-gray-500">Entrada: 08:30</p>
                                </div>
                            </div>
                            <span class="text-green-600 text-sm">Activo</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-user text-green-600 mr-3"></i>
                                <div>
                                    <p class="font-medium">Juan Pérez</p>
                                    <p class="text-sm text-gray-500">Visita Torre A</p>
                                </div>
                            </div>
                            <span class="text-green-600 text-sm">Dentro</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
