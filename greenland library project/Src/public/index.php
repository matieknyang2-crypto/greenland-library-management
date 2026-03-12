
<?php
session_start();
require_once 'config.php';

// Redirect based on login status
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
} else {
    header('Location: login_simple.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Management System</title>
</head>
<body>
    <p>Redirecting...</p>
</body>
</html>
