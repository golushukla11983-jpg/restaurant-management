<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

// Get statistics
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'"))['total'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count'];

include '../includes/header.php';
?>

<div class="container">
    <h2 style="margin-bottom: 30px;">Admin Dashboard</h2>
    
    <div class="card-grid">
        <div class="card">
            <div class="card-content">
                <h3 style="color: var(--primary-yellow);"> Total Orders</h3>
                <p style="font-size: 2.5rem; font-weight: bold; margin: 20px 0;"><?php echo $total_orders; ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-content">
                <h3 style="color: var(--primary-yellow);"> Total Users</h3>
                <p style="font-size: 2.5rem; font-weight: bold; margin: 20px 0;"><?php echo $total_users; ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-content">
                <h3 style="color: var(--primary-yellow);"> Total Revenue</h3>
                <p style="font-size: 2.5rem; font-weight: bold; margin: 20px 0;">₹<?php echo number_format($total_revenue); ?></p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-content">
                <h3 style="color: var(--primary-yellow);"> Pending Orders</h3>
                <p style="font-size: 2.5rem; font-weight: bold; margin: 20px 0;"><?php echo $pending_orders; ?></p>
            </div>
        </div>
    </div>
    
    <div style="margin-top: 40px; text-align: center;">
        <a href="manage-orders.php" class="btn-primary" style="margin: 10px;">Manage Orders</a>
        <a href="manage-menu.php" class="btn-primary" style="margin: 10px;">Manage Menu</a>
        <a href="manage-users.php" class="btn-primary" style="margin: 10px;">Manage Users</a>
        <a href="add-item.php" class="btn-secondary" style="margin: 10px;">Add Menu Item</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>