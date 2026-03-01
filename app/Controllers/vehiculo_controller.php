<?php
session_start();

// Verificar que el usuario esté logueado y sea propietario
if(!isset($_SESSION['nombre']) || $_SESSION['nombre_rol'] !== 'propietario') {
    header('Location: ../../public/login.php');
    exit();
}

require_once __DIR__ . "/../Models/conexion.php";

// Obtener la conexión a la base de datos
$conexion = Conexion::getInstancia()->getConexion();

// Función para validar datos del vehículo
function validarDatosVehiculo($datos) {
    $errores = [];
    
    // Validar placa
    if(empty($datos['placa'])) {
        $errores[] = "La placa es obligatoria";
    } elseif(!preg_match('/^[A-Z]{3}-[0-9]{3}$/', $datos['placa']) && 
             !preg_match('/^[A-Z]{3}[0-9]{3}$/', $datos['placa']) &&
             !preg_match('/^[A-Z]{3}-[0-9]{2}[A-Z]$/', $datos['placa']) &&
             !preg_match('/^[A-Z]{3}[0-9]{2}[A-Z]$/', $datos['placa'])) {
        $errores[] = "La placa debe tener el formato ABC-123, ABC123 (carros) o ABC-12D, ABC12D (motos)";
    }
    
    // Validar marca
    if(empty($datos['marca'])) {
        $errores[] = "La marca es obligatoria";
    } elseif(strlen($datos['marca']) < 2) {
        $errores[] = "La marca debe tener al menos 2 caracteres";
    }
    
    // Validar modelo
    if(empty($datos['modelo'])) {
        $errores[] = "El modelo es obligatorio";
    } elseif(strlen($datos['modelo']) < 2) {
        $errores[] = "El modelo debe tener al menos 2 caracteres";
    }
    
    // Validar color
    if(empty($datos['color'])) {
        $errores[] = "El color es obligatorio";
    }
    
    // Validar año
    if(empty($datos['ano'])) {
        $errores[] = "El año es obligatorio";
    } elseif(!is_numeric($datos['ano']) || $datos['ano'] < 1900 || $datos['ano'] > date('Y') + 1) {
        $errores[] = "El año debe ser un número válido entre 1900 y " . (date('Y') + 1);
    }
    
    // Validar tipo de vehículo
    $tipos_validos = ['carro', 'moto', 'bicicleta', 'camioneta', 'bus'];
    if(empty($datos['tipo_vehiculo']) || !in_array($datos['tipo_vehiculo'], $tipos_validos)) {
        $errores[] = "El tipo de vehículo es obligatorio y debe ser válido";
    }
    
    return $errores;
}

// Función para verificar si la placa ya existe
function verificarPlacaExistente($conexion, $placa, $id_vehiculo = null) {
    $sql = "SELECT id_vehiculo FROM vehiculo WHERE placa = ?";
    $params = [$placa];
    
    if($id_vehiculo) {
        $sql .= " AND id_vehiculo != ?";
        $params[] = $id_vehiculo;
    }
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Procesar formulario de agregar vehículo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'agregar_vehiculo') {
        $placa = strtoupper(trim($_POST['placa']));
        $marca = trim($_POST['marca']);
        $modelo = trim($_POST['modelo']);
        $color = trim($_POST['color']);
        $ano = intval($_POST['ano']);
        $tipo_vehiculo = $_POST['tipo_vehiculo'];
        $observaciones = trim($_POST['observaciones']);
        $id_usuario = $_SESSION['id'];
        
        // Validar datos
        $datos = [
            'placa' => $placa,
            'marca' => $marca,
            'modelo' => $modelo,
            'color' => $color,
            'ano' => $ano,
            'tipo_vehiculo' => $tipo_vehiculo
        ];
        
        $errores = validarDatosVehiculo($datos);
        
        // Verificar si la placa ya existe
        if(verificarPlacaExistente($conexion, $placa)) {
            $errores[] = "Ya existe un vehículo con esta placa";
        }
        
        if(empty($errores)) {
            // Insertar vehículo
            $stmt = $conexion->prepare("INSERT INTO vehiculo (id_usuario, placa, marca, modelo, color, año, tipo_vehiculo, observaciones, estado) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo')");
            $stmt->bind_param("issssiss", $id_usuario, $placa, $marca, $modelo, $color, $ano, $tipo_vehiculo, $observaciones);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = 'success';
                $_SESSION['mensaje_texto'] = 'Vehículo agregado exitosamente';
            } else {
                $_SESSION['mensaje'] = 'error';
                $_SESSION['mensaje_texto'] = 'Error al agregar el vehículo: ' . $conexion->error;
            }
        } else {
            $_SESSION['mensaje'] = 'error';
            $_SESSION['mensaje_texto'] = implode('<br>', $errores);
        }
        
        // Redirigir a la página correcta
        header('Location: ../../public/usuario.php');
        exit();
    }
    
    // Procesar formulario de editar vehículo
    if ($_POST['action'] === 'editar_vehiculo') {
        $id_vehiculo = intval($_POST['id_vehiculo']);
        $placa = strtoupper(trim($_POST['placa']));
        $marca = trim($_POST['marca']);
        $modelo = trim($_POST['modelo']);
        $color = trim($_POST['color']);
        $ano = intval($_POST['ano']);
        $tipo_vehiculo = $_POST['tipo_vehiculo'];
        $observaciones = trim($_POST['observaciones']);
        $estado = $_POST['estado'];
        $id_usuario = $_SESSION['id'];
        
        // Validar datos
        $datos = [
            'placa' => $placa,
            'marca' => $marca,
            'modelo' => $modelo,
            'color' => $color,
            'ano' => $ano,
            'tipo_vehiculo' => $tipo_vehiculo
        ];
        
        $errores = validarDatosVehiculo($datos);
        
        // Verificar si la placa ya existe (excluyendo el vehículo actual)
        if(verificarPlacaExistente($conexion, $placa, $id_vehiculo)) {
            $errores[] = "Ya existe otro vehículo con esta placa";
        }
        
        // Verificar que el vehículo pertenezca al usuario
        $stmt = $conexion->prepare("SELECT id_vehiculo FROM vehiculo WHERE id_vehiculo = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_vehiculo, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows === 0) {
            $errores[] = "No tienes permisos para editar este vehículo";
        }
        
        if(empty($errores)) {
            // Actualizar vehículo
            $stmt = $conexion->prepare("UPDATE vehiculo SET placa = ?, marca = ?, modelo = ?, color = ?, año = ?, tipo_vehiculo = ?, observaciones = ?, estado = ? WHERE id_vehiculo = ? AND id_usuario = ?");
            $stmt->bind_param("ssssisssii", $placa, $marca, $modelo, $color, $año, $tipo_vehiculo, $observaciones, $estado, $id_vehiculo, $id_usuario);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = 'success';
                $_SESSION['mensaje_texto'] = 'Vehículo actualizado exitosamente';
            } else {
                $_SESSION['mensaje'] = 'error';
                $_SESSION['mensaje_texto'] = 'Error al actualizar el vehículo: ' . $conexion->error;
            }
        } else {
            $_SESSION['mensaje'] = 'error';
            $_SESSION['mensaje_texto'] = implode('<br>', $errores);
        }
        
        // Redirigir a la página correcta
        header('Location: ../../public/usuario.php');
        exit();
    }
    
    // Procesar eliminación de vehículo
    if ($_POST['action'] === 'eliminar_vehiculo') {
        $id_vehiculo = intval($_POST['id_vehiculo']);
        $id_usuario = $_SESSION['id'];
        
        // Verificar que el vehículo pertenezca al usuario
        $stmt = $conexion->prepare("SELECT id_vehiculo FROM vehiculo WHERE id_vehiculo = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $id_vehiculo, $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            // Eliminar vehículo
            $stmt = $conexion->prepare("DELETE FROM vehiculo WHERE id_vehiculo = ? AND id_usuario = ?");
            $stmt->bind_param("ii", $id_vehiculo, $id_usuario);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = 'success';
                $_SESSION['mensaje_texto'] = 'Vehículo eliminado exitosamente';
            } else {
                $_SESSION['mensaje'] = 'error';
                $_SESSION['mensaje_texto'] = 'Error al eliminar el vehículo: ' . $conexion->error;
            }
        } else {
            $_SESSION['mensaje'] = 'error';
            $_SESSION['mensaje_texto'] = 'No tienes permisos para eliminar este vehículo';
        }
        
        // Redirigir a la página correcta
        header('Location: ../../public/usuario.php');
        exit();
    }
}

// Si no es una acción válida, redirigir
header('Location: ../../public/usuario.php');
exit();
?>
