<?php
// c:/xampp/htdocs/student_task_tracker_backend/api/tasks/verify_paths.php
// Run this from the directory itself to test relative paths

echo "Current dir: " . getcwd() . "\n";

$cors = '../../config/cors.php';
$db = '../../config/database.php';
$funcs = '../../helpers/functions.php';

echo "Checking $cors: " . (file_exists($cors) ? "Found" : "NOT FOUND") . "\n";
echo "Checking $db: " . (file_exists($db) ? "Found" : "NOT FOUND") . "\n";
echo "Checking $funcs: " . (file_exists($funcs) ? "Found" : "NOT FOUND") . "\n";

include_once $cors;
echo "Included cors.php\n";

include_once $db;
echo "Included database.php\n";

include_once $funcs;
echo "Included functions.php\n";

echo "All includes successful.\n";
?>
