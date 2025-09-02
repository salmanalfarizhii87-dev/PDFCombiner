<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Combiner - Gabungkan Halaman PDF</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📄 PDF Combiner</h1>
            <p>Gabungkan beberapa halaman PDF menjadi satu halaman dengan mudah</p>
        </div>
        
        <div class="main-content">
            <form id="pdfForm" enctype="multipart/form-data">
                <!-- Upload Section -->
                <div class="upload-section">
                    <div class="upload-area">
                        <div class="upload-icon">📁</div>
                        <div class="upload-text">Klik atau drag & drop file PDF di sini</div>
                        <div class="upload-subtext">Maksimal 10MB • Format PDF saja</div>
                        <input type="file" id="pdfFile" name="pdfFile" class="file-input" accept=".pdf" required>
                    </div>
                    
                    <div class="file-preview">
                        <div class="file-info">
                            <div class="file-icon">📄</div>
                            <div class="file-name"></div>
                        </div>
                    </div>
                </div>

                <!-- Options Section -->
                <div class="options-section">
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
                </div>

                <!-- Buttons Section -->
                <div class="buttons-section">
                    <button type="submit" id="combineBtn" class="btn btn-primary" disabled>
                        📄 Combine PDF
                    </button>
                    
                    <a href="#" id="downloadBtn" class="btn btn-success" style="display: none;">
                        ⬇️ Download PDF
                    </a>
                    
                    <button type="button" id="resetBtn" class="btn btn-danger">
                        🔄 Reset
                    </button>
                </div>
            </form>

            <!-- Loading Section -->
            <div class="loading">
                <div class="spinner"></div>
                <div class="loading-text">Sedang memproses PDF...</div>
            </div>

            <!-- Result Section -->
            <div class="result-section">
                <div class="result-info">
                    <div class="result-icon">✅</div>
                    <div class="result-text"></div>
                </div>
            </div>

            <!-- Messages -->
            <div class="error-message"></div>
            <div class="success-message"></div>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
