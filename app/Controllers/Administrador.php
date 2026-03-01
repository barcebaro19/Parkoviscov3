<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\RolModel;

class Administrador extends BaseController
{
    protected $usuarioModel;
    protected $rolModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
        $this->rolModel = new RolModel();
    }

    public function index()
    {
        // Verificar si el usuario es administrador
        if (session()->get('nombre_rol') !== 'administrador') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Panel Administrador',
            'usuarios' => $this->usuarioModel->getUsuariosConRol(),
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('administrador/dashboard', $data);
    }

    public function usuarios()
    {
        if (session()->get('nombre_rol') !== 'administrador') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Gestión de Usuarios',
            'usuarios' => $this->usuarioModel->getUsuariosConRol(),
            'roles' => $this->rolModel->findAll(),
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('administrador/usuarios', $data);
    }

    public function crearUsuario()
    {
        if (session()->get('nombre_rol') !== 'administrador') {
            return redirect()->to('/login');
        }

        if ($this->request->getMethod() === 'post') {
            $data = [
                'nombre' => $this->request->getPost('nombre'),
                'apellido' => $this->request->getPost('apellido'),
                'email' => $this->request->getPost('email'),
                'telefono' => $this->request->getPost('telefono'),
                'apartamento' => $this->request->getPost('apartamento'),
                'torre' => $this->request->getPost('torre'),
                'rol_id' => $this->request->getPost('rol_id'),
                'contrasena' => $this->request->getPost('contrasena')
            ];

            if ($this->usuarioModel->crearUsuarioConRol($data)) {
                session()->setFlashdata('success', 'Usuario creado exitosamente');
                return redirect()->to('/administrador/usuarios');
            } else {
                session()->setFlashdata('error', 'Error al crear usuario');
            }
        }

        $data = [
            'titulo' => 'Crear Usuario',
            'roles' => $this->rolModel->findAll(),
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('administrador/crear_usuario', $data);
    }

    public function eliminarUsuario($id)
    {
        if (session()->get('nombre_rol') !== 'administrador') {
            return redirect()->to('/login');
        }

        if ($this->usuarioModel->delete($id)) {
            session()->setFlashdata('success', 'Usuario eliminado exitosamente');
        } else {
            session()->setFlashdata('error', 'Error al eliminar usuario');
        }

        return redirect()->to('/administrador/usuarios');
    }
}
