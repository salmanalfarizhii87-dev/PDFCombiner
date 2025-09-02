<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include Composer autoloader
require_once 'vendor/autoload.php';

use setasign\Fpdi\Fpdi;

// Function to send JSON response
function sendResponse($success, $message, $filename = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'filename' => $filename
    ]);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

// Check if file was uploaded
if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, 'File upload error');
}

// Check if pagesPerSheet is provided
if (!isset($_POST['pagesPerSheet'])) {
    sendResponse(false, 'Pages per sheet not specified');
}

// Check if pageArrangement is provided
if (!isset($_POST['pageArrangement'])) {
    sendResponse(false, 'Page arrangement not specified');
}

// Check if paperSize is provided
if (!isset($_POST['paperSize'])) {
    sendResponse(false, 'Paper size not specified');
}

$uploadedFile = $_FILES['pdfFile'];
$pagesPerSheet = (int)$_POST['pagesPerSheet'];
$pageArrangement = $_POST['pageArrangement'];
$paperSize = $_POST['paperSize'];

// Validate file type
$fileInfo = pathinfo($uploadedFile['name']);
if (strtolower($fileInfo['extension']) !== 'pdf') {
    sendResponse(false, 'File must be a PDF');
}

// Validate file size (max 10MB)
if ($uploadedFile['size'] > 10 * 1024 * 1024) {
    sendResponse(false, 'File size too large. Maximum 10MB allowed');
}

// Validate pages per sheet
$allowedPagesPerSheet = [2, 4, 5, 6, 8];
if (!in_array($pagesPerSheet, $allowedPagesPerSheet)) {
    sendResponse(false, 'Invalid pages per sheet option');
}

// Validate page arrangement
$allowedArrangements = ['side_by_side', 'top_bottom'];
if (!in_array($pageArrangement, $allowedArrangements)) {
    sendResponse(false, 'Invalid page arrangement option');
}

// Validate paper size
$allowedPaperSizes = ['A4', 'Letter', 'Legal', 'A3', 'A5', 'B4', 'B5'];
if (!in_array($paperSize, $allowedPaperSizes)) {
    sendResponse(false, 'Invalid paper size option');
}

try {
    // Create output directory if it doesn't exist
    $outputDir = 'output';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    // Generate unique filename
    $outputFilename = 'combined_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';
    $outputPath = $outputDir . '/' . $outputFilename;

    // Create new PDF document
    $pdf = new Fpdi();
    
    // Import the uploaded PDF
    $pageCount = $pdf->setSourceFile($uploadedFile['tmp_name']);
    
    if ($pageCount === 0) {
        sendResponse(false, 'Invalid PDF file or empty PDF');
    }

    // Calculate layout based on pages per sheet and arrangement
    $layout = calculateLayout($pagesPerSheet, $pageArrangement);
    $pagesPerRow = $layout['cols'];
    $pagesPerCol = $layout['rows'];
    
    // Calculate new page size based on paper size
    $paperDimensions = getPaperDimensions($paperSize);
    $newPageWidth = $paperDimensions['width'];
    $newPageHeight = $paperDimensions['height'];
    
    // Calculate individual page size
    $pageWidth = $newPageWidth / $pagesPerRow;
    $pageHeight = $newPageHeight / $pagesPerCol;
    
    // Process pages in batches
    $currentPage = 1;
    $batchNumber = 1;
    
    while ($currentPage <= $pageCount) {
        // Add new page
        $pdf->AddPage('P', [$newPageWidth, $newPageHeight]); // Paper size
        
        // Place pages on current sheet
        for ($row = 0; $row < $pagesPerCol && $currentPage <= $pageCount; $row++) {
            for ($col = 0; $col < $pagesPerRow && $currentPage <= $pageCount; $col++) {
                // Import page
                $templateId = $pdf->importPage($currentPage);
                
                // Calculate position
                $x = $col * $pageWidth;
                $y = $row * $pageHeight;
                
                // Get original page size
                $originalSize = $pdf->getTemplateSize($templateId);
                $originalWidth = $originalSize['width'];
                $originalHeight = $originalSize['height'];
                
                // Calculate scale to fit
                $scaleX = $pageWidth / $originalWidth;
                $scaleY = $pageHeight / $originalHeight;
                $scale = min($scaleX, $scaleY);
                
                // Calculate centered position
                $scaledWidth = $originalWidth * $scale;
                $scaledHeight = $originalHeight * $scale;
                $centeredX = $x + ($pageWidth - $scaledWidth) / 2;
                $centeredY = $y + ($pageHeight - $scaledHeight) / 2;
                
                // Use the imported page
                $pdf->useTemplate($templateId, $centeredX, $centeredY, $scaledWidth, $scaledHeight);
                
                $currentPage++;
            }
        }
        
        $batchNumber++;
    }
    
    // Save the PDF
    $pdf->Output('F', $outputPath);
    
    // Verify file was created
    if (!file_exists($outputPath)) {
        sendResponse(false, 'Failed to create output file');
    }
    
    sendResponse(true, 'PDF successfully combined', $outputFilename);
    
} catch (Exception $e) {
    sendResponse(false, 'Error processing PDF: ' . $e->getMessage());
}

// Function to get paper dimensions
function getPaperDimensions($paperSize) {
    switch ($paperSize) {
        case 'A4':
            return ['width' => 210, 'height' => 297]; // mm
        case 'Letter':
            return ['width' => 215.9, 'height' => 279.4]; // mm (8.5" × 11")
        case 'Legal':
            return ['width' => 215.9, 'height' => 355.6]; // mm (8.5" × 14")
        case 'A3':
            return ['width' => 297, 'height' => 420]; // mm
        case 'A5':
            return ['width' => 148, 'height' => 210]; // mm
        case 'B4':
            return ['width' => 250, 'height' => 353]; // mm
        case 'B5':
            return ['width' => 176, 'height' => 250]; // mm
        default:
            return ['width' => 210, 'height' => 297]; // Default A4
    }
}

// Function to calculate layout based on pages per sheet and arrangement
function calculateLayout($pagesPerSheet, $pageArrangement = 'side_by_side') {
    switch ($pagesPerSheet) {
        case 2:
            if ($pageArrangement === 'top_bottom') {
                return ['rows' => 2, 'cols' => 1]; // Vertical: 2 rows, 1 column
            } else {
                return ['rows' => 1, 'cols' => 2]; // Horizontal: 1 row, 2 columns
            }
        case 4:
            if ($pageArrangement === 'top_bottom') {
                return ['rows' => 4, 'cols' => 1]; // Vertical: 4 rows, 1 column
            } else {
                return ['rows' => 2, 'cols' => 2]; // Horizontal: 2 rows, 2 columns
            }
        case 5:
            if ($pageArrangement === 'top_bottom') {
                return ['rows' => 5, 'cols' => 1]; // Vertical: 5 rows, 1 column
            } else {
                return ['rows' => 2, 'cols' => 3]; // Horizontal: 2x3 grid, but only use 5 positions
            }
        case 6:
            if ($pageArrangement === 'top_bottom') {
                return ['rows' => 6, 'cols' => 1]; // Vertical: 6 rows, 1 column
            } else {
                return ['rows' => 2, 'cols' => 3]; // Horizontal: 2 rows, 3 columns
            }
        case 8:
            if ($pageArrangement === 'top_bottom') {
                return ['rows' => 8, 'cols' => 1]; // Vertical: 8 rows, 1 column
            } else {
                return ['rows' => 2, 'cols' => 4]; // Horizontal: 2 rows, 4 columns
            }
        default:
            if ($pageArrangement === 'top_bottom') {
                return ['rows' => 2, 'cols' => 1]; // Default vertical
            } else {
                return ['rows' => 2, 'cols' => 2]; // Default horizontal
            }
    }
}
?>
