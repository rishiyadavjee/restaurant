<?php
session_start();
require_once '../includes/db.php';

// Check if admin is logged in
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$page_title = 'Menu Management';
$message = '';
$error = '';

// Handle image upload function
function handleImageUpload($file, $old_image = null) {
    $upload_dir = '../assets/images/menu/';
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return $old_image ?: 'default.jpg';
        }
        throw new Exception('Upload failed with error code: ' . $file['error']);
    }
    
    if ($file['size'] > $max_size) {
        throw new Exception('File size too large. Maximum 5MB allowed.');
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG, and GIF allowed.');
    }
    
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('menu_') . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    // Delete old image if it exists and is not default
    if ($old_image && $old_image !== 'default.jpg' && file_exists($upload_dir . $old_image)) {
        unlink($upload_dir . $old_image);
    }
    
    return $new_filename;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $category_id = $_POST['category_id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = $_POST['price'];
                $is_popular = isset($_POST['is_popular']) ? 1 : 0;
                $is_available = isset($_POST['is_available']) ? 1 : 0;
                
                // Handle image upload
                $image = 'default.jpg';
                try {
                    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $image = handleImageUpload($_FILES['image_upload']);
                    } elseif (!empty($_POST['image_filename'])) {
                        $image = $_POST['image_filename'];
                    }
                } catch (Exception $e) {
                    $error = "Image upload error: " . $e->getMessage();
                    break;
                }
                
                if (!empty($name) && !empty($category_id) && !empty($price)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO menu_items (category_id, name, description, price, image, is_popular, is_available) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$category_id, $name, $description, $price, $image, $is_popular, $is_available]);
                        $message = "Menu item added successfully!";
                    } catch (PDOException $e) {
                        $error = "Error adding menu item: " . $e->getMessage();
                    }
                } else {
                    $error = "Name, category, and price are required!";
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $category_id = $_POST['category_id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $price = $_POST['price'];
                $is_popular = isset($_POST['is_popular']) ? 1 : 0;
                $is_available = isset($_POST['is_available']) ? 1 : 0;
                
                // Get current image
                $stmt = $pdo->prepare("SELECT image FROM menu_items WHERE id = ?");
                $stmt->execute([$id]);
                $current_item = $stmt->fetch();
                $image = $current_item['image'];
                
                // Handle image upload
                try {
                    if (isset($_FILES['image_upload']) && $_FILES['image_upload']['error'] !== UPLOAD_ERR_NO_FILE) {
                        $image = handleImageUpload($_FILES['image_upload'], $current_item['image']);
                    } elseif (!empty($_POST['image_filename'])) {
                        $image = $_POST['image_filename'];
                    }
                } catch (Exception $e) {
                    $error = "Image upload error: " . $e->getMessage();
                    break;
                }
                
                if (!empty($name) && !empty($category_id) && !empty($price) && !empty($id)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image = ?, is_popular = ?, is_available = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$category_id, $name, $description, $price, $image, $is_popular, $is_available, $id]);
                        $message = "Menu item updated successfully!";
                    } catch (PDOException $e) {
                        $error = "Error updating menu item: " . $e->getMessage();
                    }
                } else {
                    $error = "Name, category, and price are required!";
                }
                break;
                
            case 'delete':
                $id = $_POST['id'];
                
                if (!empty($id)) {
                    try {
                        $stmt = $pdo->prepare("DELETE FROM menu_items WHERE id = ?");
                        $stmt->execute([$id]);
                        $message = "Menu item deleted successfully!";
                    } catch (PDOException $e) {
                        $error = "Error deleting menu item: " . $e->getMessage();
                    }
                }
                break;
                
            case 'toggle_popular':
                $id = $_POST['id'];
                $is_popular = $_POST['is_popular'] == '1' ? 0 : 1;
                
                try {
                    $stmt = $pdo->prepare("UPDATE menu_items SET is_popular = ? WHERE id = ?");
                    $stmt->execute([$is_popular, $id]);
                    $message = "Menu item popularity updated!";
                } catch (PDOException $e) {
                    $error = "Error updating menu item: " . $e->getMessage();
                }
                break;
                
            case 'toggle_available':
                $id = $_POST['id'];
                $is_available = $_POST['is_available'] == '1' ? 0 : 1;
                
                try {
                    $stmt = $pdo->prepare("UPDATE menu_items SET is_available = ? WHERE id = ?");
                    $stmt->execute([$is_available, $id]);
                    $message = "Menu item availability updated!";
                } catch (PDOException $e) {
                    $error = "Error updating menu item: " . $e->getMessage();
                }
                break;
        }
    }
}

// Get all categories for dropdown
$stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get all menu items with category names
$stmt = $pdo->query("
    SELECT m.*, c.name as category_name 
    FROM menu_items m 
    JOIN categories c ON m.category_id = c.id 
    ORDER BY c.name, m.name
");
$menu_items = $stmt->fetchAll();

// Get menu item for editing if ID is provided
$edit_item = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM menu_items WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_item = $stmt->fetch();
}

include '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Menu Management</h1>
        <div>
            <a href="manage_categories.php" class="btn btn-info btn-sm mr-2">
                <i class="fas fa-tags fa-sm text-white-50"></i> Manage Categories
            </a>
            <a href="index.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Add/Edit Menu Item Form -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php echo $edit_item ? 'Edit Menu Item' : 'Add New Menu Item'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="<?php echo $edit_item ? 'edit' : 'add'; ?>">
                        <?php if ($edit_item): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_item['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select class="form-control" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo ($edit_item && $edit_item['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Item Name *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo $edit_item ? htmlspecialchars($edit_item['name']) : ''; ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo $edit_item ? htmlspecialchars($edit_item['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="price">Price *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">$</span>
                                </div>
                                <input type="number" step="0.01" class="form-control" id="price" name="price" 
                                       value="<?php echo $edit_item ? $edit_item['price'] : ''; ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <!-- Image Upload Section -->
                        <div class="form-group">
                            <label>Product Image</label>
                            
                            <?php if ($edit_item && $edit_item['image'] && $edit_item['image'] !== 'default.jpg'): ?>
                                <div class="mb-2">
                                    <small class="text-muted">Current Image:</small><br>
                                    <img src="../assets/images/menu/<?php echo htmlspecialchars($edit_item['image']); ?>" 
                                         alt="Current Image" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="custom-file mb-2">
                                <input type="file" class="custom-file-input" id="image_upload" name="image_upload" 
                                       accept="image/jpeg,image/jpg,image/png,image/gif">
                                <label class="custom-file-label" for="image_upload">Choose image file...</label>
                            </div>
                            <small class="form-text text-muted">
                                Upload a new image (JPG, PNG, GIF - Max 5MB) or use filename below.<br>
                                Images are saved in: <code>assets/images/menu/</code>
                            </small>
                            
                            <div class="mt-2">
                                <label for="image_filename">Or specify image filename:</label>
                                <input type="text" class="form-control" id="image_filename" name="image_filename" 
                                       value="<?php echo $edit_item ? htmlspecialchars($edit_item['image']) : ''; ?>" 
                                       placeholder="e.g., dish-name.jpg">
                                <small class="form-text text-muted">Leave both empty for default image</small>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_popular" name="is_popular" 
                                       <?php echo ($edit_item && $edit_item['is_popular']) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_popular">Popular Item</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_available" name="is_available" 
                                       <?php echo (!$edit_item || $edit_item['is_available']) ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="is_available">Available</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_item ? 'Update Item' : 'Add Item'; ?>
                        </button>
                        
                        <?php if ($edit_item): ?>
                            <a href="manage_menu.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Menu Items List -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Menu Items List</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Popular</th>
                                    <th>Available</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($menu_items as $item): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $image_path = '../assets/images/menu/' . $item['image'];
                                            $display_image = (file_exists($image_path) && $item['image'] !== 'default.jpg') 
                                                ? $image_path 
                                                : '../assets/images/default.jpg';
                                            ?>
                                            <img src="<?php echo $display_image; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="img-thumbnail" 
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <?php if ($item['description']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars(substr($item['description'], 0, 50)) . '...'; ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_popular">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="is_popular" value="<?php echo $item['is_popular']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $item['is_popular'] ? 'btn-success' : 'btn-outline-secondary'; ?>">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_available">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="is_available" value="<?php echo $item['is_available']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $item['is_available'] ? 'btn-success' : 'btn-danger'; ?>">
                                                    <i class="fas <?php echo $item['is_available'] ? 'fa-check' : 'fa-times'; ?>"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <a href="manage_menu.php?edit=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Are you sure you want to delete this menu item?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for file input -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update file input label when file is selected
    const fileInput = document.getElementById('image_upload');
    const fileLabel = document.querySelector('.custom-file-label');
    
    if (fileInput && fileLabel) {
        fileInput.addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : 'Choose image file...';
            fileLabel.textContent = fileName;
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>