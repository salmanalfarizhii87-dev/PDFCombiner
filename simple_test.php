<!DOCTYPE html>
<html>
<head>
    <title>PDF Combiner - Simple Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 15px 0; }
        input, select { padding: 8px; margin: 5px 0; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        .result { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>📄 PDF Combiner - Simple Test</h1>
    
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label>Pilih File PDF:</label><br>
            <input type="file" name="pdfFile" accept=".pdf" required>
        </div>
        
        <div class="form-group">
            <label>Halaman per Sheet:</label><br>
            <select name="pagesPerSheet" required>
                <option value="2">2 halaman per 1 halaman</option>
                <option value="4" selected>4 halaman per 1 halaman</option>
                <option value="5">5 halaman per 1 halaman</option>
                <option value="6">6 halaman per 1 halaman</option>
                <option value="8">8 halaman per 1 halaman</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Pengaturan Halaman:</label><br>
            <select name="pageArrangement" required>
                <option value="side_by_side" selected>Side by Side (Horizontal)</option>
                <option value="top_bottom">Top and Bottom (Vertical)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Ukuran Kertas:</label><br>
            <select name="paperSize" required>
                <option value="A4" selected>A4 (210 × 297 mm)</option>
                <option value="Letter">Letter (8.5 × 11 in)</option>
                <option value="Legal">Legal (8.5 × 14 in)</option>
                <option value="A3">A3 (297 × 420 mm)</option>
                <option value="A5">A5 (148 × 210 mm)</option>
                <option value="B4">B4 (250 × 353 mm)</option>
                <option value="B5">B5 (176 × 250 mm)</option>
            </select>
        </div>
        
        <button type="submit" name="submit">Combine PDF</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
        echo "<div class='result'>";
        echo "<h3>Processing...</h3>";
        
        // Check if file was uploaded
        if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
            echo "<p style='color: red;'>❌ File upload error</p>";
        } else {
            $uploadedFile = $_FILES['pdfFile'];
            $pagesPerSheet = (int)$_POST['pagesPerSheet'];
            $pageArrangement = $_POST['pageArrangement'];
            $paperSize = $_POST['paperSize'];
            
            echo "<p>File: " . $uploadedFile['name'] . "</p>";
            echo "<p>Size: " . number_format($uploadedFile['size']) . " bytes</p>";
            echo "<p>Pages per sheet: " . $pagesPerSheet . "</p>";
            echo "<p>Arrangement: " . ($pageArrangement === 'side_by_side' ? 'Side by Side' : 'Top and Bottom') . "</p>";
            echo "<p>Paper size: " . $paperSize . "</p>";
            
            // Validate file type
            $fileInfo = pathinfo($uploadedFile['name']);
            if (strtolower($fileInfo['extension']) !== 'pdf') {
                echo "<p style='color: red;'>❌ File must be a PDF</p>";
            } else {
                try {
                    // Test FPDI
                    require_once 'vendor/autoload.php';
                    $pdf = new \setasign\Fpdi\Fpdi();
                    
                    // Import the uploaded PDF
                    $pageCount = $pdf->setSourceFile($uploadedFile['tmp_name']);
                    echo "<p style='color: green;'>✅ PDF loaded successfully: {$pageCount} pages</p>";
                    
                    // Create output directory if it doesn't exist
                    if (!is_dir('output')) {
                        mkdir('output', 0755, true);
                    }
                    
                    // Generate unique filename
                    $outputFilename = 'combined_' . date('Y-m-d_H-i-s') . '_' . uniqid() . '.pdf';
                    $outputPath = 'output/' . $outputFilename;
                    
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
                    }
                    
                    // Save the PDF
                    $pdf->Output('F', $outputPath);
                    
                    if (file_exists($outputPath)) {
                        echo "<p style='color: green;'>✅ PDF successfully combined!</p>";
                        echo "<p>Output file: {$outputFilename}</p>";
                        echo "<a href='download.php?file=" . urlencode($outputFilename) . "' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; margin-top: 10px;'>Download PDF</a>";
                    } else {
                        echo "<p style='color: red;'>❌ Failed to create output file</p>";
                    }
                    
                } catch (Exception $e) {
                    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
                }
            }
        }
        echo "</div>";
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
</body>
</html>
