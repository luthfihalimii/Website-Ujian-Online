# Ujian Online

Platform ujian berbasis web untuk mengelola ujian berbasis komputer dengan fitur pemantauan kecurangan, dibangun menggunakan Laravel 12, Inertia.js, dan Vue 3. Dokumentasi ini merangkum alur kerja, kebutuhan lingkungan, serta cara menjalankan proyek secara lokal maupun produksi.

## Daftar Isi
- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Kebutuhan Sistem](#kebutuhan-sistem)
- [Instalasi Cepat](#instalasi-cepat)
- [Konfigurasi Lingkungan](#konfigurasi-lingkungan)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Akun Bawaan](#akun-bawaan)
- [Alur Penggunaan](#alur-penggunaan)
- [Fitur Anti Cheat](#fitur-anti-cheat)
- [Testing](#testing)
- [Tips Deployment](#tips-deployment)
- [Lisensi](#lisensi)

## Fitur Utama
- **Manajemen konten ujian**: CRUD mata pelajaran, kelas, bank soal, sesi ujian, serta import soal dan peserta melalui Excel.
- **Pengawasan realtime**: Monitoring sesi ujian, status pengerjaan siswa, serta rekap nilai dan export hasil ke Excel.
- **Portal siswa**: SSO siswa dengan guard tersendiri, countdown durasi ujian, autosave jawaban, navigasi soal, dan rekap nilai akhir.
- **Anti cheat terintegrasi**: Deteksi fokus layar, penggunaan shortcut mencurigakan, devtools, dan pelanggaran lain yang tercatat ke tabel `cheat_events`.
- **Penanganan pelanggaran**: Peringatan otomatis hingga 3 kali, penguncian akun siswa, dan panel admin untuk membuka kunci akun.

## Teknologi
- PHP 8.2+, Laravel 12, Laravel Octane, Fortify untuk autentikasi admin.
- Inertia.js, Vue 3, Tailwind CSS 4, Axios, dan Vite untuk frontend.
- Database relasional (MySQL, PostgreSQL, atau SQLite) dengan migrasi Laravel.
- Maatwebsite Excel untuk import/export data.

## Kebutuhan Sistem
- PHP 8.2 atau lebih baru beserta ekstensi standar Laravel (`fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, dll).
- Composer 2.6+.
- Node.js 18+ dan npm, atau Bun sebagai alternatif.
- Server database (MySQL/MariaDB, PostgreSQL, atau SQLite untuk pengembangan cepat).
- Redis (opsional) bila ingin memanfaatkan queue/broadcast selain driver default.

## Instalasi Cepat
1. Clone repository dan masuk ke folder proyek.
2. Instal dependensi backend: `composer install`.
3. Instal dependensi frontend: `npm install` (atau `bun install`).
4. Salin `.env.example` menjadi `.env` dan sesuaikan variabel lingkungan.
5. Generate application key: `php artisan key:generate`.
6. Atur kredensial database pada `.env` dan buat database-nya.
7. Jalankan migrasi dan seeding dasar: `php artisan migrate --seed`.
8. Link storage bila diperlukan untuk file upload: `php artisan storage:link`.

## Konfigurasi Lingkungan
- **APP_URL**: arahkan ke domain atau base URL aplikasi (misal `http://localhost:8000`).
- **Database**: gunakan `DB_CONNECTION=mysql` (atau driver lain) dan lengkapi host, port, nama basis data, user, serta password.
- **Queue & Cache**: konfigurasi default menggunakan database driver dan aman untuk produksi kecil. Sesuaikan ke Redis bila volume tinggi.
- **Mail**: ubah `MAIL_MAILER` ke driver pilihan (SMTP, Mailgun, dll) untuk notifikasi email.
- **Guard siswa**: sesi siswa disimpan di driver database (`SESSION_DRIVER=database`). Jalankan `php artisan session:table` bila driver diubah.

## Menjalankan Aplikasi
- Jalankan web server lokal: `php artisan serve` (atau `php artisan octane:start` untuk performa lebih tinggi).
- Jalankan asset bundler: `npm run dev` untuk hot reload atau `npm run build` untuk produksi.
- Opsi all-in-one: `composer dev` akan menjalankan server Laravel, queue listener, log streaming, dan Vite secara bersamaan.

## Akun Bawaan
Seeder menyediakan akun admin awal:
- Email: `admin@gmail.com` & Kata sandi: `password`
- Email: `superadmin@example.com` & Kata sandi: `supersecret`

Tambahkan data siswa melalui menu Admin `Siswa` atau impor dengan template Excel.

## Alur Penggunaan
1. **Admin** membuat mata pelajaran, kelas, bank soal, dan menyusun ujian melalui panel admin (`/admin`).
2. Admin menjadwalkan sesi ujian, menambahkan kelompok peserta, dan memantau progres melalui dashboard.
3. Siswa login via halaman utama, melakukan konfirmasi ujian, mengerjakan soal, dan mengirim jawaban.
4. Sistem menyimpan jawaban secara otomatis, menghitung nilai, dan menampilkan rekap hasil kepada siswa serta admin.

## Fitur Anti Cheat
- Deteksi kejadian: pergantian tab/jendela, keluar fullscreen, membuka developer tools, klik kanan, shortcut copy/paste/cut/select all.
- Penguncian IP & perangkat: login baru dari alamat IP atau user agent berbeda langsung memutus sesi lama dan mencatat pelanggaran.
- Deteksi screenshot / screen recording: kombinasi listener shortcut, patch `getDisplayMedia`, dan fingerprint canvas untuk mendeteksi perubahan layar mencurigakan.
- Pelacakan waktu per soal dan flag jawaban yang terlalu cepat setelah periode idle panjang.
- Setiap kejadian dicatat dengan timestamp dan alamat IP ke tabel `cheat_events` dan muncul pada menu Admin `Monitoring Kecurangan`.
- Setelah 3 pelanggaran, akun siswa terkunci dan ujian otomatis berakhir dengan skor 0.
- Sinkronisasi waktu ujian menggunakan waktu server; manipulasi waktu lokal akan terdeteksi sebagai kecurangan.
- Admin dapat membuka kembali akun siswa melalui tombol `Buka Kunci` pada panel monitoring.

## Testing
- Jalankan test suite backend: `composer test`.
- Gunakan `php artisan test --filter` untuk menjalankan test tertentu.
- Tambahkan test baru pada direktori `tests/` guna menjaga stabilitas fitur inti.

## Tips Deployment
- Bangun asset produksi: `npm run build`.
- Jalankan migrasi dan seeding yang diperlukan di server: `php artisan migrate --force`.
- Cache konfigurasi dan route: `php artisan config:cache` dan `php artisan route:cache`.
- Konfigurasikan supervisor/PM2 untuk queue listener dan proses Octane bila digunakan.

## Lisensi
Proyek ini berada di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
