<?php
header('Content-Type: application/json');

// Get the email from POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email is required']);
    exit;
}

// Connect to database
$mysqli = new mysqli("localhost", "root", "", "country");
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get security question for the email
$stmt = $mysqli->prepare("SELECT id, security_question FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Email not found']);
    exit;
}

$user = $result->fetch_assoc();
echo json_encode([
    'success' => true,
    'userId' => $user['id'],
    'question' => $user['security_question']
]);

$stmt->close();
$mysqli->close(); 