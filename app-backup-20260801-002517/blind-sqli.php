<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? '1';
$consulted = array_key_exists('id', $_GET);
$exists = false;

if ($consulted) {
    try {
        $pdo = db();

        /*
         * Vulnerabilidad intencional:
         * el valor se concatena en la condición.
         *
         * La respuesta solamente indica verdadero o falso,
         * simulando una inyección SQL ciega.
         */
        $sql = "
            SELECT id
            FROM users
            WHERE id = $id
            LIMIT 1
        ";

        $exists = $pdo->query($sql)->fetchColumn() !== false;
    } catch (Throwable $exception) {
        /*
         * No se muestra el error de PostgreSQL.
         * El comportamiento se presenta como una respuesta falsa.
         */
        $exists = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blind SQL Injection — Service Desk FIIS</title>

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
            width: min(850px, 92%);
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

        .true {
            padding: 25px;
            color: #116329;
            font-size: 22px;
            font-weight: bold;
            background: #e6f4ea;
            border-left: 6px solid #188038;
        }

        .false {
            padding: 25px;
            color: #8b1a10;
            font-size: 22px;
            font-weight: bold;
            background: #fdecea;
            border-left: 6px solid #b3261e;
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

        code {
            padding: 3px 6px;
            background: #edf0f4;
            border-radius: 4px;
        }
    </style>
</head>

<body>
<header>
    <h1>Blind SQL Injection</h1>
    <p>Módulo 6 — Respuesta booleana</p>
</header>

<main>
    <p>
        <a class="button" href="/modules.php">Volver a módulos</a>
    </p>

    <section class="card warning">
        La página no devuelve datos de la consulta ni errores SQL.
        Solamente responde si la condición fue verdadera o falsa.
    </section>

    <section class="card">
        <h2>Comprobar usuario</h2>

        <form method="GET" action="/blind-sqli.php">
            <div>
                <label for="id">Condición asociada al ID</label>

                <input
                    id="id"
                    name="id"
                    value="<?= htmlspecialchars((string) $id, ENT_QUOTES, 'UTF-8') ?>"
                    autocomplete="off"
                >
            </div>

            <button type="submit">Comprobar</button>
        </form>
    </section>

    <?php if ($consulted): ?>
        <section class="card">
            <?php if ($exists): ?>
                <div class="true">
                    Condición verdadera: usuario encontrado
                </div>
            <?php else: ?>
                <div class="false">
                    Condición falsa: usuario no encontrado
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>
</body>
</html>