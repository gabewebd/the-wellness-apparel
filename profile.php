<?php
require 'includes/db.php';
require 'includes/navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        die("User not found");
    }
    
    $user = $result->fetch_assoc();
} catch (Exception $e) {
    die("Error fetching user details: " . $e->getMessage());
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $display_name = trim($_POST['display_name']);
    $email = trim($_POST['email']);

    try {
        // Check if email is already in use by another user
        $email_check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check_stmt->bind_param("si", $email, $user_id);
        $email_check_stmt->execute();
        $email_check_result = $email_check_stmt->get_result();

        if ($email_check_result->num_rows > 0) {
            $error_message = "Email is already in use by another account.";
        } else {
            // Update profile
            $update_stmt = $conn->prepare("UPDATE users SET display_name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $display_name, $email, $user_id);
            $update_stmt->execute();

            // Refresh user data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $success_message = "Profile updated successfully!";
        }
    } catch (Exception $e) {
        $error_message = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/navbar.css">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
        }

        .profile-content {
            max-width: 600px;
            margin: 80px auto 20px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 32px;
            margin-right: 20px;
            color: #333;
        }

        .profile-header-info h1 {
            margin: 0;
            font-size: 1.5em;
            color: #333;
            font-family: 'Alice', serif;
        }

        .role-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-top: 5px;
            background-color: #28a745;
            color: white;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #666;
            font-size: 0.9em;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
            font-family: 'Quicksand', sans-serif;
        }

        .form-group input:read-only {
            background-color: #f4f4f4;
            cursor: not-allowed;
        }

        .btn-update {
            width: 100%;
            padding: 12px;
            background-color: #df7645;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Quicksand', sans-serif;
        }

        .btn-update:hover {
            background-color: #c55e1e;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }

        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="profile-content">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['display_name'] ?: $user['username'], 0, 1)); ?>
            </div>
            <div class="profile-header-info">
                <h1><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></h1>
                <span class="role-badge">
                    <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                </span>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="display_name">Display Name</label>
                <input type="text" id="display_name" name="display_name" 
                       value="<?php echo htmlspecialchars($user['display_name'] ?: ''); ?>" 
                       placeholder="Enter display name">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($user['email']); ?>" 
                       placeholder="Enter email address">
            </div>

            <div class="form-group">
                <label>Registration Date</label>
                <input type="text" value="<?php echo date("F j, Y", strtotime($user['created_at'])); ?>" readonly>
            </div>

            <button type="submit" name="update_profile" class="btn-update">Update Profile</button>
        </form>
    </div>
</body>
</html>