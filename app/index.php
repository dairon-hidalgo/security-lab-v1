<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

if (is_authenticated()) {
    header('Location: /panel');
    exit;
}

header('Location: /login');
exit;
