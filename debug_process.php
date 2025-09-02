<?php
// Debug version of process.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Debug: Starting process.php test\n";

// Check if we can load FPDI
try {
    require_once 'vendor/autoload.php';
    echo "Debug: FPDI loaded successfully\n";
    
    $pdf = new \setasign\Fpdi\Fpdi();
    echo "Debug: FPDI object created\n";
    
} catch (Exception $e) {
    echo "Debug: FPDI Error: " . $e->getMessage() . "\n";
    exit;
}

// Check output directory
if (!is_dir('output')) {
    mkdir('output', 0755, true);
    echo "Debug: Created output directory\n";
}

if (is_writable('output')) {
    echo "Debug: Output directory is writable\n";
} else {
    echo "Debug: Output directory is NOT writable\n";
}

echo "Debug: All checks passed\n";
?>
