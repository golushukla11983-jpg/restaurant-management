<?php
require_once 'includes/config.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';

$query = "SELECT * FROM menu_items WHERE is_available = 1";
if ($category != 'all') {
    $query .= " AND category = '$category'";
}
$result = mysqli_query($conn, $query);

// Get all categories
$categories = mysqli_query($conn, "SELECT DISTINCT category FROM menu_items");

include 'includes/header.php';
?>

<div class="hero" style="padding: 60px 20px;">
    <h1>Our Menu</h1>
    <p>Discover our delicious offerings</p>
</div>

<div class="container">
    <div style="text-align: center; margin-bottom: 30px;">
        <a href="menu.php?category=all" class="btn-primary" style="margin: 5px;">All</a>
        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
            <a href="menu.php?category=<?php echo $cat['category']; ?>" class="btn-secondary" style="margin: 5px;">
                <?php echo $cat['category']; ?>
            </a>
        <?php endwhile; ?>
    </div>
    
    <div class="card-grid">
    <?php while ($item = mysqli_fetch_assoc($result)): ?>
        <div class="card">
            <div class="card-image" style="padding: 0; background: none;">
                <img src="<?php echo htmlspecialchars($item['image']); ?>" 
                     alt="<?php echo htmlspecialchars($item['name']); ?>"
                     style="width: 100%; height: 200px; object-fit: cover;">
            </div>
            <div class="card-content">
                <h3><?php echo $item['name']; ?></h3>
                <p><?php echo $item['description']; ?></p>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span class="price">₹<?php echo number_format($item['price']); ?></span>
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" action="cart.php" style="margin: 0;">
                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="btn-primary">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">Login to Order</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php include 'includes/footer.php'; ?>