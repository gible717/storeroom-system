<?php
/**
 * csrf.php - CSRF Protection Helper
 *
 * Provides functions for generating and validating CSRF tokens
 * to protect forms against Cross-Site Request Forgery attacks.
 *
 * Usage:
 *   In forms:
 *     <?php echo csrf_field(); ?>
 *
 *   In processing scripts:
 *     if (!csrf_validate()) {
 *         die('Invalid CSRF token');
 *     }
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'csrf.php') {
    http_response_code(403);
    exit('Direct access not permitted');
}

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a CSRF token
 *
 * Creates a cryptographically secure token and stores it in the session.
 * Tokens are valid for the current session.
 *
 * @return string The generated token
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generate a hidden CSRF input field
 *
 * Returns an HTML hidden input field containing the CSRF token.
 * Use this in your forms.
 *
 * @return string HTML hidden input element
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate the submitted CSRF token
 *
 * Compares the submitted token with the session token.
 * Uses timing-safe comparison to prevent timing attacks.
 *
 * @param string|null $token Optional token to validate (defaults to $_POST['csrf_token'])
 * @return bool True if valid, false otherwise
 */
function csrf_validate($token = null) {
    // Get token from POST if not provided
    if ($token === null) {
        $token = $_POST['csrf_token'] ?? '';
    }

    // Check if session token exists
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    // Use timing-safe comparison
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Validate CSRF and exit with error if invalid
 *
 * Convenience function that validates and handles the error response.
 * Use at the top of form processing scripts.
 *
 * @param string $redirectUrl Optional URL to redirect to on failure
 * @param string $errorMessage Optional custom error message
 */
function csrf_check($redirectUrl = null, $errorMessage = 'Sesi anda telah tamat. Sila cuba lagi.') {
    if (!csrf_validate()) {
        if ($redirectUrl) {
            $_SESSION['error'] = $errorMessage;
            header("Location: $redirectUrl?error=" . urlencode($errorMessage));
            exit;
        } else {
            http_response_code(403);
            die($errorMessage);
        }
    }
}

/**
 * Regenerate the CSRF token
 *
 * Forces generation of a new token. Call this after sensitive operations
 * like login or password change for extra security.
 *
 * @return string The new token
 */
function csrf_regenerate() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token as meta tag for AJAX requests
 *
 * Returns a meta tag that JavaScript can read for AJAX requests.
 * Add this to your HTML head section.
 *
 * @return string HTML meta element
 */
function csrf_meta() {
    return '<meta name="csrf-token" content="' . htmlspecialchars(csrf_token()) . '">';
}
?>
