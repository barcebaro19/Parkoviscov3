<?php
class Conexion {
    private static $instancia = null;
    private $conexion;

    private $servidor = "localhost";
    private $usuario = "root";
    private $contrasena = "";
    private $db = "sistema_vigilancia";

    private function __construct() {
        $this->conexion = new mysqli($this->servidor, $this->usuario, $this->contrasena, $this->db);
        $this->conexion->set_charset('utf8');
        if ($this->conexion->connect_error) {
            die("Falla en la conexión: " . $this->conexion->connect_error);
        }
    }

    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new Conexion();
        }
        return self::$instancia;
    }

    public function getConexion() {
        return $this->conexion;
    }
}
// Uso: $conexion = Conexion::getInstancia()->getConexion();
?>