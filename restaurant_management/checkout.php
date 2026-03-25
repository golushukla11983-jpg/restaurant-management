<?php
require_once 'includes/config.php';

if (!isLoggedIn() || empty($_SESSION['cart'])) {
    redirect('cart.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    
    // Calculate total
    $total = 0;
    foreach ($_SESSION['cart'] as $item_id => $quantity) {
        $item_query = mysqli_query($conn, "SELECT price FROM menu_items WHERE id = $item_id");
        $item = mysqli_fetch_assoc($item_query);
        $total += $item['price'] * $quantity;
    }
    
    // Create order
    $user_id = $_SESSION['user_id'];
    $order_query = "INSERT INTO orders (user_id, total_amount, delivery_address, payment_method) 
                    VALUES ($user_id, $total, '$delivery_address', '$payment_method')";
    
    if (mysqli_query($conn, $order_query)) {
        $order_id = mysqli_insert_id($conn);
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item_id => $quantity) {
            $item_query = mysqli_query($conn, "SELECT price FROM menu_items WHERE id = $item_id");
            $item = mysqli_fetch_assoc($item_query);
            $price = $item['price'];
            
            mysqli_query($conn, "INSERT INTO order_items (order_id, item_id, quantity, price) 
                                VALUES ($order_id, $item_id, $quantity, $price)");
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        // Redirect to payment
        redirect('payment.php?order_id=' . $order_id);
    } else {
        $error = 'Failed to create order. Please try again.';
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2 style="margin-bottom: 30px;">Checkout</h2>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <div>
            <h3>Order Summary</h3>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total = 0;
                        foreach ($_SESSION['cart'] as $item_id => $quantity):
                            $item_query = mysqli_query($conn, "SELECT * FROM menu_items WHERE id = $item_id");
                            $item = mysqli_fetch_assoc($item_query);
                            $subtotal = $item['price'] * $quantity;
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo $quantity; ?></td>
                            <td>₹<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2" style="font-weight: bold;">Total:</td>
                            <td style="font-weight: bold;">₹<?php echo number_format($total, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div>
            <h3>Delivery Information</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="delivery_address" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="cash">Cash on Delivery</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%;">Place Order</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>