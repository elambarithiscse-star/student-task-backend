<?php
/**
 * Dashboard Statistics API
 * Endpoint: GET /api/tasks/dashboard.php
 * Returns task statistics for the authenticated user
 */

// Include configuration files
include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../helpers/functions.php';

// Get user_id from query parameter or header
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

$headers = getallheaders();
if (!$user_id && isset($headers['User-Id'])) {
    $user_id = $headers['User-Id'];
}

if (!$user_id) {
    sendResponse(401, false, "Authentication required. Please provide user_id.");
}

try {
    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Get total tasks count
    $query = "SELECT COUNT(*) as total FROM tasks WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Get completed tasks count
    $query = "SELECT COUNT(*) as completed FROM tasks WHERE user_id = :user_id AND status = 'completed'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $completed = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];

    // Get pending tasks count
    $query = "SELECT COUNT(*) as pending FROM tasks WHERE user_id = :user_id AND status = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

    // Get in_progress tasks count
    $query = "SELECT COUNT(*) as in_progress FROM tasks WHERE user_id = :user_id AND status = 'in_progress'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $in_progress = $stmt->fetch(PDO::FETCH_ASSOC)['in_progress'];

    // Get overdue tasks count
    $query = "SELECT COUNT(*) as overdue 
              FROM tasks 
              WHERE user_id = :user_id 
              AND status != 'completed' 
              AND due_date < NOW()";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    $overdue = $stmt->fetch(PDO::FETCH_ASSOC)['overdue'];

    // Calculate completion rate
    $completion_rate = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

    // Send response
    http_response_code(200);
    echo json_encode([
        "success" => true,
        "stats" => [
            "total" => (int)$total,
            "completed" => (int)$completed,
            "pending" => (int)$pending,
            "in_progress" => (int)$in_progress,
            "overdue" => (int)$overdue,
            "completion_rate" => $completion_rate
        ]
    ]);

} catch (Exception $e) {
    sendResponse(500, false, "Server error: " . $e->getMessage());
}
?>
