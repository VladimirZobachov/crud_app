<?php
namespace Utils;

class Validator
{
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail(string $email): ?string
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}