<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\RolModel;

class Auth extends BaseController
{
    public function login()
    {
        return redirect()->to(base_url('login.php'));
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
                    log_message('info', 'Auth::authenticate redirect administrador -> /Administrador1.php');
                    return redirect()->to(base_url('Administrador1.php'));
                case 'vigilante':
                    log_message('info', 'Auth::authenticate redirect vigilante -> /vigilante.php');
                    return redirect()->to(base_url('vigilante.php'));
                case 'propietario':
                    log_message('info', 'Auth::authenticate redirect propietario -> /usuario.php');
                    return redirect()->to(base_url('usuario.php'));
                default:
                    log_message('warning', 'Auth::authenticate unknown role={role}', [
                        'role' => (string) ($user['nombre_rol'] ?? ''),
                    ]);
                    return redirect()->to(base_url('login.php?error=rol_no_valido'));
            }
        } else {
            session()->setFlashdata('error', 'Usuario o contraseña incorrectos');
            return redirect()->to(base_url('login.php?error=invalid'));
        }
    }

    public function resetPassword()
    {
        $email = $this->request->getPost('email');

        log_message('info', 'Auth::resetPassword called emailProvided={provided}', [
            'provided' => $email ? 'yes' : 'no',
        ]);

        if (! $email) {
            return redirect()->to(base_url('login.php'));
        }

        $db = db_connect();
        $user = $db->table('usuarios')
            ->select('id, nombre, apellido')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if (! $user) {
            log_message('warning', 'Auth::resetPassword email not found');
            return redirect()->to(base_url('login.php?error=email_not_found'));
        }

        $new_password = substr(md5(uniqid()), 0, 8);
        $password_hash = substr(md5($new_password), 0, 8);

        $db->table('usu_roles')
            ->where('usuarios_id', (int) $user['id'])
            ->update(['contraseña' => $password_hash]);

        log_message('info', 'Auth::resetPassword updated password for userId={id}', [
            'id' => (string) $user['id'],
        ]);

        return redirect()->to(base_url('login.php?success=password_reset'));
    }

    public function logout()
    {
        session()->destroy();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];
        session_destroy();
        return redirect()->to(base_url('login.php'));
    }
}
