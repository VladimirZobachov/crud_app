<?php
require_once 'config/Database.php';
require_once 'controllers/UserController.php';
require_once 'models/User.php';
require_once 'utils/Validator.php';

use Controllers\UserController;

$controller = new UserController();

// Start session and initialize CSRF token if necessary
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get the path and request method
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Concatenate path and method for switch cases
$route = $path . '|' . $method;

switch ($route) {
    case '/create|GET':
        require 'views/create.php';
        break;

    case '/read|GET':
        require 'views/read.php';
        break;

    case '/create|POST':
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            die("Invalid CSRF token.");
        }
        $controller->create($_POST);
        header("Location: /read");
        exit();

    case '/update|GET':  // Display the edit form
        $id = $_GET['id'] ?? null;
        if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
            die("Invalid ID.");
        }
        require 'views/update.php';
        break;

    case '/update|POST':  // Process the edit form submission
        $id = $_POST['id'] ?? null;
        if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
            die("Invalid ID.");
        }
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            die("Invalid CSRF token.");
        }
        $controller->update($id, $_POST);
        header("Location: /read");
        exit();

    case '/delete|POST': // Delete action
        $id = $_POST['id'] ?? null;
        if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
            die("Invalid ID.");
        }
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
            die("Invalid CSRF token.");
        }
        $controller->delete($id);
        header("Location: /read");
        exit();

    default:
        http_response_code(404);
        echo "404 Not Found";
        break;
}



