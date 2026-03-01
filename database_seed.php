<?php

/**
 * Script para inicializar la base de datos del sistema de parqueaderos
 */

// Configuración de la base de datos
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sistema_vigilancia';

try {
    // Conectar a MySQL
    $mysqli = new mysqli($host, $user, $password);
    
    if ($mysqli->connect_error) {
        throw new Exception("Error de conexión: " . $mysqli->connect_error);
    }
    
    // Crear base de datos si no existe
    $mysqli->query("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8 COLLATE utf8_general_ci");
    $mysqli->select_db($database);
    
    echo "Base de datos '$database' lista.\n";
    
    // Crear tabla roles
    $mysqli->query("DROP TABLE IF EXISTS usu_roles");
    $mysqli->query("DROP TABLE IF EXISTS usuarios");
    $mysqli->query("DROP TABLE IF EXISTS roles");
    
    $roles_sql = "
    CREATE TABLE roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre_rol VARCHAR(50) UNIQUE NOT NULL,
        descripcion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $mysqli->query($roles_sql);
    echo "Tabla 'roles' creada.\n";
    
    // Insertar roles básicos
    $mysqli->query("INSERT INTO roles (nombre_rol, descripcion) VALUES 
        ('administrador', 'Acceso completo al sistema'),
        ('vigilante', 'Control de acceso y vigilancia'),
        ('propietario', 'Residente del conjunto')");
    echo "Roles básicos insertados.\n";
    
    // Crear tabla usuarios
    $usuarios_sql = "
    CREATE TABLE usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        apellido VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE,
        telefono VARCHAR(20) NULL,
        apartamento VARCHAR(10) NULL,
        torre VARCHAR(20) NULL,
        estado ENUM('activo', 'inactivo') DEFAULT 'activo',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $mysqli->query($usuarios_sql);
    echo "Tabla 'usuarios' creada.\n";
    
    // Crear tabla usu_roles
    $usu_roles_sql = "
    CREATE TABLE usu_roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        usuarios_id INT NOT NULL,
        roles_idroles INT NOT NULL,
        contraseña VARCHAR(8) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (usuarios_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (roles_idroles) REFERENCES roles(id) ON DELETE CASCADE
    )";
    
    $mysqli->query($usu_roles_sql);
    echo "Tabla 'usu_roles' creada.\n";
    
    // Insertar usuarios de prueba
    $usuarios_prueba = [
        [
            'nombre' => 'Admin',
            'apellido' => 'Sistema',
            'email' => 'admin@quintanares.com',
            'telefono' => '3000000000',
            'apartamento' => 'Admin',
            'torre' => 'Oficina',
            'rol' => 'administrador',
            'contrasena' => 'admin123'
        ],
        [
            'nombre' => 'Juan',
            'apellido' => 'Vigilante',
            'email' => 'vigilante@quintanares.com',
            'telefono' => '3001111111',
            'apartamento' => 'N/A',
            'torre' => 'N/A',
            'rol' => 'vigilante',
            'contrasena' => 'vigilante123'
        ],
        [
            'nombre' => 'Ana',
            'apellido' => 'Propietaria',
            'email' => 'propietario@quintanares.com',
            'telefono' => '3002222222',
            'apartamento' => '101',
            'torre' => 'A',
            'rol' => 'propietario',
            'contrasena' => 'propietario123'
        ]
    ];
    
    foreach ($usuarios_prueba as $usuario) {
        // Insertar usuario
        $sql = "INSERT INTO usuarios (nombre, apellido, email) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        
        if (!$stmt) {
            echo "Error preparando statement: " . $mysqli->error . "\n";
            continue;
        }
        
        $stmt->bind_param("sss", $usuario['nombre'], $usuario['apellido'], $usuario['email']);
        $stmt->execute();
        $usuario_id = $mysqli->insert_id;
        $stmt->close();
        
        // Obtener ID del rol
        $rol_result = $mysqli->query("SELECT id FROM roles WHERE nombre_rol = '{$usuario['rol']}'");
        $rol_data = $rol_result->fetch_assoc();
        $rol_id = $rol_data['id'];
        
        // Hashear contraseña (mismo método que en el login original)
        $contrasena_hash = substr(md5($usuario['contrasena']), 0, 8);
        
        // Insertar relación usuario-rol
        $sql = "INSERT INTO usu_roles (usuarios_id, roles_idroles, contraseña) VALUES (?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        
        if (!$stmt) {
            echo "Error preparando statement de rol: " . $mysqli->error . "\n";
            continue;
        }
        
        $stmt->bind_param("iis", $usuario_id, $rol_id, $contrasena_hash);
        $stmt->execute();
        $stmt->close();
        
        echo "Usuario '{$usuario['nombre']}' creado con rol '{$usuario['rol']}'.\n";
    }
    
    echo "\n=== BASE DE DATOS INICIALIZADA EXITOSAMENTE ===\n";
    echo "\nUsuarios de prueba creados:\n";
    echo "1. Administrador: Cédula 1, contraseña: admin123\n";
    echo "2. Vigilante: Cédula 2, contraseña: vigilante123\n";
    echo "3. Propietario: Cédula 3, contraseña: propietario123\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
