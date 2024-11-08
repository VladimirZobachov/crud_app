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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    $created = $controller->create([
        'name' => $_POST['name'],
        'email' => $_POST['email']
    ]);

    if ($created) {
        header("Location: read.php");
        exit();
    } else {
        echo "Error creating user.";
    }
}
?>

<h2>Create New User</h2>
<form method="POST" action="">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>

    <button type="submit">Create</button>
</form>
<a href="/read">Back to User List</a>
