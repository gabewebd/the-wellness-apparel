<?php
session_start(); // Start the session
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
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <a href="index.php" class="logo">
                <img src="assets/img/icons/logo.svg" alt="The Wellness Apparel Logo" class="logo-svg" />
            </a>

            <div class="nav-right">
                <a href="cart.php" class="icon"><i class="ti ti-shopping-cart"></i></a>
                <a href="notifications.php" class="icon"><i class="ti ti-bell"></i></a>

                <div class="profile-container">
                    <i class="ti ti-user profile-icon" id="profile-icon"></i>

                    <div class="profile-menu" id="profile-menu">
                        <?php if(isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
                            <a href="profile.php"><i class="ti ti-user"></i> Profile</a>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true): ?>
                                <a href="admin/add_products.php"><i class="ti ti-plus"></i> Add Product</a>
                            <?php endif; ?>
                            <a href="settings.php"><i class="ti ti-settings"></i> Settings</a>
                            <hr>
                            <a href="logout.php"><i class="ti ti-logout"></i> Logout</a>
                            <a href="delete-account.php"><i class="ti ti-trash"></i> Delete Account</a>
                        <?php else: ?>
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
