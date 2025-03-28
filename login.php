<?php
require 'includes/db.php';
require 'includes/navbar.php';

// Redirect logged-in admins to admin panel
if (isset($_SESSION['admin_id'])) {
    header('Location: admin/send-notifications.php');
    exit();
}

// Redirect logged-in users to home page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | The Wellness Apparel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.css" />
    <link rel="stylesheet" href="assets/css/sign-up.css" />
</head>
<body>


    <div class="background">
        <main class="signup-container">
            <h1>Welcome Back!</h1>

            <!-- Display session messages -->
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="error-message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="success-message"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
            <?php endif; ?>

            <form id="login-form" action="login_process.php" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="terms">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="btn-sign-login">Login</button>
                
                <p class="login-link">Don't have an account? <a href="sign-up.php">Sign Up here</a></p>
            </form>
        </main>
    </div>
    
    <script src="assets/js/login.js"></script>
    <script src="assets/js/main.js"></script>
    <?php include 'includes/footer.php'; ?>
</body>
</html>