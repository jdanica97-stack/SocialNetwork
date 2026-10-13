<?php

/**
 * app/helpers/Csrf.php
 *
 * Cross-Site Request Forgery (CSRF) Protection Utility.
 *
 * Responsibilities:
 *   - Secure session initialization with HttpOnly, SameSite, and strict mode.
 *   - Cryptographically secure CSRF token generation via random_bytes().
 *   - Timing-safe validation via hash_equals().
 *   - Form helper for rendering hidden inputs.
 *   - Extraction from POST payloads, headers (X-CSRF-Token), and JSON requests.
 */

class Csrf
{
    public const SESSION_KEY = 'csrf_token';

    /**
     * Safely initialize session with hardened security settings.
     */
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

                session_set_cookie_params([
                    'lifetime' => 0,
                    'path'     => '/',
                    'domain'   => '',
                    'secure'   => $isSecure,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                @ini_set('session.use_strict_mode', '1');
            }

            @session_start();
        }
    }

    /**
     * Retrieve or generate the session CSRF token.
     *
     * @return string
     */
    public static function getToken(): string
    {
        self::initSession();

        if (empty($_SESSION[self::SESSION_KEY]) || !is_string($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Generate HTML hidden input containing the CSRF token.
     *
     * @return string
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    /**
     * Validate an arbitrary token against the current session token.
     *
     * @param string|null $token
     * @return bool
     */
    public static function validate(?string $token): bool
    {
        self::initSession();

        $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }

        if (!is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Extract token from POST parameter, HTTP header, or JSON payload.
     *
     * @return string|null
     */
    public static function getSubmittedToken(): ?string
    {
        // 1. Standard POST body
        if (!empty($_POST['csrf_token']) && is_string($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // 2. Standard X-CSRF-Token header in $_SERVER
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN']) && is_string($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        // 3. getallheaders() fallback (for Apache/Nginx environments)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            foreach ($headers as $key => $val) {
                if (strcasecmp($key, 'X-CSRF-Token') === 0 && is_string($val)) {
                    return $val;
                }
            }
        }

        // 4. JSON body payload fallback
        $raw = file_get_contents('php://input');
        if (!empty($raw)) {
            $json = json_decode($raw, true);
            if (is_array($json) && !empty($json['csrf_token']) && is_string($json['csrf_token'])) {
                return $json['csrf_token'];
            }
        }

        return null;
    }

    /**
     * Validate the current incoming request.
     *
     * @return bool
     */
    public static function check(): bool
    {
        return self::validate(self::getSubmittedToken());
    }
}
