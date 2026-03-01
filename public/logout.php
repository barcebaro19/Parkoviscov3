<?php
/**
 * Logout del Sistema
 * Quintanares Residencial - Sistema de Gestión de Parqueaderos
 */

session_start();
require_once '../app/Services/SessionManagerService.php';

// Cerrar sesión en la base de datos si existe
if (isset($_SESSION['user_id']) && isset($_SESSION['session_id'])) {
    $sessionManager = new SessionManagerService();
    $sessionManager->cerrarSesion(session_id());
}

// Destruir sesión PHP
session_destroy();

// Redirigir al login
header("Location: login.php");
exit();
?> 