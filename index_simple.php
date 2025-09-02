<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Combiner - Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 600px; margin: 0 auto; }
        .form-group { margin: 20px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; margin-bottom: 10px; }
        button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #005a87; }
        .result { margin-top: 20px; padding: 10px; background: #f0f0f0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📄 PDF Combiner - Test Version</h1>
        <p>Test upload dan proses PDF</p>
        
        <form id="pdfForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="pdfFile">Pilih File PDF:</label>
                <input type="file" id="pdfFile" name="pdfFile" accept=".pdf" required>
            </div>
            
            <div class="form-group">
                <label for="pagesPerSheet">Halaman per Sheet:</label>
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
            
            <button type="submit">Combine PDF</button>
        </form>
        
        <div id="result" class="result" style="display: none;"></div>
    </div>

    <script>
        document.getElementById('pdfForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            
            resultDiv.style.display = 'block';
            resultDiv.innerHTML = 'Memproses...';
            
            fetch('process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <h3>✅ Berhasil!</h3>
                        <p>File: ${data.filename}</p>
                        <a href="download.php?file=${encodeURIComponent(data.filename)}" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; display: inline-block;">Download PDF</a>
                    `;
                } else {
                    resultDiv.innerHTML = `<h3>❌ Error:</h3><p>${data.message}</p>`;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `<h3>❌ Error:</h3><p>Terjadi kesalahan: ${error.message}</p>`;
            });
        });
    </script>
</body>
</html>
