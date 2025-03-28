<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'includes/db.php'; // Ensure DB connection is included

// --- Add Helper Functions (Place these near the top or in a separate functions file) ---

/**
 * Generates a secure token pair and stores it in the database.
 * Returns an array ['selector' => ..., 'validator' => ...] on success, false on failure.
 */
function createRememberMeToken($userId, $conn) {
    $selector = bin2hex(random_bytes(16)); // 128-bit selector
    $validator = bin2hex(random_bytes(32)); // 256-bit validator
    $hashedValidator = password_hash($validator, PASSWORD_DEFAULT); // Hash validator for DB storage
    $expires = date('Y-m-d H:i:s', time() + (86400 * 30)); // 30 days expiration

    try {
        // Delete any old tokens for this user first
        $deleteStmt = $conn->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
        if (!$deleteStmt) throw new Exception("Prepare failed (delete tokens): " . $conn->error);
        $deleteStmt->bind_param("i", $userId);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Insert the new token
        $insertStmt = $conn->prepare("INSERT INTO auth_tokens (user_id, selector, hashed_validator, expires) VALUES (?, ?, ?, ?)");
        if (!$insertStmt) throw new Exception("Prepare failed (insert token): " . $conn->error);
        $insertStmt->bind_param("isss", $userId, $selector, $hashedValidator, $expires);

        if ($insertStmt->execute()) {
            $insertStmt->close();
            return ['selector' => $selector, 'validator' => $validator, 'expires_timestamp' => strtotime($expires)];
        } else {
            throw new Exception("Execute failed (insert token): " . $insertStmt->error);
        }
    } catch (Exception $e) {
        error_log("Remember Me Token Creation Error for User $userId: " . $e->getMessage());
        // Ensure statements are closed if they exist
        if (isset($deleteStmt) && $deleteStmt) $deleteStmt->close();
        if (isset($insertStmt) && $insertStmt) $insertStmt->close();
        return false;
    }
}

/**
 * Sets the remember me cookie.
 */
function setRememberMeCookie($selector, $validator, $expiresTimestamp) {
    $cookieValue = $selector . ':' . $validator;
    setcookie(
        'remember_me',          // Cookie name
        $cookieValue,           // Value (selector:validator)
        $expiresTimestamp,      // Expiry timestamp
        '/',                    // Path (available site-wide)
        '',                     // Domain (leave empty for current)
        isset($_SERVER['HTTPS']),// Secure flag (true if HTTPS)
        true                    // HttpOnly flag (prevent JS access)
        // Optional: SameSite attribute ('Lax' or 'Strict')
        // ['samesite' => 'Lax'] // Requires PHP 7.3+
    );
}

/**
 * Clears remember me tokens from DB and cookie.
 */
function clearRememberMeData($userId, $conn) {
    try {
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
         if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
         }
        // Expire the cookie immediately
        setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    } catch (Exception $e) {
         error_log("Error clearing remember me data for User $userId: " . $e->getMessage());
         if (isset($stmt) && $stmt) $stmt->close();
    }
}

// --- END Helper Functions ---


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']); // Check if the checkbox was checked

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
        if (!$stmt) {
             $_SESSION['error_message'] = "Database error during login preparation.";
             header("Location: login.php");
             exit();
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_password, $is_admin);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Password is correct, set session variables
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = $is_admin;

                // --- Handle Remember Me ---
                if ($remember) {
                    $tokenData = createRememberMeToken($id, $conn);
                    if ($tokenData) {
                        setRememberMeCookie($tokenData['selector'], $tokenData['validator'], $tokenData['expires_timestamp']);
                    } else {
                        // Failed to create token, maybe log this, but don't stop login
                        error_log("Failed to create remember me token for user ID $id on login.");
                    }
                } else {
                    // If not checked, clear any existing remember me data for this user
                    clearRememberMeData($id, $conn);
                }
                // --- End Handle Remember Me ---


                // Redirect based on role or previous destination
                $redirect_to = 'shop.php'; // Default redirect for users
                if (isset($_SESSION['redirect_to'])) {
                    $redirect_to = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                } elseif ($is_admin) {
                    $redirect_to = 'admin/dashboard.php'; // Redirect admins
                }

                $stmt->close(); // Close statement before redirect
                $conn->close(); // Close connection before redirect
                header("Location: " . $redirect_to);
                exit();

            } else {
                $_SESSION['error_message'] = "Incorrect email or password.";
            }
        } else {
            $_SESSION['error_message'] = "No account found with this email.";
        }
        $stmt->close(); // Close statement here too in case of failure

    } else {
        $_SESSION['error_message'] = "Please fill in all fields.";
    }

    // Redirect back to login page on error
    header("Location: login.php");
    exit();
} else {
     // If not POST, redirect away or show error
     header("Location: login.php");
     exit();
}

// Close connection if it hasn't been closed yet (e.g., on POST error before login check)
if (isset($conn) && $conn instanceof mysqli) {
   $conn->close();
}
?>