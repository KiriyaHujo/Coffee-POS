☕ Coffee POS (Point of Sale System)

Sistem Kasir (Point of Sale) berbasis web yang dirancang khusus untuk manajemen transaksi dan katalog produk di kedai kopi. Dibuat menggunakan framework Laravel.

🚀 Fitur Utama

Manajemen Produk (CRUD): Tambah, ubah, hapus, dan lihat daftar menu minuman & makanan.

Sistem Kasir / Transaksi (POS): Antarmuka interaktif untuk memasukkan pesanan ke dalam keranjang dan memproses transaksi secara lansung.

Pencatatan Pesanan: Menyimpan header transaksi beserta detail item yang dibeli.

Pengujian Otomatis (Automated Testing): Dilengkapi dengan pengujian Feature Test untuk memastikan fitur POS dan Manajemen Produk berjalan dengan baik.

🛠️ Prasyarat (Prerequisites)

Sebelum memulai instalasi, pastikan sistem Anda telah terpasang:

PHP (v8.2 atau lebih baru)

Composer (v2.x atau lebih baru)

Node.js & NPM

Database Server: MySQL / MariaDB / PostgreSQL / SQLite

📦 Langkah Instalasi

Ikuti langkah-langkah di bawah ini untuk menjalankan proyek secara lokal:

1. Clone Repositori

git clone https://github.com/kiriyahujo/coffee-pos.git
cd coffee-pos


2. Install Dependensi PHP & JavaScript

# Install package PHP via Composer
composer install

# Install package JavaScript via NPM
npm install


3. Konfigurasi Environment (.env)

Salin file .env.example menjadi .env:

cp .env.example .env


Buka file .env dan atur konfigurasi database sesuai environment lokal Anda:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coffee_pos
DB_USERNAME=root
DB_PASSWORD=


(Catatan: Buat database baru bernama coffee_pos di DBMS Anda terlebih dahulu)

4. Generate Application Key

php artisan key:generate


5. Jalankan Migration & Seeder Database

Jalankan migrasi tabel beserta data awal (dummy data) untuk produk:

php artisan migrate --seed


6. Build Assets & Jalankan Application Server

Jalankan perintah berikut pada terminal terpisah:

Terminal 1 (Vite Asset Bundler):

npm run dev


Terminal 2 (Laravel Development Server):

php artisan serve


Aplikasi siap diakses melalui browser di: http://127.0.0.1:8000
