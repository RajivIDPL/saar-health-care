<?php
// Run this script to generate a proper password hash
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Generated Hash: " . $hash . "\n";

// Test the hash
if (password_verify($password, $hash)) {
    echo "✅ Password verification successful!\n";
} else {
    echo "❌ Password verification failed!\n";
}

// Copy the generated hash and use it in your INSERT statement
?>