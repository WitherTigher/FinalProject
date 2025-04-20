<?php
header('Content-Type: application/json');

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$userId = $data['userId'] ?? '';
$answer = $data['answer'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if (empty($userId) || empty($answer) || empty($newPassword)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Connect to database
$mysqli = new mysqli("localhost", "root", "", "country");
if ($mysqli->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Verify security answer
$stmt = $mysqli->prepare("SELECT id FROM users WHERE id = ? AND security_answer = ?");
$stmt->bind_param("is", $userId, $answer);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Incorrect security answer']);
    exit;
}

// Update password
$passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $mysqli->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
$updateStmt->bind_param("si", $passwordHash, $userId);

if ($updateStmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update password']);
}

$stmt->close();
$updateStmt->close();
$mysqli->close(); 