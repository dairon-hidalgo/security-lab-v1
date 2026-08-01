<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$connectionStatus = 'Sin conexión';
$connectionClass = 'error';
$databaseVersion = 'No disponible';
$users = [];
$errorMessage = null;

try {
    $pdo = db();

    $connectionStatus = 'Conectado correctamente';
    $connectionClass = 'success';

    $databaseVersion = (string) $pdo
        ->query('SELECT version()')
        ->fetchColumn();

    $users = $pdo
        ->query('SELECT id, username, full_name, role FROM users ORDER BY id')
        ->fetchAll();
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Desk FIIS — Laboratorio V1</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #eef2f7;
            color: #172033;
        }

        header {
            background: #172033;
            color: white;
            padding: 24px;
        }

        main {
            width: min(1000px, 92%);
            margin: 30px auto;
        }

        .warning {
            background: #fff3cd;
            border-left: 5px solid #e6a700;
            padding: 16px;
            margin-bottom: 22px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .success {
            color: #137333;
            font-weight: bold;
        }

        .error {
            color: #b3261e;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #dce1e8;
            text-align: left;
        }

        th {
            background: #f4f6f9;
        }

        code {
            background: #edf0f4;
            padding: 3px 7px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
<header>
    <h1>Service Desk FIIS</h1>
    <p>Laboratorio académico de seguridad — Versión 1</p>
</header>

<main>
    <section class="warning">
        <strong>Entorno controlado:</strong>
        esta aplicación será deliberadamente vulnerable y deberá ejecutarse
        únicamente en localhost.
    </section>

    <section class="card">
        <h2>Estado del laboratorio</h2>

        <p>
            Aplicación:
            <strong>Apache + PHP <?= htmlspecialchars(PHP_VERSION) ?></strong>
        </p>

        <p>
            Base de datos:
            <span class="<?= $connectionClass ?>">
                <?= htmlspecialchars($connectionStatus) ?>
            </span>
        </p>

        <p>
            PostgreSQL:
            <?= htmlspecialchars($databaseVersion) ?>
        </p>

        <?php if ($errorMessage !== null): ?>
            <p class="error">
                <?= htmlspecialchars($errorMessage) ?>
            </p>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Usuarios ficticios cargados</h2>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Nombre</th>
                <th>Rol</th>
            </tr>
            </thead>

            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars((string) $user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="card">
        <h2>Primer módulo pendiente</h2>

        <p>
            El siguiente módulo será el inicio de sesión vulnerable para la
            prueba controlada de fuerza bruta.
        </p>

        <code>/login.php</code>
    </section>
</main>
</body>
</html>
