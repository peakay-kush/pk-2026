<?php
$page_title = 'Shopping Cart';
require_once 'includes/header.php';

// Initialize cart if not exists or if it's corrupted
if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = $_SESSION['cart'];
$cart_total = getCartTotal();
$cart_count = getCartItemCount();
?>

<!-- Page Header -->
<section class="hero-section">
    <div class="container">
        <h1 class="mb-0">Shopping Cart</h1>
    </div>
</section>

<!-- Cart Section -->
<section class="section-padding section-bg-alt">
    <div class="container">
        <?php if (!empty($cart) && is_array($cart) && count($cart) > 0): ?>
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                foreach ($cart as $product_id => $quantity):
                                    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
                                    $stmt->execute([$product_id]);
                                    $product = $stmt->fetch();
                                    if (!$product)
                                        continue;
                                    ?>
                                    <tr>
                                        <td data-label="Product">
                                            <div class="cart-product">
                                                <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>"
                                                    class="cart-product-img"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                <div class="cart-product-info">
                                                    <a href="product/<?php echo $product['slug']; ?>"
                                                        class="text-decoration-none">
                                                        <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                                    </a>
                                                    <span
                                                        class="badge bg-light text-dark border"><?php echo htmlspecialchars($product['category']); ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Price" class="fw-semibold">
                                            <?php echo formatPrice($product['price']); ?>
                                        </td>
                                        <td data-label="Quantity">
                                            <div class="quantity-group">
                                                <button class="quantity-btn quantity-decrease" type="button"
                                                    data-product-id="<?php echo $product_id; ?>">
                                                    <i class="fas fa-minus" style="font-size: 0.8rem;"></i>
                                                </button>
                                                <input type="number" class="quantity-input-styled quantity-input"
                                                    value="<?php echo $quantity; ?>" min="1" readonly
                                                    data-product-id="<?php echo $product_id; ?>">
                                                <button class="quantity-btn quantity-increase" type="button"
                                                    data-product-id="<?php echo $product_id; ?>">
                                                    <i class="fas fa-plus" style="font-size: 0.8rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td data-label="Total">
                                            <span
                                                class="fw-bold text-primary"><?php echo formatPrice($product['price'] * $quantity); ?></span>
                                        </td>
                                        <td class="text-end">
                                            <button class="btn btn-link text-danger p-0 btn-remove-cart"
                                                data-product-id="<?php echo $product_id; ?>" data-bs-toggle="tooltip"
                                                title="Remove Item">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-5 cart-action-buttons">
                        <a href="shop" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                        </a>
                        <button class="btn btn-outline-danger" id="clear-cart-btn">
                            <i class="fas fa-trash me-2"></i> Clear Cart
                        </button>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <h4 class="mb-4" style="color: #0B63CE; font-weight: 700;">Order Summary</h4>

                            <div class="d-flex justify-content-between mb-3">
                                <span style="color: #666;">Subtotal:</span>
                                <span style="font-weight: 600;"><?php echo formatPrice($cart_total); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span style="color: #666;">Shipping:</span>
                                <span id="shipping-cost" style="font-weight: 600;"><?php echo formatPrice(0); ?></span>
                            </div>
                            <hr style="margin: 1.5rem 0;">
                            <div class="d-flex justify-content-between mb-4">
                                <strong style="font-size: 1.1rem;">Total:</strong>
                                <strong id="order-total" style="font-size: 1.1rem; color: #0B63CE;"
                                    data-subtotal="<?php echo $cart_total; ?>"><?php echo formatPrice($cart_total); ?></strong>
                            </div>

                            <div class="mb-4">
                                <label style="font-weight: 600; margin-bottom: 0.5rem; color: #333;">Shipping
                                    location</label>
                                <?php include 'includes/shipping_locations_fetch.php'; ?>
                                <select class="form-select" id="shipping-location" name="shipping_location"
                                    style="border: 1px solid #dee2e6; padding: 0.75rem; border-radius: 6px;">
                                    <option value="" data-fee="0" selected>Select a location</option>
                                    <?php foreach ($shipping_locations as $loc): ?>
                                        <option value="<?php echo $loc['id']; ?>" data-fee="<?php echo $loc['fee']; ?>">
                                            <?php echo htmlspecialchars($loc['name']) . ' - KSh ' . ($loc['fee'] == 0 ? 'FREE' : number_format($loc['fee'], 2)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-danger mt-2" id="location-error" style="display: none;">Please select a
                                    shipping location to proceed</small>
                            </div>

                            <?php if (isLoggedIn()): ?>
                                <button onclick="proceedToCheckout()" class="btn btn-lg w-100 mb-3"
                                    style="background-color: #6c757d; color: white; border: none; padding: 0.75rem; font-weight: 600; border-radius: 6px;">
                                    Proceed to Checkout
                                </button>
                            <?php else: ?>
                                <a href="login" class="btn btn-lg w-100 mb-3"
                                    style="background-color: var(--accent-color); color: white; border: none; padding: 0.75rem; font-weight: 600; border-radius: 6px;">
                                    <i class="fas fa-sign-in-alt"></i> Login to Checkout
                                </a>
                                <p class="text-center mt-2 mb-0">
                                    <small>or <a href="register" style="color: #0B63CE;">create an account</a></small>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Empty Cart -->
            <div class="text-center py-5 empty-cart-state" style="border-radius: 12px; padding: 4rem 2rem !important;">
                <h2 style="color: #0B63CE; font-weight: 700; margin-bottom: 1rem;">Your cart is empty</h2>
                <p class="text-muted mb-4">Start shopping to add items to your cart</p>
                <a href="shop" class="btn btn-lg"
                    style="background-color: var(--accent-color); color: white; border: none; padding: 0.8rem 2rem; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-arrow-left"></i> Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>