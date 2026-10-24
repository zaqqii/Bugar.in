# Design System — Bugar.in

> Dokumen ini menjadi **sumber kebenaran visual** untuk seluruh tampilan Bugar.in.
> Update dokumen ini bersamaan dengan setiap perubahan desain yang signifikan.

---

## 1. Filosofi Desain

**Human & Editorial** — Bugar.in bukan SaaS dashboard. Ini adalah platform komunitas olahraga
yang hangat, aktif, dan personal. Desain harus mencerminkan semangat berolahraga bersama,
bukan cold data management tool.

### Prinsip Utama

| Prinsip | Artinya |
|---|---|
| **Human, bukan Admin** | Lebih banyak gambar, warna hidup, dan konteks manusiawi. Kurangi tabel angka kosong. |
| **Editorial, bukan Grid Seragam** | Variasikan ukuran kartu. Jangan setiap card punya padding & radius yang persis sama. |
| **Contextual, bukan Generic** | Setiap komponen punya konteks olahraga (ikon berlari, warna energi, foto komunitas). |
| **Micro-delight** | Tambahkan micro-animation kecil: pulse dots, hover lifts, streak flame, progress ticks. |
| **Whitespace Breathing Room** | Gunakan whitespace untuk memisahkan konten, bukan border atau divider berlebihan. |

### Anti-Pattern yang Dihindari (AI Slop)

- Kartu dengan **ukuran, padding, radius persis sama** di seluruh halaman
- Gradien **ungu-biru atau neon** yang berlebihan
- Aksen merah-merah-merah (rose) di mana-mana — ini warna penyedia komunitas, bukan user
- Terlalu banyak **angka besar tanpa konteks visual**
- Ilustrasi atau gambar placeholder generik tanpa makna
- Headline klise
- Border-left accent yang sama di semua nav item aktif

---

## 2. Color Palette

> Lihat `database.md` untuk tabel lengkap. Di bawah ini adalah panduan **penggunaan per konteks**.

### Token CSS Custom Properties

```css
:root {
  --color-primary:         #2563EB;  /* Royal Blue — CTA, nav aktif, link */
  --color-primary-light:   #EFF6FF;  /* Blue 50 — background pill aktif */
  --color-primary-mid:     #BFDBFE;  /* Blue 200 — border aktif, hover bg */
  --color-secondary:       #06B6D4;  /* Cyan — aksen sekunder, ikon progress */
  --color-secondary-light: #CFFAFE;  /* Cyan 100 — bg badge cyan */

  --color-background:      #F0F7FF;  /* Ice Blue — latar halaman */
  --color-surface:         #FFFFFF;  /* Pure White — card, modal */
  --color-surface-tint:    #F8FAFF;  /* Tint biru sangat pudar untuk variasi surface */

  --color-success:         #10B981;  /* Emerald — streak aktif, verifikasi */
  --color-success-light:   #D1FAE5;  /* Emerald 100 */
  --color-danger:          #EF4444;  /* Red — error, aksi destruktif */
  --color-accent-orange:   #F97316;  /* Orange — event badge, CTA jarang */
  --color-accent-orange-lt:#FFEDD5;  /* Orange light */
  --color-lime:            #84CC16;  /* Lime Energy — progress, poin bonus */

  --color-text-primary:    #0F172A;  /* Dark Slate — heading, teks utama */
  --color-text-secondary:  #64748B;  /* Muted Slate — subteks, caption */
  --color-border:          #E2E8F0;  /* Soft Grey — border card, divider */
}
```

### Panduan Pemakaian per Zona

| Zona | Warna Dominan | Aksen | Catatan |
|---|---|---|---|
| **Sidebar** | White surface + Blue tint | Royal Blue (active pill) | Tidak menggunakan rose/red sama sekali |
| **Header** | White + Ice Blue | Royal Blue (brand text) | Badge "Aktif" pakai Success Green, bukan rose |
| **Hero Banner** | White → Ice Blue gradient | Royal Blue heading | Bukan rose/red gradient |
| **Stat Cards** | White surface | Royal Blue value | Variasikan ikon, jangan uniform 4 kartu sama |
| **Community Cards** | White dengan cover image | Blue tag, Cyan badge | Wajib ada cover image atau ilustrasi |
| **Streak/Poin** | White dengan Cyan accents | Lime Energy (progress) | Success Green untuk verified |
| **BMI Calculator** | White surface | Success Green (normal) | Warna sesuai kategori medis |
| **Leaderboard** | White + Ice Blue podium | Royal Blue rank badge | Emas/Perak/Perunggu untuk top 3 |
| **Event Cards** | White dengan cover | Orange accent (CTA) | Bukan rose tombol |

---

## 3. Tipografi

### Font Stack

```
font-family: 'Plus Jakarta Sans', sans-serif;
```

Load dari Google Fonts. Selalu gunakan Plus Jakarta Sans untuk semua halaman user dan penyedia komunitas.

### Hierarki Teks

| Level | Ukuran | Weight | Penggunaan |
|---|---|---|---|
| Display | 2.5rem (40px) | 800 | Hero heading utama per halaman |
| Title | 1.75rem (28px) | 700 | Judul section |
| Heading | 1.25rem (20px) | 700 | Judul kartu, subheading |
| Body | 0.875rem (14px) | 400 | Teks konten utama |
| Caption | 0.75rem (12px) | 500 | Label, meta info |
| Label | 0.6875rem (11px) | 700 | Badge, tag, uppercase label |

---

## 4. Komponen Utama

### 4.1 Sidebar Navigation

**Aturan:**
- Background: putih dengan tint biru sangat ringan
- Item aktif: **pill/chip background** `#EFF6FF`, teks `#2563EB`, **TIDAK border-left stripe**
- Item hover: background `#F8FAFF`
- Ikon: rounded square container biru saat aktif
- Section label: uppercase `#94A3B8` size 11px

### 4.2 Stat Cards — "Activity Tiles"

**Aturan:**
- Jangan buat 4 kartu dengan ukuran persis sama
- Gunakan variasi: 1 kartu hero lebar + 3 tile kecil, atau layout asimetris
- Wajib ada visual pendukung (SVG illustration, ikon berwarna, atau mini chart)
- Angka besar hanya jika ada konteks visual (unit, ikon, tren)

### 4.3 Community Cards

**Aturan:**
- Wajib ada cover image (dari DB atau fallback gradient per sport_category)
- Tinggi cover minimum 140px
- Badge kategori di pojok kiri atas (bg hitam semi-transparan)
- Badge slot di pojok kanan atas (Success Green)
- Meta info dengan ikon kecil SVG, bukan emoji teks
- CTA: Royal Blue "Gabung", outline Royal Blue "Ikuti"

**Fallback cover gradient per sport_category:**
- `futsal`: #1e3a8a → #2563eb
- `basket`: #9a3412 → #ea580c
- `badminton`: #065f46 → #10b981
- `lari/running`: #4c1d95 → #7c3aed
- `renang`: #0c4a6e → #0ea5e9
- `yoga`: #701a75 → #d946ef
- `default`: #1e40af → #0891b2

### 4.4 Leaderboard Podium

**Aturan:**
- Rank 1: Gold (#F59E0B) border + elevated card
- Rank 2: Silver (#94A3B8) border
- Rank 3: Bronze (#D97706) border
- Avatar: Royal Blue to Cyan gradient (bukan merah)

### 4.5 Streak Tracker

**Aturan:**
- Hari aktif: Success Green (#10B981) dot
- Hari ini: Royal Blue background
- Streak flame 🔥 hanya jika streak > 3 hari

---

## 5. Spacing & Layout

```
Mobile  (<640px):  1 kolom, padding 16px
Tablet  (640-1024px): 2 kolom, padding 24px
Desktop (>1024px): 3-4 kolom, padding 32px
Sidebar: 260px fixed, main area flex-1
```

### Border Radius

| Konteks | Nilai |
|---|---|
| Card utama | rounded-2xl (16px) |
| Badge/pill | rounded-full |
| Button | rounded-xl (12px) |
| Input | rounded-xl (12px) |
| Avatar kecil | rounded-full atau rounded-lg |

---

## 6. Animasi & Micro-interactions

| Elemen | Animasi |
|---|---|
| Card hover | translateY(-2px), shadow bertambah, duration-200 |
| Button hover | brightness(1.05) atau background shift |
| Nav item hover | background fade, duration-150 |
| Badge "Aktif" | animate-pulse dot |
| Streak flame | animate-bounce jika streak > 7 hari |
| Progress bar fill | transition-all duration-700 |
| Sidebar slide | transition-transform duration-300 |

---

## 7. Panduan Warna per Role

| Role | Warna Dominan | Sidebar | Header |
|---|---|---|---|
| **User** | Royal Blue + Cyan | White/Ice Blue sidebar | White header |
| **Penyedia Komunitas** | Rose/Red | Rose tinted sidebar | Rose header |
| **Admin** | Slate dark | Dark sidebar | Dark header |

> **Penting:** Rose/red adalah identitas visual penyedia komunitas, BUKAN user.
> User menggunakan Royal Blue + Cyan sesuai brand Bugar.in.

---

## 8. Aturan untuk Agent AI

- Baca file ini sebelum membuat atau mengubah komponen UI, CSS, atau halaman Blade.
- Selalu gunakan palet warna dari bagian 2 di atas.
- Jangan membuat 4 kartu statistik dengan ukuran, padding, dan class yang persis sama.
- Jangan menggunakan rose/red untuk komponen user.
- Selalu sertakan cover image atau visual fallback pada community card.
- Gunakan font Plus Jakarta Sans (sudah di-load di layout).
- Pertahankan class `glass-card`, `glass-input`, `sidebar-glass` untuk konsistensi.
- Setiap perubahan UI signifikan: update file ini di commit yang sama.
