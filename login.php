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

// Connect to MySQL
$mysqli = new mysqli("localhost", "root", "", "cal");
if ($mysqli->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Lookup the user by email
$stmt = $mysqli->prepare("SELECT id, name, password_hash FROM users WHERE email = ?");
if (!$stmt) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $mysqli->error
    ]);
    exit;
}

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

// Return success
echo json_encode([
    'success' => true,
    'message' => 'Login successful'
]);

$stmt->close();
$mysqli->close();
