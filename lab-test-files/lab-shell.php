<?php

declare(strict_types=1);

$allowedCommands = [
    'whoami' => 'whoami',
    'pwd' => 'pwd',
    'id' => 'id',
    'uname' => 'uname -a',
];

$commandKey = (string) ($_GET['cmd'] ?? '');
$output = 'Use ?cmd=whoami, ?cmd=pwd, ?cmd=id o ?cmd=uname';

if (isset($allowedCommands[$commandKey])) {
    $output = (string) shell_exec(
        $allowedCommands[$commandKey] . ' 2>&1'
    );
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shell educativa local</title>
    <style>
        body {
            margin: 0;
            padding: 30px;
            font-family: Consolas, monospace;
            background: #111827;
            color: #e5e7eb;
        }

        a {
            color: #93c5fd;
        }

        pre {
            padding: 18px;
            white-space: pre-wrap;
            background: #020617;
            border: 1px solid #334155;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<h1>Shell educativa del laboratorio</h1>
<p>
    Comandos permitidos:
    <a href="?cmd=whoami">whoami</a>,
    <a href="?cmd=pwd">pwd</a>,
    <a href="?cmd=id">id</a>,
    <a href="?cmd=uname">uname</a>
</p>
<pre><?= htmlspecialchars($output, ENT_QUOTES, 'UTF-8') ?></pre>
</body>
</html>
