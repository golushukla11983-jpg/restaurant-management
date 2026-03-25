<?php
require_once 'includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: login.php");
    exit();
}

// Update order status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    mysqli_query($conn, "UPDATE orders SET status = '$status' WHERE id = $order_id");
    header("Location: manage-orders.php?success=1");
    exit();
}

// Get filter parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Build query based on filter
$query = "SELECT o.*, u.name as user_name, u.email, u.phone 
          FROM orders o 
          JOIN users u ON o.user_id = u.id";

if ($filter != 'all') {
    $query .= " WHERE o.status = '$filter'";
}

$query .= " ORDER BY o.order_date DESC";
$orders = mysqli_query($conn, $query);

// Get order counts for each status
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'"))['count'];
$confirmed_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'confirmed'"))['count'];
$preparing_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'preparing'"))['count'];
$delivered_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'delivered'"))['count'];
$cancelled_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'"))['count'];
$total_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="css/style.css">
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
        .order-count {
            background: var(--primary-yellow);
            color: var(--black);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .filter-tab.active .order-count {
            background: var(--primary-yellow);
            color: var(--black);
        }
        .status-select {
            padding: 8px 12px;
            border: 2px solid var(--primary-yellow);
            border-radius: 8px;
            background: white;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .status-select:hover {
            background: var(--light-gray);
        }
        .status-select:focus {
            outline: none;
            border-color: var(--black);
        }
        .order-details-btn {
            background: var(--primary-yellow);
            color: var(--black);
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .order-details-btn:hover {
            background: var(--dark-yellow);
            transform: translateY(-2px);
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
            overflow: auto;
        }
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        .close:hover {
            color: var(--black);
        }
        .order-items-table {
            margin-top: 20px;
        }
        .order-items-table th {
            background: var(--black);
            color: var(--primary-yellow);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
            color: var(--primary-yellow);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-card h3 {
            font-size: 2.5rem;
            margin: 10px 0;
        }
        .stat-card p {
            color: white;
            font-size: 1rem;
        }
        @media (max-width: 768px) {
            .filter-tabs {
                flex-direction: column;
            }
            .filter-tab {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <span>🍽️</span> Golden Plate
            </a>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="menu.php">Menu</a></li>
                <li><a href="admin/">Admin Dashboard</a></li>
                <li><a href="manage-orders.php">Manage Orders</a></li>
                <li><a href="admin/manage-menu.php">Manage Menu</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2 style="margin-bottom: 30px; color: var(--black);"> Manage Orders</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✓ Order status updated successfully!</div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter == 'all' ? 'active' : ''; ?>">
                 All Orders </span>
            </a>
            <a href="?filter=pending" class="filter-tab <?php echo $filter == 'pending' ? 'active' : ''; ?>">
                 Pending </span>
            </a>
            <a href="?filter=confirmed" class="filter-tab <?php echo $filter == 'confirmed' ? 'active' : ''; ?>">
                 Confirmed </span>
            </a>
            <a href="?filter=preparing" class="filter-tab <?php echo $filter == 'preparing' ? 'active' : ''; ?>">
                 Preparing </span>
            </a>
            <a href="?filter=delivered" class="filter-tab <?php echo $filter == 'delivered' ? 'active' : ''; ?>">
                 Delivered </span>
            </a>
            <a href="?filter=cancelled" class="filter-tab <?php echo $filter == 'cancelled' ? 'active' : ''; ?>">
                 Cancelled </span>
            </a>
        </div>
        
        <?php if (mysqli_num_rows($orders) == 0): ?>
            <div class="alert alert-info">No orders found for the selected filter.</div>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Date & Time</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><strong>#<?php echo str_pad($order['id'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                        <td>
                            <strong><?php echo htmlspecialchars($order['user_name']); ?></strong>
                        </td>
                        <td>
                            📧 <?php echo htmlspecialchars($order['email']); ?><br>
                            📞 <?php echo htmlspecialchars($order['phone']); ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?><br>
                            <small><?php echo date('h:i A', strtotime($order['order_date'])); ?></small>
                        </td>
                        <td><strong style="font-size: 1.1rem;">₹<?php echo number_format($order['total_amount']); ?></strong></td>
                        <td>
                            <span class="badge badge-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $order['payment_status']; ?>">
                                <?php echo ucfirst($order['payment_status']); ?>
                            </span><br>
                            <small><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></small>
                        </td>
                        <td>
                            <form method="POST" style="display: inline-block; margin-bottom: 10px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>> Pending</option>
                                    <option value="confirmed" <?php echo $order['status'] == 'confirmed' ? 'selected' : ''; ?>> Confirmed</option>
                                    <option value="preparing" <?php echo $order['status'] == 'preparing' ? 'selected' : ''; ?>> Preparing</option>
                                    <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>> Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>> Cancelled</option>
                                </select>
                            </form>
                            <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" class="order-details-btn">
                                 View Details
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal for Order Details -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalOrderId">Order Details</h2>
            <div id="modalContent">Loading...</div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
        <p>📞 Contact: +1 234 567 8900 | 📧 info@goldenplate.com</p>
    </footer>

    <script>
        function viewOrderDetails(orderId) {
            document.getElementById('orderModal').style.display = 'block';
            document.getElementById('modalOrderId').innerHTML = 'Order #' + String(orderId).padStart(5, '0');
            
            // Fetch order details via AJAX
            fetch('get-order-details.php?id=' + orderId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('modalContent').innerHTML = '<div class="alert alert-error">Failed to load order details.</div>';
                });
        }

        function closeModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-refresh every 30 seconds for real-time updates
        setInterval(function() {
            if (!document.getElementById('orderModal').style.display || 
                document.getElementById('orderModal').style.display === 'none') {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>