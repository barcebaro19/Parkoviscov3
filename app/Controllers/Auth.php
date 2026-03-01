<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\RolModel;

class Auth extends BaseController
{
    public function login()
    {
        // Si ya está logueado, redirigir al dashboard correspondiente
        if (session()->get('id')) {
            $rol = session()->get('nombre_rol');
            switch ($rol) {
                case 'administrador':
                    return redirect()->to('/administrador');
                case 'vigilante':
                    return redirect()->to('/vigilante');
                case 'propietario':
                    return redirect()->to('/propietario');
                default:
                    return redirect()->to('/login');
            }
        }
        
        return view('auth/login');
    }

    public function authenticate()
    {
        $cedula = $this->request->getPost('cedula');
        $contrasena = $this->request->getPost('contrasena');

        log_message('info', 'Auth::authenticate called cedula={cedula} hasPass={hasPass}', [
            'cedula' => (string) $cedula,
            'hasPass' => $contrasena !== null && $contrasena !== '',
        ]);

        // Hash de la contraseña para comparar (mismo método que en registro)
        $contrasena_hash = substr(md5($contrasena), 0, 8);

        log_message('debug', 'Auth::authenticate password hash (md5-8)={hash}', [
            'hash' => (string) $contrasena_hash,
        ]);
        
        $usuarioModel = new UsuarioModel();
        $user = $usuarioModel->getUsuarioConRol($cedula, $contrasena_hash);

        log_message('info', 'Auth::authenticate userFound={found}', [
            'found' => $user ? 'yes' : 'no',
        ]);

        if ($user) {
            $session = session();
            $session->set([
                'id' => $user['id'],
                'nombre' => $user['nombre'],
                'apellido' => $user['apellido'],
                'email' => $user['email'],
                'nombre_rol' => $user['nombre_rol']
            ]);

            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $_SESSION['id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['nombre_rol'] = $user['nombre_rol'];

            log_message('info', 'Auth::authenticate session set role={role}', [
                'role' => (string) ($user['nombre_rol'] ?? ''),
            ]);

            // Redirigir según el rol
            switch ($user['nombre_rol']) {
                case 'administrador':
                    log_message('info', 'Auth::authenticate redirect administrador -> /administrador');
                    return redirect()->to('/administrador');
                case 'vigilante':
                    log_message('info', 'Auth::authenticate redirect vigilante -> /vigilante');
                    return redirect()->to('/vigilante');
                case 'propietario':
                    log_message('info', 'Auth::authenticate redirect propietario -> /propietario');
                    return redirect()->to('/propietario');
                default:
                    log_message('warning', 'Auth::authenticate unknown role={role}', [
                        'role' => (string) ($user['nombre_rol'] ?? ''),
                    ]);
                    return redirect()->to('/login?error=rol_no_valido');
            }
        } else {
            session()->setFlashdata('error', 'Usuario o contraseña incorrectos');
            return redirect()->to('/login?error=invalid');
        }
    }

    public function resetPassword()
    {
        $email = $this->request->getPost('email');

        log_message('info', 'Auth::resetPassword called emailProvided={provided}', [
            'provided' => $email ? 'yes' : 'no',
        ]);

        if (! $email) {
            return redirect()->to('/login');
        }

        $db = db_connect();
        $user = $db->table('usuarios')
            ->select('id, nombre, apellido')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if (! $user) {
            log_message('warning', 'Auth::resetPassword email not found');
            return redirect()->to('/login?error=email_not_found');
        }

        // Generar nueva contraseña temporal
        $tempPassword = 'Temp' . rand(1000, 9999);
        $hashedPassword = md5($tempPassword);

        $db->table('usuarios')
            ->where('email', $email)
            ->update(['contrasena' => $hashedPassword]);

        log_message('info', 'Auth::resetPassword password reset for email={email}', [
            'email' => (string) $email,
        ]);

        return redirect()->to('/login?success=password_reset');
    }

    public function logout()
    {
        session()->destroy();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();
        return redirect()->to('/login');
    }
}
