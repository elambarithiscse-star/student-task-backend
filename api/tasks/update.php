<?php
/**
 * Update Task API
 * Endpoint: PUT /api/tasks/update.php
 * Updates an existing task
 */

// Include configuration files
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../helpers/functions.php';

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validate required fields
// Get user_id from request (body or headers)
$user_id = getUserIdFromRequest($data);

// Validate required fields
if (
empty($data->id) ||
!$user_id ||
empty($data->title) ||
empty($data->due_date)
) {
    sendResponse(400, false, "Incomplete data. Required: id, user_id, title, due_date.");
}

// Validate status
$valid_statuses = ['pending', 'in_progress', 'completed'];
$status = isset($data->status) ? $data->status : 'pending';

if (!in_array($status, $valid_statuses)) {
    sendResponse(400, false, "Invalid status. Use: pending, in_progress, or completed.");
}

// Sanitize inputs
$task_id = sanitizeInput($data->id);
// $user_id is already retrieved
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

    // Check if task exists and belongs to user
    $query = "SELECT id FROM tasks WHERE id = :id AND user_id = :user_id LIMIT 1";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $task_id);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        sendResponse(404, false, "Task not found or you don't have permission to update it.");
    }

    // Update task
    $query = "UPDATE tasks 
              SET title = :title,
                  description = :description,
                  due_date = :due_date,
                  status = :status
              WHERE id = :id AND user_id = :user_id";

    $stmt = $db->prepare($query);

    // Bind parameters
    $stmt->bindParam(":title", $title);
    $stmt->bindParam(":description", $description);
    $stmt->bindParam(":due_date", $due_date);
    $stmt->bindParam(":status", $status);
    $stmt->bindParam(":id", $task_id);
    $stmt->bindParam(":user_id", $user_id);

    // Execute query
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Task updated successfully."
        ]);
    }
    else {
        sendResponse(500, false, "Unable to update task.");
    }

}
catch (Exception $e) {
    sendResponse(500, false, "Server error: " . $e->getMessage());
}
?>
