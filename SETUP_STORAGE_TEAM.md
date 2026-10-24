# Panduan Sinkronisasi Tim: Cloudflare R2 & Media Storage

Dokumen ini menjelaskan arsitektur penyimpanan gambar Bugar.in setelah integrasi dengan **Cloudflare R2 (S3-compatible)** dan langkah-langkah yang wajib dijalankan oleh seluruh anggota tim setelah melakukan `git pull origin main`.

---

## 1. Ringkasan Perubahan Arsitektur

Sebelumnya, file gambar (banner event, cover komunitas, avatar user) disimpan secara lokal di laptop masing-masing pengembang (`storage/app/public/`). Hal ini menyebabkan **masalah inkonsistensi**: ketika satu orang membuat event dari laptopnya, file gambar fisik hanya ada di laptop pembuat dan tidak muncul (404) di laptop anggota tim lainnya meskipun data teksnya ada di Neon PostgreSQL.

### Solusi Sekarang:
1. **Centralized Cloud Storage (Cloudflare R2)**:
   - Bucket publik: `bugarin-public` (gratis egress fee, kompatibel dengan Amazon S3 API).
   - Seluruh foto cover komunitas, cover event, logo, dan avatar disimpan terpusat di cloud.
2. **High-Speed Local Disk Caching (`/media/{path}`)**:
   - Route `/media/{path}` di `routes/web.php` secara otomatis mengunduh gambar dari Cloudflare R2 pada permintaan pertama dan menyimpannya di folder `public/media/` di laptop pengembang.
   - Request selanjutnya dilayani dari disk lokal dalam waktu `< 2 ms` (bebas antre/lag).

---

## 2. Langkah-Langkah untuk Anggota Tim (Wajib Dijalankan)

Setelah melakukan `git pull origin main`, setiap anggota tim wajib menjalankan 4 langkah berikut:

### Langkah 1: Tarik Perubahan Terbaru
```bash
git pull origin main
```

### Langkah 2: Install Dependensi S3 Driver
Package `league/flysystem-aws-s3-v3` telah ditambahkan ke `composer.json` agar Laravel dapat terhubung ke Cloudflare R2. Jalankan:
```bash
composer install
```
*(Tanpa menjalankan ini, aplikasi akan error `Class "League\Flysystem\AwsS3V3\..." not found`)*

### Langkah 3: Perbarui File `.env` Masing-Masing
Karena file `.env` diabaikan oleh Git untuk keamanan, setiap anggota tim harus menambahkan konfigurasi berikut ke file `.env` di komputernya:

```env
FILESYSTEM_DISK=s3

# Cloudflare R2 Storage (Kredensial dibagikan via grup WhatsApp / Private Message)
AWS_ACCESS_KEY_ID=<masukkan_access_key_dari_whatsapp>
AWS_SECRET_ACCESS_KEY=<masukkan_secret_access_key_dari_whatsapp>
AWS_DEFAULT_REGION=auto
AWS_BUCKET=bugarin-public
AWS_PRIVATE_BUCKET=bugarin-private
AWS_ENDPOINT=https://<account_id_dari_whatsapp>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_URL="${APP_URL}/media"
```

### Langkah 4: Bersihkan Cache Konfigurasi & Jalankan Aplikasi
```bash
php artisan optimize:clear
php artisan serve
```

---

## 3. Pertanyaan Umum: Mengapa Foto Event Ada di Laptop Teman Tapi Tidak Ada di Laptop Lain?

### Kenapa ini terjadi sebelumnya?
Database Neon PostgreSQL bersifat terpusat (dipakai bersama), tetapi saat teman membuat event, file fotonya tersimpan di folder lokal `storage/app/public/` laptop teman tersebut.

### Apa yang terjadi sekarang?
- Seluruh gambar komunitas dan event yang tercatat di database saat ini **sudah disinkronkan ke Cloudflare R2**.
- Anggota tim yang baru pull **tidak perlu copy-paste file gambar manual**. Gambar otomatis terunduh dari Cloudflare R2 ke laptop mereka saat membuka halaman web.

### Jika teman memiliki foto asli event di laptopnya:
1. Login sebagai akun **Penyedia Komunitas** pemilik event tersebut.
2. Buka menu **Kelola Event** &rarr; klik tombol **Edit** pada event bersangkutan.
3. Unggah ulang file foto asli yang ada di laptop.
4. Karena driver sudah menggunakan `s3`, foto tersebut akan langsung terunggah ke Cloudflare R2 dan otomatis tampil di seluruh laptop anggota tim.

---

## 4. Kebijakan Seeder & Data Dummy
- Komunitas dummy seeder (ID 7–16) telah dihapus agar katalog komunitas realistis.
- Seeder `CommunityAndScheduleSeeder` telah dinonaktifkan di `DatabaseSeeder.php`. **Jangan menjalankan `php artisan migrate:fresh --seed`** tanpa konfirmasi tim agar data event dan komunitas asli di Neon PostgreSQL tidak terhapus.
