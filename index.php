<?php
/**
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 * Punto de entrada principal del sistema
 * 
 * Este archivo redirige todas las peticiones al directorio public/
 * siguiendo las mejores prácticas de seguridad web.
 */

// Redirigir al directorio public
header('Location: public/index.php');
exit();
?>

