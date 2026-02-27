<?php
/**
 * Helper Functions
 * Utility functions for authentication and validation
 */

/**
 * Get user ID from authorization header
 * For simplicity, we're extracting user_id from request
 * In production, validate JWT token here
 */
function getUserIdFromRequest($data = null)
{
    // Try to get all headers
    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
    }

    // Check $_SERVER for headers too (fallback for some environments)
    $serverId = isset($_SERVER['HTTP_USER_ID']) ? $_SERVER['HTTP_USER_ID'] : null;

    // 1. Check for User-Id header
    if (isset($headers['user-id'])) {
        return $headers['user-id'];
    }
    if ($serverId) {
        return $serverId;
    }

    // 2. Check for Authorization header (Bearer token)
    if (isset($headers['authorization'])) {
        $authHeader = $headers['authorization'];
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        // $token = $matches[1];
        }
    }

    // 3. Fallback: check provided data, POST body, or query params for user_id
    if ($data && isset($data->user_id)) {
        return $data->user_id;
    }

    // Check query params (for GET requests)
    if (isset($_GET['user_id'])) {
        return $_GET['user_id'];
    }

    // Only read stream if not already provided
    if (!$data) {
        $json = json_decode(file_get_contents("php://input"));
        if ($json && isset($json->user_id)) {
            return $json->user_id;
        }
    }

    return null;
}

/**
 * Validate required fields
 */
function validateRequired($data, $fields)
{
    foreach ($fields as $field) {
        if (empty($data->$field)) {
            return false;
        }
    }
    return true;
}

/**
 * Send JSON response
 */
function sendResponse($code, $success, $message, $data = null)
{
    http_response_code($code);
    $response = [
        "success" => $success,
        "message" => $message
    ];
    if ($data !== null) {
        $response = array_merge($response, $data);
    }
    echo json_encode($response);
    exit();
}

/**
 * Sanitize input
 */
function sanitizeInput($data)
{
    return htmlspecialchars(strip_tags($data));
}

/**
 * Validate date format
 */
function isValidDate($date, $format = 'Y-m-d H:i:s')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}
