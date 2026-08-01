<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? '1';
$users = [];
$errorMessage = null;
$sqlExecuted = null;

try {
    $pdo = db();

    /*
     * VULNERABILIDAD INTENCIONAL:
     * El parámetro id se concatena directamente en la consulta.
     * No se utilizan consultas preparadas ni validación.
     */
    $sqlExecuted = "
        SELECT
            id,
            username,
            full_name,
            role
        FROM users
        WHERE id = $id
    ";

    $users = $pdo->query($sqlExecuted)->fetchAll();
} catch (Throwable $exception) {
    /*
     * También se muestra el error de PostgreSQL de manera intencional
     * para facilitar el análisis académico del laboratorio.
     */
    $errorMessage = $exception->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Consulta de usuarios — SQL Injection</title>

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
            padding: 24px;
            background: #172033;
            color: white;
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
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .warning {
            padding: 16px;
            background: #fff3cd;
            border-left: 5px solid #e6a700;
        }

        .error {
            padding: 14px;
            overflow-wrap: anywhere;
            color: #8b1a10;
            background: #fdecea;
            border-left: 5px solid #b3261e;
        }

        form {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input {
            min-width: 300px;
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

        th,
        td {
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
            background: #172033;
            color: #e8edf5;
            border-radius: 7px;
        }

        .empty {
            color: #606b7b;
        }

        nav {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
<header>
    <h1>Consulta de usuarios</h1>
    <p>Módulos 4 y 5 — SQL Injection manual y automatizada</p>
</header>

<main>
    <nav>
        <a class="button" href="/">Volver al inicio</a>
    </nav>

    <section class="card warning">
        <strong>Vulnerabilidad intencional:</strong>
        el valor recibido mediante el parámetro
        <code>id</code> se concatena directamente en una consulta SQL.
    </section>

    <section class="card">
        <h2>Buscar usuario por ID</h2>

        <form method="GET" action="user-search.php">
            <div>
                <label for="id">Identificador o expresión</label>

                <input
                    id="id"
                    name="id"
                    type="text"
                    value="<?= htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                >
            </div>

            <button type="submit">Consultar</button>
        </form>
    </section>

    <section class="card">
        <h2>Consulta ejecutada</h2>

        <pre><?= htmlspecialchars((string) $sqlExecuted, ENT_QUOTES, 'UTF-8') ?></pre>
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
        <h2>Resultados</h2>

        <?php if ($users === []): ?>
            <p class="empty">No se encontraron registros.</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Nombre completo</th>
                    <th>Rol</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($users as $user): ?>
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