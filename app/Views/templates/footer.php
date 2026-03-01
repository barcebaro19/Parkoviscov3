</main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Activar el enlace actual en el sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(link => {
                if (link.getAttribute('href') === currentPath || 
                    currentPath.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
        });

        // Función para confirmar eliminaciones
        function confirmDelete(message = '¿Está seguro de eliminar este registro?') {
            return confirm(message);
        }

        // Función para mostrar loading
        function showLoading() {
            const loadingHtml = `
                <div id="loading-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                     background: rgba(0,0,0,0.5); z-index: 9999; display: flex; align-items: center; 
                     justify-content: center;">
                    <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', loadingHtml);
        }

        function hideLoading() {
            const loading = document.getElementById('loading-overlay');
            if (loading) {
                loading.remove();
            }
        }

        // Auto-ocultar alertas
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
