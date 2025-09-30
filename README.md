# 📘 Technical Test – E-commerce API (Laravel + Midtrans)

## 📌 Deskripsi
Proyek ini adalah implementasi **E-commerce API sederhana** menggunakan **Laravel (fresh install)** dengan integrasi **Payment Gateway Midtrans**.  
API ini mendukung autentikasi, manajemen produk (buku), checkout, pembayaran, dan riwayat pemesanan.  
Karena keterbatasan resource (tidak menggunakan hosting berbayar), webhook Midtrans diuji menggunakan ngrok untuk menerima callback notifikasi pembayaran.

---

## ⚙️ Tech Stack
- **Laravel 12** (Backend Framework)  
- **MySQL** (Database)  
- **Midtrans** (Payment Gateway)  
- **ngrok** (Webhook Testing)  

---

## 🚀 Fitur Utama
1. **Autentikasi**
   - Register API  
   - Login API  
   - Protected routes dengan `Authorization` header (Access Key / Secret Key).  

2. **Produk (Buku)**
   - List Buku  
   - Detail Buku  

3. **Checkout**
   - Membuat pesanan baru.  

4. **Payment**
   - Integrasi dengan Midtrans (Snap).  
   - Mendapatkan redirect URL untuk pembayaran.  

5. **Webhook Midtrans**
   - Update status pesanan secara otomatis ketika pembayaran sukses / gagal.  

6. **Riwayat Pemesanan**
   - User dapat melihat riwayat transaksi.  

---

## 🛠️ Setup & Instalasi

1. **Clone repository & install dependencies**
   ```bash
   git clone <repo-url>
   cd project
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. **Konfigurasi Database**
   Edit file `.env`:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bookstore_api
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Jalankan migrasi:
   ```bash
   php artisan migrate --seed
   ```

3. **Konfigurasi Midtrans**
   Tambahkan ke `.env`:
   ```env
   MIDTRANS_SERVER_KEY=your_server_key
   MIDTRANS_CLIENT_KEY=your_client_key
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_MERCHANT_ID=your_merchant_id
   ```

4. **Jalankan Server**
   ```bash
   php artisan serve
   ```

5. **Jalankan ngrok (untuk webhook)**
   ```bash
   ngrok http 8000
   ```
   Gunakan URL ngrok untuk setting **Notification URL** di Midtrans Dashboard.  
   Contoh:
   ```
   https://random.ngrok.io/api/midtrans/callback
   ```

---

## 🔑 API Authentication
Setiap request ke endpoint **protected** harus mengirimkan header berikut:

```http
Authorization: Bearer <access_token>
x-api-key: <your-secret-key>
```

---

## 📑 API Docs
Dokumentasi lengkap API tersedia dalam **Postman Collection** yang sudah disediakan di repo:  
`bookstore-postman-collection.json`  

Import ke Postman untuk langsung mencoba semua endpoint.