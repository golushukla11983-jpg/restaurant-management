<?php
require_once '../includes/config.php';

if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../login.php");
    exit();
}

// Delete item
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get image path and delete file
    $item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM menu_items WHERE id = $id"));
    if ($item && file_exists('../' . $item['image'])) {
        unlink('../' . $item['image']);
    }
    
    mysqli_query($conn, "DELETE FROM menu_items WHERE id = $id");
    header("Location: manage-menu.php?success=deleted");
    exit();
}

// Toggle availability
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    mysqli_query($conn, "UPDATE menu_items SET is_available = NOT is_available WHERE id = $id");
    header("Location: manage-menu.php");
    exit();
}

// Update item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
    // Handle image upload if new image provided
    $image_update = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($filetype), $allowed)) {
            $new_filename = uniqid() . '_' . time() . '.' . $filetype;
            $upload_path = '../uploads/menu/' . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image
                $old_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT image FROM menu_items WHERE id = $id"));
                if ($old_item && file_exists('../' . $old_item['image'])) {
                    unlink('../' . $old_item['image']);
                }
                
                $image_path = 'uploads/menu/' . $new_filename;
                $image_update = ", image='$image_path'";
            }
        }
    }
    
    $query = "UPDATE menu_items SET name='$name', description='$description', price=$price, category='$category'$image_update WHERE id=$id";
    mysqli_query($conn, $query);
    header("Location: manage-menu.php?success=updated");
    exit();
}

$items = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY category, name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Menu - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .edit-form {
            display: none;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-top: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .edit-form.active {
            display: block;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .action-buttons button,
        .action-buttons a {
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-edit {
            background-color: #17a2b8;
            color: white;
        }
        .btn-toggle {
            background-color: #ffc107;
            color: black;
        }
        .btn-delete {
            background-color: #dc3545;
            color: white;
        }
        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }
        .btn-save {
            background-color: #28a745;
            color: white;
        }
        .edit-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .edit-group {
            display: flex;
            flex-direction: column;
        }
        .edit-group label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .edit-group input,
        .edit-group select,
        .edit-group textarea {
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .menu-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="../index.php" class="logo">
                <span>🍽️</span> Golden Plate
            </a>
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="../manage-orders.php">Orders</a></li>
                <li><a href="manage-menu.php">Menu</a></li>
                <li><a href="manage-users.php">Users</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h2>Manage Menu Items</h2>
            <a href="add-item.php" class="btn-primary"> Add New Item</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                Menu item <?php echo htmlspecialchars($_GET['success']); ?> successfully!
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td>
                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                 class="menu-image">
                        </td>
                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td class="price">₹<?php echo number_format($item['price']); ?></td>
                        <td>
                            <span class="badge <?php echo $item['is_available'] ? 'badge-delivered' : 'badge-cancelled'; ?>">
                                <?php echo $item['is_available'] ? 'Available' : 'Unavailable'; ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="toggleEdit(<?php echo $item['id']; ?>)" class="btn-edit"> Edit</button>
                                <a href="?toggle=<?php echo $item['id']; ?>" class="btn-toggle">
                                    <?php echo $item['is_available'] ? ' Disable' : ' Enable'; ?>
                                </a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn-delete" 
                                   onclick="return confirm('Delete this item?')"> Delete</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="7" style="padding: 0; border: none;">
                            <div id="edit-form-<?php echo $item['id']; ?>" class="edit-form">
                                <h3 style="margin-bottom: 15px;">Edit Menu Item</h3>
                                <form method="POST" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    
                                    <div style="margin-bottom: 15px;">
                                        <label style="font-weight: bold; display: block; margin-bottom: 5px;">Current Image:</label>
                                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" 
                                             alt="Current" class="image-preview">
                                    </div>
                                    
                                    <div class="edit-grid">
                                        <div class="edit-group">
                                            <label>Name</label>
                                            <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                                        </div>
                                        <div class="edit-group">
                                            <label>Category</label>
                                            <select name="category" required>
                                                <option value="Pizza" <?php echo $item['category'] == 'Pizza' ? 'selected' : ''; ?>>Pizza</option>
                                                <option value="Burgers" <?php echo $item['category'] == 'Burgers' ? 'selected' : ''; ?>>Burgers</option>
                                                <option value="Salads" <?php echo $item['category'] == 'Salads' ? 'selected' : ''; ?>>Salads</option>
                                                <option value="Pasta" <?php echo $item['category'] == 'Pasta' ? 'selected' : ''; ?>>Pasta</option>
                                                <option value="Seafood" <?php echo $item['category'] == 'Seafood' ? 'selected' : ''; ?>>Seafood</option>
                                                <option value="Desserts" <?php echo $item['category'] == 'Desserts' ? 'selected' : ''; ?>>Desserts</option>
                                                <option value="Beverages" <?php echo $item['category'] == 'Beverages' ? 'selected' : ''; ?>>Beverages</option>
                                            </select>
                                        </div>
                                        <div class="edit-group">
                                            <label>Price</label>
                                            <input type="number" name="price" step="0.01" value="<?php echo $item['price']; ?>" required>
                                        </div>
                                        <div class="edit-group">
                                            <label>New Image (optional)</label>
                                            <input type="file" name="image" accept="image/*" onchange="previewEditImage(event, <?php echo $item['id']; ?>)">
                                        </div>
                                    </div>
                                    <div class="edit-group" style="margin-top: 15px;">
                                        <label>Description</label>
                                        <textarea name="description" rows="3" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                                    </div>
                                    <div id="preview-<?php echo $item['id']; ?>" style="margin-top: 10px;"></div>
                                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                                        <button type="submit" name="update" class="btn-save"> Save</button>
                                        <button type="button" onclick="toggleEdit(<?php echo $item['id']; ?>)" class="btn-cancel"> Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>

    <script>
        function toggleEdit(itemId) {
            const form = document.getElementById('edit-form-' + itemId);
            form.classList.toggle('active');
        }
        
        function previewEditImage(event, itemId) {
            const preview = document.getElementById('preview-' + itemId);
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="image-preview" alt="New preview">';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>