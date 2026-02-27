<?php
/**
 * User Login API
 * Endpoint: POST /api/login.php
 * Authenticates user and returns token
 */

// Include configuration files
include_once '../config/cors.php';
include_once '../config/database.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate input
if (empty($data->email) || empty($data->password)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Please provide email and password."
    ]);
    exit();
}

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get user by email
    $query = "SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();

    // Check if user exists
    if ($stmt->rowCount() == 0) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password."
        ]);
        exit();
    }

    // Get user data
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if (password_verify($data->password, $row['password'])) {
        
        // Generate simple token (for production, use JWT)
        $token = bin2hex(random_bytes(32));

        // Successful login
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Login successful.",
            "token" => $token,
            "user" => [
                "id" => $row['id'],
                "name" => $row['name'],
                "email" => $row['email']
            ]
        ]);

    } else {
        // Invalid password
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password."
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
