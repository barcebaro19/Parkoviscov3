<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['id', 'nombre', 'apellido', 'email', 'celular'];
    protected $returnType = 'array';

    public function getUsuarioConRol($cedula, $contrasena)
    {
        $builder = $this->db->table('usuarios u');
        $builder->join('usu_roles ur', 'u.id = ur.usuarios_id');
        $builder->join('roles r', 'ur.roles_idroles = r.idroles');
        $builder->where('u.id', $cedula);
        $builder->where('ur.contraseña', $contrasena);
        
        return $builder->get()->getRowArray();
    }

    public function getUsuariosConRol()
    {
        $builder = $this->db->table('usuarios u');
        $builder->join('usu_roles ur', 'u.id = ur.usuarios_id');
        $builder->join('roles r', 'ur.roles_idroles = r.idroles');
        $builder->select('u.*, r.nombre_rol');
        
        return $builder->get()->getResultArray();
    }

    public function crearUsuarioConRol($data)
    {
        $this->db->transStart();
        
        // Insertar usuario
        $usuarioData = [
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'] ?? null,
            'telefono' => $data['telefono'] ?? null,
            'apartamento' => $data['apartamento'] ?? null,
            'torre' => $data['torre'] ?? null,
            'estado' => 'activo'
        ];
        
        $this->insert($usuarioData);
        $usuarioId = $this->getInsertID();
        
        // Insertar rol y contraseña
        $rolData = [
            'usuarios_id' => $usuarioId,
            'roles_idroles' => $data['rol_id'],
            'contraseña' => substr(md5($data['contrasena']), 0, 8)
        ];
        
        $this->db->table('usu_roles')->insert($rolData);
        
        $this->db->transComplete();
        
        return $this->db->transStatus();
    }
}
