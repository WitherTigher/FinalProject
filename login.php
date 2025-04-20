<?php
header('Content-Type: application/json');

// Start session
session_start();

// Read JSON input
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

// Validate incoming fields
if (!isset($data['email'], $data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required.'
    ]);
    exit;
}

$email = $data['email'];
$password = $data['password'];
$remember = isset($data['remember']) ? $data['remember'] : false;

// Connect to MySQL
$mysqli = new mysqli("localhost", "root", "", "calendar");
if ($mysqli->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Lookup the user by email
$stmt = $mysqli->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password.'
    ]);
    $stmt->close();
    $mysqli->close();
    exit;
}

// Bind and fetch
$stmt->bind_result($userId, $name, $hash);
$stmt->fetch();

// Verify password
if (!password_verify($password, $hash)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email or password.'
    ]);
    $stmt->close();
    $mysqli->close();
    exit;
}

// Set session variables
$_SESSION['userId'] = $userId;
$_SESSION['userName'] = $name;

// Handle remember me
if ($remember) {
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $updateStmt = $mysqli->prepare("UPDATE users SET remember_token = ?, token_expiry = ? WHERE id = ?");
    $updateStmt->bind_param("ssi", $token, $expiry, $userId);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Set cookie for 30 days
    setcookie('remember_token', $token, time() + (86400 * 30), "/", "", true, true);
}

// Update last_login & login_count
$update = $mysqli->prepare("
    UPDATE users 
    SET last_login = NOW(),
        login_count = login_count + 1
    WHERE id = ?
");
$update->bind_param("i", $userId);
$update->execute();
$update->close();

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Login successful'
]);

$stmt->close();
$mysqli->close();
