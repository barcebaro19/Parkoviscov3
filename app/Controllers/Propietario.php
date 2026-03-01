<?php

namespace App\Controllers;

class Propietario extends BaseController
{
    public function index()
    {
        // Verificar si el usuario es propietario
        if (session()->get('nombre_rol') !== 'propietario') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Panel Propietario',
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('propietario/dashboard', $data);
    }

    public function misVehiculos()
    {
        if (session()->get('nombre_rol') !== 'propietario') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Mis Vehículos',
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('propietario/mis_vehiculos', $data);
    }

    public function registrarVisitante()
    {
        if (session()->get('nombre_rol') !== 'propietario') {
            return redirect()->to('/login');
        }

        $data = [
            'titulo' => 'Registrar Visitante',
            'nombre_usuario' => session()->get('nombre') . ' ' . session()->get('apellido')
        ];

        return view('propietario/registrar_visitante', $data);
    }
}
