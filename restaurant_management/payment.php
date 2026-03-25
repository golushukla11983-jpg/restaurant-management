<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
$user_id = $_SESSION['user_id'];

// Verify order belongs to user
$order_query = mysqli_query($conn, "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
if (mysqli_num_rows($order_query) == 0) {
    redirect('index.php');
}

$order = mysqli_fetch_assoc($order_query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update payment status
    mysqli_query($conn, "UPDATE orders SET payment_status = 'paid', status = 'confirmed' WHERE id = $order_id");
    redirect('my-orders.php?success=1');
}

include 'includes/header.php';
?>

<div class="container">
    <div class="form-container">
        <h2 style="text-align: center; margin-bottom: 30px;">Complete Payment</h2>
        
        <div class="alert alert-info">
            <strong>Order ID:</strong> #<?php echo $order_id; ?><br>
            <strong>Total Amount:</strong> ₹<?php echo number_format($order['total_amount']); ?>
        </div>
        
        <?php if ($order['payment_method'] == 'cash'): ?>
            <div class="alert alert-success">
                <strong>Cash on Delivery Selected</strong><br>
                Your order has been placed successfully. Please pay when your order is delivered.
            </div>
            
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>