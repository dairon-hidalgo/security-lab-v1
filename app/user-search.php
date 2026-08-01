<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$pdo = db();

$suppliedId = trim((string) ($_GET['id'] ?? ''));
$executedQuery = '';
$results = [];
$errorMessage = null;
$resultCount = 0;

if ($suppliedId !== '') {
    /*
     * VULNERABILIDAD INTENCIONAL:
     * El contenido de "id" se concatena directamente en la consulta.
     *
     * Esta práctica no debe utilizarse en una aplicación real.
     */
    $executedQuery = "
        SELECT
            id,
            username,
            full_name,
            role
        FROM users
        WHERE id = {$suppliedId}
        ORDER BY id
    ";

    try {
        $statement = $pdo->query($executedQuery);
        $results = $statement->fetchAll();
        $resultCount = count($results);
    } catch (Throwable $exception) {
        $errorMessage = $exception->getMessage();
    }

    /*
     * El registro de auditoría sí utiliza una consulta preparada
     * para que el historial no altere la vulnerabilidad estudiada.
     */
    try {
        $audit = $pdo->prepare(
            '
                INSERT INTO sqli_audit (
                    supplied_id,
                    executed_query,
                    result_count,
                    error_message,
                    client_ip,
                    user_agent
                )
                VALUES (
                    :supplied_id,
                    :executed_query,
                    :result_count,
                    :error_message,
                    :client_ip,
                    :user_agent
                )
            '
        );

        $audit->execute([
            ':supplied_id' => $suppliedId,
            ':executed_query' => $executedQuery,
            ':result_count' => $resultCount,
            ':error_message' => $errorMessage,
            ':client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ]);
    } catch (Throwable $auditException) {
        // La falla del registro no debe impedir la práctica principal.
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

    <title>SQL Injection Manual — Service Desk FIIS</title>

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
            padding: 22px;
        }

        header a {
            color: #dbeafe;
        }

        main {
            width: min(1050px, 94%);
            margin: 30px auto;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            margin-bottom: 20px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
        }

        .warning {
            background: #fff3cd;
            border-left: 5px solid #e6a700;
        }

        .error {
            background: #fde8e7;
            border-left: 5px solid #b3261e;
            color: #7f1d1d;
        }

        form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: end;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 7px;
        }

        input {
            width: 340px;
            max-width: 100%;
            padding: 11px;
            border: 1px solid #aeb7c4;
            border-radius: 6px;
            font-size: 16px;
        }

        button {
            padding: 11px 20px;
            border: 0;
            border-radius: 6px;
            background: #1d4ed8;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1e40af;
        }

        pre {
            overflow-x: auto;
            background: #111827;
            color: #d1fae5;
            padding: 16px;
            border-radius: 7px;
            white-space: pre-wrap;
            word-break: break-word;
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

        .counter {
            font-weight: bold;
            color: #1d4ed8;
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
    <p>Módulo 4 — Inyección SQL manual</p>
    <a href="/index.php">← Volver al panel principal</a>
</header>

<main>
    <section class="card warning">
        <h2>Vulnerabilidad intencional</h2>

        <p>
            El parámetro <code>id</code> se concatena directamente dentro
            de una consulta SQL. Este comportamiento existe exclusivamente
            para realizar una práctica académica en localhost.
        </p>
    </section>

    <section class="card">
        <h2>Buscar usuario por ID</h2>

        <form method="get" action="/user-search.php">
            <div>
                <label for="id">Identificador del usuario</label>

                <input
                    type="text"
                    id="id"
                    name="id"
                    value="<?= htmlspecialchars(
                        $suppliedId,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    placeholder="Ejemplo: 1"
                    autocomplete="off"
                >
            </div>

            <button type="submit">Buscar</button>
        </form>
    </section>

    <?php if ($executedQuery !== ''): ?>
        <section class="card">
            <h2>Consulta ejecutada por la aplicación</h2>

            <pre><?= htmlspecialchars(
                trim($executedQuery),
                ENT_QUOTES,
                'UTF-8'
            ) ?></pre>
        </section>
    <?php endif; ?>

    <?php if ($errorMessage !== null): ?>
        <section class="card error">
            <h2>Error generado por PostgreSQL</h2>

            <p><?= htmlspecialchars(
                $errorMessage,
                ENT_QUOTES,
                'UTF-8'
            ) ?></p>
        </section>
    <?php endif; ?>

    <?php if ($suppliedId !== '' && $errorMessage === null): ?>
        <section class="card">
            <h2>Resultado de la consulta</h2>

            <p class="counter">
                Registros encontrados: <?= $resultCount ?>
            </p>

            <?php if ($resultCount === 0): ?>
                <p>No se encontró ningún usuario.</p>
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
                    <?php foreach ($results as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars(
                                (string) $user['id'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></td>

                            <td><?= htmlspecialchars(
                                (string) $user['username'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></td>

                            <td><?= htmlspecialchars(
                                (string) $user['full_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></td>

                            <td><?= htmlspecialchars(
                                (string) $user['role'],
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <section class="card">
        <h2>Objetivo académico</h2>

        <p>
            Comparar una búsqueda normal con una entrada capaz de modificar
            la condición de la consulta SQL.
        </p>

        <p>
            La corrección futura consistirá en usar parámetros enlazados y
            consultas preparadas.
        </p>
    </section>
</main>
</body>
</html>