# InvLab — Sistem Informasi Inventaris Laboratorium

## 📋 Daftar Isi

1. [Arsitektur Sistem](#1-arsitektur-sistem)
2. [Struktur Database & Relasi](#2-struktur-database--relasi)
3. [Role & Hak Akses](#3-role--hak-akses)
4. [Flowchart Aplikasi](#4-flowchart-aplikasi)
5. [Alur Bisnis Transaksi](#5-alur-bisnis-transaksi)
   - [5.1 Peminjaman Lab](#51-peminjaman-lab)
   - [5.2 Patroli & Monitoring Kondisi Alat](#52-patroli--monitoring-kondisi-alat)
   - [5.3 Laporan Kerusakan Barang](#53-laporan-kerusakan-barang)
   - [5.4 Pengadaan Barang](#54-pengadaan-barang)
   - [5.5 Penambahan Alat Baru](#55-penambahan-alat-baru)
   - [5.6 Penggantian Alat Lama ke Baru](#56-penggantian-alat-lama-ke-baru)
   - [5.7 Scan QR Code End-to-End](#57-scan-qr-code-end-to-end)
   - [5.8 Diagram Status Lifecycle](#58-diagram-status-lifecycle)
6. [Daftar Bug & Isu](#6-daftar-bug--isu-yang-ditemukan)

---

## 1. Arsitektur Sistem

```
┌──────────────────────────────────────────────────────────────────┐
│                     LAYER PRESENTASI                              │
│  Blade Views (resources/views/)                                   │
│  Tailwind CSS + jQuery + DataTables + SweetAlert2                 │
│  Livewire (Role Management, QR Print, Patrol Execution)           │
└──────────────────────────┬───────────────────────────────────────┘
                           │ AJAX / Form Submit
┌──────────────────────────▼───────────────────────────────────────┐
│                     LAYER API / WEB ROUTES                        │
│  routes/web.php   → Web Controllers (read-only views)            │
│  routes/api.php   → API Controllers (full CRUD via JSON)         │
│  Sanctum Token Auth untuk API                                    │
└──────────────────────────┬───────────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────────┐
│                     LAYER BISNIS                                  │
│  Controllers (validation, activity logs, business logic)          │
│  Form Requests (StoreUserRequest, UpdateUserRequest)              │
│  Middleware (RoleMiddleware, PermissionMiddleware)                │
└──────────────────────────┬───────────────────────────────────────┘
                           │
┌──────────────────────────▼───────────────────────────────────────┐
│                     LAYER DATA                                    │
│  Eloquent Models (17 Models)                                      │
│  Database: 19 Tables Relasional                                   │
│  Migrations (25 Files)                                            │
└──────────────────────────────────────────────────────────────────┘
```

### Pola Arsitektur

- **Dual Controller**: Web controller (render view) + API controller (CRUD JSON)
- **AJAX + DataTables**: Sebagian besar CRUD menggunakan modal + AJAX ke endpoint API
- **Standard Form Submit**: Users & Equipment menggunakan form submit biasa
- **Livewire**: Role management, QR Print, Patrol execution (real-time interaktif)
- **Sanctum Auth**: Token-based untuk API; Session-based untuk web
- **Activity Log**: Setiap operasi CRUD penting tercatat otomatis

---

## 2. Struktur Database & Relasi

### Entity Relationship Diagram (Textual)

```
BUILDINGS
  │
  └── ROOMS (building_id)
        │
        └── LABORATORIES (room_id) ─── UNIQUE (1 lab = 1 ruangan)
              │
              ├── responsible_person_id ────────→ USERS
              │
              ├── EQUIPMENT (laboratory_id)
              │     │
              │     ├── category_id ────────────→ EQUIPMENT_CATEGORIES
              │     │
              │     ├── EQUIPMENT_ITEMS (equipment_id)
              │     │     │
              │     │     ├── last_checked_by ──→ USERS
              │     │     ├── replaces_equipment_item_id ──→ EQUIPMENT_ITEMS (self)
              │     │     │
              │     │     └── PATROL_LOGS (equipment_item_id)
              │     │           │
              │     │           ├── patrol_schedule_id ──→ PATROL_SCHEDULES
              │     │           └── checked_by ──────────→ USERS
              │     │
              │     ├── EQUIPMENT_CONDITIONS (equipment_id)
              │     │     └── checked_by ───────→ USERS
              │     │
              │     └── DAMAGE_REPORTS (equipment_id)
              │           │
              │           ├── reported_by ──────→ USERS
              │           └── handled_by ───────→ USERS
              │
              ├── LAB_BORROWINGS (laboratory_id)
              │     │
              │     ├── user_id ────────────────→ USERS (peminjam)
              │     └── approved_by ────────────→ USERS (approver)
              │
              └── PATROL_SCHEDULES (laboratory_id)
                    │
                    ├── user_id ────────────────→ USERS (asisten)
                    └── PATROL_LOGS (patrol_schedule_id)

USERS
  │
  ├── role_id ───────────────→ ROLES ─── PERMISSIONS (via role_permission pivot)
  │
  ├── PROCUREMENTS (requested_by)
  │     │
  │     ├── approved_by ─────→ USERS
  │     │
  │     └── PROCUREMENT_ITEMS (procurement_id)
  │           │
  │           ├── replaces_equipment_id ───────→ EQUIPMENT
  │           └── replaces_equipment_item_id ──→ EQUIPMENT_ITEMS
  │
  └── ACTIVITY_LOGS (user_id, model_type, model_id) — polymorphic audit trail
```

### Daftar Tabel (19 Tables)

| Tabel | Primary Key | Foreign Keys |
|-------|-------------|--------------|
| `users` | id | role_id → roles |
| `roles` | id | — |
| `permissions` | id | — |
| `role_permission` | (role_id, permission_id) | role_id → roles, permission_id → permissions |
| `buildings` | id | — |
| `rooms` | id | building_id → buildings |
| `laboratories` | id | room_id → rooms (UNIQUE), responsible_person_id → users |
| `equipment_categories` | id | — |
| `equipment` | id | laboratory_id → laboratories, category_id → categories |
| `equipment_items` | id | equipment_id → equipment, last_checked_by → users, replaces_equipment_item_id → equipment_items |
| `equipment_conditions` | id | equipment_id → equipment, checked_by → users |
| `lab_borrowings` | id | laboratory_id → laboratories, user_id → users, approved_by → users |
| `patrol_schedules` | id | user_id → users, laboratory_id → laboratories |
| `patrol_logs` | id | patrol_schedule_id → patrol_schedules, equipment_item_id → equipment_items, checked_by → users |
| `damage_reports` | id | equipment_id → equipment, reported_by → users, handled_by → users |
| `procurements` | id | requested_by → users, approved_by → users |
| `procurement_items` | id | procurement_id → procurements, replaces_equipment_id → equipment, replaces_equipment_item_id → equipment_items |
| `activity_logs` | id | user_id → users (polymorphic: model_type + model_id) |
| `personal_access_tokens` | id | tokenable_id (Sanctum) |

---

## 3. Role & Hak Akses

### Role System

Sistem menggunakan **dual role system** untuk backward compatibility:

1. **Dynamic RBAC** (baru): `roles` + `permissions` via `role_id` di users
2. **Legacy Enum** (lama): kolom `role` ENUM('admin_lab','asisten_lab','pengguna')

User dicek melalui `role_id` dulu, fallback ke kolom `role` jika tidak ada.

### Matriks Fitur per Role

| Fitur | Admin | Asisten | Pengguna |
|-------|-------|---------|----------|
| **Dashboard** | ✅ Statistik global | ✅ Patroli hari ini | ✅ Status saya |
| **Master Data** | | | |
| &nbsp;&nbsp;Laboratorium | ✅ CRUD + lihat | ✅ Lihat saja | ✅ Lihat saja |
| &nbsp;&nbsp;Gedung | ✅ CRUD | ❌ | ❌ |
| &nbsp;&nbsp;Ruangan | ✅ CRUD | ❌ | ❌ |
| &nbsp;&nbsp;Kategori Alat | ✅ CRUD (via API) | ❌ | ❌ |
| &nbsp;&nbsp;Kelola Pengguna | ✅ CRUD | ❌ | ❌ |
| &nbsp;&nbsp;Manajemen Role | ✅ Livewire | ❌ | ❌ |
| **Inventaris** | | | |
| &nbsp;&nbsp;Alat Lab | ✅ CRUD | ✅ Lihat | ❌ |
| &nbsp;&nbsp;Cetak QR Code | ✅ | ❌ | ❌ |
| &nbsp;&nbsp;Kondisi Barang | ✅ CRUD | ✅ CRUD | ❌ |
| **Patroli & Jadwal** | | | |
| &nbsp;&nbsp;Jadwal Patroli | ✅ CRUD | ❌ | ❌ |
| &nbsp;&nbsp;Eksekusi Patroli | ❌ | ✅ Scan QR | ❌ |
| **Peminjaman** | | | |
| &nbsp;&nbsp;Ajukan/Riwayat | ✅ | ✅ | ✅ (hanya milik sendiri) |
| &nbsp;&nbsp;Setujui/Tolak | ✅ | ✅ | ❌ |
| **Laporan & Pengadaan** | | | |
| &nbsp;&nbsp;Laporan Kerusakan | ✅ CRUD | ✅ CRUD | ✅ (hanya milik sendiri) |
| &nbsp;&nbsp;Pengadaan | ✅ Setujui | ✅ Ajukan | ✅ Ajukan |
| **Akun** | | | |
| &nbsp;&nbsp;Profil | ✅ Edit | ✅ Edit | ✅ Edit |

---

## 4. Flowchart Aplikasi

### Navigasi Sidebar per Role

```
                          ┌──────────────────────┐
                          │      LOGIN PAGE       │
                          │   POST /api/login     │
                          └──────────┬───────────┘
                                     │
                          ┌──────────▼───────────┐
                          │      DASHBOARD        │
                          │   GET /dashboard      │
                          └──────────┬───────────┘
                                     │
              ┌──────────────────────┼──────────────────────┐
              │                      │                      │
       ┌──────▼──────┐       ┌──────▼──────┐       ┌──────▼──────┐
       │   ADMIN      │       │   ASISTEN   │       │  PENGGUNA   │
       └──────┬──────┘       └──────┬──────┘       └──────┬──────┘
              │                     │                      │
  ┌───────────┼───────────┐        │                      │
  │           │           │        │                      │
  ▼           ▼           ▼        ▼                      ▼
Master      Inventaris   Laporan  Dashboard              Dashboard
  Data                   &        (Patroli               (Status
  ├─ Gedung  ├─ Alat     Pengadaan hari ini)              Peminjaman
  ├─ Ruangan ├─ QR Code  ├─ Laporan   │                   saya)
  ├─ Lab     ├─ Kondisi  │  Kerusakan ▼
  ├─ Kategori│  Barang   ├─ Pengadaan Patroli
  ├─ Pengguna│           │  Barang    Scan QR
  └─ Role    └─ Patroli  └─ Riwayat   (Livewire)
              & Jadwal
```

### Alur Halaman per Menu

```
MASTER DATA (Admin only)
├── /buildings         → DataTable + Modal CRUD (AJAX ke /api/buildings)
├── /rooms             → DataTable + Modal CRUD (AJAX ke /api/rooms)
├── /laboratories      → DataTable + Modal CRUD (AJAX ke /api/laboratories)
├── /categories        → DataTable (read-only web, CRUD via API)
├── /users             → Standard form CRUD (POST/GET users.store/update)
└── /roles             → Livewire component (RoleManagement)

INVENTARIS
├── /equipment         → DataTable (read-only) + Standard form create/edit
│                        POST /equipment → EquipmentController@store
│                        PUT  /equipment/{id} → EquipmentController@update
├── /qr-codes          → Livewire: pilih lab → pilih alat → print QR
└── /conditions        → DataTable + Modal CRUD (AJAX ke /api/conditions)

PATROLI & JADWAL
├── /patrol-schedules  → DataTable + Modal CRUD (AJAX ke /api/patrol-schedules)
└── /patrol/{scheduleId} → Livewire: Scan QR + Catat Kondisi

PEMINJAMAN
├── /borrowings        → DataTable (AJAX ke /api/borrowings)
├── /borrowings/create → Form → AJAX POST /api/borrowings
├── /borrowings/schedule → View jadwal peminjaman
└── /borrowings/{id}   → Detail peminjaman

LAPORAN & PENGADAAN
├── /damage-reports    → DataTable + Form create + Detail
│                        Form submit AJAX ke /api/damage-reports
├── /procurements      → DataTable + Form create + Detail
│                        Form submit AJAX ke /api/procurements
└── /profile           → Edit profil + ganti password
```

---

## 5. Alur Bisnis Transaksi

### 5.1 Peminjaman Lab

**Aktor**: Pengguna (peminjam) → Admin/Asisten (approver)

**Flow Lengkap:**

```
PENGAJUAN (oleh Pengguna)
├── Buka /borrowings/create
├── Pilih laboratorium (dropdown dari database)
├── Pilih tanggal (min: hari ini)
├── Pilih jam mulai & jam selesai (end > start)
├── Isi tujuan peminjaman (required)
├── Isi jenis kegiatan (opsional)
├── Isi catatan (opsional)
├── Submit → AJAX POST /api/borrowings
└── Status → "pending"

💡 SISTEM:
   - Validasi: lab exists, tanggal >= today, end_time > start_time
   - Buat record LabBorrowing (user_id = auth)
   - Catat ActivityLog: "Mengajukan peminjaman: {lab} untuk {purpose}"

PERSETUJUAN (oleh Admin/Asisten)
├── Buka /borrowings → DataTable menampilkan semua peminjaman
├── [Approve]
│   ├── POST /api/borrowings/{id}/approve
│   ├── Status → "approved"
│   ├── approved_by → admin yang approve
│   ├── approved_at → timestamp now
│   └── Catat ActivityLog
│
├── [Reject]
│   ├── Wajib isi alasan penolakan
│   ├── POST /api/borrowings/{id}/reject
│   ├── Status → "rejected"
│   └── rejection_reason tersimpan
│
└── [Cancel] (oleh siapa saja)
    ├── POST /api/borrowings/{id}/cancel
    └── Status → "cancelled"

PENYELESAIAN
└── [Complete]
    ├── POST /api/borrowings/{id}/complete
    └── Status → "completed"

⚠️ CATATAN PENTING:
   - Tidak ada state-machine guard: complete/cancel bisa dari status apapun
   - Tidak ada update stok atau equipment — peminjaman hanya level ruang lab
   - Tidak ada peminjaman item spesifik
```

**Status Lifecycle:**

```
pending ──┬──> approved ──> completed
          ├──> rejected
          └──> cancelled
```

---

### 5.2 Patroli & Monitoring Kondisi Alat

**Aktor**: Admin (jadwal) → Asisten (eksekusi)

**Flow Lengkap:**

```
─── TAHAP 1: ADMIN MEMBUAT JADWAL PATROLI ───

├── Buka /patrol-schedules
├── Klik Tambah → Modal form
├── Pilih asisten (user dengan role asisten)
├── Pilih laboratorium yang akan dipatroli
├── Pilih hari (Senin-Minggu)
├── Tentukan jam patroli (start_time - end_time)
├── Status: active
├── Submit → AJAX POST /api/patrol-schedules
└── Catatan: Jadwal bisa multiple per asisten per hari

─── TAHAP 2: ASISTEN MELAKSANAKAN PATROLI ───

├── Buka Dashboard → Lihat "Patroli Hari Ini"
│   (Data dari PatrolSchedule::forToday() + forUser())
│
├── Klik tombol "Mulai Patroli"
│   → Buka /patrol/{scheduleId} (Livewire component)
│
├── System load data:
│   ├── Data schedule + lab
│   ├── Semua equipment + items di lab tersebut
│   ├── Hitung totalItemsInLab
│   └── Load patrol logs yang sudah dicatat hari ini
│
├── LOOP SCAN QR:
│   ├── Scan QR Code (barcode scanner → input field)
│   │   (atau ketik manual kode QR)
│   │
│   ├── VALIDASI (berlapis):
│   │   ├── Apakah QR ditemukan di database?
│   │   │   → Tidak: tampilkan error "QR Code tidak ditemukan dalam sistem"
│   │   │   → Ya: lanjut
│   │   │
│   │   ├── Apakah item milik laboratorium yang dijadwalkan?
│   │   │   → Tidak: tampilkan error "Bukan milik lab ini"
│   │   │   → Ya: lanjut
│   │   │
│   │   └── Apakah item sudah di-scan hari ini untuk patroli ini?
│   │       → Ya: tampilkan error "Sudah di-scan hari ini"
│   │       → Tidak: lanjut
│   │
│   ├── Tampilkan form kondisi alat:
│   │   ├── Nama alat
│   │   ├── Kondisi sebelumnya (default terpilih)
│   │   ├── Opsi: Baik / Rusak Ringan / Rusak Berat / Hilang
│   │   └── Catatan (opsional)
│   │
│   └── Asisten submit → SISTEM:
│       ├── Buat PatrolLog:
│       │   ├── patrol_schedule_id
│       │   ├── equipment_item_id
│       │   ├── condition (baru)
│       │   ├── previous_condition
│       │   ├── checked_by (asisten)
│       │   ├── notes
│       │   └── checked_at (now)
│       │
│       ├── UPDATE equipment_item:
│       │   ├── condition = kondisi baru
│       │   ├── condition_notes = notes
│       │   ├── last_checked_at = now
│       │   └── last_checked_by = asisten
│       │
│       └── BUAT EquipmentCondition history:
│           ├── equipment_id
│           ├── equipment_item_id
│           ├── checked_by
│           ├── condition
│           ├── previous_condition
│           ├── check_date
│           └── description = "Pemeriksaan via patroli lab"
│
└── Lanjut scan item berikutnya hingga selesai

📊 OUTPUT PATROLI:
   - Riwayat pengecekan tiap item alat
   - Perubahan kondisi terekam di equipment_item
   - History kondisi terekam di equipment_conditions
```

---

### 5.3 Laporan Kerusakan Barang

**Aktor**: Siapa saja (pelapor) → Admin/Asisten (penangani)

**Flow Lengkap:**

```
─── TAHAP 1: PELAPORAN KERUSAKAN ───

├── Buka /damage-reports/create
├── Pilih laboratorium → filter equipment
├── Pilih equipment (alat) → filter equipment_item
├── Pilih equipment_item spesifik (QR code)
├── Tentukan tipe kerusakan:
│   ├── Ringan → kondisi: rusak_ringan
│   ├── Sedang → kondisi: rusak_ringan
│   └── Berat  → kondisi: rusak_berat
├── Upload foto (opsional, max 2MB)
├── Deskripsi kerusakan (required)
├── Tanggal kejadian
├── Submit → AJAX POST /api/damage-reports
│
    └── 💥 SISTEM (side effects):
        ├── 1. Buat DamageReport:
        │   ├── equipment_id, equipment_item_id
        │   ├── reported_by = pelapor
        │   ├── damage_type
        │   ├── status = "reported"
        │   └── photo = upload (jika ada)
        │
        ├── 2. UPDATE kondisi fisik INVENTARIS LANGSUNG:
        │   └── equipment_item.condition:
        │       ├── Jika berat → "rusak_berat"
        │       └── Jika ringan/sedang → "rusak_ringan"
        │   └── (Hanya item, BUKAN equipment induk — quantity parent tetap)
        │
        └── 3. BUAT EquipmentCondition history:
            ├── checked_by = pelapor
            ├── condition = kondisi baru
            ├── previous_condition = kondisi sebelum rusak
            └── description = "Otomatis dari Laporan Kerusakan: {deskripsi}"

─── TAHAP 2: PENANGANAN OLEH ADMIN ───

├── Buka /damage-reports
├── Lihat detail laporan (GET /api/damage-reports/{id})
│
├── Status Flow:
│   reported → in_review → in_repair → [repaired / unrepairable] → closed
│
├── PUT /api/damage-reports/{id}/status dengan status baru
│
├── [repaired] → Selesai Diperbaiki
│   ├── RESTORE equipment_item.condition → "baik"
│   ├── RESTORE equipment.condition → "baik"
│   ├── Buat EquipmentCondition history: "Selesai Perbaikan"
│   └── Catat repair_cost + repair_notes
│
├── [unrepairable] → Tidak Bisa Diperbaiki (Afkir)
│   ├── equipment_item.condition → "rusak_berat"
│   │   (kondisi fisik berubah, quantity equipment induk TIDAK dikurangi
│   │    agar data historis tetap utuh — pengurangan qty dilakukan
│   │    terpisah via pengadaan pengganti atau soft-delete manual)
│   ├── Buat EquipmentCondition history: "Dinyatakan Tidak Bisa Diperbaiki"
│   └── Catat repair_notes
│
└── [closed] → Tutup laporan
    └── Jika status belum resolved, set resolved_at = now
```

**Status Lifecycle:**

```
reported ──> in_review ──> in_repair ──┬──> repaired ──> closed
                                       └──> unrepairable ──> closed
```

**Aturan Bisnis Penting:**
1. Kondisi alat **langsung diturunkan** saat laporan dibuat (tidak nunggu review)
2. Saat diperbaiki (`repaired`), kondisi **dikembalikan ke "baik"**
3. Saat tidak bisa diperbaiki (`unrepairable`), `equipment_item.condition` di-set `'rusak_berat'`
   — **quantity parent TIDAK dikurangi** (data historis tetap utuh).
   Pengurangan quantity aktual terjadi ketika pengadaan pengganti di-approve (via procurement) atau soft-delete manual pada EDIT Equipment.

---

### 5.4 Pengadaan Barang

**Aktor**: Siapa saja (pengaju) → Admin (approver)

**Flow Lengkap:**

```
─── TAHAP 1: PENGAJUAN PENGADAAN ───

├── Buka /procurements/create
├── Judul pengadaan (required)
├── Deskripsi (opsional)
├── Priority: Low / Medium / High / Urgent
├── Daftar item (bisa tambah multiple baris):
│   ├── Nama barang
│   ├── Spesifikasi
│   ├── Quantity
│   ├── Unit (default: "unit")
│   ├── Estimasi harga satuan
│   ├── [OPSIONAL] replaces_equipment_item_id
│   │   └── Jika ini pengganti alat lama, pilih item yang diganti
│   └── Subtotal = quantity × estimated_price (auto)
│
├── Total estimasi biaya (auto-sum)
├── Submit → AJAX POST /api/procurements
│
└── SISTEM:
    ├── Generate nomor pengadaan: PRC-{YYYYMM}-{NNNN}
    │   Contoh: PRC-202606-0001
    ├── Status → "submitted"
    ├── requested_by = auth user
    └── Catat ActivityLog

─── TAHAP 2: PERSETUJUAN OLEH ADMIN ───

├── Buka /procurements → DataTable
├── [Reject] → POST /api/procurements/{id}/reject
│   ├── Wajib isi alasan penolakan
│   └── `procurements.status` = 'rejected'
│
└── [Approve] → POST /api/procurements/{id}/approve
    └── 💥 SISTEM:
        ├── 1. Update status: `procurements.status` = 'approved'
        │   ├── approved_by = admin
        │   └── approved_at = now
        │
        └── 2. LOOP setiap data di tabel `procurement_items` yang terkait:
            │
            ├─── KATEGORI 1: Pengadaan Alat Baru (Murni Tambah Stok, Bukan Pengganti) ───
            │   ├── Kondisi jika: `replaces_equipment_item_id` IS NULL
            │   └── SISTEM: NO INVENTORY CHANGE
            │       (Penambahan murni alat baru dilakukan terpisah di CRUD Equipment
            │        agar tertib administrasi.)
            │
            └─── KATEGORI 2: Penggantian Barang Rusak / Hilang (Replacement) ───
                ├── Kondisi jika: `replaces_equipment_item_id` IS NOT NULL
                │
                ├── LANGKAH 1: Nonaktifkan Barang Fisik Lama
                │   ├── Cari data di tabel `equipment_items` berdasarkan
                │   │   `replaces_equipment_item_id`
                │   └── Update kolom `condition` = 'rusak_berat'
                │       (menandakan barang lama sudah afkir)
                │
                ├── LANGKAH 2: Deteksi Target Parent ID
                │   │  (Pencarian Pintar via `item_name`)
                │   ├── Cari di tabel `equipment` mana yang `name` SAMA dengan
                │   │   `procurement_items.item_name`
                │   │
                │   ├── JIKA KETEMU:
                │   │   └── Ambil `id`-nya sebagai Target Parent ID
                │   │
                │   └── JIKA TIDAK KETEMU:
                │       └── Buat baris induk BARU di tabel `equipment` menggunakan
                │           informasi `item_name` tersebut, lalu ambil `id` baris
                │           baru sebagai Target Parent ID
                │
                ├── LANGKAH 3: Jalankan Logika Stok Berdasarkan Target Parent
                │   │
                │   ├── KASUS 2A: Penggantian Jenis SAMA
                │   │   │  (Target Parent ID == `replaces_equipment_id`)
                │   │   │  Contoh: PC Lenovo rusak diganti PC Lenovo baru
                │   │   │
                │   │   ├── KONDISI 1: Jumlah pengganti SAMA (1 Rusak → 1 Baru)
                │   │   │   ├── Kolom `equipment.quantity` = TIDAK BERUBAH
                │   │   │   │   (tetap sinkron karena 1 afkir, 1 masuk)
                │   │   │   └── Insert 1 data baru ke `equipment_items`
                │   │   │       dengan Target Parent ID
                │   │   │
                │   │   └── KONDISI 2: Jumlah pengganti BERBEDA (1 Rusak → 2 Baru)
                │   │       ├── Hitung selisih: (`procurement_items.quantity` - 1)
                │   │       ├── Kolom `equipment.quantity` = TAMBAH sebanyak
                │   │       │   nilai selisih tersebut
                │   │       └── Insert data baru ke `equipment_items` sebanyak
                │   │           `procurement_items.quantity`
                │   │
                │   └── KASUS 2B: Penggantian Jenis BERBEDA
                │       │  (Target Parent ID != `replaces_equipment_id`)
                │       │  Contoh: Arduino rusak diganti dengan ESP32 baru
                │       │
                │       ├── 1. KURANGI STOK PARENT LAMA:
                │       │   ├── Cari di tabel `equipment` berdasarkan
                │       │   │   `replaces_equipment_id`
                │       │   └── Kurangi kolom `quantity` sebesar -1
                │       │
                │       ├── 2. TAMBAH STOK PARENT BARU:
                │       │   ├── Cari di tabel `equipment` berdasarkan
                │       │   │   Target Parent ID (dari LANGKAH 2)
                │       │   └── Tambah kolom `quantity` sebesar
                │       │       +`procurement_items.quantity`
                │       │
                │       └── 3. GENERATE ITEMS BARU:
                │           └── Insert data baru ke `equipment_items` sebanyak
                │               `procurement_items.quantity` terhubung dengan
                │               Target Parent ID
                │
                └── LANGKAH 4: Hubungkan Histori pada Item yang Baru Dibuat
                    └── Pada baris data `equipment_items` baru yang berhasil
                        di-insert, isi kolom `replaces_equipment_item_id`
                        dengan ID barang lama yang dinonaktifkan di LANGKAH 1

├── [Delete] → DELETE /api/procurements/{id}
```

**Status Lifecycle:**

```
draft ──> submitted ──┬──> approved ──> (inventory injected via replacement)
                      └──> rejected
```

**⚠️ PENTING:**

- **KATEGORI 1** (`replaces_equipment_item_id IS NULL`): Tidak ada perubahan stok. Penambahan alat baru murni dilakukan lewat CRUD Equipment terpisah.
- **KATEGORI 2** (`replaces_equipment_item_id IS NOT NULL`): Sistem akan mendeteksi secara pintar apakah penggantian sejenis (parent sama) atau beda jenis (parent berbeda), lalu menyesuaikan stok masing-masing parent secara otomatis.

---

### 5.5 Penambahan Alat Baru

**Aktor**: Admin

**Flow Lengkap:**

```
─── TAMBAH ALAT BARU (Equipment CRUD) ───

├── Buka /equipment/create (standard form submit)
├── Pilih laboratorium tujuan
├── Pilih kategori alat
├── Nama alat (required)
├── Kode inventaris (required, UNIQUE)
├── Brand/Merk (opsional)
├── Model (opsional)
├── Serial Number (opsional)
├── Tahun perolehan (opsional)
├── Harga (opsional)
├── Quantity = jumlah unit fisik (required)
├── Kondisi awal (default: "baik")
├── Status awal (default: "available")
├── Upload foto (opsional)
├── Deskripsi & Catatan (opsional)
├── Submit → POST /equipment (standard form)
│
└── 💥 SISTEM (auto-generate items):
    ├── 1. Buat Equipment (parent) record
    │
    ├── 2. 🔄 Trigger: booted() created → generateItems()
    │   └── Loop i = 1 to quantity:
    │       └── Buat EquipmentItem:
    │           ├── equipment_id = parent
    │           ├── sequence_number = i (001, 002, 003...)
    │           ├── qr_code = format:
    │           │   "{kode_gedung} {kode_ruangan} {kode_lab} {nama_alat} {nomor}"
    │           │   Contoh: "A 201 FISIKA Mikroskop 001"
    │           └── condition = mengikuti parent
    │
    └── 3. Catat ActivityLog

─── EDIT EQUIPMENT (Aksi CRUD pada tabel `equipment`) ───

├── Buka /equipment/{id}/edit (standard form submit)
├── Ubah data (partial update allowed, termasuk quantity)
├── Submit → POST /equipment/{id} (with PUT spoof)
│
├── 💥 KONDISI A: Angka Qty NAIK (Misal: 5 → 10)
│   └── SISTEM:
│       ├── Update `equipment.quantity` = 10
│       └── Jalankan generateItems() → Insert 5 baris baru ke tabel `equipment_items`:
│           ├── `sequence_number` = melanjutkan nomor terakhir (max + 1)
│           └── `condition` = `'baik'`
│
└── 💥 KONDISI B: Angka Qty TURUN (Misal: 10 → 5)
    └── SISTEM:
        ├── 1. Hitung selisih pengurangan (10 - 5 = 5 item yang harus di-soft-delete)
        ├── 2. Ambil 5 data `equipment_items` terakhir dari `equipment_id` ini
        │      dengan kondisi `deleted_at` IS NULL,
        │      urutkan berdasarkan `sequence_number` DESC
        │      (mengurangi dari urutan paling belakang)
        ├── 3. Eksekusi SOFT-DELETE pada 5 item tersebut:
        │      └── $item->delete() → kolom `deleted_at` terisi timestamp
        └── 4. Update kolom `equipment.quantity` = 5

─── HAPUS EQUIPMENT ───

└── DELETE /api/equipment/{id}
    └── HARD-DELETE CASCADE: semua EquipmentItem child ikut terhapus permanen

─── SOFT-DELETE UNTUK REPLACEMENT ───

└── Ketika equipment_item lama ditandai sebagai diganti (via procurement approve),
    `condition` di-set `'rusak_berat'` — item TIDAK di-soft-delete agar histori
    replacement tetap terbaca. Soft-delete hanya untuk pengurangan qty di EDIT.
```

**Aturan Bisnis `generateItems()`:**

```
existingCount = items()->whereNull('deleted_at')->count()
if quantity > existingCount:
    for i = existingCount+1 to quantity:
        create EquipmentItem(
            sequence_number = i,
            qr_code = generate(),
            condition = 'baik'
        )
elif quantity < existingCount:
    selisih = existingCount - quantity
    items()->whereNull('deleted_at')->orderByDesc('sequence_number')->limit(selisih)->delete()

⚠️ Triggered pada update quantity (naik/turun)
⚠️ Soft-delete: item tidak hilang dari DB, hanya kolom `deleted_at` terisi
```

---

### 5.6 Penggantian Alat Lama ke Baru

**Aktor**: Admin

**Flow Lengkap:**

```
─── SKENARIO 1: PENGGANTIAN JENIS SAMA ───
    Contoh: Mikroskop A001 rusak, diganti Mikroskop baru

STEP 1: LAPORKAN KERUSAKAN
├── Buat DamageReport → unrepairable
└── equipment_item A001 → condition "rusak_berat"

STEP 2: AJUKAN PENGADAAN PENGGANTI
├── Buat Procurement dengan item:
│   ├── item_name = "Mikroskop" (harus SAMA dengan equipment.name)
│   ├── replaces_equipment_item_id = ID A001
│   ├── quantity = 1 (bisa lebih jika 1 rusak diganti 2)
│   └── Status → "submitted"

STEP 3: APPROVE → SISTEM:
├── LANGKAH 1: A001.condition → "rusak_berat"
├── LANGKAH 2: Cari Target Parent ID via item_name = "Mikroskop"
│   └── Ketemu → Target Parent ID = equipment parent A001
├── LANGKAH 3 (KASUS 2A - Jenis SAMA):
│   └── Karena qty = 1 (sama dengan 1 rusak):
│       └── quantity TIDAK BERUBAH, insert 1 item baru
└── LANGKAH 4: item_baru.replaces_equipment_item_id = A001.id

─── SKENARIO 2: PENGGANTIAN JENIS BERBEDA ───
    Contoh: Arduino A001 rusak, diganti ESP32 baru

STEP 1: LAPORKAN KERUSAKAN
├── Buat DamageReport → unrepairable
└── equipment_item A001 → condition "rusak_berat"

STEP 2: AJUKAN PENGADAAN PENGGANTI
├── Buat Procurement dengan item:
│   ├── item_name = "ESP32" (BERBEDA dengan equipment.name = "Arduino")
│   ├── replaces_equipment_item_id = ID A001
│   └── quantity = 1

STEP 3: APPROVE → SISTEM:
├── LANGKAH 1: A001.condition → "rusak_berat"
├── LANGKAH 2: Cari Target Parent ID via item_name = "ESP32"
│   └── TIDAK ketemu → Buat EQUIPMENT BARU "ESP32" → Target Parent ID = id baru
├── LANGKAH 3 (KASUS 2B - Jenis Berbeda):
│   ├── KURANGI quantity parent ARDUINO = -1
│   └── TAMBAH quantity parent ESP32 = +1
└── LANGKAH 4: item_baru.replaces_equipment_item_id = A001.id

─── ALTERNATIF: ALAT JENIS BARU (BUKAN PENGGANTI) ───

└── Langsung: Equipment CRUD → POST /equipment
    ├── Buat equipment baru
    └── Auto-generate items dengan QR code
```

---

### 5.7 Scan QR Code End-to-End

**Format QR Code:**

```
{kode_gedung} {kode_ruangan} {kode_lab} {nama_alat} {nomor_urut_3digit}

Contoh: "A 201 FISIKA Mikroskop 001"
```

**Generate QR:**

- Saat EquipmentItem dibuat oleh `generateItems()`
- Fungsi: `EquipmentItem::generateQrCode($equipment, $sequenceNumber)`
- Komponen: `Building.code` `Room.code` `Laboratory.code` `Equipment.name` `Sequence`

**Scan QR — Digunakan di 2 Tempat:**

```
─── A. PATROLI (oleh Asisten via Livewire) ───

Scan QR (barcode scanner/keyboard)
  → Livewire onQrScanned()
  → VALIDASI:
     1. QR ditemukan?                        → Error jika tidak
     2. Item milik lab yang dipatroli?       → Error jika bukan
     3. Sudah di-scan hari ini?              → Error jika sudah
  → Tampilkan form kondisi
  → Submit condition
  → PatrolLog + Update Item + History

─── B. CETAK QR (oleh Admin via Livewire) ───

Pilih lab → Pilih equipment → Pilih items
  → Tampilkan daftar QR code
  → Print (cetak ke kertas)
  → Tempel pada alat fisik
```

---

### 5.8 Diagram Status Lifecycle

```
PEMINJAMAN LAB:
  pending ──┬──> approved ──> completed
            ├──> rejected
            └──> cancelled

PENGADAAN BARANG:
  draft ──> submitted ──┬──> approved ──> (inventory injected)
                        └──> rejected

LAPORAN KERUSAKAN:
  reported ──> in_review ──> in_repair ──┬──> repaired ──> closed
                                         └──> unrepairable ──> closed

KONDISI FISIK (equipment_items.condition):
  baik ◄────► rusak_ringan    (bisa pulih via perbaikan)
  baik ──────► rusak_berat    (tidak bisa kembali)
  rusak_ringan ──► rusak_berat
  (semua bisa ke "hilang")

STATUS KETERSEDIAAN (equipment.status):
  available ──> in_use
  available ──> borrowed
  available ──> maintenance

📌 Catatan:
  - `equipment.status` = status ketersediaan administratif (TIDAK otomatis berubah
    saat kerusakan). Perubahan manual via CRUD Equipment.
  - `equipment_items.condition` = kondisi fisik barang (berubah otomatis via
    damage report, patroli, dll).
  - Saat afkir (unrepairable): hanya `condition` berubah jadi 'rusak_berat'.
    Quantity equipment induk TIDAK dikurangi — data historis tetap utuh.
  - Pengurangan qty aktual terjadi di: EDIT Equipment (soft-delete) atau
    PROCUREMENT APPROVE (penggantian beda jenis).
```

---

## 6. Daftar Bug & Isu yang Ditemukan

### 🔴 Critical Bugs (Sudah Diperbaiki)

| # | File | Issue | Status |
|---|------|-------|--------|
| 1 | `app/Models/User.php:114-117` | Relasi `schedules()` merujuk `LabSchedule::class` yang tidak ada | ✅ Dihapus |
| 2 | `LaboratoryApiController.php:56` | Eager loading `schedules.user` harusnya `patrolSchedules.user` | ✅ Diperbaiki |
| 3 | `BorrowingApiController.php:56` | Eager load `items.equipment` — relasi `items()` tidak ada di LabBorrowing | ✅ Dihapus |
| 4 | `CategoryApiController.php` | `store()` tanpa validasi, `update()` tanpa null check, `destroy()` tanpa validasi | ✅ Ditambahkan validasi + null checks + usage check |
| 5 | `Equipment.php:17-19` | `booted() updated` trigger `generateItems()` di setiap field update (tidak hanya quantity) | ✅ Hanya trigger saat `quantity` berubah |
| 6 | 11 API Controllers | Divisi by zero jika `length=0` di DataTables pagination | ✅ Safeguard `max($limit, 1)` |

### 🟡 SoftDeletes (Fitur Baru)

| # | Tabel | Model | Migration |
|---|-------|-------|-----------|
| 1 | `equipment_items` | `EquipmentItem` (sebelumnya sudah ada) | ✅ |
| 2-16 | Semua tabel lain (`buildings`, `rooms`, `equipment`, `equipment_categories`, `equipment_conditions`, `lab_borrowings`, `laboratories`, `patrol_schedules`, `patrol_logs`, `procurements`, `procurement_items`, `damage_reports`, `users`, `roles`, `permissions`) | Masing-masing + trait `SoftDeletes` | ✅ Migrasi batch `2026_06_01_000002` |

Semua DataTables query otomatis exclude soft-deleted records (default Eloquent behavior).

### 🟠 Missing Controller Methods (Dead Routes — Tidak Diperbaiki, Tidak Bermasalah)

| # | Route | Controller | Method | Catatan |
|---|-------|-----------|--------|---------|
| 7 | `POST /login` | `Auth\AuthController` | `login()` | Form via AJAX ke `/api/login` (berfungsi) |
| 8 | `POST /register` | `Auth\AuthController` | `register()` | Form via AJAX ke `/api/register` (berfungsi) |
| 9 | `POST /logout` | `Auth\AuthController` | `logout()` | Logout via AJAX ke `/api/logout` (berfungsi) |
| 10 | `POST /damage-reports` | `DamageReportController` | `store()` | Form via AJAX ke `/api/damage-reports` (berfungsi) |
| 11 | `POST /procurements` | `ProcurementController` | `store()` | Form via AJAX ke `/api/procurements` (berfungsi) |

### 🟢 Stale Code (Referensi Mati)

| # | File | Detail |
|---|------|--------|
| 12 | `app/Models/User.php:116` | `schedules()` → `LabSchedule::class` — model sudah dihapus **(SUDAH DIPERBAIKI)** |
| 13 | `routes/web.php` | Route `POST /damage-reports` dan `POST /procurements` — method tidak ada (dead code) |
| 14 | `Equipment::scopeByCondition()` | Scope didefinisikan tapi tidak pernah dipanggil |
| 15 | `Permission::scopeByGroup()` | Scope didefinisikan tapi tidak pernah dipanggil |
| 16 | `Role::scopeDefault()` | Scope didefinisikan tapi tidak pernah dipanggil |

---

## 7. Perubahan Terbaru (1 Juni 2026)

### 7.1 Dashboard — Penambahan Total Stok
- **Dashboard Admin (API):** `total_stok` = jumlah `equipment_items` yang tidak soft-deleted
- **Dashboard Admin (Blade):** Card "Total Alat" menampilkan sub-text "Total Stok: XX item"
- **Dashboard Asisten:** Card "Total Alat" menampilkan sub-text "Total Stok: XX item"

### 7.2 SoftDeletes pada Semua Model
Semua model yang memiliki CRUD kini menggunakan trait `SoftDeletes`:
- `Building`, `Room`, `Equipment`, `EquipmentCategory`, `EquipmentCondition`
- `LabBorrowing`, `Laboratory`, `PatrolSchedule`, `PatrolLog`
- `Procurement`, `ProcurementItem`, `DamageReport`
- `User`, `Role`, `Permission`
- `EquipmentItem` (sebelumnya sudah)

### 7.3 Perbaikan Bug CRUD Kategori
- `store()`: Validasi `required|string|max:255|unique`
- `update()`: Null check + validasi unique exclude self
- `destroy()`: Null check + cek equipment terdaftar sebelum hapus

### 7.4 Perbaikan Equipment `generateItems()`
- Hanya trigger saat field `quantity` berubah (bukan setiap field update)
- **KONDISI A (Qty Naik):** Sequence number = max existing + 1, condition = 'baik'
- **KONDISI B (Qty Turun):** Soft-delete N item terakhir

### 7.5 Perbaikan Division by Zero
Semua kontroler API DataTables: guard `$limit = max($limit, 1)` sebelum pembagian.

---

*dibuat: 1 Juni 2026 | Terakhir diperbarui: 1 Juni 2026*
