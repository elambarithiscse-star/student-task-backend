<?php
/**
 * Get All Tasks API
 * Endpoint: GET /api/tasks/list.php
 * Returns all tasks for the authenticated user
 */

// Include configuration files
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../helpers/functions.php';

// This endpoint expects user_id in the request
// In production, extract user_id from JWT token

// For this project, get user_id from query parameter or header
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : getUserIdFromRequest();

if (!$user_id) {
    sendResponse(401, false, "Authentication required. Please provide user_id.");
}

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Query to get all tasks for user
    $query = "SELECT 
                id,
                title,
                description,
                due_date,
                status,
                created_at,
                updated_at
              FROM tasks 
              WHERE user_id = :user_id 
              ORDER BY due_date ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    // Fetch all tasks
    $tasks = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $tasks[] = [
            "id" => $row['id'],
            "title" => $row['title'],
            "description" => $row['description'],
            "due_date" => $row['due_date'],
            "status" => $row['status'],
            "created_at" => $row['created_at'],
            "updated_at" => $row['updated_at']
        ];
    }

    // Send success response
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "tasks" => $tasks,
        "count" => count($tasks)
    ]);

}
catch (Exception $e) {
    sendResponse(500, false, "Server error: " . $e->getMessage());
}