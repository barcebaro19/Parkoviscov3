<?php

namespace App\Controllers;

class Vigilante extends BaseController
{
    public function index()
    {
        // Verificar si el usuario es vigilante
        if (session()->get('nombre_rol') !== 'vigilante') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Panel Vigilante',
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('vigilante/dashboard', $data);
    }

    public function controlAcceso()
    {
        if (session()->get('nombre_rol') !== 'vigilante') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Control de Acceso',
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('vigilante/control_acceso', $data);
    }

    public function validarQR()
    {
        if (session()->get('nombre_rol') !== 'vigilante') {
            return redirect()->to('/login');
        }

        $qr_data = $this->request->getPost('qr_data');
        
        if ($qr_data) {
            // Lógica para validar QR aquí
            session()->setFlashdata('success', 'QR validado correctamente');
        }

        return redirect()->to('/vigilante/control-acceso');
    }
}
