<?php
/**
 * General Helper Functions
 */

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate slug from string
 * @param string $string
 * @return string
 */
function generateSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Get favorites count
 * @return int
 */
function getFavoritesCount()
{
    if (!isset($_SESSION['favorites']) || empty($_SESSION['favorites'])) {
        return 0;
    }
    return count($_SESSION['favorites']);
}

/**
 * Format price
 * @param float $price
 * @return string
 */
function formatPrice($price)
{
    return 'KSh ' . number_format($price, 2);
}

/**
 * Format date
 * @param string $date
 * @return string
 */
function formatDate($date)
{
    return date('M d, Y', strtotime($date));
}

/**
 * Generate star rating HTML
 * @param float $rating
 * @return string
 */
function generateStarRating($rating)
{
    $html = '<div class="star-rating">';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

    for ($i = 0; $i < $fullStars; $i++) {
        $html .= '<i class="fas fa-star"></i>';
    }

    if ($halfStar) {
        $html .= '<i class="fas fa-star-half-alt"></i>';
    }

    for ($i = 0; $i < $emptyStars; $i++) {
        $html .= '<i class="far fa-star"></i>';
    }

    $html .= ' <span class="rating-value">(' . number_format($rating, 1) . ')</span>';
    $html .= '</div>';

    return $html;
}

/**
 * Get cart item count
 * @return int
 */
function getCartItemCount()
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    $count = 0;
    foreach ($_SESSION['cart'] as $quantity) {
        $count += $quantity;
    }

    return $count;
}

/**
 * Get cart total
 * @return float
 */
function getCartTotal()
{
    global $conn;

    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    $total = 0;
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            $total += $product['price'] * $quantity;
        }
    }

    return $total;
}

/**
 * Send email using SMTP via PHPMailer
 * @param string $to
 * @param string $subject
 * @param string $message
 * @return bool
 */
function sendEmail($to, $subject, $message)
{
    // Use Composer autoload for PHPMailer
    $autoload = __DIR__ . '/../vendor/autoload.php';
    if (file_exists($autoload)) {
        require_once $autoload;
    } else {
        error_log('PHPMailer autoload not found');
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['EMAIL_USERNAME'] ?? '';
        $mail->Password = $_ENV['EMAIL_PASSWORD'] ?? '';

        $port = (int) ($_ENV['EMAIL_PORT'] ?? 465);
        $mail->Port = $port;

        if ($port === 465) {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->setFrom($_ENV['EMAIL_FROM'] ?? 'noreply@example.com', SITE_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body = $message;
        $mail->send();
        return true;
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Redirect with message
 * @param string $url
 * @param string $message
 * @param string $type (success, error, info)
 */
function redirect($url, $message = '', $type = 'info')
{
    if (!empty($message)) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return $message;
    }

    // Check for legacy session keys
    if (isset($_SESSION['success'])) {
        $message = ['text' => $_SESSION['success'], 'type' => 'success'];
        unset($_SESSION['success']);
        return $message;
    }

    if (isset($_SESSION['error'])) {
        $message = ['text' => $_SESSION['error'], 'type' => 'error'];
        unset($_SESSION['error']);
        return $message;
    }

    return null;
}

/**
 * Set flash message
 * @param string $message
 * @param string $type (success, error, info, warning)
 */
function setFlashMessage($message, $type = 'info')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}
/**
 * Generate CSRF token
 * @return string
 */
function generateCSRFToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verifyCSRFToken($token)
{
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF input field HTML
 * @return string
 */
function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Block unauthorized CSRF requests (for POST)
 */
function validateCSRF()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!verifyCSRFToken($token)) {
            die('Security token mismatch. Please refresh the page and try again.');
        }
    }
}
?>