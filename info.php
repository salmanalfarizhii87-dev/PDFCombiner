<?php
echo "PHP is working!<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current directory: " . getcwd() . "<br>";

// Test FPDI
try {
    require_once 'vendor/autoload.php';
    $pdf = new \setasign\Fpdi\Fpdi();
    echo "✅ FPDI is working!<br>";
} catch (Exception $e) {
    echo "❌ FPDI Error: " . $e->getMessage() . "<br>";
}

// Test file permissions
if (is_writable('output/')) {
    echo "✅ Output directory is writable<br>";
} else {
    echo "❌ Output directory is not writable<br>";
}
?>
