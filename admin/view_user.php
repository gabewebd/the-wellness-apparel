<?php
require 'includes/db.php'; // Include database connection
require 'includes/navbar.php';

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php"); // Redirect to home page if not an admin
    exit();
}

// Check if a user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID");
}

$user_id = $_GET['id'];

// Fetch user details
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - <?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/navbar.css">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
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
        }

        .admin-badge {
            background-color: #dc3545;
            color: white;
        }

        .user-badge {
            background-color: #28a745;
            color: white;
        }

        .profile-details {
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }

        .profile-details p {
            margin: 10px 0;
            color: #666;
            font-family: 'Quicksand', sans-serif;
        }

        .profile-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 15px;
        }

        .back-link, .delete-btn {
            text-decoration: none;
            font-family: 'Quicksand', sans-serif;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-link {
            color: #fff;
            border: 1px solid #df7645;
            background-color: #df7645;
        }

        .back-link:hover {
            color: #fff;
            border-color:rgb(199, 105, 46);
            background-color: rgb(199, 105, 46);
        }

        .delete-btn {
            color: #dc3545;
            border: 1px solid #dc3545;
            background-color: transparent;
            cursor: pointer;
        }

        .delete-btn:hover {
            color: white;
            background-color: #dc3545;
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
                <span class="role-badge <?php echo $user['is_admin'] ? 'admin-badge' : 'user-badge'; ?>">
                    <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                </span>
            </div>
        </div>

        <div class="profile-details">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Registration Date:</strong> <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
        </div>

        <div class="profile-actions">
            <?php if (!$user['is_admin']): ?>
            <?php endif; ?>
            <a href="users.php" class="back-link">
                <i class="ti ti-arrow-left"></i> Back to User Management
            </a>
        </div>
    </div>

</body>
</html>