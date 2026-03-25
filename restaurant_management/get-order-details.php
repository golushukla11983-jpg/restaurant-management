<?php
require_once 'includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    echo '<div class="alert alert-error">Access denied</div>';
    exit();
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order_query = mysqli_query($conn, "
    SELECT o.*, u.name as user_name, u.email, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = $order_id
");

if (mysqli_num_rows($order_query) == 0) {
    echo '<div class="alert alert-error">Order not found</div>';
    exit();
}

$order = mysqli_fetch_assoc($order_query);

// Get order items with images
$items_query = mysqli_query($conn, "
    SELECT oi.*, mi.name, mi.image, mi.category 
    FROM order_items oi 
    JOIN menu_items mi ON oi.item_id = mi.id 
    WHERE oi.order_id = $order_id
");
?>

<div style="background: var(--light-gray); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Customer Name:</strong></p>
            <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($order['user_name']); ?></p>
        </div>
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Email:</strong></p>
            <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($order['email']); ?></p>
        </div>
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Phone:</strong></p>
            <p style="font-size: 1.1rem;"><?php echo htmlspecialchars($order['phone']); ?></p>
        </div>
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Order Date:</strong></p>
            <p style="font-size: 1.1rem;"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>
        </div>
    </div>
</div>

<div style="background: var(--light-gray); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
    <p style="color: #666; margin-bottom: 10px;"><strong>Delivery Address:</strong></p>
    <p style="font-size: 1.1rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($order['delivery_address'])); ?></p>
</div>

<div style="background: var(--light-gray); padding: 20px; border-radius: 10px; margin-bottom: 20px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Order Status:</strong></p>
            <span class="badge badge-<?php echo $order['status']; ?>" style="font-size: 1rem; padding: 8px 16px;">
                <?php echo ucfirst($order['status']); ?>
            </span>
        </div>
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Payment Status:</strong></p>
            <span class="badge badge-<?php echo $order['payment_status']; ?>" style="font-size: 1rem; padding: 8px 16px;">
                <?php echo ucfirst($order['payment_status']); ?>
            </span>
        </div>
        <div>
            <p style="color: #666; margin-bottom: 5px;"><strong>Payment Method:</strong></p>
            <p style="font-size: 1.1rem;"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>
        </div>
    </div>
</div>

<h3 style="margin: 30px 0 15px 0; color: var(--black);">📋 Order Items</h3>

<table class="order-items-table" style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background: var(--black); color: var(--primary-yellow);">
            <th style="padding: 12px; text-align: left;">Image</th>
            <th style="padding: 12px; text-align: left;">Item</th>
            <th style="padding: 12px; text-align: center;">Category</th>
            <th style="padding: 12px; text-align: center;">Quantity</th>
            <th style="padding: 12px; text-align: right;">Price</th>
            <th style="padding: 12px; text-align: right;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $subtotal = 0;
        while ($item = mysqli_fetch_assoc($items_query)): 
            $item_subtotal = $item['price'] * $item['quantity'];
            $subtotal += $item_subtotal;
        ?>
        <tr style="border-bottom: 1px solid #ddd;">
            <td style="padding: 15px;">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                     style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
                     onerror="this.src='uploads/menu/placeholder.jpg'">
            </td>
            <td style="padding: 15px;">
                <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($item['name']); ?></strong>
            </td>
            <td style="padding: 15px; text-align: center;">
                <span class="badge badge-confirmed"><?php echo htmlspecialchars($item['category']); ?></span>
            </td>
            <td style="padding: 15px; text-align: center;">
                <strong style="font-size: 1.1rem;">×<?php echo $item['quantity']; ?></strong>
            </td>
            <td style="padding: 15px; text-align: right;">₹<?php echo number_format($item['price'], 2); ?></td>
            <td style="padding: 15px; text-align: right;">
                <strong style="font-size: 1.1rem;">₹<?php echo number_format($item_subtotal, 2); ?></strong>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
    <tfoot>
        <tr style="background: var(--light-gray);">
            <td colspan="5" style="padding: 15px; text-align: right;">
                <strong style="font-size: 1.2rem;">Total Amount:</strong>
            </td>
            <td style="padding: 15px; text-align: right;">
                <strong style="font-size: 1.5rem; color: var(--primary-yellow); background: var(--black); padding: 8px 16px; border-radius: 8px; display: inline-block;">
                    ₹<?php echo number_format($order['total_amount'], 2); ?>
                </strong>
            </td>
        </tr>
    </tfoot>
</table>

<style>
    .badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: bold;
        display: inline-block;
    }
    .badge-pending {
        background-color: #ffc107;
        color: #000;
    }
    .badge-confirmed {
        background-color: #17a2b8;
        color: #fff;
    }
    .badge-preparing {
        background-color: #fd7e14;
        color: #fff;
    }
    .badge-delivered {
        background-color: #28a745;
        color: #fff;
    }
    .badge-cancelled {
        background-color: #dc3545;
        color: #fff;
    }
    .badge-paid {
        background-color: #28a745;
        color: #fff;
    }
</style>