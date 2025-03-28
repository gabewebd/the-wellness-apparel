<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/includes/db.php'; // Use absolute path for includes

// --- Include or Define Helper Function ---
// Option A: If functions are in a separate file
// require_once __DIR__ . '/includes/auth_functions.php';

// Option B: Define the function here if not using a separate file
if (!function_exists('clearRememberMeData')) {
    /**
     * Clears remember me tokens from DB and cookie.
     * Needs the $conn variable (database connection).
     */
    function clearRememberMeData($userId, $conn) {
         if (!$conn || !($conn instanceof mysqli)) {
             error_log("clearRememberMeData Error: Invalid database connection provided.");
             setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
             return;
         }
        try {
            $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
             if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->close();
             } else {
                  error_log("clearRememberMeData Error: Prepare failed (delete tokens): " . $conn->error);
             }
            setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true); // Expire cookie
        } catch (Exception $e) {
             error_log("Error clearing remember me data for User $userId: " . $e->getMessage());
             if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
              setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true); // Expire cookie
        }
    }
}
// --- End Helper Function ---


// Check if user is logged in before trying to log out
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Clear Remember Me data FIRST (needs user_id from session)
    if (isset($conn) && $conn instanceof mysqli) { // Ensure $conn exists
        clearRememberMeData($user_id, $conn);
    } else {
        error_log("Logout Error: Database connection not available to clear remember me token.");
        // Still attempt to clear cookie even if DB fails
        setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }

    // Now, unset all session variables
    $_SESSION = array();

    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Finally, destroy the session data on the server
    session_destroy();
} else {
     // If not logged in via session, still try to clear a potential lingering cookie
     setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Close the database connection if it's open
if (isset($conn) && $conn instanceof mysqli) {
   $conn->close();
}

// Redirect to the homepage
header('Location: index.php');
exit(); // Stop script execution
?>