<?php
require_once __DIR__ . '/../controllers/UserController.php';

use Controllers\UserController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$controller = new UserController();

// Handle the delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify the CSRF token
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Validate the 'id' parameter
    $id = $_POST['id'] ?? null;
    if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
        die("Invalid user ID.");
    }

    // Attempt to delete the user
    $deleted = $controller->delete($id);

    if ($deleted) {
        header("Location: read.php");
        exit();
    } else {
        echo "Error deleting user. User may not exist.";
    }
}