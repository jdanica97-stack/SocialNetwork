<?php

/**
 * app/helpers/PasswordValidator.php
 *
 * Centralized password complexity validation helper for Registration and Update Password.
 *
 * Requirements:
 *   1. Minimum of 8 characters long
 *   2. At least one uppercase letter (A–Z)
 *   3. At least one lowercase letter (a–z)
 *   4. At least one number (0–9)
 *   5. At least one special character (e.g. @, #, $, %, !)
 *   6. Password confirmation must exactly match
 */
class PasswordValidator
{
    /**
     * Validate a password against complexity requirements.
     *
     * @param string $password The password to validate.
     * @param string|null $confirmPassword Optional confirmation password.
     * @param string $label Prefix for error messages ('Password' or 'New password').
     * @return array Array of error messages, empty if all requirements are met.
     */
    public static function validate(string $password, ?string $confirmPassword = null, string $label = 'Password'): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "{$label} must be at least 8 characters long.";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "{$label} must contain at least one number (0–9).";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "{$label} must contain at least one special character (e.g. !@#$%^&*).";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "{$label} must contain at least one uppercase letter (A–Z).";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "{$label} must contain at least one lowercase letter (a–z).";
        }

        if ($confirmPassword !== null && $password !== $confirmPassword) {
            $errors[] = ($label === 'New password')
                ? 'The new password and confirmation do not match.'
                : 'Passwords do not match.';
        }

        return $errors;
    }

    /**
     * Check if a password meets all complexity rules without generating messages.
     *
     * @param string $password
     * @return bool
     */
    public static function isValid(string $password): bool
    {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/[0-9]/', $password) === 1
            && preg_match('/[^a-zA-Z0-9]/', $password) === 1;
    }
}
