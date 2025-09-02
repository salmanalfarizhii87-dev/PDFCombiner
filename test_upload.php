<?php
// Test upload functionality
echo "<h1>Test Upload PDF</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdfFile'])) {
    echo "<h2>File Uploaded:</h2>";
    echo "Name: " . $_FILES['pdfFile']['name'] . "<br>";
    echo "Size: " . $_FILES['pdfFile']['size'] . " bytes<br>";
    echo "Type: " . $_FILES['pdfFile']['type'] . "<br>";
    echo "Error: " . $_FILES['pdfFile']['error'] . "<br>";
    
    if ($_FILES['pdfFile']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color: green;'>✅ File uploaded successfully!</p>";
        
        // Test FPDI
        try {
            require_once 'vendor/autoload.php';
            $pdf = new \setasign\Fpdi\Fpdi();
            $pageCount = $pdf->setSourceFile($_FILES['pdfFile']['tmp_name']);
            echo "<p style='color: green;'>✅ FPDI can read PDF: {$pageCount} pages</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ FPDI Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Upload failed with error code: " . $_FILES['pdfFile']['error'] . "</p>";
    }
} else {
    echo "<form method='post' enctype='multipart/form-data'>";
    echo "<p>Select a PDF file to test:</p>";
    echo "<input type='file' name='pdfFile' accept='.pdf' required><br><br>";
    echo "<button type='submit'>Test Upload</button>";
    echo "</form>";
}
?>
