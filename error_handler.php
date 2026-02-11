<?php
/**
 * error_handler.php - Global Error & Exception Handler
 *
 * Catches uncaught exceptions and fatal errors, logs them,
 * and shows a user-friendly error page instead of raw PHP errors.
 *
 * Included via config.php (auto-loaded on every page).
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'error_handler.php') {
    http_response_code(403);
    exit('Direct access not permitted');
}

/**
 * Custom error handler - converts PHP errors to exceptions
 */
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    // Don't handle errors that are suppressed with @
    if (!(error_reporting() & $errno)) {
        return false;
    }

    error_log("[STOREROOM] PHP Error [$errno]: $errstr in $errfile:$errline");

    // In production, suppress display
    if (function_exists('isDebug') && !isDebug()) {
        return true; // Prevent default PHP error handler
    }

    return false; // Let default handler show in debug mode
});

/**
 * Custom exception handler - catches uncaught exceptions
 */
set_exception_handler(function ($exception) {
    error_log("[STOREROOM] Uncaught Exception: " . $exception->getMessage()
        . " in " . $exception->getFile() . ":" . $exception->getLine()
        . "\nStack trace: " . $exception->getTraceAsString());

    // In production, show friendly error page
    if (function_exists('isDebug') && !isDebug()) {
        http_response_code(500);

        // Check if this is an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Ralat sistem. Sila hubungi pentadbir.'
            ]);
        } else {
            // Redirect to error page if it exists
            if (file_exists(__DIR__ . '/500.php')) {
                include __DIR__ . '/500.php';
            } else {
                echo '<h1>Ralat Sistem</h1><p>Sila hubungi pentadbir sistem.</p>';
            }
        }
        exit;
    }

    // In debug mode, show the full error
    throw $exception;
});

/**
 * Shutdown handler - catches fatal errors that bypass set_error_handler
 */
register_shutdown_function(function () {
    $error = error_get_last();

    // Only handle fatal errors
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        error_log("[STOREROOM] Fatal Error: {$error['message']} in {$error['file']}:{$error['line']}");

        if (function_exists('isDebug') && !isDebug()) {
            // Clean any output that was already sent
            if (ob_get_level()) {
                ob_end_clean();
            }

            http_response_code(500);

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ralat sistem. Sila hubungi pentadbir.'
                ]);
            } else {
                if (file_exists(__DIR__ . '/500.php')) {
                    include __DIR__ . '/500.php';
                } else {
                    echo '<h1>Ralat Sistem</h1><p>Sila hubungi pentadbir sistem.</p>';
                }
            }
        }
    }
});

/**
 * Configure PHP error display based on environment
 */
if (function_exists('isDebug') && !isDebug()) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
    error_reporting(E_ALL);
}
?>
