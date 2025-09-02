<?php
// Test file untuk process.php
echo "Testing process.php...\n";

// Simulate POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_FILES['pdfFile'] = [
    'name' => 'test.pdf',
    'type' => 'application/pdf',
    'tmp_name' => 'test.pdf',
    'error' => UPLOAD_ERR_OK,
    'size' => 1024
];
$_POST['pagesPerSheet'] = '4';

// Include process.php
ob_start();
include 'process.php';
$output = ob_get_clean();

echo "Output: " . $output . "\n";
?>
