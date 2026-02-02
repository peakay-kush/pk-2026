<?php
/**
 * Authentication Functions
 * Handles user registration, login, and session management
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

/**
 * Register a new user
 * @param string $name
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function registerUser($name, $email, $password)
{
    global $conn;

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)");

    if ($stmt->execute([$name, $email, $password_hash])) {
        return ['success' => true, 'message' => 'Registration successful'];
    } else {
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

/**
 * Login user
 * @param string $email
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function loginUser($email, $password)
{
    global $conn;

    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }

    $stmt = $conn->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }

    if (password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        return ['success' => true, 'message' => 'Login successful'];
    } else {
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn()
{
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin()
{
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Require login (redirect if not logged in)
 * @param string $redirect_to
 */
function requireLogin($redirect_to = '/login')
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirect_to);
        exit;
    }
}

/**
 * Require admin (redirect if not admin)
 * @param string $redirect_to
 */
function requireAdmin($redirect_to = '/index')
{
    if (!isAdmin()) {
        header('Location: ' . $redirect_to);
        exit;
    }
}

/**
 * Logout user
 */
function logoutUser()
{
    session_unset();
    session_destroy();
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser()
{
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}
?>