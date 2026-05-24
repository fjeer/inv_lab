# 📋 Rencana Pengembangan Sistem InvLab

> **Terakhir diperbarui:** 27 April 2026  
> **Status saat ini:** Semua fitur Data Master & Data Transaksi dasar sudah berjalan.

---

## 🔥 Prioritas Tinggi — Fitur Inti yang Sangat Dibutuhkan

### A. Transaksi & Workflow

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 1 | **Return / Pengembalian Barang** | Alur pengembalian setelah peminjaman disetujui — catat tanggal kembali, kondisi barang saat dikembalikan, dan denda keterlambatan jika ada. | ⭐⭐ |
| 2 | **Maintenance / Perbaikan Terjadwal** | Jadwal perawatan berkala untuk alat lab (preventive maintenance) dengan reminder otomatis ketika sudah mendekati jadwal servis. | ⭐⭐⭐ |
| 3 | **Stok Opname / Stock Taking** | Fitur audit berkala untuk mencocokkan jumlah alat fisik vs data di sistem. Hasilkan laporan selisih otomatis. | ⭐⭐ |
| 4 | **Pemindahan Barang Antar Lab (Transfer)** | Catat perpindahan alat dari satu lab ke lab lain dengan approval dan riwayat transfer. | ⭐⭐ |
| 5 | **Disposal / Penghapusan Aset** | Alur formal untuk menghapus alat yang sudah tidak layak pakai (berita acara, approval berjenjang, arsip). | ⭐⭐ |

### B. Notifikasi & Reminder

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 6 | **Notifikasi In-App (Real-time)** | Bell notification di topbar untuk status peminjaman, persetujuan pengadaan, laporan kerusakan baru, dll. | ⭐⭐⭐ |
| 7 | **Email Notification** | Kirim email otomatis saat: peminjaman disetujui/ditolak, pengadaan di-approve, jadwal maintenance mendekat. | ⭐⭐ |
| 8 | **WhatsApp / Telegram Bot** | Integrasi notifikasi ke WA/Telegram untuk reminder peminjaman jatuh tempo atau alert kerusakan. | ⭐⭐⭐ |

---

## 🟡 Prioritas Sedang — Peningkatan Pengalaman & Efisiensi

### C. Dashboard & Analitik

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 9 | **Dashboard Chart Interaktif** | Grafik menggunakan Chart.js / ApexCharts: tren peminjaman per bulan, distribusi kondisi alat (pie chart), top 5 lab paling aktif. | ⭐⭐ |
| 10 | **Laporan & Export (PDF/Excel)** | Generate laporan inventaris, rekap peminjaman, dan laporan kerusakan dalam format PDF (DomPDF) dan Excel (Maatwebsite). | ⭐⭐ |
| 11 | **Statistik Utilisasi Lab** | Hitung persentase penggunaan tiap lab berdasarkan jadwal & peminjaman. Identifikasi lab yang underutilized. | ⭐⭐ |
| 12 | **Heat Map Jadwal** | Tampilan visual jadwal penggunaan lab dalam format heat map / kalender mingguan. | ⭐⭐ |

### D. Pengelolaan Alat Lanjutan

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 13 | **QR Code / Barcode pada Alat** | Generate QR code unik per alat. Scan QR untuk lihat detail, riwayat kondisi, dan pinjam langsung. | ⭐⭐ |
| 14 | **Riwayat Lengkap per Alat (Timeline)** | Timeline visual: kapan dibeli, kapan dipinjam, kapan diservis, kapan rusak — semua dalam satu halaman. | ⭐⭐ |
| 15 | **Depresiasi Aset** | Hitung nilai penyusutan alat otomatis berdasarkan metode garis lurus. Tampilkan nilai buku saat ini. | ⭐⭐ |
| 16 | **Foto Bukti Kondisi** | Upload foto saat pengecekan kondisi dan saat pengembalian barang untuk dokumentasi visual. | ⭐ |
| 17 | **Threshold Stok Minimum** | Set batas minimum jumlah alat. Jika di bawah threshold, otomatis buat draft pengadaan atau kirim alert. | ⭐⭐ |

### E. Manajemen Pengguna & Akses

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 18 | **Approval Berjenjang (Multi-level)** | Peminjaman besar atau pengadaan mahal butuh persetujuan bertingkat (Asisten → Admin → Kepala Lab). | ⭐⭐⭐ |
| 19 | **Blacklist Pengguna** | Tandai pengguna yang sering telat mengembalikan atau merusak alat. Batasi akses peminjaman. | ⭐⭐ |
| 20 | **Guest / Visitor Booking** | Peminjaman lab oleh pihak luar kampus dengan alur verifikasi dan deposit. | ⭐⭐ |

---

## 🟢 Prioritas Rendah — Inovasi & Nice-to-Have

### F. Integrasi & Teknologi

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 21 | **SSO Kampus (LDAP/OAuth)** | Login menggunakan akun kampus (Single Sign-On) untuk kemudahan akses. | ⭐⭐⭐ |
| 22 | **Mobile App / PWA** | Progressive Web App agar bisa diakses seperti aplikasi native dari smartphone. | ⭐⭐⭐ |
| 23 | **API Public untuk Integrasi** | Sediakan API terbuka untuk integrasi dengan sistem akademik kampus (SIAKAD, e-learning). | ⭐⭐ |
| 24 | **IoT Sensor Monitoring** | Sensor suhu/kelembaban ruang lab yang datanya masuk otomatis ke sistem (untuk lab kimia/biologi). | ⭐⭐⭐⭐ |

### G. Audit & Keamanan

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 25 | **Activity Log yang Terlihat di UI** | Tampilkan log aktivitas pengguna (siapa mengubah apa, kapan) di halaman admin. Sudah ada tabel `activity_logs`, tinggal buat UI-nya. | ⭐ |
| 26 | **Soft Delete & Recycle Bin** | Alat yang dihapus tidak langsung hilang, masuk "tempat sampah" dan bisa di-restore dalam 30 hari. | ⭐⭐ |
| 27 | **Two-Factor Authentication (2FA)** | Lapisan keamanan tambahan untuk akun admin menggunakan TOTP (Google Authenticator). | ⭐⭐⭐ |
| 28 | **Data Backup Otomatis** | Backup database terjadwal (daily) dengan notifikasi jika backup gagal. | ⭐⭐ |

### H. UX & Tampilan

| # | Fitur | Deskripsi | Kompleksitas |
|---|-------|-----------|:------------:|
| 29 | **Dark Mode** | Toggle mode gelap untuk kenyamanan pengguna saat bekerja di malam hari. | ⭐ |
| 30 | **Multi-bahasa (i18n)** | Dukungan Bahasa Indonesia & English untuk akses internasional. | ⭐⭐ |
| 31 | **Onboarding / Tour Guide** | Panduan interaktif untuk pengguna baru saat pertama kali login. | ⭐⭐ |
| 32 | **Bulk Import/Export CSV** | Import data alat dari Excel/CSV untuk migrasi data massal. | ⭐⭐ |

---

## 📌 Rekomendasi Urutan Pengerjaan

```
Fase 1 (Segera)
├── #25  Activity Log UI (sudah ada tabel, tinggal tampilkan)
├── #1   Return / Pengembalian Barang
├── #10  Laporan & Export PDF/Excel
└── #6   Notifikasi In-App

Fase 2 (Minggu Depan)
├── #9   Dashboard Chart Interaktif
├── #13  QR Code pada Alat
├── #4   Transfer Barang Antar Lab
└── #7   Email Notification

Fase 3 (Bulan Depan)
├── #3   Stok Opname
├── #2   Maintenance Terjadwal
├── #14  Timeline Riwayat Alat
├── #18  Approval Berjenjang
└── #17  Threshold Stok Minimum

Fase 4 (Jangka Panjang)
├── #22  PWA / Mobile
├── #21  SSO Kampus
├── #15  Depresiasi Aset
└── #27  2FA
```

---

> **Catatan:** Pilih fitur yang paling berdampak untuk pengguna terlebih dahulu.
> Tandai fitur yang sudah selesai dengan ✅ di kolom status.
