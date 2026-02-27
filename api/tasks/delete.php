<?php
/**
 * Delete Task API
 * Endpoint: DELETE /api/tasks/delete.php
 * Deletes a task
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
if (empty($data->id) || !$user_id) {
    sendResponse(400, false, "Incomplete data. Required: id, user_id.");
}

// Sanitize inputs
$task_id = sanitizeInput($data->id);
// $user_id is already retrieved

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
        sendResponse(404, false, "Task not found or you don't have permission to delete it.");
    }

    // Delete task
    $query = "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":id", $task_id);
    $stmt->bindParam(":user_id", $user_id);

    // Execute query
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Task deleted successfully."
        ]);
    }
    else {
        sendResponse(500, false, "Unable to delete task.");
    }

}
catch (Exception $e) {
    sendResponse(500, false, "Server error: " . $e->getMessage());
}
?>
