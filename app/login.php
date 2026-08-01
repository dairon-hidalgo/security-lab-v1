<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth-log.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if (is_authenticated()) {
    header('Location: /dashboard.php');
    exit;
}

$errorMessage = null;
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $submittedToken = isset($_POST['csrf_token'])
        ? (string) $_POST['csrf_token']
        : null;

    if (!csrf_token_is_valid($submittedToken)) {
        http_response_code(400);
        $errorMessage = 'La solicitud no es válida. Actualiza la página.';
    } elseif (
        $username === ''
        || strlen($username) > 50
        || strlen($password) > 200
    ) {
        $errorMessage = 'Las credenciales ingresadas no son correctas.';
    } else {
        try {
            $pdo = db();

            if (login_is_temporarily_locked($pdo, $username)) {
                http_response_code(429);

                $errorMessage =
                    'Demasiados intentos fallidos. Inténtalo nuevamente '
                    . 'dentro de 15 minutos.';
            } else {
                $statement = $pdo->prepare(
                    'SELECT id, username, password, full_name, role
                     FROM users
                     WHERE username = :username
                     LIMIT 1'
                );

                $statement->execute([
                    'username' => $username,
                ]);

                $user = $statement->fetch();

                $wasSuccessful = $user !== false
                    && password_verify(
                        $password,
                        (string) $user['password']
                    );

                register_login_attempt(
                    $pdo,
                    $username,
                    $wasSuccessful
                );

                if ($wasSuccessful) {
                    if (
                        password_needs_rehash(
                            (string) $user['password'],
                            PASSWORD_DEFAULT
                        )
                    ) {
                        $newHash = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $update = $pdo->prepare(
                            'UPDATE users
                             SET password = :password
                             WHERE id = :id'
                        );

                        $update->execute([
                            'password' => $newHash,
                            'id' => (int) $user['id'],
                        ]);
                    }

                    establish_authenticated_session($user);

                    header('Location: /dashboard.php');
                    exit;
                }

                usleep(350000);

                $errorMessage =
                    'Las credenciales ingresadas no son correctas.';
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());

            $errorMessage =
                'No fue posible procesar el inicio de sesión.';
        }
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

    <title>Acceso — FIIS Security Lab</title>

    <script>
        (() => {
            const savedTheme = localStorage.getItem('securityLabTheme');

            if (savedTheme) {
                document.documentElement.dataset.theme = savedTheme;
            }
        })();
    </script>

    <link
        rel="icon"
        href="/assets/favicon.svg"
        type="image/svg+xml"
    >

    <link rel="stylesheet" href="/styles.css">
</head>

<body class="login-body">
<div class="login-layout">
    <section class="login-hero">
        <div class="brand">
            <div class="brand-logo">FI</div>

            <div>
                <div class="brand-name">FIIS Security Lab</div>

                <div class="brand-subtitle">
                    Service Desk · Versión segura
                </div>
            </div>
        </div>

        <div class="hero-content">
            <div class="hero-chip">
                <span class="hero-chip-dot"></span>
                Entorno local activo
            </div>

            <h1>
                Seguridad web aplicada en un escenario controlado.
            </h1>

            <p>
                Laboratorio académico desarrollado con PHP 8.2,
                Apache, PostgreSQL 16 y Docker Desktop.
            </p>

            <div class="hero-features">
                <article class="hero-feature">
                    <strong>PHP 8.2</strong>
                    <span>Aplicación y lógica web</span>
                </article>

                <article class="hero-feature">
                    <strong>PostgreSQL 16</strong>
                    <span>Persistencia del laboratorio</span>
                </article>

                <article class="hero-feature">
                    <strong>Docker</strong>
                    <span>Despliegue reproducible</span>
                </article>
            </div>
        </div>

        <div class="hero-footer">
            Uso exclusivamente académico · localhost · datos ficticios
        </div>
    </section>

    <section class="login-side">
        <div class="login-box">
            <div class="mobile-brand">
                <div class="brand-logo">FI</div>

                <div>
                    <div class="brand-name">FIIS Security Lab</div>
                    <div class="brand-subtitle">Service Desk V2</div>
                </div>
            </div>

            <div class="login-heading">
                <span>Versión segura</span>

                <h2>Iniciar sesión</h2>

                <p>
                    Utiliza una cuenta ficticia para ingresar al laboratorio.
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
                    action="/login.php"
                    autocomplete="off"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
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
                        Ingresar al laboratorio
                        <span>→</span>
                    </button>
                </form>

                <div class="demo-area">
                    <div class="demo-title">
                        Accesos de demostración
                    </div>

                    <div class="demo-users">
                        <button
                            type="button"
                            class="demo-user"
                            data-user="admin"
                            data-password="admin123"
                        >
                            <span class="demo-user-info">
                                <span class="demo-avatar">AD</span>

                                <span>
                                    <strong>Administrador</strong>
                                    <small>admin / admin123</small>
                                </span>
                            </span>

                            <span>Usar</span>
                        </button>

                        <button
                            type="button"
                            class="demo-user"
                            data-user="analista"
                            data-password="fiis2026"
                        >
                            <span class="demo-user-info">
                                <span class="demo-avatar">AN</span>

                                <span>
                                    <strong>Analista</strong>
                                    <small>analista / fiis2026</small>
                                </span>
                            </span>

                            <span>Usar</span>
                        </button>

                        <button
                            type="button"
                            class="demo-user"
                            data-user="usuario"
                            data-password="password"
                        >
                            <span class="demo-user-info">
                                <span class="demo-avatar">US</span>

                                <span>
                                    <strong>Usuario</strong>
                                    <small>usuario / password</small>
                                </span>
                            </span>

                            <span>Usar</span>
                        </button>
                    </div>
                </div>
            </div>

            <p class="login-note">
                Aplicación reforzada para comparar los controles de seguridad.
                No utilizar información real.
            </p>
        </div>
    </section>
</div>

<script src="/assets/app.js"></script>

<script>
const usernameInput = document.getElementById('username');
const passwordInput = document.getElementById('password');
const passwordToggle = document.getElementById('passwordToggle');

document.querySelectorAll('.demo-user').forEach((button) => {
    button.addEventListener('click', () => {
        usernameInput.value = button.dataset.user;
        passwordInput.value = button.dataset.password;
        passwordInput.focus();
    });
});

passwordToggle.addEventListener('click', () => {
    const passwordIsHidden = passwordInput.type === 'password';

    passwordInput.type = passwordIsHidden ? 'text' : 'password';
    passwordToggle.textContent = passwordIsHidden ? 'Ocultar' : 'Ver';
});
</script>
</body>
</html>