# PDF Combiner

Aplikasi web berbasis Node.js untuk menggabungkan beberapa halaman PDF menjadi satu halaman PDF dengan layout yang dapat dikustomisasi. Aplikasi ini dapat di-deploy ke Vercel dengan mudah.

## Fitur

- Upload file PDF dengan drag & drop atau klik
- Pilihan layout: 2, 4, 5, 6, atau 8 halaman per sheet
- Validasi file PDF (maksimal 10MB)
- Preview nama file yang diupload
- Loading spinner saat proses
- Download hasil PDF
- Reset form untuk upload ulang
- UI modern dan responsif
- Deploy ke Vercel dengan mudah

## Deployment ke Vercel

### Persyaratan
- Akun Vercel (gratis)
- Git repository (GitHub, GitLab, atau Bitbucket)
- Node.js (untuk Vercel CLI)

### Langkah-langkah Deployment

1. **Install Vercel CLI** (opsional):
   ```bash
   npm install -g vercel
   ```

2. **Push kode ke Git repository**:
   ```bash
   git add .
   git commit -m "Prepare for Vercel deployment"
   git push origin main
   ```

3. **Deploy ke Vercel**:
   
   **Opsi A: Via Vercel Dashboard**
   - Buka [vercel.com](https://vercel.com)
   - Login dengan akun GitHub/GitLab/Bitbucket
   - Klik "New Project"
   - Import repository Anda
   - Vercel akan otomatis detect konfigurasi PHP

   **Opsi B: Via Vercel CLI**
   ```bash
   vercel login
   vercel --prod
   ```

4. **Konfigurasi Environment Variables** (jika diperlukan):
   - Buka project di Vercel Dashboard
   - Go to Settings > Environment Variables
   - Tambahkan variabel yang diperlukan

### Struktur untuk Vercel

```
PDFCombiner/
├── api/
│   ├── combine.js       # API endpoint untuk combine PDF
│   └── download.js      # API endpoint untuk download
├── public/
│   └── index.html       # Frontend aplikasi
├── node_modules/        # Node.js dependencies (dari npm install)
├── vercel.json          # Konfigurasi Vercel
├── package.json         # Node.js dependencies
└── .env.example         # Environment variables template
```

## Instalasi Lokal

1. Clone atau download project ini
2. Install dependencies dengan npm:
   ```bash
   npm install
   ```
3. Jalankan development server:
   ```bash
   npm run dev
   ```
4. Akses aplikasi melalui web browser

## Struktur Project

```
PDFCombiner/
├── index.php          # Halaman utama
├── process.php        # Proses penggabungan PDF
├── download.php       # Handler download file
├── composer.json      # Dependencies
├── assets/
│   ├── style.css      # Styling CSS
│   └── script.js      # JavaScript functionality
├── output/            # Folder hasil PDF
└── vendor/            # Dependencies (setelah composer install)
```

## Cara Penggunaan

1. Buka aplikasi di web browser
2. Upload file PDF dengan drag & drop atau klik area upload
3. Pilih jumlah halaman per sheet (2, 4, 5, 6, atau 8)
4. Klik tombol "Combine PDF"
5. Tunggu proses selesai
6. Download hasil PDF
7. Klik "Reset" untuk upload file baru

## Teknologi yang Digunakan

- **Node.js**: Backend processing
- **pdf-lib**: Library untuk manipulasi PDF
- **multer**: File upload handling
- **HTML5**: Struktur halaman
- **CSS3**: Styling modern dengan gradient dan animasi
- **JavaScript**: Interaksi dan validasi client-side
- **npm**: Dependency management

## Konfigurasi

### Ukuran File Maksimal
Default: 10MB. Untuk mengubah, edit di `api/combine.js`:
```javascript
limits: {
  fileSize: 10 * 1024 * 1024, // Ubah nilai 10 sesuai kebutuhan
}
```

### Layout Halaman
Untuk menambah opsi layout baru, edit di `public/index.html`:
```html
<option value="9">9 halaman per 1 halaman</option>
```

Dan tambahkan case di `api/combine.js`:
```javascript
case 9:
  return { rows: 3, cols: 3 };
```

## Troubleshooting

### Error: "Cannot find module 'pdf-lib'"
- Pastikan sudah menjalankan `npm install`
- Pastikan file `node_modules/` ada

### Error: "File upload failed"
- Periksa ukuran file (maksimal 10MB)
- Pastikan file adalah PDF yang valid

### Error: "PDF processing failed"
- Pastikan PDF tidak corrupt
- Coba dengan PDF yang lebih kecil

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan dapat digunakan secara bebas.

## Kontribusi

Silakan buat issue atau pull request jika ada bug atau fitur yang ingin ditambahkan.
