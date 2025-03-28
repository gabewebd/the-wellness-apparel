<?php
require 'includes/db.php';
require 'includes/navbar.php';  


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | The Wellness Apparel</title>
    
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
        <h1>Create Your Account</h1>

        <form id="signup-form" action="sign-up_process.php" method="POST">
            <div class="input-group">
                <label for="display-name">Display Name</label>
                <input type="text" id="display-name" name="display_name" required>
                <span class="error-message" id="display-name-error"></span>
            </div>

            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <span class="error-message" id="email-error"></span>
            </div>

            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
                <span class="error-message" id="username-error"></span>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <small id="password-strength"></small>
                <span class="error-message" id="password-error"></span>
            </div>

            <div class="terms">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the Terms & Conditions</label>
                <span class="error-message" id="terms-error"></span>
            </div>

            <button type="submit" class="btn-sign-login">Sign Up</button>
            
            <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </main>

    <script src="assets/js/sign-up.js"></script>
    <script src="assets/js/main.js"></script>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>