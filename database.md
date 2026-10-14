# Database — Bugar.in

DBMS: PostgreSQL (Neon). Framework: Laravel 13 (PHP 8.3).
Ringkasan schema per 2026-10-14. **Sumber kebenaran tetap `database/migrations/`**; kalau dokumen ini beda dengan migration, migration yang menang.

## Design System — Color Palette Bugar.in

Gunakan palet ini secara konsisten pada halaman publik, halaman pengguna, dashboard pemilik komunitas, dan dashboard admin. Warna UI ini tidak mengubah schema database.

### Warna brand

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `primary` | Royal Blue | `#2563EB` | Warna utama brand, tombol CTA, navigasi aktif, dan link |
| `secondary` | Cyan | `#06B6D4` | Aksen sekunder, ikon/elemen aktif, dan visual pendukung |
| `gradient-signature` | Royal Blue → Cyan | `#2563EB` → `#06B6D4` | Gradien untuk elemen brand atau banner unggulan; gunakan seperlunya |

### Warna pelengkap

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `background` | Ice Blue | `#F0F7FF` | Latar belakang halaman |
| `surface` | Pure White | `#FFFFFF` | Card, modal, pop-up, dan permukaan form |
| `danger` | Alert Red | `#EF4444` | Error, aksi berbahaya, dan status urgent |
| `accent-orange` | Orange | `#F97316` | Sorotan event atau CTA sekunder yang jarang digunakan |
| `accent-orange-light` | Soft Orange | `#FFEDD5` | Latar badge atau informasi dengan aksen oranye |

### Warna semantik dan status

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `success` | Success Green | `#10B981` | Goal tercapai, streak aktif, pendaftaran berhasil, dan status sukses |
| `success-light` | Success Light | `#D1FAE5` | Latar badge sukses, tag status berhasil, dan notifikasi positif |

### Warna netral dan tipografi

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `text-primary` | Dark Slate | `#0F172A` | Heading, teks utama, dan ikon penting |
| `text-secondary` | Muted Slate | `#64748B` | Subteks, caption, placeholder, dan ikon nonaktif |
| `border` | Soft Grey | `#E2E8F0` | Border card, divider, dan pemisah daftar |

### Aksen kebugaran

| Token | Nama | Hex | Penggunaan |
|---|---|---|---|
| `fitness-highlight` | Lime Energy | `#84CC16` | Progress aktivitas/kalori, badge level keaktifan, dan poin bonus |

### Aturan penggunaan

- Royal Blue adalah warna identitas utama; Cyan menjadi aksen pendamping.
- Gunakan gradien biru-ke-cyan secara terbatas agar tidak mengganggu keterbacaan.
- Gunakan Success Green hanya untuk feedback/status positif, bukan sebagai warna utama tombol di seluruh halaman.
- Lime Energy untuk elemen highlight, bukan teks kecil.
- Gunakan Dark Slate untuk teks utama dan Muted Slate untuk teks sekunder.
- Pastikan kontras teks dan background tetap terbaca; tombol Royal Blue sebaiknya memakai teks putih.
- Warna BMI harus mengikuti kategori dan hasil perhitungan yang benar; jangan menyimpulkan kategori medis hanya dari warna.

## Aturan untuk agent AI

- Baca file ini sebelum membuat atau mengubah migration, model, query, seeder, atau controller yang menyentuh database.
- Pakai nama tabel, kolom, dan nilai enum persis seperti di sini. Jangan mengarang kolom atau nilai baru.
- Perlu kolom/tabel baru? Buat migration baru (`php artisan make:migration`), jangan edit migration lama yang sudah dijalankan di Neon, lalu update dokumen ini di commit yang sama.
- Baca bagian "Catatan desain" sebelum membangun fitur absensi, poin, streak, atau notifikasi.

## Konvensi umum

- Primary key: `id` (`bigserial`), kecuali tabel sistem Laravel tertentu.
- `created_at` / `updated_at`: `timestamp`, nullable (default Laravel).
- Soft delete (`deleted_at`) hanya di: `communities`, `events`, `schedules`, `contents`. Model terkait harus memakai trait `SoftDeletes`.
- Kolom bertipe "enum" disimpan sebagai `varchar(255)` + `CHECK` constraint (bukan tipe enum Postgres). Nilai yang diizinkan ada di bagian "Nilai enum".
- Kolom polymorphic memakai pasangan `*_type` + `*_id` dan **tidak punya foreign key**:
  `participants.participatable_*`, `points_histories.pointable_*`, `reports.reportable_*`, `notifications.notifiable_*`, `audit_logs.auditable_*`.
  Perhatikan ejaan `participatable` (bukan `participantable`).
- Tiga role pengguna: `user`, `community_owner` (penyedia komunitas), `admin`.

## Peta relasi

```
users 1─N communities                    (communities.owner_id)
users M─N communities  via community_followers   (follow)
users M─N communities  via community_members     (bergabung, punya status)
users M─N communities  via community_ratings     (rating + komentar, 1 per user per komunitas)
communities 1─N schedules
communities 1─N events
users 1─N participants ──(participatable_type/id)──> schedules | events
participants 1─1 attendances             (attendances.marked_by -> users)
users 1─N streaks                        (satu baris per sport_category)
users 1─N points_histories               (pointable_* -> sumber poin)
users 1─N reports                        (reporter_id); reportable_* -> community | event | user; handled_by -> users (admin)
users 1─N notifications
users 1─N audit_logs                     (auditable_* -> objek yang diubah)
users 1─N contents                       (created_by)
users 1─N bmi_records
```

## Nilai enum (CHECK constraint)

| Tabel.kolom | Nilai yang diizinkan |
|---|---|
| `users.role` | `user`, `community_owner`, `admin` |
| `users.gender` | `male`, `female`, `other` |
| `communities.verification_status` | `pending`, `verified`, `rejected`, `suspended` |
| `community_members.status` | `pending`, `active`, `banned`, `left` |
| `schedules.day_of_week` | `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday` |
| `schedules.status` | `open`, `almost_full`, `full`, `cancelled`, `completed` |
| `events.type` | `fun_match`, `tournament`, `gathering`, `workshop`, `other` |
| `events.status` | `draft`, `published`, `registration_closed`, `ongoing`, `completed`, `cancelled` |
| `participants.status` | `registered`, `waiting_list`, `cancelled`, `attended`, `no_show` |
| `attendances.method` | `qr_scan`, `manual`, `auto` |
| `points_histories.source` | `attendance`, `streak_bonus`, `event_participation`, `referral`, `manual`, `other` |
| `reports.status` | `pending`, `under_review`, `resolved`, `dismissed` |
| `contents.type` | `article`, `announcement`, `tip`, `news` |
| `contents.status` | `draft`, `published`, `archived` |
| `bmi_records.category` | `underweight`, `normal`, `overweight`, `obese` |

## Tabel domain

### users
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| name | varchar(255) | wajib |
| username | varchar(255) | opsional, unik |
| email | varchar(255) | wajib, unik |
| email_verified_at | timestamp | opsional |
| password | varchar(255) | wajib, hash (jangan di-hash dua kali jika model memakai cast `hashed`) |
| role | varchar(255) | wajib, default `user` |
| avatar | varchar(255) | opsional, path file |
| bio | text | opsional |
| phone | varchar(255) | opsional |
| date_of_birth | date | opsional |
| gender | varchar(255) | opsional |
| height_cm | numeric(5,2) | opsional |
| weight_kg | numeric(5,2) | opsional |
| total_points | integer | wajib, default 0 (counter denormalisasi) |
| is_active | boolean | wajib, default true |
| remember_token | varchar(100) | opsional |
| created_at, updated_at | timestamp | |

### communities
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| owner_id | bigint | wajib, FK `users` |
| name | varchar(255) | wajib |
| slug | varchar(255) | wajib, unik |
| description | text | opsional |
| logo, cover_image | varchar(255) | opsional, path file |
| sport_category | varchar(255) | wajib |
| location, city, province | varchar(255) | opsional |
| contact_email, contact_phone | varchar(255) | opsional |
| instagram, website | varchar(255) | opsional |
| member_limit | integer | opsional |
| followers_count | integer | wajib, default 0 (counter) |
| members_count | integer | wajib, default 0 (counter) |
| average_rating | numeric(3,2) | wajib, default 0 (counter) |
| ratings_count | integer | wajib, default 0 (counter) |
| verification_status | varchar(255) | wajib, default `pending` |
| rejection_reason | text | opsional |
| verified_at | timestamp | opsional |
| is_active | boolean | wajib, default true |
| is_public | boolean | wajib, default true |
| created_at, updated_at, deleted_at | timestamp | soft delete |

### community_followers
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` |
| community_id | bigint | wajib, FK `communities` |
| followed_at | timestamp | wajib, default now |

Unik: (`user_id`, `community_id`). Tidak punya `created_at`/`updated_at`.

### community_members
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` |
| community_id | bigint | wajib, FK `communities` |
| status | varchar(255) | wajib, default `active` |
| joined_at | timestamp | wajib, default now |
| left_at | timestamp | opsional |

Unik: (`user_id`, `community_id`). Tidak punya `created_at`/`updated_at`. Follow (`community_followers`) dan bergabung (`community_members`) adalah dua konsep berbeda.

### community_ratings
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| community_id | bigint | wajib, FK `communities` |
| user_id | bigint | wajib, FK `users` |
| rating | smallint | wajib (tidak ada CHECK 1–5 di DB, validasi di aplikasi) |
| comment | text | opsional |
| is_visible | boolean | wajib, default true |
| created_at, updated_at | timestamp | |

Unik: (`community_id`, `user_id`).

### schedules
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| community_id | bigint | wajib, FK `communities` |
| title | varchar(255) | wajib |
| description | text | opsional |
| location, maps_url | varchar(255) | opsional |
| day_of_week | varchar(255) | wajib |
| start_time, end_time | time | wajib |
| capacity | integer | opsional |
| status | varchar(255) | wajib, default `open` |
| is_recurring | boolean | wajib, default true |
| is_active | boolean | wajib, default true |
| created_at, updated_at, deleted_at | timestamp | soft delete |

Tidak ada kolom tanggal; jadwal hanya hari + jam.

### events
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| community_id | bigint | wajib, FK `communities` |
| title | varchar(255) | wajib |
| slug | varchar(255) | wajib, unik |
| description | text | opsional |
| cover_image | varchar(255) | opsional |
| type | varchar(255) | wajib, default `other` |
| location | varchar(255) | wajib |
| maps_url | varchar(255) | opsional |
| event_date | date | wajib |
| start_time | time | wajib |
| end_time | time | opsional |
| capacity | integer | opsional |
| registration_fee | numeric(10,2) | wajib, default 0 |
| requirements, prizes | text | opsional |
| registration_open_at, registration_close_at | timestamp | opsional |
| status | varchar(255) | wajib, default `draft` |
| participants_count | integer | wajib, default 0 (counter) |
| points_reward | integer | wajib, default 0 |
| is_active | boolean | wajib, default true |
| created_at, updated_at, deleted_at | timestamp | soft delete |

### participants
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` |
| participatable_type | varchar(255) | wajib, jenis target (schedule / event) |
| participatable_id | bigint | wajib, id target |
| status | varchar(255) | wajib, default `registered` |
| qr_code | varchar(255) | opsional, unik |
| notes | varchar(255) | opsional |
| registered_at | timestamp | wajib, default now |
| cancelled_at | timestamp | opsional |
| created_at, updated_at | timestamp | |

Unik `unique_participant`: (`user_id`, `participatable_id`, `participatable_type`). Index: (`participatable_type`, `participatable_id`).

### attendances
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| participant_id | bigint | wajib, **unik**, FK `participants` |
| marked_by | bigint | opsional, FK `users` (owner yang mencatat) |
| check_in_at, check_out_at | timestamp | opsional |
| method | varchar(255) | wajib, default `manual` |
| notes | text | opsional |
| created_at, updated_at | timestamp | |

### streaks
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` |
| sport_category | varchar(255) | wajib |
| current_streak | integer | wajib, default 0 |
| longest_streak | integer | wajib, default 0 |
| last_activity_date | date | opsional |
| streak_started_at | date | opsional |
| created_at, updated_at | timestamp | |

Unik: (`user_id`, `sport_category`).

### points_histories
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` |
| points | integer | wajib |
| source | varchar(255) | wajib, default `attendance` |
| pointable_type, pointable_id | varchar(255), bigint | wajib, objek pemicu poin |
| description | text | opsional |
| balance_after | integer | wajib, saldo poin setelah transaksi |
| created_at, updated_at | timestamp | |

### reports
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| reporter_id | bigint | wajib, FK `users` |
| reportable_type, reportable_id | varchar(255), bigint | wajib, objek yang dilaporkan (community / event / user) |
| reason | varchar(255) | wajib |
| description | text | opsional |
| evidence | varchar(255) | opsional, path file |
| status | varchar(255) | wajib, default `pending` |
| handled_by | bigint | opsional, FK `users` (admin) |
| admin_notes | text | opsional |
| handled_at | timestamp | opsional |
| created_at, updated_at | timestamp | |

### notifications
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` (penerima) |
| type | varchar(255) | wajib |
| title | varchar(255) | wajib |
| body | text | opsional |
| data | json | opsional |
| notifiable_type, notifiable_id | varchar(255), bigint | wajib, objek terkait |
| read_at | timestamp | opsional |
| created_at, updated_at | timestamp | |

Tabel ini custom, bukan format bawaan Laravel Notifications (channel `database` memakai `id` uuid dan skema berbeda). Jangan memakai `$user->notify()` ke channel database tanpa menyesuaikan.

### audit_logs
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | opsional, FK `users` (pelaku) |
| action | varchar(255) | wajib |
| auditable_type, auditable_id | varchar(255), bigint | wajib |
| old_values, new_values | json | opsional |
| ip_address | varchar(45) | opsional |
| user_agent | varchar(255) | opsional |
| created_at, updated_at | timestamp | |

### contents
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| created_by | bigint | wajib, FK `users` |
| title | varchar(255) | wajib |
| slug | varchar(255) | wajib, unik |
| excerpt | text | opsional |
| body | text | wajib |
| cover_image | varchar(255) | opsional |
| type | varchar(255) | wajib, default `article` |
| tags | json | opsional |
| status | varchar(255) | wajib, default `draft` |
| published_at | timestamp | opsional |
| views_count | integer | wajib, default 0 |
| created_at, updated_at, deleted_at | timestamp | soft delete |

### bmi_records
| Kolom | Tipe | Catatan |
|---|---|---|
| id | bigserial | PK |
| user_id | bigint | wajib, FK `users` |
| height_cm, weight_kg, bmi | numeric(5,2) | wajib |
| category | varchar(255) | opsional |
| notes | text | opsional |
| recorded_date | date | wajib |
| created_at, updated_at | timestamp | |

## Tabel sistem Laravel (jangan diubah manual)

`migrations`, `sessions` (`user_id` nullable, di-index), `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens` (PK `email`).
`.env` memakai driver `database` untuk session, cache, dan queue, jadi tabel-tabel ini wajib ada.

## Foreign key dan perilaku hapus

| Kolom | Referensi | ON DELETE |
|---|---|---|
| attendances.participant_id | participants | CASCADE |
| attendances.marked_by | users | SET NULL |
| audit_logs.user_id | users | SET NULL |
| bmi_records.user_id | users | CASCADE |
| communities.owner_id | users | CASCADE |
| community_followers.user_id / community_id | users / communities | CASCADE |
| community_members.user_id / community_id | users / communities | CASCADE |
| community_ratings.user_id / community_id | users / communities | CASCADE |
| contents.created_by | users | CASCADE |
| events.community_id | communities | CASCADE |
| notifications.user_id | users | CASCADE |
| participants.user_id | users | CASCADE |
| points_histories.user_id | users | CASCADE |
| reports.reporter_id | users | CASCADE |
| reports.handled_by | users | SET NULL |
| schedules.community_id | communities | CASCADE |
| streaks.user_id | users | CASCADE |

CASCADE hanya berjalan pada hard delete. Soft delete (`deleted_at`) tidak memicu cascade, jadi data turunan tetap ada.

## Catatan desain (baca sebelum membangun fitur terkait)

1. **Absensi jadwal mingguan belum bisa tercatat per pertemuan.** `schedules` tidak punya tanggal (hanya `day_of_week` + jam), `participants` unik per (user, target), dan `attendances.participant_id` unik. Akibatnya satu pendaftaran hanya bisa punya satu catatan kehadiran. Untuk event sekali jalan ini aman. Untuk jadwal rutin, streak dan poin per minggu butuh tambahan, misalnya tabel sesi per tanggal atau kolom tanggal sesi di `attendances` tanpa unik. **Belum diputuskan.**
2. **Nilai `*_type` pada kolom polymorphic belum distandarkan.** Disarankan memakai morph map (`Relation::enforceMorphMap([...])`) supaya nilainya pendek dan konsisten (`schedule`, `event`, `community`, `user`, ...), bukan nama class lengkap. Gunakan nama relasi `participatable` pada `morphTo`/`morphMany`.
3. **Counter denormalisasi tidak diurus trigger database:** `communities.followers_count`, `members_count`, `average_rating`, `ratings_count`, `events.participants_count`, `users.total_points`. Update di transaksi yang sama dengan aksi terkait (follow, join, rating, daftar event, poin), atau hitung dengan `withCount`. Kalau tidak, dashboard akan menampilkan angka yang basi.
4. **Slug unik juga berlaku untuk baris yang sudah di-soft-delete** (`communities.slug`, `events.slug`, `contents.slug`). Saat membuat slug, cek termasuk `withTrashed()` dan beri akhiran jika bentrok.
5. **Satu owner bisa punya banyak komunitas** (tidak ada UNIQUE di `communities.owner_id`). Dashboard penyedia komunitas harus jelas komunitas mana yang sedang dipilih.
6. **Komunitas baru berstatus `pending`** sampai admin memverifikasi. Tampilan dan fitur penyedia sebaiknya menyesuaikan status ini.
7. `events.registration_fee` ada di schema, tetapi sistem pembayaran di luar scope proyek. Perlakukan sebagai informasi tampilan, default 0.
8. `community_ratings.rating` tidak dibatasi di DB, validasi 1–5 dilakukan di aplikasi.

## Cara mengubah schema

- Migration baru lewat `php artisan make:migration`. `CHECK` constraint dibuat dengan `DB::statement(...)` karena Schema Builder tidak mendukungnya.
- Jalankan `php artisan migrate` dengan connection string **direct** Neon (host tanpa `-pooler`). Connection string pooler bisa memicu error `SQLSTATE[25P02]` pada DDL berurutan.
- Jangan menaruh kredensial (`DB_URL`, `APP_KEY`) di file markdown atau repo. Gunakan `.env` (di-ignore git) dan `.env.example` tanpa nilai rahasia.

## Belum ada di schema

- Integrasi Strava (direncanakan di proposal): tabel penyimpan token OAuth belum dibuat.
