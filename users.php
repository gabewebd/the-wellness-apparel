<?php
require 'includes/db.php'; // Include database connection
require 'includes/navbar.php';

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php"); // Redirect to home page if not an admin
    exit();
}

// Fetch all users, including their roles
try {
    $stmt = $conn->prepare("SELECT id, username, email, display_name, created_at, is_admin FROM users ORDER BY created_at DESC");
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    die("Error fetching users: " . $e->getMessage());
}

// Handle user deletion
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Prevent deleting own admin account
    if ($delete_id == $_SESSION['user_id']) {
        die("<div class='error-message'>You cannot delete your own admin account.</div>");
    }

    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Deletion successful, redirect to refresh the page
            header("Location: users.php");
            exit();
        } else {
            die("User not found or could not be deleted.");
        }
    } catch (Exception $e) {
        die("Error deleting user: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/users.css" />
    <style>
        /* Comprehensive Styling */
        body {
            font-family: 'Alice', serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            background-color: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            padding: 30px 50px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            font-family: 'Alice', serif;
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .user-card {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-5px);
        }

        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .user-header h3 {
            margin: 0;
            color: #333;
            font-family: 'Quicksand', sans-serif;
        }

        .role-badge {
            padding: 4px 10px;
            font-size: 12px;
            font-weight: bold;
            border-radius: 12px;
            text-transform: uppercase;
        }

        .admin-badge {
            background-color: #df7645;
            color: white;
        }

        .user-badge {
            background-color: #6c757d;
            color: white;
        }

        .user-card p {
            margin: 8px 0;
            color: #555;
            font-family: 'Quicksand', sans-serif;
        }

        .user-date {
            font-size: 12px;
            color: #888;
            margin-top: 10px;
        }

        .user-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .btn-view, .btn-delete {
            flex: 1;
            padding: 10px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Quicksand', sans-serif;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
        }

        .btn-view {
            background-color: #28a745;
        }

        .btn-view:hover {
            background-color: #218838;
        }

        .btn-delete {
            background-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #c82333;
        }

        .no-users {
            grid-column: 1 / -1;
            text-align: center;
            color: #666;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 12px;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px auto;
            max-width: 600px;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            font-family: 'Quicksand', sans-serif;
        }

        .modal-container {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
            max-width: 90%;
            padding: 30px;
            text-align: center;
        }

        .modal-header {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            font-family: 'Quicksand', sans-serif;
        }

        .modal-body {
            color: #666;
            margin-bottom: 20px;
            font-family: 'Alice', serif;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Quicksand', sans-serif;
            font-weight: 600;
        }

        .modal-btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .modal-btn-confirm {
            background-color: #dc3545;
            color: white;
        }

        .modal-btn:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .admin-container {
                padding: 20px 15px;
                margin: 20px 10px;
            }

            .users-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>User Management</h1>
        
        <div class="users-grid">
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-card">
                        <div class="user-header">
                            <h3><?php echo htmlspecialchars($user['display_name'] ?: $user['username']); ?></h3>
                            <span class="role-badge <?php echo $user['is_admin'] ? 'admin-badge' : 'user-badge'; ?>">
                                <?php echo $user['is_admin'] ? 'Admin' : 'User'; ?>
                            </span>
                        </div>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="user-date">
                            Registered: <?php echo date("F j, Y", strtotime($user['created_at'])); ?>
                        </div>
                        <div class="user-actions">
                            <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn-view">View Profile</a>
                            <?php if (!$user['is_admin']): ?>
                                <a href="delete_user.php?delete_id=<?php echo $user['id']; ?>" class="btn-delete delete-user">Delete</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-users">No users found.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">Confirm User Deletion</div>
            <div class="modal-body">
                Are you sure you want to delete this user account? 
                This action cannot be undone.
            </div>
            <div class="modal-buttons">
                <button id="cancelDelete" class="modal-btn modal-btn-cancel">Cancel</button>
                <button id="confirmDelete" class="modal-btn modal-btn-confirm">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('.delete-user');
            const modal = document.getElementById('deleteConfirmModal');
            const cancelBtn = document.getElementById('cancelDelete');
            const confirmBtn = document.getElementById('confirmDelete');
            let currentDeleteLink = null;

            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentDeleteLink = this;
                    modal.style.display = 'flex';
                });
            });

            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                currentDeleteLink = null;
            });

            confirmBtn.addEventListener('click', function() {
                if (currentDeleteLink) {
                    window.location.href = currentDeleteLink.href;
                }
            });

            // Close modal if clicked outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    currentDeleteLink = null;
                }
            });
        });
    </script>
</body>
</html>