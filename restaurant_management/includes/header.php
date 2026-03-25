<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>" class="logo">
                <span>🍽️</span> Golden Plate
            </a>
            <ul>
                <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                <li><a href="<?php echo SITE_URL; ?>menu.php">Menu</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="<?php echo SITE_URL; ?>my-orders.php">My Orders</a></li>
                    <li><a href="<?php echo SITE_URL; ?>cart.php">Cart </a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="<?php echo SITE_URL; ?>admin/">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="<?php echo SITE_URL; ?>logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>