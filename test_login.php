<?php
session_start();
$_POST['username'] = 'admin';
$_POST['password'] = 'admin123';
$_POST['btn_login'] = '1';

// Simulate REMOTE_ADDR and HTTP_USER_AGENT
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'TestScript';

// Tangkap output header location
ob_start();
include __DIR__ . '/modules/auth/process_login.php';
$output = ob_get_clean();

echo "Session data:\n";
print_r($_SESSION);
