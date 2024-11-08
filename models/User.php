<?php
namespace Models;

use Config\Database;
use PDO;
use PDOException;

class User
{
    public static function create(string $name, string $email): bool
    {
        try {
            $stmt = Database::getConnection()->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
            return $stmt->execute([$name, $email]);
        } catch (PDOException $e) {
            error_log("Create Error: " . $e->getMessage());
            return false;
        }
    }

    public static function findAll(): array
    {
        $stmt = Database::getConnection()->query("SELECT * FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::getConnection()->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function update(int $id, string $name, string $email): bool
    {
        try {
            $stmt = Database::getConnection()->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            return $stmt->execute([$name, $email, $id]);
        } catch (PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
            return false;
        }
    }

    public static function delete(int $id): bool
    {
        try {
            $stmt = Database::getConnection()->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Delete Error: " . $e->getMessage());
            return false;
        }
    }
}