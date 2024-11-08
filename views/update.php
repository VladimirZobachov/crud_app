<?php
require_once __DIR__ . '/../controllers/UserController.php';

use Controllers\UserController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new UserController();
$id = $_GET['id'] ?? $_POST['id'] ?? null;

// Check if ID is valid
if (!$id || !filter_var($id, FILTER_VALIDATE_INT)) {
    die("Invalid ID.");
}

// Handle GET request: display the edit form
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch the current user data
    $user = $controller->read($id);
    if (!$user) {
        die("User not found.");
    }
}

// Handle POST request: process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    // Attempt to update the user
    $updated = $controller->update($id, [
        'name' => $_POST['name'],
        'email' => $_POST['email']
    ]);

    if ($updated) {
        header("Location: /read");
        exit();
    } else {
        echo "Error updating user.";
    }
}
?>

<h2>Update User</h2>
<form method="POST" action="/update">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">

    Name: <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required><br>

    <button type="submit">Update</button>
</form>
<a href="/read">Back to User List</a>




