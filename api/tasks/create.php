<?php
/**
 * Create Task API
 * Endpoint: POST /api/tasks/create.php
 * Creates a new task for the authenticated user
 */

// Include configuration files
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../helpers/functions.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Get user_id from request (body or headers)
$user_id = getUserIdFromRequest($data);
if (!$user_id) {
    sendResponse(400, false, "Authentication required. Missing user_id.");
}
if (empty($data->title)) {
    sendResponse(400, false, "Missing field: title.");
}
if (empty($data->due_date)) {
    sendResponse(400, false, "Missing field: due_date.");
}

// Validate status
$valid_statuses = ['pending', 'in_progress', 'completed'];
$status = isset($data->status) ? $data->status : 'pending';

if (!in_array($status, $valid_statuses)) {
    sendResponse(400, false, "Invalid status. Use: pending, in_progress, or completed.");
}

// Sanitize inputs
// $user_id is already retrieved
// $user_id = sanitizeInput($data->user_id); // Removed as it might come from headers
$title = sanitizeInput($data->title);
$description = isset($data->description) ? sanitizeInput($data->description) : '';
$due_date = sanitizeInput($data->due_date);

// Validate title length
if (strlen($title) < 3) {
    sendResponse(400, false, "Title must be at least 3 characters long.");
}

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Verify user exists
    $query = "SELECT id FROM users WHERE id = :user_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        sendResponse(404, false, "User not found.");
    }

    // Insert new task
    $query = "INSERT INTO tasks 
              (user_id, title, description, due_date, status) 
              VALUES 
              (:user_id, :title, :description, :due_date, :status)";

    $stmt = $db->prepare($query);

    // Bind parameters
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":due_date", $due_date);
    $stmt->bindParam(":status", $status);

    // Execute query
    if ($stmt->execute()) {
        $task_id = $db->lastInsertId();

        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Task created successfully.",
            "task_id" => $task_id
        ]);
    }
    else {
        sendResponse(500, false, "Unable to create task.");
    }

}
catch (Exception $e) {
    sendResponse(500, false, "Server error: " . $e->getMessage());
}
?>
