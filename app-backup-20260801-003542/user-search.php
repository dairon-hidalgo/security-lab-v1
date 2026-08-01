<?php

declare(strict_types=1);

$query = $_SERVER['QUERY_STRING'] ?? '';
$target = '/sqli.php' . ($query !== '' ? '?' . $query : '');
header('Location: ' . $target);
exit;
