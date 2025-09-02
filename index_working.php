<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Combiner - Gabungkan Halaman PDF</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .main-content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        input[type="file"], select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
            transition: border-color 0.3s ease;
        }

        input[type="file"]:focus, select:focus {
            outline: none;
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #4caf50 0%, #45a049 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
            color: white;
        }

        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }

        .result.success {
            background: #e8f5e8;
            border: 2px solid #4caf50;
            color: #2e7d32;
        }

        .result.error {
            background: #ffebee;
            border: 2px solid #f44336;
            color: #c62828;
        }

        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 PDF Combiner</h1>
            <p>Gabungkan beberapa halaman PDF menjadi satu halaman dengan mudah</p>
        </div>
        
        <div class="main-content">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="pdfFile">Pilih File PDF:</label>
                    <input type="file" id="pdfFile" name="pdfFile" accept=".pdf" required>
                </div>

                <div class="form-group">
                    <label for="pagesPerSheet">Pilih jumlah halaman per sheet:</label>
                    <select id="pagesPerSheet" name="pagesPerSheet" required>
                        <option value="2">2 halaman per 1 halaman</option>
                        <option value="4" selected>4 halaman per 1 halaman</option>
                        <option value="5">5 halaman per 1 halaman</option>
                        <option value="6">6 halaman per 1 halaman</option>
                        <option value="8">8 halaman per 1 halaman</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="pageArrangement">Pengaturan Halaman:</label>
                    <select id="pageArrangement" name="pageArrangement" required>
                        <option value="side_by_side" selected>Side by Side (Horizontal)</option>
                        <option value="top_bottom">Top and Bottom (Vertical)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="paperSize">Ukuran Kertas:</label>
                    <select id="paperSize" name="paperSize" required>
                        <option value="A4" selected>A4 (210 × 297 mm)</option>
                        <option value="Letter">Letter (8.5 × 11 in)</option>
                        <option value="Legal">Legal (8.5 × 14 in)</option>
                        <option value="A3">A3 (297 × 420 mm)</option>
                        <option value="A5">A5 (148 × 210 mm)</option>
                        <option value="B4">B4 (250 × 353 mm)</option>
                        <option value="B5">B5 (176 × 250 mm)</option>
                    </select>
                </div>

                <button type="submit" name="submit" class="btn btn-primary">
                    📄 Combine PDF
                </button>
            </form>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
                echo "<div class='result'>";
                
                // Check if file was uploaded
                if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] !== UPLOAD_ERR_OK) {
                    echo "<div class='result error' style='display: block;'>";
                    echo "<h3>❌ Error</h3>";
                    echo "<p>File upload error. Please try again.</p>";
                    echo "</div>";
                } else {
                    $uploadedFile = $_FILES['pdfFile'];
                    $pagesPerSheet = (int)$_POST['pagesPerSheet'];
                    $pageArrangement = $_POST['pageArrangement'];
                    $paperSize = $_POST['paperSize'];
                    
                    // Show file info
                    echo "<div class='file-info'>";
                    echo "<h3>📄 File Information</h3>";
                    echo "<p><strong>Name:</strong> " . htmlspecialchars($uploadedFile['name']) . "</p>";
                    echo "<p><strong>Size:</strong> " . number_format($uploadedFile['size']) . " bytes</p>";
                    echo "<p><strong>Pages per sheet:</strong> " . $pagesPerSheet . "</p>";
                    echo "<p><strong>Arrangement:</strong> " . ($pageArrangement === 'side_by_side' ? 'Side by Side' : 'Top and Bottom') . "</p>";
                    echo "<p><strong>Paper size:</strong> " . $paperSize . "</p>";
                    echo "</div>";
                    
                    // Validate file type
                    $fileInfo = pathinfo($uploadedFile['name']);
                    if (strtolower($fileInfo['extension']) !== 'pdf') {
                        echo "<div class='result error' style='display: block;'>";
                        echo "<h3>❌ Error</h3>";
                        echo "<p>File must be a PDF</p>";
                        echo "</div>";
                    } else {
                        try {
                            // Include FPDI
                            require_once 'vendor/autoload.php';
                            $pdf = new \setasign\Fpdi\Fpdi();
                            
                            // Import the uploaded PDF
                            $pageCount = $pdf->setSourceFile($uploadedFile['tmp_name']);
                            
                            if ($pageCount === 0) {
                                throw new Exception('Invalid PDF file or empty PDF');
                            }
                            
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
                                echo "<div class='result success' style='display: block;'>";
                                echo "<h3>✅ PDF Berhasil Digabungkan!</h3>";
                                echo "<p>File berhasil dibuat: <strong>" . htmlspecialchars($outputFilename) . "</strong></p>";
                                echo "<p>Total halaman asli: <strong>{$pageCount}</strong></p>";
                                echo "<p>Layout: <strong>{$pagesPerCol}x{$pagesPerRow}</strong></p>";
                                echo "<br>";
                                echo "<a href='download.php?file=" . urlencode($outputFilename) . "' class='btn btn-success'>⬇️ Download PDF</a>";
                                echo "</div>";
                            } else {
                                echo "<div class='result error' style='display: block;'>";
                                echo "<h3>❌ Error</h3>";
                                echo "<p>Failed to create output file</p>";
                                echo "</div>";
                            }
                            
                        } catch (Exception $e) {
                            echo "<div class='result error' style='display: block;'>";
                            echo "<h3>❌ Error</h3>";
                            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
                            echo "</div>";
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
        </div>
    </div>
</body>
</html>
