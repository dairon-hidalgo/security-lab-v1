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
         * Vulnerabilidades intencionales de la V1:
         * - Contraseñas almacenadas en texto plano.
         * - Sin bloqueo de cuenta.
         * - Sin límite de intentos.
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Acceso — Service Desk FIIS</title>

    <link rel="stylesheet" href="/styles.css">
</head>

<body>
<div class="login-layout">
    <section class="login-hero">
        <div class="brand">
            <div class="brand-logo">FI</div>

            <div>
                <div class="brand-name">Service Desk FIIS</div>
                <div class="brand-subtitle">
                    Laboratorio de seguridad informática
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
                Plataforma académica diseñada para documentar, ejecutar y
                comparar pruebas de seguridad sobre una aplicación construida
                con PHP, Apache, PostgreSQL y Docker.
            </p>

            <div class="hero-features">
                <article class="hero-feature">
                    <strong>PHP 8.2</strong>
                    <span>Aplicación y lógica del laboratorio</span>
                </article>

                <article class="hero-feature">
                    <strong>PostgreSQL 16</strong>
                    <span>Persistencia y consultas de prueba</span>
                </article>

                <article class="hero-feature">
                    <strong>Docker</strong>
                    <span>Despliegue local reproducible</span>
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
                    <div class="brand-name">Service Desk FIIS</div>
                    <div class="brand-subtitle">Laboratorio V1</div>
                </div>
            </div>

            <div class="login-heading">
                <span>Versión vulnerable</span>

                <h2>Bienvenido nuevamente</h2>

                <p>
                    Ingresa con una cuenta ficticia para acceder al laboratorio.
                </p>
            </div>

            <div class="login-card">
                <?php if ($errorMessage !== null): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($errorMessage) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/login.php" autocomplete="off">
                    <div class="form-group">
                        <label for="username">Nombre de usuario</label>

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
                        <label for="password">Contraseña</label>

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
                                aria-label="Mostrar contraseña"
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
                Aplicación deliberadamente vulnerable. No utilizar datos reales.
            </p>
        </div>
    </section>
</div>

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
    const hidden = passwordInput.type === 'password';

    passwordInput.type = hidden ? 'text' : 'password';
    passwordToggle.textContent = hidden ? 'Ocultar' : 'Ver';
});
</script>
</body>
</html>