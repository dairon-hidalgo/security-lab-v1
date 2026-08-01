<?php

declare(strict_types=1);

require_once __DIR__ . '/session.php';

destroy_current_session();

header('Location: /login.php');
exit;