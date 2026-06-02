<?php
class AuthController {
    public function login() {
        require_once 'models/Usuario.php';
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? (string)$_POST['password'] : '';

            if ($username === '' || $password === '') {
                $error = 'Debes ingresar usuario y contraseña';
            } else {
                $usuarioModel = new Usuario();
                $user = $usuarioModel->findByUsername($username);
                if (!$user) {
                    $error = 'Usuario no encontrado';
                } else {
                    if ($usuarioModel->verifyPassword($user, $password)) {
                        loginUser($user['username']);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['nombre_completo'] = $user['nombre'] ?? $user['username'];
                        header('Location: ' . url('dashboard'));
                        exit;
                    } else {
                        $error = 'Credenciales inválidas';
                    }
                }
            }
        }

        $data = [
            'title' => 'Iniciar Sesión - ' . APP_NAME,
            'error' => $error,
        ];
        $this->loadView('auth/login', $data);
    }

    public function logout() {
        logoutUser();
        header('Location: ' . url('login'));
        exit;
    }

    private function loadView($view, $data = []) {
        extract($data);
        require_once "views/{$view}.php";
    }
}
?>
