<!-- ============================================
     FOOTER COMPONENT - PARKOVISCO
     ============================================ -->
<footer class="bg-gray-800 text-white py-8 mt-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Información de la Empresa -->
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center mb-4">
                    <i class="fas fa-parking text-3xl text-indigo-400 mr-3"></i>
                    <div>
                        <h3 class="text-xl font-bold">Parkovisco</h3>
                        <p class="text-gray-400 text-sm">Sistema de Gestión Residencial</p>
                    </div>
                </div>
                <p class="text-gray-300 mb-4">
                    Soluciones integrales para la gestión de conjuntos residenciales, 
                    parqueaderos y sistemas de vigilancia.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                        <i class="fab fa-facebook text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                        <i class="fab fa-twitter text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                        <i class="fab fa-instagram text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 transition-colors">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                </div>
            </div>

            <!-- Enlaces Rápidos -->
            <div>
                <h4 class="text-lg font-semibold mb-4">Enlaces Rápidos</h4>
                <ul class="space-y-2">
                    <li><a href="index.php" class="text-gray-300 hover:text-indigo-400 transition-colors">Inicio</a></li>
                    <li><a href="Administrador1.php" class="text-gray-300 hover:text-indigo-400 transition-colors">Dashboard</a></li>
                    <li><a href="gestion_vigilantes.php" class="text-gray-300 hover:text-indigo-400 transition-colors">Vigilantes</a></li>
                    <li><a href="gestion_propietarios.php" class="text-gray-300 hover:text-indigo-400 transition-colors">Propietarios</a></li>
                    <li><a href="parqueaderos.php" class="text-gray-300 hover:text-indigo-400 transition-colors">Parqueaderos</a></li>
                </ul>
            </div>

            <!-- Contacto -->
            <div>
                <h4 class="text-lg font-semibold mb-4">Contacto</h4>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-indigo-400 mr-2"></i>
                        <span class="text-gray-300">info@parkovisco.com</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-phone text-indigo-400 mr-2"></i>
                        <span class="text-gray-300">+57 (1) 234-5678</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-indigo-400 mr-2"></i>
                        <span class="text-gray-300">Bogotá, Colombia</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Línea divisoria -->
        <div class="border-t border-gray-700 mt-8 pt-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400 text-sm">
                    © <?php echo date('Y'); ?> Parkovisco. Todos los derechos reservados.
                </p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-indigo-400 text-sm transition-colors">Política de Privacidad</a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 text-sm transition-colors">Términos de Servicio</a>
                    <a href="#" class="text-gray-400 hover:text-indigo-400 text-sm transition-colors">Ayuda</a>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts adicionales -->
<script>
    // Funcionalidad adicional del footer si es necesaria
    document.addEventListener('DOMContentLoaded', function() {
        // Smooth scroll para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    });
</script>









