<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? '1';
$results = [];
$sqlExecuted = '';
$errorMessage = null;

try {
    $pdo = db();

    /*
     * Vulnerabilidad intencional del laboratorio:
     * el parámetro id se concatena directamente.
     */
    $sqlExecuted = "
        SELECT id, username, full_name, role
        FROM users
        WHERE id = $id
        ORDER BY id
    ";

    $results = $pdo->query($sqlExecuted)->fetchAll();
} catch (Throwable $exception) {
    $errorMessage = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection — Service Desk FIIS</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #eef2f7;
            color: #172033;
        }

        header {
            padding: 25px;
            color: white;
            background: #172033;
        }

        main {
            width: min(1000px, 92%);
            margin: 30px auto;
        }

        .card {
            margin-bottom: 20px;
            padding: 22px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, .08);
        }

        .warning {
            background: #fff3cd;
            border-left: 5px solid #e6a700;
        }

        .error {
            padding: 14px;
            color: #8b1a10;
            overflow-wrap: anywhere;
            background: #fdecea;
            border-left: 5px solid #b3261e;
        }

        form {
            display: flex;
            gap: 12px;
            align-items: end;
            flex-wrap: wrap;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            width: min(600px, 80vw);
            padding: 11px;
            border: 1px solid #aeb7c5;
            border-radius: 6px;
        }

        button,
        .button {
            display: inline-block;
            padding: 11px 18px;
            color: white;
            text-decoration: none;
            background: #2356a8;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dce1e8;
        }

        th {
            background: #f4f6f9;
        }

        pre {
            padding: 15px;
            overflow-x: auto;
            white-space: pre-wrap;
            color: #e8edf5;
            background: #172033;
            border-radius: 7px;
        }

        code {
            padding: 3px 6px;
            background: #edf0f4;
            border-radius: 4px;
        }
    </style>
</head>

<body>
<header>
    <h1>SQL Injection</h1>
    <p>Módulos 4 y 5 — Prueba manual y automatizada</p>
</header>

<main>
    <p>
        <a class="button" href="/modules.php">Volver a módulos</a>
    </p>

    <section class="card warning">
        <strong>Vulnerabilidad intencional:</strong>
        el parámetro <code>id</code> se concatena directamente en la
        consulta enviada a PostgreSQL.
    </section>

    <section class="card">
        <h2>Buscar usuario por identificador</h2>

        <form method="GET" action="/sqli.php">
            <div>
                <label for="id">Valor del parámetro ID</label>

                <input
                    id="id"
                    name="id"
                    value="<?= htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                >
            </div>

            <button type="submit">Consultar</button>
        </form>
    </section>

    <section class="card">
        <h2>Consulta ejecutada</h2>

        <pre><?= htmlspecialchars($sqlExecuted, ENT_QUOTES, 'UTF-8') ?></pre>
    </section>

    <?php if ($errorMessage !== null): ?>
        <section class="card">
            <h2>Error de PostgreSQL</h2>

            <div class="error">
                <?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        </section>
    <?php endif; ?>

    <section class="card">
        <h2>Resultados: <?= count($results) ?></h2>

        <?php if ($results === []): ?>
            <p>No se encontraron usuarios.</p>
        <?php else: ?>
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
                <?php foreach ($results as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) $user['id']) ?></td>
                        <td><?= htmlspecialchars((string) $user['username']) ?></td>
                        <td><?= htmlspecialchars((string) $user['full_name']) ?></td>
                        <td><?= htmlspecialchars((string) $user['role']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</main>
</body>
</html>