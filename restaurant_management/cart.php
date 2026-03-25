<?php
require_once 'includes/config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $item_id = intval($_POST['item_id']);
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id]++;
        } else {
            $_SESSION['cart'][$item_id] = 1;
        }
    } elseif ($_POST['action'] == 'remove') {
        unset($_SESSION['cart'][$_POST['item_id']]);
    } elseif ($_POST['action'] == 'update') {
        $item_id = intval($_POST['item_id']);
        $quantity = intval($_POST['quantity']);
        if ($quantity > 0) {
            $_SESSION['cart'][$item_id] = $quantity;
        } else {
            unset($_SESSION['cart'][$item_id]);
        }
    }
    redirect('cart.php');
}

include 'includes/header.php';
?>

<style>
    .cart-item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .cart-item-details {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .cart-item-name {
        font-weight: bold;
        font-size: 1.1rem;
        color: var(--black);
    }
    .cart-item-category {
        font-size: 0.9rem;
        color: #666;
    }
    .quantity-input {
        width: 70px;
        padding: 8px;
        border: 2px solid var(--primary-yellow);
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        font-size: 1rem;
    }
    .quantity-input:focus {
        outline: none;
        border-color: var(--black);
    }
    .remove-btn {
        background: #dc3545;
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s;
    }
    .remove-btn:hover {
        background: #c82333;
        transform: translateY(-2px);
    }
    .cart-summary {
        background: linear-gradient(135deg, var(--black) 0%, var(--dark-gray) 100%);
        color: var(--primary-yellow);
        padding: 30px;
        border-radius: 15px;
        margin-top: 30px;
    }
    .cart-summary h3 {
        margin-bottom: 20px;
        font-size: 1.5rem;
    }
    .cart-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255, 215, 0, 0.3);
    }
    .cart-summary-row:last-child {
        border-bottom: none;
        font-size: 1.5rem;
        font-weight: bold;
        margin-top: 10px;
        padding-top: 20px;
        border-top: 2px solid var(--primary-yellow);
    }
    .empty-cart {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-cart-icon {
        font-size: 5rem;
        margin-bottom: 20px;
    }
    @media (max-width: 768px) {
        .cart-item-image {
            width: 60px;
            height: 60px;
        }
        .cart-item-details {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="container">
    <h2 style="margin-bottom: 30px; color: var(--black);"> Shopping Cart</h2>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-cart">
            <div class="empty-cart-icon"></div>
            <div class="alert alert-info" style="max-width: 500px; margin: 0 auto;">
                <h3>Your cart is empty</h3>
                <p style="margin: 15px 0;">Start adding delicious items to your cart!</p>
                <a href="menu.php" class="btn-primary" style="display: inline-block; margin-top: 10px;">Browse Our Menu</a>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    $item_count = 0;
                    foreach ($_SESSION['cart'] as $item_id => $quantity):
                        $item_query = mysqli_query($conn, "SELECT * FROM menu_items WHERE id = $item_id");
                        
                        // Check if item exists
                        if (mysqli_num_rows($item_query) == 0) {
                            unset($_SESSION['cart'][$item_id]);
                            continue;
                        }
                        
                        $item = mysqli_fetch_assoc($item_query);
                        $subtotal = $item['price'] * $quantity;
                        $total += $subtotal;
                        $item_count += $quantity;
                    ?>
                    <tr>
                        <td>
                            <div class="cart-item-details">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="cart-item-image"
                                     onerror="this.src='uploads/menu/placeholder.jpg'">
                                <div>
                                    <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="cart-item-category">
                                        <span class="badge badge-confirmed" style="font-size: 0.8rem;">
                                            <?php echo htmlspecialchars($item['category']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong style="font-size: 1.1rem;">₹<?php echo number_format($item['price']); ?></strong>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                <input type="hidden" name="action" value="update">
                                <input type="number" 
                                       name="quantity" 
                                       value="<?php echo $quantity; ?>" 
                                       min="1" 
                                       max="99"
                                       class="quantity-input" 
                                       onchange="this.form.submit()">
                            </form>
                        </td>
                        <td>
                            <strong style="font-size: 1.2rem; color: var(--primary-yellow); background: var(--black); padding: 5px 12px; border-radius: 8px; display: inline-block;">
                                ₹<?php echo number_format($subtotal); ?>
                            </strong>
                        </td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="remove-btn" onclick="return confirm('Remove this item from cart?')">
                                     Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Cart Summary -->
        <div class="cart-summary">
            <h3>Order Summary</h3>
            <div class="cart-summary-row">
                <span>Items in Cart:</span>
                <span><?php echo $item_count; ?> item<?php echo $item_count != 1 ? 's' : ''; ?></span>
            </div>
            <div class="cart-summary-row">
                <span>Subtotal:</span>
                <span>₹<?php echo number_format($total); ?></span>
            </div>
            <div class="cart-summary-row">
            </div>
            <div class="cart-summary-row">
                <span>Total Amount:</span>
                <span>₹<?php echo number_format($total); ?></span>
            </div>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px; gap: 15px; flex-wrap: wrap;">
            <a href="menu.php" class="btn-secondary" style="padding: 15px 30px; text-decoration: none;">
                Continue Shopping
            </a>
            <a href="checkout.php" class="btn-primary" style="padding: 15px 40px; text-decoration: none; font-size: 1.1rem;">
                Proceed to Checkout 
            </a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>