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
                <p class="text-sm text-gray-400">Panel Propietario</p>
            </div>
            <nav class="mt-8">
                <a href="/propietario" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="/propietario/mis-vehiculos" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-car mr-2"></i> Mis Vehículos
                </a>
                <a href="/propietario/registrar-visitante" class="block py-2 px-4 hover:bg-gray-700">
                    <i class="fas fa-user-plus mr-2"></i> Registrar Visitante
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
                        <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">Propietario</span>
                    </div>
                </div>
            </header>

            <main class="p-6">
                <!-- Welcome Card -->
                <div class="bg-gradient-to-r from-purple-600 to-blue-600 text-white p-6 rounded-lg shadow mb-8">
                    <h2 class="text-2xl font-bold mb-2">¡Bienvenido a Quintanares!</h2>
                    <p class="opacity-90">Gestiona tus vehículos, visitantes y pagos desde aquí.</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 rounded-full">
                                <i class="fas fa-car text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">Mis Vehículos</p>
                                <p class="text-2xl font-semibold">2</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 rounded-full">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">Visitantes Mes</p>
                                <p class="text-2xl font-semibold">8</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 rounded-full">
                                <i class="fas fa-dollar-sign text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-500 text-sm">Pagos Pendientes</p>
                                <p class="text-2xl font-semibold">1</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Vehicles Section -->
                <div class="bg-white p-6 rounded-lg shadow mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Mis Vehículos</h2>
                        <button class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                            <i class="fas fa-plus mr-2"></i> Agregar Vehículo
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-car text-purple-600 mr-3 text-xl"></i>
                                <div>
                                    <p class="font-medium">ABC-123</p>
                                    <p class="text-sm text-gray-500">Chevrolet Spark - Blanco</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 border rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-motorcycle text-purple-600 mr-3 text-xl"></i>
                                <div>
                                    <p class="font-medium">XYZ-456</p>
                                    <p class="text-sm text-gray-500">Honda - Rojo</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Visitors -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold">Visitantes Recientes</h2>
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                            <i class="fas fa-user-plus mr-2"></i> Registrar Visitante
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-user text-blue-600 mr-3"></i>
                                <div>
                                    <p class="font-medium">María González</p>
                                    <p class="text-sm text-gray-500">Visita: 10:30 AM - Hoy</p>
                                </div>
                            </div>
                            <span class="text-green-600 text-sm">Finalizada</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div class="flex items-center">
                                <i class="fas fa-user text-blue-600 mr-3"></i>
                                <div>
                                    <p class="font-medium">Carlos Ruiz</p>
                                    <p class="text-sm text-gray-500">Visita: 02:15 PM - Ayer</p>
                                </div>
                            </div>
                            <span class="text-green-600 text-sm">Finalizada</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
