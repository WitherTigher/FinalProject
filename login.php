<?php
header('Content-Type: application/json');

// 1. Read JSON input
$input = file_get_contents('php://input');
$data  = json_decode($input, true);

// 2. Validate incoming fields
if (!isset($data['email'], $data['password'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Email and password are required.'
    ]);
    exit;
}

$email    = $data['email'];
$password = $data['password'];

// 3. Connect to MySQL
$mysqli = new mysqli("localhost", "root", "", "country");
if ($mysqli->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'DB connection failed: ' . $mysqli->connect_error
    ]);
    exit;
}

// 4. Lookup the user by email
$stmt = $mysqli->prepare("SELECT id, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'No account found with that email.'
    ]);
    $stmt->close();
    $mysqli->close();
    exit;
}

// 5. Bind and fetch
$stmt->bind_result($userId, $hash);
$stmt->fetch();

// 6. Verify password
if (!password_verify($password, $hash)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid credentials.'
    ]);
    $stmt->close();
    $mysqli->close();
    exit;
}

// ——————————————
// **INSERT SESSION LOGIC HERE**
// ——————————————
session_start();
$_SESSION['userId'] = $userId;

// 7. Update last_login & login_count
$update = $mysqli->prepare("
    UPDATE users
       SET last_login = NOW(),
           login_count = login_count + 1
     WHERE id = ?
");
$update->bind_param("i", $userId);
$update->execute();
$update->close();

// 8. Generate a token (optional, for fetch‑based flows)
$token = bin2hex(random_bytes(16));

// 9. Return success
echo json_encode([
    'success' => true,
    'token'   => $token
]);

$stmt->close();
$mysqli->close();
