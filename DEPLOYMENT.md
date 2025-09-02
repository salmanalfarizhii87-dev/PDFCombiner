# Panduan Deployment ke Vercel

## Persiapan Sebelum Deploy

### 1. Pastikan Dependencies Terinstall
```bash
composer install
```

### 2. Test Aplikasi Lokal (Opsional)
```bash
# Install Vercel CLI
npm install -g vercel

# Test lokal
vercel dev
```

## Langkah-langkah Deployment

### Opsi 1: Deploy via Vercel Dashboard (Recommended)

1. **Push ke Git Repository**
   ```bash
   git add .
   git commit -m "Prepare for Vercel deployment"
   git push origin main
   ```

2. **Deploy via Dashboard**
   - Buka [vercel.com](https://vercel.com)
   - Login dengan akun GitHub/GitLab/Bitbucket
   - Klik "New Project"
   - Import repository Anda
   - Vercel akan otomatis detect konfigurasi PHP dari `vercel.json`

3. **Konfigurasi Environment Variables** (jika diperlukan)
   - Buka project di Vercel Dashboard
   - Go to Settings > Environment Variables
   - Tambahkan:
     - `UPLOAD_MAX_SIZE`: `10485760` (10MB)

### Opsi 2: Deploy via Vercel CLI

1. **Install Vercel CLI**
   ```bash
   npm install -g vercel
   ```

2. **Login ke Vercel**
   ```bash
   vercel login
   ```

3. **Deploy**
   ```bash
   # Deploy ke preview
   vercel
   
   # Deploy ke production
   vercel --prod
   ```

## Struktur File untuk Vercel

```
PDFCombiner/
├── api/
│   ├── combine.php      # API endpoint untuk combine PDF
│   └── download.php     # API endpoint untuk download
├── public/
│   └── index.html       # Frontend aplikasi
├── vendor/              # PHP dependencies (dari composer install)
├── vercel.json          # Konfigurasi Vercel
├── package.json         # Node.js dependencies
├── composer.json        # PHP dependencies
├── .vercelignore        # File yang diabaikan saat deploy
├── .gitignore           # File yang diabaikan di Git
└── .env.example         # Template environment variables
```

## Konfigurasi Vercel

### vercel.json
File ini mengatur:
- Build configuration untuk PHP functions
- Routing untuk API endpoints
- Static file serving
- Function timeout dan memory settings (dalam `config` object)
- Environment variables

**Catatan**: Vercel tidak mengizinkan penggunaan `functions` dan `builds` secara bersamaan. Konfigurasi timeout dan memory harus diletakkan dalam `config` object di dalam `builds`.

### Environment Variables
- `UPLOAD_MAX_SIZE`: Maksimal ukuran file upload (default: 10MB)

## Troubleshooting

### Warning: "The `functions` property cannot be used in conjunction with the `builds` property"
- **Solusi**: Hapus property `functions` dari `vercel.json`
- Konfigurasi timeout dan memory harus diletakkan dalam `config` object di dalam `builds`
- Contoh yang benar:
  ```json
  {
    "builds": [
      {
        "src": "api/combine.php",
        "use": "@vercel/php",
        "config": {
          "maxDuration": 30,
          "memory": 1024
        }
      }
    ]
  }
  ```

### Error: "Function timeout"
- Pastikan `maxDuration` di `config` object cukup (default: 30 detik)
- Optimasi kode PHP untuk performa yang lebih baik

### Error: "File not found"
- Pastikan path file di API endpoints benar
- Gunakan `/tmp/` untuk temporary files di Vercel

### Error: "Permission denied"
- Vercel menggunakan `/tmp/` untuk temporary files
- Pastikan menggunakan path yang benar

### Error: "Class not found"
- Pastikan `composer install` sudah dijalankan
- Pastikan `vendor/` folder ter-commit ke repository

## Monitoring dan Logs

1. **Vercel Dashboard**
   - Buka project di Vercel Dashboard
   - Go to Functions tab untuk melihat logs
   - Monitor performance dan errors

2. **Vercel CLI**
   ```bash
   vercel logs [deployment-url]
   ```

## Update Deployment

Untuk update aplikasi:
```bash
git add .
git commit -m "Update application"
git push origin main
```

Vercel akan otomatis deploy ulang jika menggunakan GitHub integration.

## Custom Domain (Opsional)

1. Buka project di Vercel Dashboard
2. Go to Settings > Domains
3. Add custom domain
4. Follow instruksi untuk setup DNS

## Performance Tips

1. **Optimasi File Size**
   - Compress PDF sebelum upload
   - Limit ukuran file upload

2. **Caching**
   - Vercel otomatis cache static files
   - API responses bisa di-cache jika diperlukan

3. **Monitoring**
   - Monitor function execution time
   - Optimasi kode jika diperlukan
