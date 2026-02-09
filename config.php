<?php
/**
 * config.php - Environment Configuration Loader
 *
 * Loads environment variables from .env file and provides
 * helper functions for accessing configuration values.
 *
 * Usage:
 *   require_once 'config.php';
 *   $dbHost = env('DB_HOST', 'localhost');
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'config.php') {
    http_response_code(403);
    exit('Direct access not permitted');
}

/**
 * Load environment variables from .env file
 */
function loadEnv($path = null) {
    $envFile = $path ?? __DIR__ . '/.env';

    if (!file_exists($envFile)) {
        // In production, .env should exist. In development, fall back gracefully.
        return false;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }

            // Set in environment
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }

    return true;
}

/**
 * Get environment variable with optional default
 *
 * @param string $key Environment variable name
 * @param mixed $default Default value if not found
 * @return mixed
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    // Convert string booleans
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
        case 'empty':
        case '(empty)':
            return '';
    }

    return $value;
}

/**
 * Check if running in debug mode
 *
 * @return bool
 */
function isDebug() {
    return env('APP_DEBUG', false) === true;
}

/**
 * Check if running in production environment
 *
 * @return bool
 */
function isProduction() {
    return env('APP_ENV', 'production') === 'production';
}

/**
 * Get a safe error message for display
 *
 * In production, hides technical details and logs the real error.
 * In development, shows the full error message.
 *
 * @param string $userMessage Generic message shown to users
 * @param string $technicalDetail The actual exception/error message
 * @return string
 */
function safeError($userMessage, $technicalDetail = '') {
    if ($technicalDetail) {
        error_log("[STOREROOM ERROR] $userMessage - $technicalDetail");
    }
    if (isDebug()) {
        return $userMessage . ($technicalDetail ? ': ' . $technicalDetail : '');
    }
    return $userMessage;
}

// Auto-load .env on include
loadEnv();
?>
