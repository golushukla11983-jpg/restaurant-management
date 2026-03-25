<?php
require_once 'includes/config.php';
include 'includes/header.php';
?>

<div class="hero">
    <h1>🍽️ Welcome to Golden Plate Restaurant</h1>
    <p>Experience the finest dining with our exquisite menu</p>
    <a href="menu.php" class="btn-primary" style="display: inline-block; margin-top: 20px;">View Menu</a>
</div>

<div class="container">
    <h2 style="text-align: center; margin-bottom: 30px; color: var(--black);">Why Choose Us?</h2>
    
    <div class="card-grid">
        <div class="card">
            <div class="card-image">🍕</div>
            <div class="card-content">
                <h3>Fresh Ingredients</h3>
                <p>We use only the freshest, locally-sourced ingredients in all our dishes.</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-image">⚡</div>
            <div class="card-content">
                <h3>Fast Delivery</h3>
                <p>Hot and fresh meals delivered to your doorstep in record time.</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-image">👨‍🍳</div>
            <div class="card-content">
                <h3>Expert Chefs</h3>
                <p>Our experienced chefs craft each dish with passion and expertise.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>