<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Verificar si el usuario está logueado
        if (!session()->get('id')) {
            return redirect()->to('/login');
        }

        // Si se especifica un rol, verificar que el usuario tenga ese rol
        if ($arguments) {
            $userRole = session()->get('nombre_rol');
            if (!in_array($userRole, $arguments)) {
                session()->setFlashdata('error', 'No tienes permisos para acceder a esta página');
                return redirect()->to('/login');
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No se necesita procesamiento después
    }
}
