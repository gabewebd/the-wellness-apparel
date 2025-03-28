<?php
// Ensure session is started before output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force admin login (for testing/development)
$_SESSION['is_admin'] = true; // Remove this in production

// Include database connection
require_once 'includes/db.php';

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
    
    <link rel="stylesheet" href="assets/css/navbar.css" />
    <script src="assets/js/main.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="dashboard.php" class="logo">
                <img src="assets/img/icons/logo.svg" alt="The Wellness Apparel Logo" class="logo-svg" />
            </a>

            <div class="nav-right">
                    <a href="dashboard.php" class="icon"><i class="ti ti-home"></i> Dashboard</a>

                <div class="profile-container">
                <i class="ti ti-user profile-icon" id="profile-icon"></i>
                    <div class="profile-menu" id="profile-menu">
                        <?php if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                            <a href="profile.php"><i class="ti ti-user"></i> Profile</a>
                            <hr>

                            <a href="add_product.php" class="profile-icon"><i class="ti ti-plus"></i> Add Product</a>
                            <a href="notifications.php" class="profile-icon"><i class="ti ti-bell"></i> Notifications</a>

                            <hr>

                            <a href="products.php" class="profile-icon"><i class="ti ti-shirt"></i> Products</a>
                            <a href="orders.php" class="profile-icon"><i class="ti ti-history"></i> Orders</a>
                            <a href="users.php" class="profile-icon"><i class="ti ti-users"></i> Users</a>

                            <hr>

                            <a href="logout.php"><i class="ti ti-logout"></i> Logout</a>
                            <?php else: ?>
                            <a href="login.php"><i class="ti ti-login"></i> Login</a>
                            <a href="sign-up.php"><i class="ti ti-user-plus"></i> Register</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <script>
        function toggleProfileMenu() {
            var menu = document.getElementById("profile-menu");
            menu.classList.toggle("visible");
        }
    </script>

</body>
</html>