<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($titulo ?? 'Sistema de Parqueaderos') ?> - Quintanares Residencial</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .navbar-brand {
            font-weight: 700;
            color: #667eea !important;
        }
        .user-info {
            background: rgba(102, 126, 234, 0.1);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="user-info text-white text-center">
                        <i class="fas fa-user-circle fa-3x mb-2"></i>
                        <h6 class="mb-1"><?= esc($nombre_usuario ?? 'Usuario') ?></h6>
                        <small><?= ucfirst(session()->get('nombre_rol') ?? '') ?></small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <?php if (session()->get('nombre_rol') === 'administrador'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('administrador') ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('administrador/usuarios') ?>">
                                    <i class="fas fa-users me-2"></i> Usuarios
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('administrador/reportes') ?>">
                                    <i class="fas fa-chart-bar me-2"></i> Reportes
                                </a>
                            </li>
                        <?php elseif (session()->get('nombre_rol') === 'vigilante'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('vigilante') ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('vigilante/control-acceso') ?>">
                                    <i class="fas fa-qrcode me-2"></i> Control Acceso
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('vigilante/registros') ?>">
                                    <i class="fas fa-clipboard-list me-2"></i> Registros
                                </a>
                            </li>
                        <?php elseif (session()->get('nombre_rol') === 'propietario'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('propietario') ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('propietario/mis-vehiculos') ?>">
                                    <i class="fas fa-car me-2"></i> Mis Vehículos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= site_url('propietario/registrar-visitante') ?>">
                                    <i class="fas fa-user-plus me-2"></i> Registrar Visitante
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <li class="nav-item mt-3">
                            <a class="nav-link text-danger" href="<?= site_url('auth/logout') ?>">
                                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><?= esc($titulo ?? 'Dashboard') ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <span class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('d/m/Y H:i') ?>
                        </span>
                    </div>
                </div>

                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('success') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= session()->getFlashdata('error') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
