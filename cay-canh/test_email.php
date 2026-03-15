<?php
require_once 'config.php';
require_once 'functions.php';

echo "Testing email configuration...\n";

try {
    $test_email = 'test@example.com'; // Thay bằng email thật để test
    $test_token = 'test123';
    
    if (sendPasswordResetEmail($test_email, $test_token)) {
        echo "Email sent successfully! Check logs/email_log.txt for details.\n";
    } else {
        echo "Failed to send email. Check logs/email_log.txt for error details.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>