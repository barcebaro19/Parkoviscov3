<?php
session_start();
if(!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}

// Aquí puedes obtener la información del usuario desde la base de datos
// Por ejemplo:
$nombre = $_SESSION['nombre'];
$email = $_SESSION['email']; // Suponiendo que el email está guardado en la sesión
// Agrega más campos según sea necesario
?>
<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Parkovisco</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.2/dist/full.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .dashboard-container {
            flex: 1;
            background: linear-gradient(135deg, #EEF2FF 0%, #E0E7FF 100%);
            display: flex;
            flex-direction: column;
        }

        .navbar-menu {
            background: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            z-index: 1000;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        main {
            flex: 1;
            padding-top: 80px;
            padding-bottom: 60px; 
        }
        .footer {
            background-color: #1F2937;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        .footer a {
            color: #A1A1AA; 
        }

        .footer a:hover {
            color: white; 
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Navbar -->
        <nav class="navbar-menu">
            <div class="container mx-auto flex justify-between items-center p-4">
                <a href="usuario.php" class="text-xl font-bold text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i> Volver
                </a>
                <h1 class="text-xl font-bold text-gray-800">Mi Perfil</h1>
            </div>
        </nav>

        <!-- Contenido principal -->
        <main class="container mx-auto px-4 py-8">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h2 class="text-2xl font-bold mb-4">Información del Usuario</h2>
                <div class="mb-4">
                    <label class="block text-gray-700">Nombre:</label>
                    <p class="text-gray-600"><?php echo $nombre; ?></p>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Email:</label>
                    <p class="text-gray-600"><?php echo $email; ?></p>
                </div>
                <!-- Agrega más campos según sea necesario -->

                <button onclick="document.getElementById('modal-editar').showModal()" class="btn btn-primary mt-4">
                    <i class="fas fa-edit mr-2"></i> Editar Perfil
                </button>
            </div>
        </main>

        <!-- Modal Editar Perfil -->
        <dialog id="modal-editar" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg mb-4">Editar Perfil</h3>
                <form method="POST" action="controller/editar_perfil.php">
                    <div class="space-y-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Nombre</span>
                            </label>
                            <input type="text" name="nombre" class="input input-bordered" value="<?php echo $nombre; ?>" required>
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" name="email" class="input input-bordered" value="<?php echo $email; ?>" required>
                        </div>
                        <!-- Agrega más campos según sea necesario -->
                    </div>
                    <div class="modal-action">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn" onclick="document.getElementById('modal-editar').close()">Cancelar</button>
                    </div>
                </form>
            </div>
        </dialog>

        <!-- Footer -->
        <footer class="footer">
            <div class="container mx-auto text-center">
                <div class="flex justify-center space-x-4 mb-4">
                    <a href="#" class="hover:underline">Inicio</a>
                    <a href="#" class="hover:underline">Nosotros</a>
                    <a href="#" class="hover:underline">Contacto</a>
                </div>
                <p class="text-sm">© 2023 Parkovisco. Todos los derechos reservados.</p>
            </div>
        </footer>
    </div>
</body>
</html> 