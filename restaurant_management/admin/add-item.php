<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('login.php');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = $_POST['price'];
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    // Handle image upload
    $image_path = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            // Create unique filename
            $new_filename = uniqid() . '_' . time() . '.' . $filetype;
            $upload_path = '../uploads/menu/' . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = 'uploads/menu/' . $new_filename;
            } else {
                $error = 'Failed to upload image.';
            }
        } else {
            $error = 'Invalid file type. Only JPG, PNG, GIF, and WEBP allowed.';
        }
    } else {
        $error = 'Please select an image.';
    }
    
    if (!$error && $image_path) {
        $query = "INSERT INTO menu_items (name, description, price, category, image) 
                  VALUES ('$name', '$description', $price, '$category', '$image_path')";
        
        if (mysqli_query($conn, $query)) {
            $success = 'Menu item added successfully!';
        } else {
            $error = 'Failed to add menu item.';
        }
    }
}

include '../includes/header.php';
?>

<div class="container">
    <h2 style="margin-bottom: 30px;">Add New Menu Item</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?> <a href="manage-menu.php">View all items</a></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label>Item Name</label>
                <input type="text" name="name" required>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3" required></textarea>
            </div>
            
            <div class="form-group">
                <label>Price </label>
                <input type="number" name="price" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <select name="category" required>
                    <option value="Pizza">Pizza</option>
                    <option value="Burgers">Burgers</option>
                    <option value="Salads">Salads</option>
                    <option value="Pasta">Pasta</option>
                    <option value="Seafood">Seafood</option>
                    <option value="Desserts">Desserts</option>
                    <option value="Beverages">Beverages</option>
                    <option value="Appetizers">Appetizers</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Item Image</label>
                <input type="file" name="image" accept="image/*" required onchange="previewImage(event)">
            </div>
            
            <div class="form-group">
                <img id="preview" src="" alt="Preview" style="max-width: 300px; max-height: 300px; display: none; border-radius: 10px; margin-top: 10px;">
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">Add Item</button>
        </form>
    </div>
</div>

<script>
function previewImage(event) {
    const preview = document.getElementById('preview');
    const file = event.target.files[0];
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
}
</script>

<?php include '../includes/footer.php'; ?>