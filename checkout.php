<?php

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();
$user_id = $_SESSION['user_id'];

// Fetch user info
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$user_id]);
$user = $user_query->fetch();

// Fetch addresses
$addr_query = $conn->prepare("SELECT * FROM delivery_addresses WHERE user_id = ? ORDER BY is_primary DESC, id ASC");
$addr_query->execute([$user_id]);
$addresses = $addr_query->fetchAll();

// Cart
$cart = $_SESSION['cart'] ?? [];
$cart_items = [];
$cart_total = 0;
foreach ($cart as $product_id => $quantity) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if ($product) {
        $product['quantity'] = $quantity;
        $product['subtotal'] = $product['price'] * $quantity;
        $cart_items[] = $product;
        $cart_total += $product['subtotal'];
    }
}

// Redirect if cart is empty
if (empty($cart_items)) {
    header('Location: shop');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    $user_id = $_SESSION['user_id'];
    $name = sanitize($_POST['name'] ?? $user['name']);
    $email = sanitize($_POST['email'] ?? $user['email']);
    $phone = sanitize($_POST['phone'] ?? $user['phone']);
    $address_line = sanitize($_POST['address_line'] ?? '');
    $street = sanitize($_POST['street'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $payment_method = sanitize($_POST['payment_method']);
    $shipping_location_id = (int) ($_POST['shipping_location'] ?? 0);

    // Fetch shipping location details
    $shipping_name = "Not Specified";
    $shipping_fee = 0;
    if ($shipping_location_id > 0) {
        $loc_stmt = $conn->prepare("SELECT name, fee FROM shipping_locations WHERE id = ?");
        $loc_stmt->execute([$shipping_location_id]);
        $loc = $loc_stmt->fetch();
        if ($loc) {
            $shipping_name = $loc['name'];
            $shipping_fee = $loc['fee'];
        }
    }

    $grand_total = $cart_total + $shipping_fee;

    if ($cart_total > 0 && count($cart_items) > 0) {
        try {
            $conn->beginTransaction();
            $created_at = date('Y-m-d H:i:s');

            // Insert Order with payment method and shipping info
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, shipping_address_line, shipping_street, shipping_city, shipping_phone, shipping_location_id, shipping_fee, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $status = 'pending';
            $stmt->execute([$user_id, $grand_total, $payment_method, $address_line, $street, $city, $phone, $shipping_location_id, $shipping_fee, $status, $created_at]);
            $order_id = $conn->lastInsertId();

            // Insert Order Items and Update Stock
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

            foreach ($cart_items as $item) {
                // Insert item
                $item_stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);

                // Reduce stock
                $stock_stmt->execute([$item['quantity'], $item['id'], $item['quantity']]);
                if ($stock_stmt->rowCount() === 0) {
                    throw new Exception("Insufficient stock for product: " . $item['name']);
                }
            }

            // Shipping detail block for emails
            $shipping_details = "<h3>Shipping Information:</h3>";
            $shipping_details .= "<p><strong>Address:</strong> " . $address_line . "</p>";
            $shipping_details .= "<p><strong>Street:</strong> " . $street . "</p>";
            $shipping_details .= "<p><strong>City:</strong> " . $city . "</p>";
            $shipping_details .= "<p><strong>Phone:</strong> " . $phone . "</p>";
            $shipping_details .= "<p><strong>Location:</strong> " . $shipping_name . " (KSh " . number_format($shipping_fee, 2) . ")</p>";

            // Send Confirmation Email to User
            $user_subject = "Order Confirmed! - PK Automations #" . $order_id;
            $user_message = "<div style='font-family: sans-serif; max-width: 600px; margin: auto; border: 1px solid #eee; padding: 20px; border-radius: 10px;'>";
            $user_message .= "<h2 style='color: #0B63CE;'>Thank you for your order!</h2>";
            $user_message .= "<p>Hi " . htmlspecialchars($name) . ", your order <strong>#" . $order_id . "</strong> has been placed successfully.</p>";
            $user_message .= "<h3>Order Summary:</h3>";
            $user_message .= "<ul style='list-style: none; padding: 0;'>";
            foreach ($cart_items as $item) {
                $user_message .= "<li style='padding: 10px 0; border-bottom: 1px solid #eee;'>" . htmlspecialchars($item['name']) . " <span style='float: right;'>x " . $item['quantity'] . "</span></li>";
            }
            $user_message .= "</ul>";
            $user_message .= "<div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
            $user_message .= "<strong>Shipping To:</strong><br>";
            $user_message .= htmlspecialchars($address_line) . ", " . htmlspecialchars($street) . "<br>";
            $user_message .= htmlspecialchars($city) . "<br>Phone: " . htmlspecialchars($phone);
            $user_message .= "</div>";
            $user_message .= "<p style='font-size: 1.2rem;'><strong>Total Paid: " . formatPrice($grand_total) . "</strong></p>";
            $user_message .= "<p>Payment Method: " . ucfirst($payment_method) . "</p>";
            $user_message .= "<hr><p style='color: #666; font-size: 0.8rem;'>PK Automations - Innovate. Automate. Elevate.</p></div>";
            sendEmail($email, $user_subject, $user_message);

            // Send Notification Email to Admin
            $admin_subject = "NEW ORDER ALERT! [#" . $order_id . "] - " . $name;
            $admin_message = "<div style='font-family: sans-serif; border: 2px solid #0B63CE; padding: 25px; border-radius: 10px;'>";
            $admin_message .= "<h1 style='color: #0B63CE; margin-top: 0;'>🚀 New Order Received!</h1>";
            $admin_message .= "<p style='font-size: 1.1rem;'><strong>Order ID:</strong> #" . $order_id . "</p>";
            $admin_message .= "<p><strong>Customer:</strong> " . htmlspecialchars($name) . " (" . htmlspecialchars($email) . ")</p>";
            $admin_message .= "<h3>Items Ordered:</h3><ul style='background: #f4f4f4; padding: 20px; border-radius: 8px;'>";
            foreach ($cart_items as $item) {
                $admin_message .= "<li>" . htmlspecialchars($item['name']) . " (Qty: " . $item['quantity'] . ") - " . formatPrice($item['price']) . " each</li>";
            }
            $admin_message .= "</ul>";
            $admin_message .= "<h3>Shipping Address:</h3>";
            $admin_message .= "<p>" . htmlspecialchars($address_line) . ", " . htmlspecialchars($street) . ", " . htmlspecialchars($city) . "<br>Tel: " . htmlspecialchars($phone) . "</p>";
            $admin_message .= "<p><strong>Grand Total: " . formatPrice($grand_total) . "</strong></p>";
            $admin_message .= "<p><strong>Payment Method:</strong> " . strtoupper($payment_method) . "</p>";
            $admin_message .= "<div style='margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;'>";
            $admin_message .= "<a href='" . SITE_URL . "/admin/' style='background: #0B63CE; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>GO TO ADMIN PANEL</a>";
            $admin_message .= "</div></div>";
            sendEmail(ADMIN_EMAIL, $admin_subject, $admin_message);

            $conn->commit();
            $_SESSION['cart'] = [];
            $_SESSION['order_success'] = true;
            header('Location: dashboard?section=orders');
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log('Order placement failed: ' . $e->getMessage());
            header('Location: checkout?error=1');
            exit;
        }
    }
}

$page_title = "Checkout";
require_once 'includes/header.php';
?>

<div class="container py-5 mt-5">
    <div class="row g-4">
        <!-- Left Column: Forms -->
        <div class="col-lg-8">
            <div class="checkout-card mb-4">
                <div class="checkout-stepper mb-4">
                    <div class="step active"><span class="step-circle"><i class="fas fa-user"></i></span> Details</div>
                    <div class="step active"><span class="step-circle"><i class="fas fa-map-marker-alt"></i></span>
                        Shipping</div>
                    <div class="step active"><span class="step-circle"><i class="fas fa-credit-card"></i></span> Payment
                    </div>
                </div>

                <div class="checkout-header d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                    <h2 class="h3 fw-bold mb-0">Checkout</h2>
                    <a href="cart" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>
                </div>

                <form method="post" id="checkoutForm">
                    <?php echo csrfField(); ?>
                    <!-- Section 1: Customer Details -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3"><i class="fas fa-info-circle text-primary me-2"></i> 1. Customer
                            Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($user['name']); ?>" placeholder="Name"
                                        required>
                                    <label for="name">Full Name</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating mb-3">
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($user['email']); ?>" placeholder="Email"
                                        required>
                                    <label for="email">Email Address</label>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                                        placeholder="Phone" required>
                                    <label for="phone">Phone Number</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Delivery Address -->
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3"><i class="fas fa-truck text-primary me-2"></i> 2. Delivery Address</h5>
                        <?php
                        $selected_address = null;
                        if (!empty($addresses)) {
                            foreach ($addresses as $address) {
                                if ($address['is_primary']) {
                                    $selected_address = $address;
                                    break;
                                }
                            }
                            if (!$selected_address)
                                $selected_address = $addresses[0];
                        }
                        ?>

                        <?php if ($selected_address): ?>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="address_line" name="address_line"
                                            value="<?php echo htmlspecialchars($selected_address['address_line']); ?>"
                                            placeholder="Address" required>
                                        <label for="address_line">Address / Apartment</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="street" name="street"
                                            value="<?php echo htmlspecialchars($selected_address['street'] ?? ''); ?>"
                                            placeholder="Street" required>
                                        <label for="street">Street</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating mb-3">
                                        <input type="text" class="form-control" id="city" name="city"
                                            value="<?php echo htmlspecialchars($selected_address['city']); ?>"
                                            placeholder="City" required>
                                        <label for="city">City</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <?php include 'includes/shipping_locations_fetch.php'; ?>
                                    <div class="mb-3">
                                        <label for="shipping_location" class="form-label small text-muted ms-2">Shipping
                                            Location & Fee</label>
                                        <select name="shipping_location" id="shipping_location"
                                            class="form-select form-select-lg" required>
                                            <option value="">Select a location</option>
                                            <?php foreach ($shipping_locations as $loc): ?>
                                                <option value="<?php echo $loc['id']; ?>" data-fee="<?php echo $loc['fee']; ?>">
                                                    <?php echo htmlspecialchars($loc['name']) . ' (KSh ' . number_format($loc['fee'], 2) . ')'; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i> No delivery address found.
                                <a href="dashboard?section=addresses" class="alert-link">Add an Address</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Section 3: Payment Method -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="fas fa-wallet text-primary me-2"></i> 3. Payment Method</h5>
                        <div class="payment-methods">
                            <label class="payment-method-card" for="mpesa-radio" id="card-mpesa">
                                <input type="radio" name="payment_method" value="mpesa" id="mpesa-radio" required>
                                <div class="card-content">
                                    <i class="fas fa-mobile-alt fa-2x mb-2"></i>
                                    <div class="small fw-bold">M-PESA</div>
                                </div>
                            </label>
                            <label class="payment-method-card" for="paypal-radio" id="card-paypal">
                                <input type="radio" name="payment_method" value="paypal" id="paypal-radio">
                                <div class="card-content">
                                    <i class="fab fa-paypal fa-2x mb-2"></i>
                                    <div class="small fw-bold">PayPal</div>
                                </div>
                            </label>
                            <label class="payment-method-card" for="free-radio" id="card-free">
                                <input type="radio" name="payment_method" value="free" id="free-radio">
                                <div class="card-content">
                                    <i class="fas fa-gift fa-2x mb-2"></i>
                                    <div class="small fw-bold">Free</div>
                                </div>
                            </label>
                        </div>

                        <!-- M-PESA Instructions -->
                        <div id="mpesa-instructions" class="p-4 rounded border-0 shadow-sm d-none"
                            style="background-color: #e3f2fd;">
                            <div class="d-flex align-items-center mb-3">
                                <img src="assets/images/mpesa.png" alt="Mpesa" style="height:40px;">
                                <h5 class="ms-3 mb-0 fw-bold">Lipa na M-PESA</h5>
                            </div>
                            <p class="mb-3">Pay to Till Number: <strong class="text-primary h5">7944029</strong></p>
                            <ol class="small mb-3">
                                <li>Go to M-PESA menu, select <strong>Lipa na M-PESA</strong></li>
                                <li>Select <strong>Buy Goods and Services</strong></li>
                                <li>Enter Till Number: <strong>7944029</strong></li>
                                <li>Enter Amount & Pin</li>
                            </ol>
                            <p class="small mb-0 text-muted">Recipient Name: <strong>Peter Kuria Ngaruiya</strong></p>
                        </div>

                        <!-- PayPal Button -->
                        <div id="paypal-button-container" class="mt-4 d-none"></div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 mt-4 fw-bold shadow-sm"
                            id="place-order-btn">
                            PLACE ORDER
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-lg-4">
            <div class="sticky-order-summary">
                <div class="card border-0 shadow-md rounded-4 overflow-hidden">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold small"><?php echo htmlspecialchars($item['name']); ?>
                                                </div>
                                                <div class="text-muted tiny">Qty: <?php echo $item['quantity']; ?></div>
                                            </td>
                                            <td class="text-end small fw-bold">
                                                KSh <?php echo number_format($item['subtotal'], 2); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <hr class="my-3 opacity-10">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Subtotal</span>
                            <span class="fw-bold small">KSh <?php echo number_format($cart_total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Shipping</span>
                            <span class="fw-bold small" id="summary-shipping-fee">KSh 0.00</span>
                        </div>
                        <hr class="my-3 opacity-10">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h6 mb-0 fw-bold">Total</span>
                            <span class="h5 mb-0 fw-bold text-primary" id="summary-grand-total">
                                KSh <?php echo number_format($cart_total, 2); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-0 py-3 text-center">
                        <p class="text-muted tiny mb-0"><i class="fas fa-lock me-1"></i> Secure Checkout Guaranteed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script
    src="https://www.paypal.com/sdk/js?client-id=AdCIvar3OzaYe5FemO0FejxAiYy3sKZFEEtCTziXMoDMYlvLeJZjhdc9FUjUmcmLh9EovP2j0dbwee3X&currency=USD"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle payment method visibility using d-none class
        function togglePaymentGroups() {
            const mpesaRadio = document.getElementById('mpesa-radio');
            const paypalRadio = document.getElementById('paypal-radio');
            const freeRadio = document.getElementById('free-radio');

            const mpesaInstructions = document.getElementById('mpesa-instructions');
            const paypalContainer = document.getElementById('paypal-button-container');
            const placeOrderBtn = document.getElementById('place-order-btn');

            // Helper to show/hide elements
            const show = (el) => el && el.classList.remove('d-none');
            const hide = (el) => el && el.classList.add('d-none');

            // Reset all active classes on cards
            document.querySelectorAll('.payment-method-card').forEach(card => card.classList.remove('active'));

            // Handle M-PESA
            if (mpesaRadio && mpesaRadio.checked) {
                show(mpesaInstructions);
                document.getElementById('card-mpesa')?.classList.add('active');
            } else {
                hide(mpesaInstructions);
            }

            // Handle PayPal
            if (paypalRadio && paypalRadio.checked) {
                show(paypalContainer);
                if (placeOrderBtn) placeOrderBtn.style.display = 'none'; // Button uses inline style toggling or we can use d-none
                // Note: placeOrderBtn is typically block, so we'll hide it. 
                // Using style.display for button to ensure we don't mess up its specific display type if not block
                document.getElementById('card-paypal')?.classList.add('active');
                renderPayPalButtons();
            } else {
                hide(paypalContainer);
                if (placeOrderBtn) placeOrderBtn.style.display = 'block';
            }

            // Handle Free
            if (freeRadio && freeRadio.checked) {
                document.getElementById('card-free')?.classList.add('active');
            }
        }

        // Add direct click listeners to the cards
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('click', function (e) {
                // Return if clicking the radio itself to avoid double-trigger
                if (e.target.type === 'radio') return;

                const radio = this.querySelector('input[type="radio"]');
                if (radio && !radio.checked) {
                    radio.checked = true;
                    // Dispatch change event so other listeners know
                    radio.dispatchEvent(new Event('change'));
                    togglePaymentGroups();
                }
            });
        });

        // Add listeners to radios
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', togglePaymentGroups);
        });

        // Initialize state
        togglePaymentGroups();

        // PayPal Integration
        let paypalRendered = false;
        function renderPayPalButtons() {
            if (paypalRendered || typeof paypal === 'undefined') return;
            const container = document.getElementById('paypal-button-container');
            if (!container) return;

            // Ensure container is visible before rendering to avoid dimension issues
            container.classList.remove('d-none');

            paypal.Buttons({
                createOrder: function (data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: '<?php echo number_format($cart_total / 129, 2); ?>'
                            }
                        }]
                    });
                },
                onApprove: function (data, actions) {
                    return actions.order.capture().then(function (details) {
                        var form = document.getElementById('checkoutForm');
                        var hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = 'payment_method';
                        hiddenInput.value = 'paypal';
                        form.appendChild(hiddenInput);
                        form.submit();
                    });
                },
                onError: function (err) {
                    console.error('PayPal Error:', err);
                    alert('PayPal encountered an error. Please try again.');
                }
            }).render('#paypal-button-container');

            paypalRendered = true;
        }

        // Shipping fee calculation
        const shippingSelect = document.getElementById('shipping_location');
        if (shippingSelect) {
            shippingSelect.addEventListener('change', function () {
                const fee = parseFloat(this.options[this.selectedIndex].getAttribute('data-fee')) || 0;
                const subtotal = <?php echo $cart_total; ?>;
                const total = subtotal + fee;

                const feeDisplay = document.getElementById('summary-shipping-fee');
                const totalDisplay = document.getElementById('summary-grand-total');

                if (feeDisplay) feeDisplay.innerText = 'KSh ' + fee.toLocaleString('en-US', { minimumFractionDigits: 2 });
                if (totalDisplay) totalDisplay.innerText = 'KSh ' + total.toLocaleString('en-US', { minimumFractionDigits: 2 });
            });
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>