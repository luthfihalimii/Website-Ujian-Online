# Website Ujian Online

Project ini adalah sebuah aplikasi website untuk ujian online (computer based test) yang dapat membantu guru atau manajer dalam menilai pengetahuan dari setiap siswa dan karyawan mereka. Aplikasi ini dibangun menggunakan framework PHP populer yaitu Laravel.

## Fitur

- **Manajemen Ujian**: Membuat, mengedit, dan menghapus ujian dengan mudah.
- **Manajemen Peserta**: Menambahkan peserta ujian dan mengelola data peserta.
- **Penilaian Otomatis**: Sistem otomatis untuk menilai jawaban peserta ujian.
- **Laporan dan Statistik**: Menyediakan laporan dan statistik hasil ujian.
- **Keamanan**: Autentikasi dan otorisasi untuk memastikan keamanan data.
- **Anti Menyontek**: Fitur unggulan yang mendeteksi dan mencegah kecurangan selama ujian berlangsung.

## Teknologi yang Digunakan

- **Laravel**: Framework PHP yang digunakan untuk membangun backend aplikasi.
- **Vue.js**: Framework JavaScript yang digunakan untuk membangun frontend aplikasi.
- **Blade**: Template engine yang digunakan oleh Laravel.
- **CSS**: Digunakan untuk styling halaman web.
- **JavaScript**: Digunakan untuk interaksi dinamis pada halaman web.

## Komposisi Bahasa Pemrograman

- **CSS**: 56%
- **Vue**: 20.4%
- **PHP**: 15%
- **JavaScript**: 4.8%
- **Blade**: 3.8%

## Instalasi

1. Clone repository ini:
    ```bash
    git clone https://github.com/luthfihalimii/Website-Ujian-Online.git
    cd Website-Ujian-Online
    ```

2. Install dependencies menggunakan Composer:
    ```bash
    composer install
    ```

3. Install dependencies frontend menggunakan NPM:
    ```bash
    npm install
    ```

4. Copy file `.env.example` menjadi `.env` dan sesuaikan konfigurasi yang diperlukan:
    ```bash
    cp .env.example .env
    ```

5. Generate application key:
    ```bash
    php artisan key:generate
    ```

6. Migrasi database:
    ```bash
    php artisan migrate
    ```

7. Jalankan server lokal:
    ```bash
    php artisan serve
    ```

Aplikasi akan berjalan di `http://localhost:8000`.

## Kontribusi

Jika Anda ingin berkontribusi pada proyek ini, silakan fork repository ini, buat branch baru untuk fitur atau perbaikan Anda, dan buat pull request. Kami sangat menghargai kontribusi Anda!

## Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

---

Dibuat dengan ❤️ oleh [luthfihalimii](https://github.com/luthfihalimii)
