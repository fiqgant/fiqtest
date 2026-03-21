# fiqtest — Platform Ujian Online

Platform ujian online berbasis web untuk institusi akademik. Mendukung 6 tipe soal (termasuk coding dengan eksekusi kode nyata), proctoring otomatis, grading otomatis, dan monitoring real-time.

---

## Daftar Isi

- [Tech Stack](#tech-stack)
- [Instalasi & Setup](#instalasi--setup)
- [Akses Admin](#akses-admin)
- [Fitur Admin](#fitur-admin)
  - [1. Manajemen Akademik](#1-manajemen-akademik)
  - [2. Manajemen Mahasiswa](#2-manajemen-mahasiswa)
  - [3. Bank Soal](#3-bank-soal)
  - [4. Manajemen Ujian](#4-manajemen-ujian)
  - [5. Pengaturan Ujian](#5-pengaturan-ujian)
  - [6. Live Monitor](#6-live-monitor)
  - [7. Grading Essay](#7-grading-essay)
  - [8. Reset Attempt](#8-reset-attempt)
  - [9. Export Nilai](#9-export-nilai)
  - [10. Laporan & Analitik](#10-laporan--analitik)
  - [11. Pengaturan Sistem](#11-pengaturan-sistem)
- [Fitur Mahasiswa](#fitur-mahasiswa)
  - [1. Halaman Beranda](#1-halaman-beranda)
  - [2. Mulai Ujian](#2-mulai-ujian)
  - [3. Workspace Ujian](#3-workspace-ujian)
  - [4. Tipe Soal](#4-tipe-soal)
  - [5. Eksekusi Kode](#5-eksekusi-kode)
  - [6. Hint / Petunjuk](#6-hint--petunjuk)
  - [7. Hasil Ujian](#7-hasil-ujian)
- [Sistem Proctoring & Integritas Akademik](#sistem-proctoring--integritas-akademik)
- [Sistem Grading & Penilaian](#sistem-grading--penilaian)
- [Deployment (VPS + Docker)](#deployment-vps--docker)
- [Ringkasan Semua URL](#ringkasan-semua-url)

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Tailwind CSS v4, Alpine.js, Monaco Editor |
| Database | MySQL 8 |
| Eksekusi Kode | Judge0 CE |
| PDF | Barryvdh/DomPDF |
| Excel | PhpOffice/PhpSpreadsheet |
| Deteksi Device | Jenssegers/Agent |
| Animasi Welcome | Three.js |
| Math Rendering | KaTeX |
| Containerisasi | Docker + Docker Compose |

---

## Instalasi & Setup

### Requirement
- Docker & Docker Compose
- Git

### Langkah

```bash
# Clone repo
git clone https://github.com/fiqgant/fiqtest.git
cd fiqtest

# Salin environment file
cp .env.example .env

# Isi variabel wajib di .env:
# APP_KEY=
# DB_PASSWORD=
# JUDGE0_URL= (opsional, untuk eksekusi kode)

# Build dan jalankan semua container
docker compose up -d --build
```

Container secara otomatis menjalankan:
- `php artisan migrate`
- `php artisan config:cache`
- `php artisan storage:link`

Akses aplikasi di `http://localhost` atau domain yang dikonfigurasi.

### Membuat Admin Pertama

```bash
docker compose exec app php artisan tinker
```

```php
\App\Models\Admin::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('password'),
]);
```

---

## Akses Admin

| URL | Keterangan |
|---|---|
| `/admin/login` | Halaman login admin |
| `/admin/dashboard` | Dashboard utama |

---

## Fitur Admin

### 1. Manajemen Akademik

Hierarki data: **Academic Period → Course → Course Offering → Enrollment**

#### Academic Period (Semester/Periode)
**URL:** `/admin/academic-periods`

- Buat, edit, hapus periode akademik (misal: Semester Genap 2025/2026)
- Tandai satu periode sebagai **aktif**
- Periode aktif tampil sebagai filter default di seluruh admin panel

#### Course (Mata Kuliah)
**URL:** `/admin/courses`

- Buat, edit, hapus mata kuliah
- Setiap mata kuliah memiliki nama dan kode (misal: `PWEB`, `ALGO`)

#### Course Offering (Kelas)
**URL:** `/admin/course-offerings`

- Gabungkan mata kuliah + periode + nama kelas (misal: PWEB — Semester Genap 2025 — Kelas A)
- Satu offering memiliki bank soal dan ujian sendiri
- Kelola enrollment mahasiswa per offering

#### Enrollment Mahasiswa
**URL:** `/admin/course-offerings/{id}/enrollment`

- Tambah/hapus mahasiswa dari kelas
- Hanya mahasiswa yang terdaftar di offering yang bisa mengikuti ujiannya

---

### 2. Manajemen Mahasiswa

**URL:** `/admin/students`

#### Tambah Mahasiswa Manual
- Isi NIM, nama, dan email (opsional)
- NIM harus unik di seluruh sistem

#### Import Massal
**URL:** `/admin/students/bulk/import`

Dua cara import:

1. **Upload Excel (.xlsx)** — kolom: `name`, `nim`, `email` (opsional). Baris header diabaikan.
2. **Copy-paste CSV** — format: `nama,nim` atau `nama,nim,email` — satu baris per mahasiswa

Proses:
1. Upload/paste data → klik Preview
2. Cek hasil parse di halaman preview
3. Konfirmasi → data disimpan
4. NIM duplikat otomatis dilewati

#### Hapus Massal
Centang beberapa mahasiswa di tabel → tombol **Bulk Delete**.

---

### 3. Bank Soal

**URL:** `/admin/questions`

#### Filter & Pencarian
- Filter by: Course Offering, Tipe soal, Tingkat kesulitan
- Pencarian teks bebas di judul soal

#### Membuat Soal
**URL:** `/admin/questions/create`

Pilih tipe soal:

| Tipe | Auto-Grade | Keterangan |
|---|---|---|
| **Coding** | Ya | Kode mahasiswa dieksekusi lawan test cases |
| **Multiple Choice** | Ya | Satu jawaban benar |
| **Multiple Select** | Ya | Beberapa jawaban benar |
| **True / False** | Ya | Pilihan Benar atau Salah |
| **Fill in Blank** | Ya | Isi titik-titik |
| **Essay** | Tidak | Dinilai manual oleh admin |

Field yang tersedia per soal:
- **Judul** dan **Deskripsi** (mendukung Markdown + KaTeX untuk rumus matematika)
- **Tingkat Kesulitan** — Easy / Medium / Hard
- **Bobot Default** (poin)
- **Tags** — untuk filter soal saat membuat ujian
- **Hint** — petunjuk opsional untuk mahasiswa
- Untuk **Coding**: bahasa pemrograman, starter code, reference solution, test cases (visible/hidden, input/output)
- Untuk **MC/MS**: daftar opsi jawaban, tandai yang benar
- Untuk **TF**: pilih jawaban benar (True/False)
- Untuk **Fill Blank**: isi jawaban yang benar

#### Preview Soal
**URL:** `/admin/questions/{id}/preview`

Lihat tampilan soal dari sudut pandang mahasiswa. Untuk soal coding, bisa langsung menjalankan kode.

#### Duplikasi Soal
Tombol **Duplicate** di halaman daftar atau detail soal → soal baru dengan data yang sama, siap diedit.

#### Statistik Soal
**URL:** `/admin/questions/{id}/stats`

- Berapa kali soal digunakan di ujian
- Rata-rata skor mahasiswa
- Tingkat keberhasilan per test case (untuk soal coding)

#### Import Massal Soal
**URL:** `/admin/questions/bulk/import`

1. Download template Excel di `/admin/questions/bulk/template`
2. Isi soal-soal di spreadsheet sesuai format template
3. Upload → Preview → Konfirmasi import

#### Hapus Massal Soal
Centang beberapa soal → tombol **Bulk Delete**.

#### Tags Soal
**URL:** `/admin/question-tags`

- Buat tag untuk kategorisasi soal (misal: `array`, `rekursi`, `OOP`)
- Tag digunakan sebagai filter pool soal saat membuat ujian

---

### 4. Manajemen Ujian

**URL:** `/admin/exams`

#### Membuat Ujian Baru
**URL:** `/admin/exams/create`

1. Isi semua pengaturan ujian (lihat seksi [Pengaturan Ujian](#5-pengaturan-ujian))
2. Simpan sebagai **Draft**
3. Cek pool soal di `/admin/exams/{id}/question-pool` — pastikan jumlah soal cukup
4. Jika soal sudah cukup, klik **Publish**

#### Status Ujian

| Status | Keterangan |
|---|---|
| **Draft** | Tidak terlihat mahasiswa, bisa diedit bebas |
| **Published** | Aktif — mahasiswa bisa akses sesuai jadwal buka/tutup |
| **Closed** | Tidak bisa diakses lagi, semua data tetap tersimpan |

#### Publish Ujian
Sistem memvalidasi otomatis:
- Jumlah soal easy/medium/hard yang tersedia di bank soal sudah cukup sesuai konfigurasi distribusi
- Jika kurang, muncul pesan error dengan informasi kekurangan per kesulitan

#### Question Pool
**URL:** `/admin/exams/{id}/question-pool`

Lihat daftar soal yang memenuhi syarat untuk ujian ini, berdasarkan filter tag dan distribusi kesulitan yang dikonfigurasi.

---

### 5. Pengaturan Ujian

Semua pengaturan tersedia di form create/edit exam.

#### Jadwal & Durasi

| Pengaturan | Keterangan |
|---|---|
| **Opens At** | Tanggal & jam ujian mulai bisa diakses mahasiswa |
| **Closes At** | Deadline — setelah lewat tidak bisa mulai ujian baru |
| **Duration (menit)** | Batas waktu mengerjakan per mahasiswa (1–600 menit) |
| **Show Score Immediately** | Tampilkan nilai langsung setelah mahasiswa submit |

#### Distribusi & Bobot Soal

| Pengaturan | Keterangan |
|---|---|
| **Easy Count** | Jumlah soal mudah yang diambil acak dari bank |
| **Medium Count** | Jumlah soal sedang |
| **Hard Count** | Jumlah soal sulit |
| **Easy Weight** | Poin per soal mudah |
| **Medium Weight** | Poin per soal sedang |
| **Hard Weight** | Poin per soal sulit |
| **Question Pool Filter (Tags)** | Filter pool berdasarkan tag (logika OR). Kosongkan = semua soal |

Soal diambil **acak** dari bank sesuai distribusi saat mahasiswa memulai ujian — setiap mahasiswa bisa mendapat soal berbeda.

#### Sistem Hint

| Pengaturan | Keterangan |
|---|---|
| **Enable Hints** | Aktifkan tombol hint di workspace ujian |
| **Max Hints per Question** | 0 = tidak terbatas. Berlaku untuk semua soal dalam ujian ini |

#### Proctoring & Keamanan

| Pengaturan | Keterangan |
|---|---|
| **Max Tab Switches** | Batas perpindahan tab/aplikasi. 0 = nonaktif |
| **Warn at Switch #** | Tampilkan peringatan saat mencapai switch ke-N |
| **Inactivity Limit (detik)** | Auto-disqualify jika tidak ada aktivitas. 0 = nonaktif |
| **Inactivity Warning (detik)** | Tampilkan peringatan N detik sebelum batas inaktivitas |
| **Disable DevTools & Right-Click** | Blokir F12, Ctrl+Shift+I, klik kanan, view-source selama ujian |
| **Detect Copy-Paste Activity** | Catat semua aktivitas copy/cut/paste beserta isi teksnya |
| **Shuffle MC/MS Options** | Acak urutan pilihan jawaban — berbeda tiap mahasiswa |

---

### 6. Live Monitor

**URL:** `/admin/exams/{id}/monitor`

Monitor real-time mahasiswa yang sedang mengerjakan ujian. Auto-refresh setiap 10 detik.

#### Stats Bar
| Kartu | Keterangan |
|---|---|
| **Active Now** | Jumlah mahasiswa sedang mengerjakan |
| **Submitted** | Jumlah yang sudah submit |
| **Total Enrolled** | Total mahasiswa terdaftar di kelas |
| **Last Updated** | Waktu refresh terakhir |

#### Tabel Mahasiswa Aktif

| Kolom | Keterangan |
|---|---|
| Student | Nama dan NIM |
| Started | Jam mulai ujian |
| Time Remaining | Countdown (merah & berkedip jika < 5 menit) |
| Progress | Soal terjawab / total + progress bar |
| Tab Switches | Jumlah tab switch (kuning ≥1, merah & bold ≥3) |
| Last Activity | Waktu aktivitas terakhir |
| IP | Alamat IP mahasiswa |
| Device | Tipe device, browser + versi, OS + versi |
| Status | In Progress / Disqualified |

---

### 7. Grading Essay

**URL:** `/admin/exams/{id}/attempts/{attemptId}`

#### Cara Menilai Essay
1. Buka halaman detail attempt mahasiswa
2. Scroll ke bagian soal essay
3. Isi kolom **Manual Score** (angka, 0 s/d bobot soal)
4. Isi kolom **Feedback** (opsional — teks untuk mahasiswa)
5. Klik **Save Grade**

Total skor attempt diperbarui otomatis setelah menyimpan nilai.

#### Informasi di Halaman Attempt Detail
- **Proctoring Log** — tab switches, status disqualified + alasan, IP, device info
- **Copy-Paste Log** — daftar lengkap aktivitas clipboard (jika fitur aktif di ujian)
- **Per soal** — jawaban mahasiswa, untuk coding: kode + output + test case pass/fail

---

### 8. Reset Attempt

**URL:** `/admin/exams/{id}/attempts`

Jika mahasiswa mengalami kendala teknis (putus koneksi, laptop mati, dll), admin bisa mereset attempt.

**Cara:**
1. Buka halaman Attempts ujian
2. Temukan mahasiswa yang ingin direset
3. Klik tombol **Reset** (tombol merah di kolom Actions)
4. Konfirmasi dialog

Attempt lama dihapus beserta semua jawaban. Mahasiswa bisa memulai ujian dari awal.

> **Catatan:** Reset bersifat permanen — semua data attempt sebelumnya hilang.

---

### 9. Export Nilai

**URL:** `/admin/exams/{id}/export` — atau tombol **Export** di halaman Attempts.

Download file Excel berisi:
- NIM dan nama mahasiswa
- Total skor dan persentase
- Jumlah tab switch
- Status disqualified + alasan

---

### 10. Laporan & Analitik

**URL:** `/admin/reports`

#### Laporan per Course Offering
**URL:** `/admin/reports/offering/{id}`

- Tabel semua ujian dalam kelas beserta statistik
- Nilai per mahasiswa di setiap ujian
- Rata-rata nilai, jumlah peserta
- Tombol export Excel

#### Laporan per Periode Akademik
**URL:** `/admin/reports/period/{id}`

- Overview semua kelas dalam satu semester
- Perbandingan statistik antar kelas

#### Laporan per Mahasiswa
**URL:** `/admin/reports/student/{id}`

- Riwayat semua attempt mahasiswa
- Detail nilai per ujian

---

### 11. Pengaturan Sistem

#### Judge0 API
**URL:** `/admin/settings/judge0`

Konfigurasi untuk eksekusi kode mahasiswa:
- **Judge0 URL** — URL instance Judge0 (self-hosted atau cloud)
- **API Host** / **API Key** — Jika menggunakan RapidAPI
- **Timeout** — Batas waktu eksekusi (detik)
- Tombol **Test Connection** — Verifikasi koneksi ke Judge0

#### Profil Admin
**URL:** `/admin/profile`

- Ubah nama dan email
- Ganti password

#### Dark Mode
Toggle di sidebar admin panel. Preferensi disimpan di `localStorage` browser — persistent antar sesi.

---

## Fitur Mahasiswa

### 1. Halaman Beranda

**URL:** `/`

- Menampilkan daftar ujian yang sedang aktif (status Published, dalam rentang waktu buka–tutup)
- Setiap kartu ujian menampilkan: nama ujian, mata kuliah, durasi, waktu tutup
- Animasi latar belakang Three.js

---

### 2. Mulai Ujian

**URL:** `/exam/{slug}`

**Langkah:**
1. Halaman instruksi menampilkan: nama ujian, durasi, jumlah soal, aturan proctoring yang aktif
2. Masukkan **NIM** untuk verifikasi identitas
3. Sistem mengecek:
   - NIM terdaftar di kelas ujian tersebut
   - Belum ada attempt aktif (atau attempt lama sudah di-reset admin)
   - Ujian sedang dalam rentang waktu buka–tutup
4. Klik **Mulai Ujian** → masuk ke workspace

---

### 3. Workspace Ujian

Tampilan terbagi menjadi dua panel:
- **Panel Kiri:** Navigasi soal — daftar semua soal dengan indikator terjawab/belum dan warna per kesulitan
- **Panel Kanan/Utama:** Area jawab soal + timer di atas

#### Timer
- Countdown real-time di toolbar atas
- Berubah warna merah dan berkedip jika tersisa < 5 menit
- Auto-submit otomatis saat waktu habis

#### Autosave
- Jawaban tersimpan otomatis secara berkala
- Indikator "Saving..." dan "Saved ✓" di UI

#### Submit Ujian
- Tombol **Submit** di toolbar — muncul dialog konfirmasi sebelum submit
- Setelah submit, diarahkan ke halaman konfirmasi atau langsung hasil (tergantung setting)

---

### 4. Tipe Soal

#### Coding
- Monaco Editor dengan syntax highlighting penuh
- Starter code otomatis dimuat dari pengaturan soal
- Pilihan bahasa pemrograman sesuai yang diset di soal
- Tombol **Run** untuk menguji kode lawan visible test cases
- Tampilkan output, pesan error, dan waktu eksekusi

#### Multiple Choice
- Pilih satu jawaban dari daftar opsi
- Opsi bisa diacak urutan tampilannya (jika diaktifkan admin)

#### Multiple Select
- Centang semua jawaban yang benar
- Opsi bisa diacak

#### True / False
- Dua tombol: **True** dan **False**

#### Fill in Blank
- Input teks bebas

#### Essay
- Textarea besar untuk jawaban panjang
- Tidak ada batasan karakter

---

### 5. Eksekusi Kode

1. Tulis kode di Monaco Editor
2. Klik tombol **Run**
3. Kode dikirim ke Judge0 untuk dieksekusi
4. Hasil tampil: output, error, waktu eksekusi
5. Status per visible test case (pass/fail) ditampilkan

> **Catatan:** Hanya *visible test cases* yang bisa dijalankan mahasiswa. *Hidden test cases* hanya digunakan untuk grading final saat submit.

---

### 6. Hint / Petunjuk

Jika admin mengaktifkan hints di ujian:
1. Tombol **Hint** muncul di setiap soal yang memiliki hint
2. Klik → petunjuk tampil
3. Penggunaan hint dicatat (tidak mempengaruhi nilai)
4. Jika ada batas (misal max 2 hint per soal), tombol terkunci setelah batas tercapai

---

### 7. Hasil Ujian

Jika admin mengaktifkan **Show Score Immediately**:

**URL:** `/attempt/{id}/result`

Menampilkan:
- Total skor dan persentase
- Breakdown per soal: skor diraih vs skor maksimal
- Untuk coding: test case pass/fail detail
- Untuk essay: feedback dari admin (jika sudah dinilai)
- Tombol **Download PDF** untuk menyimpan hasil

---

## Sistem Proctoring & Integritas Akademik

### Tab / App Switch Detection
- Deteksi setiap kali jendela browser kehilangan fokus
- Peringatan muncul saat mencapai batas warning yang dikonfigurasi
- Auto-disqualify saat mencapai batas maksimum tab switch
- Alasan disqualifikasi dicatat beserta timestamp
- Terlihat di: live monitor, halaman attempts, attempt detail

### Inactivity Detection
- Monitor aktivitas mouse dan keyboard secara real-time
- Peringatan tampil N detik sebelum batas waktu inaktivitas
- Auto-disqualify jika tidak ada aktivitas hingga batas waktu habis

### Copy-Paste Detection
Jika diaktifkan per ujian (toggle **Detect Copy-Paste Activity**):
- Mendeteksi: `Ctrl+C`, `Ctrl+X`, `Ctrl+V`, klik kanan copy/paste
- Mencatat: waktu kejadian, jenis event (copy/cut/paste), **isi teks** yang di-copy/paste (max 2000 karakter)
- Log tersimpan dan bisa dilihat admin di halaman attempt detail
- Mahasiswa tidak mendapat notifikasi bahwa aktivitas ini dipantau

### DevTools / Inspect Block
Jika diaktifkan (toggle **Disable DevTools & Right-Click**):

| Diblokir | Shortcut |
|---|---|
| DevTools | F12, Ctrl+Shift+I |
| Console | Ctrl+Shift+J |
| Inspect Element | Ctrl+Shift+C |
| Klik kanan | Context menu |
| View Source | Ctrl+U |
| Print | Ctrl+P, Ctrl+Shift+P |
| Save | Ctrl+S |

### Device & IP Tracking
Otomatis dicatat saat attempt pertama kali dibuat (tidak membutuhkan konfigurasi):
- Alamat IP mahasiswa
- User Agent string lengkap
- Browser + versi (parsed)
- OS + versi (parsed)
- Tipe device: Desktop / Mobile / Tablet

Terlihat di: attempt detail (Proctoring Log) dan live monitor (kolom Device).

### Shuffle Options
Jika diaktifkan, urutan pilihan jawaban soal MC/MS diacak per mahasiswa — tiap mahasiswa mendapat urutan berbeda untuk soal yang sama.

### Enrollment Verification
Sebelum bisa memulai ujian, sistem memverifikasi bahwa NIM mahasiswa terdaftar di kelas (course offering) ujian tersebut.

---

## Sistem Grading & Penilaian

### Auto-Grading (Dijalankan Otomatis Saat Submit)

| Tipe Soal | Cara Penilaian |
|---|---|
| **Coding** | Kode dieksekusi lawan semua test cases (termasuk hidden). Skor = (test case lulus ÷ total) × bobot |
| **Multiple Choice** | Cocok dengan satu jawaban benar. Full poin atau 0 |
| **Multiple Select** | Semua pilihan benar harus dipilih dan tidak ada yang salah |
| **True / False** | Cocok dengan jawaban benar yang diset di soal |
| **Fill in Blank** | String comparison dengan jawaban benar |
| **Essay** | Tidak auto-graded — menunggu penilaian manual admin |

### Manual Grading (Essay)
- Admin bisa memberikan skor berapa saja (0 s/d bobot soal)
- Bisa tambahkan feedback teks untuk mahasiswa
- Total skor attempt diperbarui otomatis setelah disimpan

### Kalkulasi Skor

```
Skor per soal = (nilai yang diraih ÷ nilai maks soal) × bobot kesulitan

Total Skor = Σ (skor per soal)
Persentase = (Total Skor ÷ Maks Skor) × 100
```

### Bobot per Kesulitan
Dikonfigurasi per ujian:
- Soal Easy → **easy_weight** poin
- Soal Medium → **medium_weight** poin
- Soal Hard → **hard_weight** poin

---

## Deployment (VPS + Docker)

### Stack Produksi

| Container | Fungsi |
|---|---|
| **app** | Laravel (PHP-FPM 8.4) |
| **nginx** | Web server (port 80) |
| **mysql** | Database utama |
| **judge0** | Engine eksekusi kode |
| **judge0-worker** | Worker antrian eksekusi |
| **judge0-redis** | Queue untuk Judge0 |
| **judge0-postgres** | Database untuk Judge0 |

### Deploy Update

```bash
# Di server VPS
cd /root/fiqtest
git pull

# Rebuild image dan recreate container
docker compose up -d --build --force-recreate app

# Migration dijalankan otomatis oleh entrypoint.sh saat container start
```

### Konfigurasi Penting (`.env`)

```env
APP_KEY=base64:...
APP_URL=https://yourdomain.com

DB_DATABASE=coding_exam_platform
DB_USERNAME=appuser
DB_PASSWORD=your_db_password
DB_ROOT_PASSWORD=rootpassword

# Opsional: Judge0 via RapidAPI
JUDGE0_URL=https://judge0-ce.p.rapidapi.com
```

---

## Ringkasan Semua URL

### Admin Panel

| URL | Fitur |
|---|---|
| `/admin/login` | Login admin |
| `/admin/dashboard` | Dashboard + live feed ujian aktif |
| `/admin/academic-periods` | Kelola periode akademik / semester |
| `/admin/courses` | Kelola mata kuliah |
| `/admin/course-offerings` | Kelola kelas (offering) |
| `/admin/course-offerings/{id}/enrollment` | Kelola enrollment mahasiswa per kelas |
| `/admin/students` | Kelola data mahasiswa |
| `/admin/students/bulk/import` | Import massal mahasiswa (Excel/CSV) |
| `/admin/question-tags` | Kelola tag soal |
| `/admin/questions` | Bank soal |
| `/admin/questions/create` | Buat soal baru |
| `/admin/questions/bulk/import` | Import massal soal dari Excel |
| `/admin/questions/bulk/template` | Download template Excel soal |
| `/admin/questions/{id}` | Edit soal |
| `/admin/questions/{id}/preview` | Preview soal sebagai mahasiswa |
| `/admin/questions/{id}/stats` | Statistik performa soal |
| `/admin/exams` | Daftar semua ujian |
| `/admin/exams/create` | Buat ujian baru |
| `/admin/exams/{id}/edit` | Edit ujian |
| `/admin/exams/{id}/question-pool` | Lihat pool soal ujian |
| `/admin/exams/{id}/monitor` | Live monitor ujian real-time |
| `/admin/exams/{id}/attempts` | Daftar attempt + histogram distribusi nilai |
| `/admin/exams/{id}/attempts/{attemptId}` | Detail attempt mahasiswa + grading essay |
| `/admin/exams/{id}/export` | Export nilai ke Excel |
| `/admin/reports` | Pilihan laporan |
| `/admin/reports/offering/{id}` | Laporan per kelas |
| `/admin/reports/period/{id}` | Laporan per semester |
| `/admin/reports/student/{id}` | Laporan per mahasiswa |
| `/admin/settings/judge0` | Konfigurasi Judge0 API |
| `/admin/profile` | Profil & ganti password admin |

### Mahasiswa

| URL | Fitur |
|---|---|
| `/` | Beranda — daftar ujian aktif |
| `/exam/{slug}` | Halaman instruksi & verifikasi NIM |
| `/attempt/{id}/workspace` | Workspace ujian (jawab soal) |
| `/attempt/{id}/submitted` | Konfirmasi setelah submit |
| `/attempt/{id}/result` | Hasil ujian (jika Show Score Immediately aktif) |
| `/attempt/{id}/result/pdf` | Download hasil ujian sebagai PDF |
