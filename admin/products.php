<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    // CSRF Protection
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }

    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['flash_message'] = 'Product deleted successfully';
    $_SESSION['flash_type'] = 'success';
    header('Location: products.php');
    exit;
}

$page_title = 'Manage Products';
require_once 'header.php';

// Fetch products
$products = $conn->query("SELECT * FROM products ORDER BY name");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="page-title mb-0"><i class="fas fa-box"></i> Manage Products</h2>
    <a href="product_add.php" class="btn btn-success">
        <i class="fas fa-plus"></i> Add New Product
    </a>
</div>

<div class="card">
    <div class="card-header">
        <i class="fas fa-list"></i> All Products
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Rating</th>
                        <th>Featured</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($product = $products->fetch()): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="../assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                    style="width: 50px; height: 50px; object-fit: cover;" class="rounded"
                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><span
                                    class="badge bg-secondary"><?php echo htmlspecialchars($product['category']); ?></span>
                            </td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td><?php echo $product['stock']; ?></td>
                            <td><?php echo $product['rating']; ?> <i class="fas fa-star text-warning"></i></td>
                            <td>
                                <?php if ($product['featured']): ?>
                                    <i class="fas fa-star text-warning" title="Featured"></i>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap">
                                <a href="product_edit.php?id=<?php echo $product['id']; ?>"
                                    class="btn btn-sm btn-primary me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="products.php?delete=<?php echo $product['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                    class="btn btn-sm btn-danger btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this product?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>