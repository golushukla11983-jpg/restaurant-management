<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");

include 'includes/header.php';
?>

<div class="container">
    <h2 style="margin-bottom: 30px;">My Orders</h2>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Order placed successfully!</div>
    <?php endif; ?>
    
    <?php if (mysqli_num_rows($orders) == 0): ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="menu.php">Browse our menu</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td>#<?php echo $order['id']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                        <td>₹<?php echo number_format($order['total_amount']); ?></td>
                        <td><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td><span class="badge badge-<?php echo $order['payment_status']; ?>"><?php echo ucfirst($order['payment_status']); ?></span></td>
                        <td>
                            <button onclick="viewOrder(<?php echo $order['id']; ?>)" class="btn-primary">View</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
function viewOrder(orderId) {
    alert('Order details for #' + orderId + '\nThis would show order items in a modal or separate page.');
}
</script>

<?php include 'includes/footer.php'; ?>