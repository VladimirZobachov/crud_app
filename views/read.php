<?php
require_once __DIR__ . '/../controllers/UserController.php';

use Controllers\UserController;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new UserController();
$users = $controller->readAll();
?>

<h2>User List</h2>
<a href="create">Add New User</a>
<table border="1">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($users)): ?>
        <tr>
            <td colspan="4">No users found.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="update?id=<?= urlencode($user['id']) ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>">Edit</a>
                    <form method="POST" action="/delete" style="display:inline;">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($user['id'], ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>


