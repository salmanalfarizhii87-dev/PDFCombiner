# PDF Combiner

Aplikasi web berbasis PHP untuk menggabungkan beberapa halaman PDF menjadi satu halaman PDF dengan layout yang dapat dikustomisasi.

## Fitur

- Upload file PDF dengan drag & drop atau klik
- Pilihan layout: 2, 4, 5, 6, atau 8 halaman per sheet
- Validasi file PDF (maksimal 10MB)
- Preview nama file yang diupload
- Loading spinner saat proses
- Download hasil PDF
- Reset form untuk upload ulang
- UI modern dan responsif

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- Composer
- Web server (Apache/Nginx)
- Ekstensi PHP: GD, mbstring

## Instalasi

1. Clone atau download project ini
2. Install dependencies dengan Composer:
   ```bash
   composer install
   ```
3. Pastikan folder `output/` memiliki permission write (755)
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

- **PHP**: Backend processing
- **FPDI**: Library untuk manipulasi PDF
- **HTML5**: Struktur halaman
- **CSS3**: Styling modern dengan gradient dan animasi
- **JavaScript**: Interaksi dan validasi client-side
- **Composer**: Dependency management

## Konfigurasi

### Ukuran File Maksimal
Default: 10MB. Untuk mengubah, edit di `process.php`:
```php
if ($uploadedFile['size'] > 10 * 1024 * 1024) {
    // Ubah nilai 10 sesuai kebutuhan
}
```

### Layout Halaman
Untuk menambah opsi layout baru, edit di `index.php`:
```html
<option value="9">9 halaman per 1 halaman</option>
```

Dan tambahkan case di `process.php`:
```php
case 9:
    return ['rows' => 3, 'cols' => 3];
```

## Troubleshooting

### Error: "Class 'setasign\Fpdi\Fpdi' not found"
- Pastikan sudah menjalankan `composer install`
- Pastikan file `vendor/autoload.php` ada

### Error: "Permission denied" pada folder output
- Set permission folder output: `chmod 755 output/`
- Pastikan web server memiliki akses write ke folder

### File tidak bisa diupload
- Periksa setting `upload_max_filesize` dan `post_max_size` di php.ini
- Pastikan folder upload memiliki permission yang tepat

## Lisensi

Project ini dibuat untuk keperluan pembelajaran dan dapat digunakan secara bebas.

## Kontribusi

Silakan buat issue atau pull request jika ada bug atau fitur yang ingin ditambahkan.
