<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Add Helper Functions (Ideally move to a separate file like includes/auth_functions.php) ---

/**
 * Clears remember me tokens from DB and cookie.
 * Needs the $conn variable (database connection).
 */
function clearRememberMeData($userId, $conn) {
    // Ensure $conn is a valid mysqli connection
     if (!$conn || !($conn instanceof mysqli)) {
         error_log("clearRememberMeData Error: Invalid database connection provided.");
         // Expire the cookie anyway
         setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
         return; // Stop execution if DB connection is invalid
     }
    try {
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM auth_tokens WHERE user_id = ?");
         if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
         } else {
              // Log error if prepare fails but don't throw exception to ensure cookie is cleared
              error_log("clearRememberMeData Error: Prepare failed (delete tokens): " . $conn->error);
         }
        // Expire the cookie immediately
        setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    } catch (Exception $e) {
         error_log("Error clearing remember me data for User $userId: " . $e->getMessage());
         if (isset($stmt) && $stmt instanceof mysqli_stmt) { // Check if $stmt is a valid statement object
             $stmt->close();
         }
         // Expire cookie even if DB operation failed
          setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
}

// You might need createRememberMeToken and setRememberMeCookie here if you implement token refreshing
// function createRememberMeToken(...) { ... }
// function setRememberMeCookie(...) { ... }

// --- End Helper Functions ---


// --- Add Remember Me Check Logic ---
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    // Only check cookie if user is NOT already logged in via session

    // Establish DB connection ONLY if needed and not already established
    // This require might be redundant if navbar is included after db.php elsewhere,
    // but using require_once ensures it's loaded safely if needed here first.
    require_once __DIR__ . '/db.php';

    // Check if connection is valid before proceeding
    if (isset($conn) && $conn instanceof mysqli && $conn->ping()) { // $conn->ping() checks if connection is alive

        $cookieParts = explode(':', $_COOKIE['remember_me'], 2);
        $selector = $cookieParts[0] ?? '';
        $validator = $cookieParts[1] ?? '';


        if (!empty($selector) && !empty($validator)) {
            try {
                $stmt = $conn->prepare("SELECT user_id, hashed_validator, expires FROM auth_tokens WHERE selector = ?");
                if (!$stmt) throw new Exception("Prepare failed (token lookup): " . $conn->error);

                $stmt->bind_param("s", $selector);
                $stmt->execute();
                $result = $stmt->get_result();
                $tokenData = $result->fetch_assoc();
                $stmt->close(); // Close statement promptly

                if ($tokenData) {
                    // Token found, verify validator and expiry
                    $current_time = time();
                    $expires_time = strtotime($tokenData['expires']);

                    // Use password_verify for secure comparison
                    if ($expires_time > $current_time && password_verify($validator, $tokenData['hashed_validator'])) {
                        // Token is valid! Log the user in.
                        $user_id = $tokenData['user_id'];

                        // Fetch user details to populate session
                        $userStmt = $conn->prepare("SELECT username, is_admin FROM users WHERE id = ?");
                         if (!$userStmt) throw new Exception("Prepare failed (user lookup): " . $conn->error);

                        $userStmt->bind_param("i", $user_id);
                        $userStmt->execute();
                        $userResult = $userStmt->get_result();
                        $userData = $userResult->fetch_assoc();
                        $userStmt->close(); // Close statement promptly

                        if ($userData) {
                            session_regenerate_id(true); // Regenerate session ID for security
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $userData['username'];
                            $_SESSION['is_admin'] = $userData['is_admin'];

                            // Optional: Refresh token (improves security)
                            // clearRememberMeData($user_id, $conn); // Clear old one first
                            // $newTokenData = createRememberMeToken($user_id, $conn); // Need this function here or included
                            // if ($newTokenData) {
                            //    setRememberMeCookie($newTokenData['selector'], $newTokenData['validator'], $newTokenData['expires_timestamp']); // Need this function here or included
                            // }

                        } else {
                             // User associated with token not found - clear data
                             clearRememberMeData($user_id, $conn);
                        }
                    } else {
                        // Invalid validator or expired token - clear data
                        clearRememberMeData($tokenData['user_id'], $conn); // Pass user_id
                    }
                } else {
                     // Selector not found - clear the cookie as it's invalid
                     setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
                }
            } catch (Exception $e) {
                 error_log("Remember Me Check Error: " . $e->getMessage());
                 // Ensure statements closed if error occurred mid-process
                 if (isset($stmt) && $stmt instanceof mysqli_stmt) $stmt->close();
                 if (isset($userStmt) && $userStmt instanceof mysqli_stmt) $userStmt->close();
                 // Clear the potentially compromised/invalid cookie
                 setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
            }
        } else {
            // Invalid cookie format
             setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
        }
        // Close connection if opened by this block - be cautious if $conn is needed later in the script
        // if (isset($conn) && $conn instanceof mysqli) { $conn->close(); }

    } else {
         // Database connection failed or $conn is not set properly
         error_log("Remember Me Check Error: Invalid or unavailable database connection.");
         // Clear cookie if DB is unavailable? Optional, depends on desired behavior.
         // setcookie('remember_me', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
    }
}
// --- End Remember Me Check Logic ---


// --- Your Original Navbar HTML Starts Here ---
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />

    <link rel="stylesheet" href="assets/css/navbar.css"/>
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="assets/img/icons/logo.svg" alt="The Wellness Apparel Logo" class="logo-svg" />
            </a>

            <div class="nav-right">
                <a href="index.php" class="icon"><i class="ti ti-home"></i> Home</a>
                <a href="shop.php" class="icon"><i class="ti ti-shirt"></i> Shop</a>

                <div class="profile-container">
                    <i class="ti ti-user profile-icon" id="profile-icon"></i>

                    <div class="profile-menu" id="profile-menu">
                        <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>

                            <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): // ADMIN VIEW ?>
                                <a href="admin/profile.php"><i class="ti ti-user"></i> Profile</a>
                                <hr>
                                <a href="admin/add_product.php" class="profile-icon"><i class="ti ti-plus"></i> Add Product</a>
                                <a href="admin/notifications.php" class="profile-icon"><i class="ti ti-bell"></i> Notifications</a>
                                <hr>
                                <a href="admin/products.php" class="profile-icon"><i class="ti ti-shirt"></i> Products</a>
                                <a href="admin/orders.php" class="profile-icon"><i class="ti ti-history"></i> Orders</a>
                                <a href="admin/users.php" class="profile-icon"><i class="ti ti-users"></i> Users</a>
                                <hr>
                                <a href="logout.php"><i class="ti ti-logout"></i> Logout</a>

                            <?php else: // REGULAR USER VIEW ?>
                                <a href="profile.php"><i class="ti ti-user"></i> Profile</a>
                                <hr>
                                <a href="notifications.php" class="profile-icon"><i class="ti ti-message"></i> Send Message</a>
                                <hr>
                                <a href="cart.php" class="profile-icon"><i class="ti ti-shopping-cart"></i> Cart</a>
                                <a href="order-history.php" class="profile-icon"><i class="ti ti-history"></i> Order History</a>
                                <hr>
                                <a href="logout.php"><i class="ti ti-logout"></i> Logout</a>
                                <a href="delete-account.php"><i class="ti ti-trash"></i> Delete Account</a>
                            <?php endif; ?>

                        <?php else: // NOT LOGGED IN ?>
                            <a href="login.php"><i class="ti ti-login"></i> Login</a>
                            <a href="sign-up.php"><i class="ti ti-user-plus"></i> Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    </body>
</html>