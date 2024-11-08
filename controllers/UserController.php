<?php
namespace Controllers;

use Config\Database;
use Models\User;
use PDO;
use PDOException;
use Utils\Validator;

class UserController
{
    public function create(array $data): bool
    {
        $name = Validator::sanitizeString($data['name']);
        $email = Validator::validateEmail($data['email']);

        if ($name && $email) {
            return User::create($name, $email);
        }

        error_log("Invalid input: name or email");
        return false;
    }

    public function delete(int $id): bool
    {
        return User::delete($id);
    }
    public function readAll(): array
    {
        return User::findAll();
    }

    public function read(int $id): ?array
    {
        return User::find($id);
    }

    public function update($id, $data): bool
    {
        $name = htmlspecialchars(trim($data['name']), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL);

        if ($name && $email) {
            return User::update($id, $name, $email);
        }
        return false;
    }
}