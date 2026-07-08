<?php
require 'auth.php';
require_login();

// Only superadmin can access
if (get_user_role() !== 'superadmin') {
    header("Location: index.php");
    exit;
}

require 'db.php';

// Ensure plain_password column exists
$check_col = $conn->query("SHOW COLUMNS FROM `users` LIKE 'plain_password'");
if ($check_col && $check_col->num_rows == 0) {
    $conn->query("ALTER TABLE `users` ADD COLUMN `plain_password` VARCHAR(255) DEFAULT NULL");
    $conn->query("UPDATE `users` SET `plain_password` = 'superadmin' WHERE `username` = 'superadmin'");
    $conn->query("UPDATE `users` SET `plain_password` = 'admin' WHERE `username` = 'admin'");
    $conn->query("UPDATE `users` SET `plain_password` = 'user' WHERE `username` = 'user'");
}

$error = '';
$success = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $new_username = trim($_POST['username'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $new_role = $_POST['role'] ?? 'user';
    
    if ($new_username && $new_password) {
        // Check if username exists
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->bind_param("s", $new_username);
        $stmt_check->execute();
        if ($stmt_check->get_result()->fetch_assoc()) {
            $error = "Username already exists.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, plain_password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $new_username, $hashed_password, $new_password, $new_role);
            if ($stmt->execute()) {
                $success = "User added successfully.";
            } else {
                $error = "Error adding user.";
            }
        }
    } else {
        $error = "Please provide username and password.";
    }
}

// Handle Delete User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $del_id = (int)$_POST['user_id'];
    // Prevent deleting oneself
    if ($del_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        if ($stmt->execute()) {
            $success = "User deleted successfully.";
        } else {
            $error = "Error deleting user.";
        }
    }
}

// Fetch all users
$result = $conn->query("SELECT id, username, password, plain_password, role FROM users ORDER BY role, username");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Social Services Monthly Progress</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #e2e8f0;
            font-size: 14px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 14px;
        }
        .btn-primary {
            padding: 10px 20px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-primary:hover { background: #059669; }
        .btn-danger {
            padding: 6px 12px;
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-danger:hover { background: #dc2626; }
        
        .error-msg { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 20px; }
        .success-msg { color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 10px; border-radius: 6px; margin-bottom: 20px; }
        
        .flex-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .flex-child {
            flex: 1;
        }
        .table-container th, .table-container td {
            padding: 12px 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        @media (max-width: 768px) {
            .flex-container {
                flex-direction: column;
            }
            .flex-container .glass-panel {
                flex: 1 !important;
            }
        }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    
    <div class="container">
        <nav class="navbar">
            <div class="navbar-user">
                Logged in as: <span style="color: #3b82f6; text-transform: capitalize;"><?php echo htmlspecialchars(get_username()); ?> (Superadmin)</span>
                | <a href="logout.php">Logout</a>
            </div>
            <div class="navbar-menu">
                <a href="index.php" class="back-btn" style="background: #6b7280; border-color: #4b5563;">ආපසු (Back)</a>
            </div>
        </nav>
        
        <header>
            <h1>පරිශීලක කළමනාකරණය (User Management)</h1>
        </header>

        <?php if ($error): ?><div class="error-msg"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success-msg"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

        <div class="flex-container">
            <div class="glass-panel flex-child" style="flex: 0.4;">
                <h3 style="color: white; margin-top: 0;">නව පරිශීලකයෙක් එකතු කරන්න (Add User)</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>පරිශීලක නාමය (Username)</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>මුරපදය (Password)</label>
                        <input type="text" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>කාර්යභාරය (Role)</label>
                        <select name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-primary">එකතු කරන්න (Add)</button>
                </form>
            </div>
            
            <div class="glass-panel flex-child">
                <h3 style="color: white; margin-top: 0;">පරිශීලක ලැයිස්තුව (Users List)</h3>
                <div class="table-container" style="max-height: 500px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse; color: #fff;">
                        <thead style="background: rgba(255,255,255,0.1);">
                            <tr>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Role</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td style="white-space: nowrap;">
                                    <span class="pwd-text" id="pwd-<?php echo $user['id']; ?>" style="font-family: monospace;">••••••••</span>
                                    <button type="button" class="btn-show-pwd" onclick="togglePwd(this, <?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['plain_password'] ?? 'N/A'); ?>')" style="background: none; border: none; color: #3b82f6; cursor: pointer; margin-left: 10px; font-size: 12px; font-weight: 500; outline: none;">පෙන්වන්න (Show)</button>
                                </td>
                                <td style="text-transform: capitalize;"><?php echo htmlspecialchars($user['role']); ?></td>
                                <td>
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn-danger">Delete</button>
                                    </form>
                                    <?php else: ?>
                                        <span style="color: #6b7280; font-size: 12px;">Current User</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function togglePwd(btn, id, plainPwd) {
            const span = document.getElementById('pwd-' + id);
            if (span.textContent === '••••••••') {
                span.textContent = plainPwd;
                btn.textContent = 'සඟවන්න (Hide)';
                btn.style.color = '#ef4444';
            } else {
                span.textContent = '••••••••';
                btn.textContent = 'පෙන්වන්න (Show)';
                btn.style.color = '#3b82f6';
            }
        }
    </script>
</body>
</html>
