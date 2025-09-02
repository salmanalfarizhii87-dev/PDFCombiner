document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('pdfFile');
    const uploadArea = document.querySelector('.upload-area');
    const filePreview = document.querySelector('.file-preview');
    const fileName = document.querySelector('.file-name');
    const combineBtn = document.getElementById('combineBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const resetBtn = document.getElementById('resetBtn');
    const loading = document.querySelector('.loading');
    const resultSection = document.querySelector('.result-section');
    const errorMessage = document.querySelector('.error-message');
    const successMessage = document.querySelector('.success-message');
    const form = document.getElementById('pdfForm');

    // Drag and drop functionality
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleFileSelect(files[0]);
        }
    });

    // Click to upload
    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });

    // Handle file selection
    function handleFileSelect(file) {
        // Validate file type
        if (file.type !== 'application/pdf') {
            showError('Silakan pilih file PDF yang valid.');
            return;
        }

        // Validate file size (max 10MB)
        if (file.size > 10 * 1024 * 1024) {
            showError('Ukuran file terlalu besar. Maksimal 10MB.');
            return;
        }

        // Show file preview
        fileName.textContent = file.name;
        filePreview.classList.add('show');
        combineBtn.disabled = false;
        
        // Hide previous messages
        hideMessages();
    }

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!fileInput.files[0]) {
            showError('Silakan pilih file PDF terlebih dahulu.');
            return;
        }

        // Show loading
        showLoading();
        hideMessages();

        // Create FormData
        const formData = new FormData();
        formData.append('pdfFile', fileInput.files[0]);
        formData.append('pagesPerSheet', document.getElementById('pagesPerSheet').value);
        formData.append('pageArrangement', document.getElementById('pageArrangement').value);
        formData.append('paperSize', document.getElementById('paperSize').value);

        // Send request
        fetch('process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                showSuccess('PDF berhasil digabungkan!');
                showResult(data.filename);
                downloadBtn.href = 'download.php?file=' + encodeURIComponent(data.filename);
                downloadBtn.style.display = 'inline-flex';
            } else {
                showError(data.message || 'Terjadi kesalahan saat memproses PDF.');
            }
        })
        .catch(error => {
            hideLoading();
            showError('Terjadi kesalahan koneksi. Silakan coba lagi.');
            console.error('Error:', error);
        });
    });

    // Reset button
    resetBtn.addEventListener('click', function() {
        resetForm();
    });

    // Download button
    downloadBtn.addEventListener('click', function() {
        // The download will be handled by the href attribute
        setTimeout(() => {
            showSuccess('File berhasil diunduh!');
        }, 1000);
    });

    // Utility functions
    function showLoading() {
        loading.style.display = 'block';
        combineBtn.disabled = true;
        combineBtn.innerHTML = '<div class="spinner"></div>Memproses...';
    }

    function hideLoading() {
        loading.style.display = 'none';
        combineBtn.disabled = false;
        combineBtn.innerHTML = '📄 Combine PDF';
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.classList.add('show');
        successMessage.classList.remove('show');
    }

    function showSuccess(message) {
        successMessage.textContent = message;
        successMessage.classList.add('show');
        errorMessage.classList.remove('show');
    }

    function hideMessages() {
        errorMessage.classList.remove('show');
        successMessage.classList.remove('show');
    }

    function showResult(filename) {
        const resultInfo = resultSection.querySelector('.result-info');
        const resultText = resultSection.querySelector('.result-text');
        
        resultText.textContent = `File berhasil dibuat: ${filename}`;
        resultSection.classList.add('show');
    }

    function resetForm() {
        // Reset form
        form.reset();
        
        // Hide file preview
        filePreview.classList.remove('show');
        
        // Hide result section
        resultSection.classList.remove('show');
        
        // Hide download button
        downloadBtn.style.display = 'none';
        
        // Hide messages
        hideMessages();
        
        // Reset button states
        combineBtn.disabled = true;
        combineBtn.innerHTML = '📄 Combine PDF';
        
        // Reset file input
        fileInput.value = '';
    }

    // Initialize
    combineBtn.disabled = true;
    downloadBtn.style.display = 'none';
});
