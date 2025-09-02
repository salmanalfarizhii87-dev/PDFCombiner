<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Check if file was uploaded
    if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error. Please try again.');
    }

    $uploadedFile = $_FILES['pdfFile'];
    $pagesPerSheet = (int)($_POST['pagesPerSheet'] ?? 4);
    $pageArrangement = $_POST['pageArrangement'] ?? 'side_by_side';
    $paperSize = $_POST['paperSize'] ?? 'A4';
    
    // Validate file type
    $fileInfo = pathinfo($uploadedFile['name']);
    if (strtolower($fileInfo['extension']) !== 'pdf') {
        throw new Exception('File must be a PDF');
    }

    // Check file size (10MB limit)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($uploadedFile['size'] > $maxSize) {
        throw new Exception('File size too large. Maximum 10MB allowed.');
    }

    // Include FPDI
    require_once __DIR__ . '/../vendor/autoload.php';
    $pdf = new \setasign\Fpdi\Fpdi();
    
    // Import the uploaded PDF
    $pageCount = $pdf->setSourceFile($uploadedFile['tmp_name']);
    
    if ($pageCount === 0) {
        throw new Exception('Invalid PDF file or empty PDF');
    }
    
    // Create output directory if it doesn't exist
    $outputDir = '/tmp/output';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    // Generate unique filename
    $outputFilename = 'combined_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';
    $outputPath = $outputDir . '/' . $outputFilename;
    
    // Calculate layout
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
    
    // Process pages
    $currentPage = 1;
    
    while ($currentPage <= $pageCount) {
        // Add new page
        $pdf->AddPage('P', [$newPageWidth, $newPageHeight]);
        
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
    }
    
    // Save the PDF
    $pdf->Output('F', $outputPath);
    
    if (file_exists($outputPath)) {
        // Return success response with file info
        echo json_encode([
            'success' => true,
            'message' => 'PDF berhasil digabungkan!',
            'filename' => $outputFilename,
            'pageCount' => $pageCount,
            'layout' => $pagesPerCol . 'x' . $pagesPerRow,
            'downloadUrl' => '/api/download?file=' . urlencode($outputFilename)
        ]);
    } else {
        throw new Exception('Failed to create output file');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
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
