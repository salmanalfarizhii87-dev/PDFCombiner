<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Check if file parameter is provided
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    die('File parameter is required');
}

$filename = $_GET['file'];

// Sanitize filename to prevent directory traversal
$filename = basename($filename);

// Check if file exists in temp directory
$filepath = '/tmp/output/' . $filename;

if (!file_exists($filepath)) {
    http_response_code(404);
    die('File not found');
}

// Check if file is actually a PDF
$fileInfo = pathinfo($filepath);
if (strtolower($fileInfo['extension']) !== 'pdf') {
    http_response_code(400);
    die('Invalid file type');
}

// Set headers for file download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output the file
readfile($filepath);

// Clean up: Delete the file after download (optional)
// unlink($filepath);
?>
