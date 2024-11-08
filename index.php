<?php
require_once 'vendor/autoload.php';

use Config\Database;
use Controllers\UserController;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include routes to handle incoming requests
require_once 'routes.php';

