<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

if (is_authenticated()) {
    header('Location: /dashboard.php');
    exit;
}

$errorMessage = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    try {
        $pdo = db();

        /*
         * Vulnerabilidades intencionales de autenticación:
         * - Contraseñas almacenadas como texto plano.
         * - Sin límite de intentos.
         * - Sin bloqueo temporal.
         * - Sin CAPTCHA.
         * - Sin MFA.
         */
        $statement = $pdo->prepare(
            'SELECT id, username, password, full_name, role
             FROM users
             WHERE username = :username
             AND password = :password'
        );

        $statement->execute([
            'username' => $username,
            'password' => $password,
        ]);

        $user = $statement->fetch();

        if ($user !== false) {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
            ];

            $_SESSION['login_time'] = date('Y-m-d H:i:s');

            header('Location: /dashboard.php');
            exit;
        }

        $errorMessage = 'Usuario o contraseña incorrectos.';
    } catch (Throwable $exception) {
        $errorMessage = 'No fue posible consultar la base de datos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Service Desk FIIS</title>
    <link rel="stylesheet" href="/styles.css">
</head>

<body>
<div class="login-page">
    <section class="login-info">
        <h1>Service Desk FIIS</h1>

        <p>
            Laboratorio académico de seguridad informática construido con
            PHP 8.2, Apache, PostgreSQL 16 y Docker.
        </p>

        <p>
            Esta es la versión deliberadamente vulnerable. Debe utilizarse
            exclusivamente en localhost y con información ficticia.
        </p>
    </section>

    <section class="login-form-container">
        <div class="login-card">
            <div class="card">
                <span class="badge">V1 vulnerable</span>

                <h2>Iniciar sesión</h2>

                <p>
                    Ingresa con una de las cuentas ficticias del laboratorio.
                </p>

                <?php if ($errorMessage !== null): ?>
                    <div class="alert-error">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/login.php" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Usuario</label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($username) ?>"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                        >
                    </div>

                    <button type="submit" class="button">
                        Ingresar
                    </button>
                </form>

                <div class="credentials">
                    <strong>Cuentas de demostración</strong>

                    <code>admin / admin123</code>
                    <code>analista / fiis2026</code>
                    <code>usuario / password</code>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
</html>