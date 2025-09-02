# 📄 PDF Combiner

Aplikasi web berbasis PHP untuk menggabungkan beberapa halaman PDF menjadi satu halaman PDF dengan layout yang dapat dikustomisasi.

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![GitHub Stars](https://img.shields.io/github/stars/yourusername/PDFCombiner.svg)](https://github.com/yourusername/PDFCombiner/stargazers)

## ✨ Fitur Utama

- 📁 **Upload Mudah** - Drag & drop atau klik untuk upload file PDF
- ⚙️ **Layout Fleksibel** - 2, 4, 5, 6, atau 8 halaman per sheet
- 📏 **Ukuran Kertas** - A4, Letter, Legal, A3, A5, B4, B5
- 🎨 **Pengaturan Halaman** - Side by Side (Horizontal) atau Top and Bottom (Vertical)
- 🔒 **Validasi File** - Hanya PDF, maksimal 10MB
- ⚡ **Proses Cepat** - Menggunakan library FPDI yang powerful
- 🎯 **UI Modern** - Interface responsif dengan animasi smooth
- 📱 **Mobile Friendly** - Bekerja sempurna di desktop dan mobile

## Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- Composer
- Web server (Apache/Nginx)
- Ekstensi PHP: GD, mbstring

## 🚀 Demo

### Versi yang Tersedia:
- **[Simple Test](simple_test.php)** - Versi sederhana untuk testing (Recommended)
- **[Modern UI](index_working.php)** - UI modern tanpa JavaScript
- **[Full Featured](index.php)** - Versi lengkap dengan drag & drop
- **[Paper Sizes Demo](paper_sizes_demo.html)** - Demo visual ukuran kertas

### Screenshots:
![PDF Combiner Interface](screenshots/interface.png)
*Interface utama dengan opsi pengaturan*

![Paper Sizes](screenshots/paper-sizes.png)
*Demo ukuran kertas yang tersedia*

## 📦 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/PDFCombiner.git
cd PDFCombiner
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Set Permissions
```bash
chmod 755 output/
```

### 4. Akses Aplikasi
Buka browser dan akses: `http://localhost/PDFCombiner/`

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
