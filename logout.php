<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/db.php';

// --- Include or Define Helper Function ---
if (!function_exists('clearRememberMeData')) {
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
            setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        } catch (Exception $e) {
             error_log("Error clearing remember me data for User $userId: " . $e->getMessage());
             if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
              setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }
    }
}
// --- End Helper Function ---

$logged_out_successfully = false;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Clear Remember Me data
     if (isset($conn) && $conn instanceof mysqli) {
        clearRememberMeData($user_id, $conn);
    } else {
        error_log("Admin Logout Error: Database connection not available to clear remember me token.");
        setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }

    // Unset session variables
    $_SESSION = array();

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy session data
    session_destroy();
    $logged_out_successfully = true;
} else {
    setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    // $logged_out_successfully = true; // Uncomment if desired
}

// Close DB connection
if (isset($conn) && $conn instanceof mysqli) {
   $conn->close();
}

// *** Set the session message BEFORE redirecting ***
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if ($logged_out_successfully) {
    $_SESSION['logout_message'] = "Successfully logged out.";
}


// Redirect to the main site homepage
header("Location: ../index.php");
exit();
?>