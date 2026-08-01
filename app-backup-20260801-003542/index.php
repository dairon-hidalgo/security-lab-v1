<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

if (is_authenticated()) {
    header('Location: /dashboard.php');
    exit;
}

header('Location: /login.php');
exit;