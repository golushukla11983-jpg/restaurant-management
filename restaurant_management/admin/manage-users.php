<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Delete user
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Prevent deleting yourself
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id = $id");
        header("Location: manage-users.php?success=deleted");
        exit();
    } else {
        header("Location: manage-users.php?error=cannot_delete_self");
        exit();
    }
}

// Toggle user role
if (isset($_GET['toggle_role']) && isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']);
    $new_role = $_GET['toggle_role'] == 'admin' ? 'user' : 'admin';
    
    // Prevent changing your own role
    if ($user_id != $_SESSION['user_id']) {
        mysqli_query($conn, "UPDATE users SET role = '$new_role' WHERE id = $user_id");
        header("Location: manage-users.php?success=role_updated");
        exit();
    } else {
        header("Location: manage-users.php?error=cannot_change_own_role");
        exit();
    }
}

// Update user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Check if email already exists for another user
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email' AND id != $id");
    if (mysqli_num_rows($check) > 0) {
        header("Location: manage-users.php?error=email_exists");
        exit();
    }
    
    $query = "UPDATE users SET name='$name', email='$email', phone='$phone', role='$role' WHERE id=$id";
    mysqli_query($conn, $query);
    header("Location: manage-users.php?success=updated");
    exit();
}

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";

if ($filter != 'all') {
    $query .= " AND role = '$filter'";
}

if ($search != '') {
    $query .= " AND (name LIKE '%$search%' OR email LIKE '%$search%' OR phone LIKE '%$search%')";
}

$query .= " ORDER BY created_at DESC";
$users = mysqli_query($conn, $query);

// Get counts
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users"))['count'];
$admin_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'"))['count'];
$user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 12px 24px;
            background: white;
            border: 2px solid var(--primary-yellow);
            color: var(--black);
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .filter-tab:hover {
            background: var(--primary-yellow);
            transform: translateY(-2px);
        }
        .filter-tab.active {
            background: var(--black);
            color: var(--primary-yellow);
            border-color: var(--black);
        }
        .user-count {
            background: var(--primary-yellow);
            color: var(--black);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .filter-tab.active .user-count {
            background: var(--primary-yellow);
            color: var(--black);
        }
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .search-box input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid var(--primary-yellow);
            border-radius: 25px;
            font-size: 1rem;
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--black);
        }
        .search-box button {
            padding: 12px 30px;
            background: var(--primary-yellow);
            color: var(--black);
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .search-box button:hover {
            background: var(--black);
            color: var(--primary-yellow);
        }
        .action-buttons {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .action-buttons button,
        .action-buttons a {
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            font-weight: 600;
        }
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        .btn-edit:hover {
            background-color: #138496;
        }
        .btn-toggle {
            background-color: #ffc107;
            color: black;
        }
        .btn-toggle:hover {
            background-color: #e0a800;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background-color: #c82333;
        }
        .edit-form {
            display: none;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .edit-form.active {
            display: block;
        }
        .edit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .edit-group {
            display: flex;
            flex-direction: column;
        }
        .edit-group label {
            font-weight: bold;
            margin-bottom: 5px;
            color: var(--black);
        }
        .edit-group input,
        .edit-group select {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }
        .edit-group input:focus,
        .edit-group select:focus {
            outline: none;
            border-color: var(--primary-yellow);
        }
        .btn-save {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-save:hover {
            background-color: #218838;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background-color: #5a6268;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
            color: var(--primary-yellow);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-card h3 {
            font-size: 3rem;
            margin: 10px 0;
        }
        .stat-card p {
            color: white;
            font-size: 1.1rem;
        }
        .role-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
            display: inline-block;
        }
        .role-admin {
            background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
            color: white;
        }
        .role-user {
            background: linear-gradient(135deg, #4ecdc4, #44a08d);
            color: white;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-yellow), var(--dark-yellow));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--black);
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">
                <span>🍽️</span> Golden Plate
            </a>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="../manage-orders.php">Orders</a></li>
                <li><a href="manage-menu.php">Menu</a></li>
                <li><a href="manage-users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin-bottom: 30px; color: var(--black);"> Manage Users</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php 
                if ($_GET['success'] == 'deleted') echo ' User deleted successfully!';
                elseif ($_GET['success'] == 'updated') echo ' User updated successfully!';
                elseif ($_GET['success'] == 'role_updated') echo ' User role updated successfully!';
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php 
                if ($_GET['error'] == 'cannot_delete_self') echo '✗ You cannot delete your own account!';
                elseif ($_GET['error'] == 'cannot_change_own_role') echo '✗ You cannot change your own role!';
                elseif ($_GET['error'] == 'email_exists') echo '✗ Email already exists!';
                ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-tabs">
            <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
               class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                 All Users </span>
            </a>
            <a href="?filter=admin<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
               class="filter-tab <?php echo $filter == 'admin' ? 'active' : ''; ?>">
                 Administrators </span>
            </a>
            <a href="?filter=user<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
               class="filter-tab <?php echo $filter == 'user' ? 'active' : ''; ?>">
                 Regular Users </span>
            </a>
        </div>
        
        <?php if (mysqli_num_rows($users) == 0): ?>
            <div class="alert alert-info">No users found matching your criteria.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><strong>#<?php echo $user['id']; ?></strong></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                                <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge badge-confirmed" style="font-size: 0.75rem;">You</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>📧 <?php echo htmlspecialchars($user['email']); ?></td>
                        <td>📞 <?php echo htmlspecialchars($user['phone']); ?></td>
                        <td>
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo $user['role'] == 'admin' ? ' Admin' : ' User'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="toggleEdit(<?php echo $user['id']; ?>)" class="btn-edit">
                                     Edit
                                </button>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?toggle_role=<?php echo $user['role']; ?>&user_id=<?php echo $user['id']; ?>" 
                                       class="btn-toggle"
                                       onclick="return confirm('Change user role to <?php echo $user['role'] == 'admin' ? 'User' : 'Admin'; ?>?')">
                                        <?php echo $user['role'] == 'admin' ? ' Make User' : ' Make Admin'; ?>
                                    </a>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       class="btn-delete"
                                       onclick="return confirm('Are you sure you want to delete this user?')">
                                         Delete
                                    </a>
                                <?php else: ?>
                                    <span class="badge badge-pending" style="padding: 8px 15px;">Current User</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" style="padding: 0; border: none;">
                            <div id="edit-form-<?php echo $user['id']; ?>" class="edit-form">
                                <h3 style="margin-bottom: 15px; color: var(--black);">Edit User Information</h3>
                                <form method="POST" action="">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <div class="edit-grid">
                                        <div class="edit-group">
                                            <label>Full Name</label>
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        </div>
                                        <div class="edit-group">
                                            <label>Email Address</label>
                                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="edit-group">
                                            <label>Phone Number</label>
                                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                        </div>
                                        <div class="edit-group">
                                            <label>User Role</label>
                                            <select name="role" required <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                                        <button type="submit" name="update_user" class="btn-save"> Save Changes</button>
                                        <button type="button" onclick="toggleEdit(<?php echo $user['id']; ?>)" class="btn-cancel"> Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        <p>📞 Contact: +1 234 567 8900 | 📧 info@goldenplate.com</p>
    </footer>

    <script>
        function toggleEdit(userId) {
            const form = document.getElementById('edit-form-' + userId);
            form.classList.toggle('active');
        }
    </script>
</body>
</html>