<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="eliminarModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar este usuario?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
                <form id="formEliminar" action="controller/eliminar_usuario.php" method="POST">
                    <input type="hidden" name="id" id="id_eliminar">
                    <input type="hidden" name="eliminar" value="ok">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
                <button type="submit" form="formEliminar" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-2"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div> 