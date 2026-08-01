<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth-log.php';

if (is_authenticated()) {
    header('Location: /panel');
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
         * Debilidades intencionales de la V1:
         * - Contraseñas almacenadas como texto plano.
         * - Sin límite de intentos.
         * - Sin bloqueo temporal.
         * - Sin segundo factor.
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

        $wasSuccessful = $user !== false;

        register_login_attempt(
            $pdo,
            $username,
            $wasSuccessful
        );

        if ($wasSuccessful) {
            $_SESSION['user'] = [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
            ];

            $_SESSION['login_time'] = date('Y-m-d H:i:s');

            header('Location: /panel');
            exit;
        }

        $errorMessage = 'Las credenciales ingresadas no son correctas.';
    } catch (Throwable $exception) {
        $errorMessage = 'No fue posible establecer conexión con el servicio.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Acceso — Service Desk FIIS</title>

    <link
        rel="icon"
        href="/assets/favicon.svg"
        type="image/svg+xml"
    >

    <link rel="stylesheet" href="/styles.css">
</head>

<body class="login-body">
<div class="login-layout">
    <main class="login-box">
        <div class="login-brand">
            <div class="brand-logo">FI</div>

            <div>
                <div class="brand-name">Service Desk FIIS</div>

                <span class="brand-subtitle">
                    Acceso al sistema
                </span>
            </div>
        </div>

        <div class="login-heading">
            <span>Acceso al sistema</span>

            <h2>Iniciar sesión</h2>

            <p>
                Ingresa con tu cuenta para acceder al panel.
            </p>
        </div>

        <div class="login-card">
            <?php if ($errorMessage !== null): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form
                method="post"
                action="/login"
                autocomplete="off"
            >
                <div class="form-group">
                    <label for="username">
                        Nombre de usuario
                    </label>

                    <div class="input-wrapper">
                        <span class="input-icon">●</span>

                        <input
                            class="input-control"
                            type="text"
                            id="username"
                            name="username"
                            value="<?= htmlspecialchars($username) ?>"
                            placeholder="Ingresa tu usuario"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">
                        Contraseña
                    </label>

                    <div class="input-wrapper">
                        <span class="input-icon">◆</span>

                        <input
                            class="input-control"
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ingresa tu contraseña"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="passwordToggle"
                        >
                            Ver
                        </button>
                    </div>
                </div>

                <button type="submit" class="primary-button">
                    Ingresar al sistema
                    <span>→</span>
                </button>
            </form>
        </div>

        <p class="login-note">
            Service Desk FIIS · Soporte y mesa de ayuda
        </p>
    </main>
</div>

<script src="/assets/app.js"></script>

<script>
const passwordInput = document.getElementById('password');
const passwordToggle = document.getElementById('passwordToggle');

passwordToggle.addEventListener('click', () => {
    const passwordIsHidden = passwordInput.type === 'password';

    passwordInput.type = passwordIsHidden ? 'text' : 'password';
    passwordToggle.textContent = passwordIsHidden ? 'Ocultar' : 'Ver';
});
</script>
</body>
</html>