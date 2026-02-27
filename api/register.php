<?php
/**
 * User Registration API
 * Endpoint: POST /api/register.php
 * Creates a new user account
 */

// Include configuration files
include_once '../config/cors.php';
include_once '../config/database.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (
    empty($data->name) ||
    empty($data->email) ||
    empty($data->password)
) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Incomplete data. Please provide name, email, and password."
    ]);
    exit();
}

// Validate email format
if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format."
    ]);
    exit();
}

// Validate password length
if (strlen($data->password) < 6) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters long."
    ]);
    exit();
}

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check if email already exists
    $query = "SELECT id FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Email already registered."
        ]);
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($data->password, PASSWORD_BCRYPT);

    // Insert new user
    $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";
    $stmt = $db->prepare($query);

    // Bind parameters
    $stmt->bindParam(":name", $data->name);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $hashed_password);

    // Execute query
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "User registered successfully."
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Unable to register user."
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>
