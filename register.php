<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Tell the browser we'll be returning JSON
header('Content-Type: application/json');

try {
    // Read the JSON from the POST body
    $inputJSON = file_get_contents('php://input');
    if ($inputJSON === false) {
        throw new Exception("Failed to read input data");
    }

    $data = json_decode($inputJSON, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
    }

    // Basic validation of input fields
    if(!isset($data['name'], $data['email'], $data['password'], $data['securityQuestion'], $data['securityAnswer'])){
        throw new Exception("Missing required fields");
    }

    // Pull out the form data
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $securityQuestion = $data['securityQuestion'];
    $securityAnswer = $data['securityAnswer'];

    // Validate name length
    if (strlen($name) > 100) {
        throw new Exception("Name must be less than 100 characters");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format");
    }

    // Validate password strength
    if (strlen($password) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }

    // Connect to MySQL
    $mysqli = mysqli_connect("sql306.infinityfree.com", "if0_38810052", "GEEXEOxOg8Po", "if0_38810052_calendar");

    if (!$mysqli) {
        throw new Exception("Database connection failed: " . mysqli_connect_error());
    }

    // Check if user already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $stmt->store_result();
    if($stmt->num_rows > 0) {
        throw new Exception("Email already registered");
    }
    $stmt->close();

    // Hash the user's password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if ($hashedPassword === false) {
        throw new Exception("Password hashing failed");
    }

    // Insert user with only the required fields
    $sql = "INSERT INTO users (name, email, password_hash, security_question, security_answer) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $mysqli->error);
    }

    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $securityQuestion, $securityAnswer);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    // Registration success
    echo json_encode([
        "success" => true,
        "message" => "Registration successful!"
    ]);

    $stmt->close();
    $mysqli->close();

} catch (Exception $e) {
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>