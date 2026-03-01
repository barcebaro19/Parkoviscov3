<?= $this->include('templates/header') ?>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Usuarios
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= count($usuarios ?? []) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Propietarios
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <!-- Contar propietarios de la lista de usuarios -->
                            <?php 
                            $propietarios = 0;
                            if (isset($usuarios)) {
                                foreach ($usuarios as $usuario) {
                                    if ($usuario['nombre_rol'] === 'propietario') $propietarios++;
                                }
                            }
                            echo $propietarios;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Vigilantes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $vigilantes = 0;
                            if (isset($usuarios)) {
                                foreach ($usuarios as $usuario) {
                                    if ($usuario['nombre_rol'] === 'vigilante') $vigilantes++;
                                }
                            }
                            echo $vigilantes;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Administradores
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $administradores = 0;
                            if (isset($usuarios)) {
                                foreach ($usuarios as $usuario) {
                                    if ($usuario['nombre_rol'] === 'administrador') $administradores++;
                                }
                            }
                            echo $administradores;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Usuarios Recientes</h6>
            </div>
            <div class="card-body">
                <?php if (isset($usuarios) && count($usuarios) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Apartamento</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($usuarios, 0, 5) as $usuario): ?>
                                    <tr>
                                        <td><?= $usuario['id'] ?></td>
                                        <td><?= esc($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                        <td><?= esc($usuario['email'] ?? 'N/A') ?></td>
                                        <td><?= esc($usuario['apartamento'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $usuario['nombre_rol'] === 'administrador' ? 'danger' : ($usuario['nombre_rol'] === 'vigilante' ? 'warning' : 'info') ?>">
                                                <?= ucfirst($usuario['nombre_rol']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $usuario['estado'] === 'activo' ? 'success' : 'secondary' ?>">
                                                <?= ucfirst($usuario['estado']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No hay usuarios registrados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="<?= site_url('administrador/usuarios') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i> Gestionar Usuarios
                    </a>
                    <a href="<?= site_url('administrador/crear-usuario') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-plus me-2"></i> Nuevo Usuario
                    </a>
                    <a href="<?= site_url('administrador/reportes') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-chart-bar me-2"></i> Ver Reportes
                    </a>
                    <a href="<?= site_url('administrador/configuracion') ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-cog me-2"></i> Configuración
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Sistema</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <p><strong>Versión:</strong> 1.0.0</p>
                    <p><strong>Framework:</strong> CodeIgniter 4</p>
                    <p><strong>Última actualización:</strong> <?= date('d/m/Y') ?></p>
                    <p><strong>Base de datos:</strong> MySQL</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('templates/footer') ?>
