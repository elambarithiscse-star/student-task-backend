<?php
// Test script to verify create.php
// Run with: c:\xampp\php\php.exe test_create_task.php

// Mock POST data
$data = [
    "user_id" => 1, // Ensure this user exists or use valid ID
    "title" => "Test Task",
    "description" => "Test Description",
    "due_date" => "2026-12-31 23:59:59",
    "status" => "pending"
];

// We can't easily mock php://input for command line without a wrapper or using php-cgi
// Instead, let's just try to include the file and see if it crashes before reading input
// Or, we can use php-cgi if available.

echo "Testing create.php inclusion...\n";

// We need to set working directory to api/tasks because create.php uses relative paths
chdir('api/tasks');

// Mock request method
$_SERVER['REQUEST_METHOD'] = 'POST';

// We can't really test the full flow this way easily because of php://input
// But we can check for syntax errors or include errors.

try {
    // This will likely fail on reading input or similar, but we want to see if it crashes earlier
    // Actually, let's use a different approach.
    // We will use curl to hit the local server if it was running, but I don't have curl working reliably maybe?
    // Let's try to run `php -l` to check syntax first.

    echo "Syntax check:\n";
    system("c:\\xampp\\php\\php.exe -l create.php");

}
catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
?>
