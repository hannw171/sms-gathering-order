# Design System Tokens: PBF Client Gathering Platform
> Version: 1.1.0  
> Target Surfaces: Mobile Web App (390×844px) & Big Screen Stage Display (1920×1080px 16:9)  
> Primary Typeface: Poppins (Google Fonts)

---

## 1. Typography System

Menggunakan **Poppins** sebagai single typeface. Karakter font Poppins yang geometric dengan x-height besar dan proporsi huruf lebar diimbangi dengan line-height dan letter-spacing yang lebih rapat pada ukuran display agar efisien di layar mobile.

### 1.1 Font Family & Weights
* **Primary Family:** `'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif`
* **Weights:**
  * Regular: `400` (Body copy, deskripsi produk, microcopy)
  * Medium: `500` (Label form, sub-item, metadata apotek)
  * SemiBold: `600` (Nama produk, tombol CTA, tab aktif, header sub-section)
  * Bold: `700` (Judul halaman, angka nominal harga, tier hadiah, rank card)
  * ExtraBold: `800` (Angka leaderboard, rank podium #1 #2 #3, display panggung)

---

### 1.2 Type Scale — Mobile Guest App (Viewport 390px)

| Token Name | Size (px / rem) | Line Height | Letter Spacing | Weight | Penggunaan Utama |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `text-display-sm` | `28px / 1.75rem` | `36px (1.28)` | `-0.02em` | `Bold (700)` | Header Order Success, Total Grand Akhir |
| `text-heading-xl` | `22px / 1.375rem` | `30px (1.36)` | `-0.015em` | `Bold (700)` | Judul Screen ("Pilih Apotek", "Keranjang") |
| `text-heading-lg` | `18px / 1.125rem` | `26px (1.44)` | `-0.01em` | `SemiBold (600)` | Nama Pabrik di Tab, Section Title, Total Per Brand |
| `text-heading-md` | `16px / 1.0rem` | `24px (1.50)` | `0em` | `SemiBold (600)` | Nama Produk Obat di Katalog |
| `text-body-lg` | `15px / 0.9375rem`| `22px (1.46)` | `0em` | `Regular (400)` | Teks deskripsi keranjang, konfirmasi modal |
| `text-body-md` | `14px / 0.875rem` | `20px (1.42)` | `0em` | `Regular / Medium` | Teks input form, satuan kemasan obat (UOM) |
| `text-body-sm` | `12px / 0.75rem` | `18px (1.50)` | `+0.01em` | `Medium (500)` | Keterangan progress bar reward, badge status |
| `text-caption` | `11px / 0.6875rem`| `16px (1.45)` | `+0.02em` | `Regular (400)` | Alamat apotek, disclaimer footer, timestamp |
| `text-price-lg` | `18px / 1.125rem` | `24px (1.33)` | `-0.01em` | `Bold (700)` | Nominal harga di Bottom Bar checkout |
| `text-price-md` | `15px / 0.9375rem`| `20px (1.33)` | `0em` | `Bold (700)` | Harga satuan produk di katalog |

---

### 1.3 Type Scale — Big Screen Projector Display (1920×1080px)

Didesain khusus dengan kontras tinggi agar terbaca jelas dari jarak 10–18 meter di ballroom hotel.

| Token Name | Size (px / rem) | Line Height | Letter Spacing | Weight | Penggunaan di Proyektor |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `text-stage-giant` | `72px / 4.5rem` | `80px` | `-0.03em` | `ExtraBold (800)`| Angka Countdown & Nominal Juara 1 Podium |
| `text-stage-title` | `44px / 2.75rem` | `52px` | `-0.02em` | `ExtraBold (800)`| Judul Besar Panggung "MOST SPENDER LEADERBOARD" |
| `text-stage-podium-name`| `26px / 1.625rem`| `34px` | `-0.01em` | `Bold (700)` | Nama Apotek Juara 1, 2, 3 |
| `text-stage-podium-val` | `32px / 2.0rem` | `40px` | `-0.01em` | `Bold (700)` | Total Transaksi Juara 1, 2, 3 (Glowing Gold) |
| `text-stage-list-name` | `20px / 1.25rem` | `28px` | `0em` | `SemiBold (600)` | Nama Apotek Rank 4–10 |
| `text-stage-list-val` | `22px / 1.375rem`| `30px` | `0em` | `Bold (700)` | Nominal Transaksi Rank 4–10 |
| `text-stage-ticker` | `18px / 1.125rem`| `24px` | `+0.01em` | `Medium (500)` | Teks berjalan (Running Marquee) di bawah |

---

## 2. Color System & Design Tokens

### 2.1 Primitive Palette

#### Deep Navy Blue (Brand & Primary Tones)
* `brand-primary`: `#1565C0` (Deep Navy Blue - Brand Utama)
* `brand-primary-dark`: `#0D47A1` (Dark Navy Accent / Hover / Pressed)
* `brand-primary-light-tint`: `#E3F2FD` (10% Soft Navy Tint untuk Background Card & Badge)
* `brand-primary-container`: `#1565C0`
* `brand-on-primary`: `#FFFFFF`
* `brand-on-primary-container`: `#DAE5FF`

#### Navy & Slate (Neutrals & Dark Foundation)
* `slate-50`: `#F8FAFC` (Mobile App Base Background)
* `slate-100`: `#F1F5F9` (Input & Card Inactive)
* `slate-200`: `#E2E8F0` (Borders & Dividers)
* `slate-300`: `#CBD5E1` (Placeholder Text)
* `slate-400`: `#94A3B8` (Muted Text)
* `slate-500`: `#64748B` (Secondary Body Text)
* `slate-700`: `#334155` (Dark Headings)
* `slate-800`: `#1E293B` (Card Surface on Stage)
* `slate-900`: `#0F172A` (Deep Text & Headers)
* `slate-950`: `#090D16` (Stage Projector Base Background)

#### Emerald (Reward, Success & Unlocked State)
* `emerald-50`: `#ECFDF5`
* `emerald-100`: `#D1FAE5`
* `emerald-500`: `#10B981` (Active Progress Bar & Unlocked Bonus)
* `emerald-600`: `#059669` (Order Success Accent)
* `emerald-700`: `#047857`

#### Amber / Gold (Gamifikasi, Podium 1 & Urgensi)
* `gold-100`: `#FEF3C7`
* `gold-300`: `#FCD34D`
* `gold-400`: `#FBBF24` (Rank 1 Podium Glow)
* `gold-500`: `#F59E0B` (Trophy & Crown Accent)
* `gold-600`: `#D97706`

#### Silver & Bronze (Podium 2 & 3)
* `silver-400`: `#94A3B8`
* `silver-200`: `#E2E8F0`
* `bronze-400`: `#CD7F32`
* `bronze-200`: `#F5D6BA`

#### Crimson (Alerts, Error & Cancel)
* `red-50`: `#FEF2F2`
* `red-200`: `#FECACA`
* `red-500`: `#EF4444` (Error validation & warning icons)
* `red-600`: `#DC2626`

---

### 2.2 Semantic Color Tokens (Light Mode — Mobile Guest)

```css
:root {
  /* Surface / Background */
  --color-bg-canvas: #F8FAFC;
  --color-bg-surface: #FFFFFF;
  --color-bg-surface-elevated: #FFFFFF;
  --color-bg-muted: #F1F5F9;

  /* Text & Icons */
  --color-text-primary: #0F172A;
  --color-text-secondary: #475569;
  --color-text-muted: #94A3B8;
  --color-text-inverse: #FFFFFF;

  /* Borders */
  --color-border-subtle: #E2E8F0;
  --color-border-strong: #CBD5E1;
  --color-border-focus: #1565C0;

  /* Actions & Interactions */
  --color-action-primary: #1565C0;
  --color-action-primary-hover: #0D47A1;
  --color-action-primary-fg: #FFFFFF;
  --color-action-light-tint: #E3F2FD;
  --color-action-container-text: #DAE5FF;

  /* Rewards & Progress */
  --color-reward-track: #E2E8F0;
  --color-reward-fill-start: #10B981;
  --color-reward-fill-end: #1565C0;
  --color-reward-surface: #ECFDF5;
  --color-reward-border: #A7F3D0;
  --color-reward-text: #065F46;

  /* Feedback */
  --color-feedback-danger-bg: #FEF2F2;
  --color-feedback-danger-border: #FECACA;
  --color-feedback-danger-fg: #DC2626;
  --color-feedback-warning-bg: #FFFBEB;
  --color-feedback-warning-border: #FDE68A;
  --color-feedback-warning-fg: #D97706;
}
```

---

### 2.3 Semantic Color Tokens (Dark Mode — Stage Projector Display)

```css
[data-theme="stage-display"] {
  /* Stage Surface */
  --color-stage-bg: #090D16;
  --color-stage-card-bg: rgba(30, 41, 59, 0.70);
  --color-stage-card-border: rgba(255, 255, 255, 0.10);
  --color-stage-card-hover: rgba(51, 65, 85, 0.80);

  /* Stage Text */
  --color-stage-text-primary: #F8FAFC;
  --color-stage-text-secondary: #94A3B8;
  --color-stage-text-gold: #FCD34D;

  /* Podium Accents */
  --color-stage-podium-1: #F59E0B;
  --color-stage-podium-1-glow: rgba(245, 158, 11, 0.35);
  --color-stage-podium-2: #CBD5E1;
  --color-stage-podium-2-glow: rgba(203, 213, 225, 0.25);
  --color-stage-podium-3: #D97706;
  --color-stage-podium-3-glow: rgba(217, 119, 6, 0.25);

  /* Stage Ticker */
  --color-stage-ticker-bg: rgba(15, 23, 42, 0.95);
  --color-stage-ticker-border: rgba(245, 158, 11, 0.25);
}
```

---

## 3. Spacing & Layout Tokens

Sistem grid dasar berbasis 4px / 8px:

| Token Name | Value (px / rem) | Penggunaan |
| :--- | :--- | :--- |
| `space-1` | `4px / 0.25rem` | Jarak mikro (antar icon dan badge text) |
| `space-2` | `8px / 0.5rem` | Padding stepper (`- [2] +`), gap inline badge |
| `space-3` | `12px / 0.75rem` | Padding card produk kecil, gap horizontal pill tabs |
| `space-4` | `16px / 1.0rem` | **Standard Base Padding** (margin tepi layar mobile, padding card) |
| `space-5` | `20px / 1.25rem` | Padding modal pop-up, internal padding keranjang belanja |
| `space-6` | `24px / 1.5rem` | Margin vertikal antar-section di mobile |
| `space-8` | `32px / 2.0rem` | Padding luar container stage display |
| `space-12` | `48px / 3.0rem` | Gap pilar podium 1, 2, dan 3 di panggung |
| `space-16` | `64px / 4.0rem` | Header vertical spacing pada panggung |

---

## 4. Border Radius Tokens

| Token Name | Radius (px) | Penggunaan |
| :--- | :--- | :--- |
| `radius-none` | `0px` | Tabel data di admin / garis pemisah |
| `radius-xs` | `4px` | Tag UOM kecil ("Box @ 10"), micro-badge |
| `radius-sm` | `6px` | Input form field, alert inline banner |
| `radius-md` | `8px` | Stepper Qty container, item card di keranjang |
| `radius-lg` | `12px` | Pill tab pabrik, tombol aksi CTA biasa |
| `radius-xl` | `16px` | Card produk katalog, banner tracker reward |
| `radius-2xl` | `20px` | Kartu modal konfirmasi apotek, rank highlight box |
| `radius-3xl` | `24px` | Bottom sheet drawer keranjang belanja |
| `radius-full` | `9999px` | Floating cart pill button, avatar lingkaran, badge peringkat |

---

## 5. Shadows & Elevation Tokens

### 5.1 Mobile Web Elevation
```css
/* Card produk standar */
--shadow-sm: 0 1px 2px 0 rgba(15, 23, 42, 0.05);

/* Active card / Hover state / Dropdown autocomplete */
--shadow-md: 0 4px 6px -1px rgba(15, 23, 42, 0.08), 0 2px 4px -2px rgba(15, 23, 42, 0.04);

/* Floating Bottom Bar (Sticky Cart button) */
--shadow-floating: 0 10px 25px -3px rgba(15, 23, 42, 0.15), 0 4px 6px -4px rgba(15, 23, 42, 0.10);

/* Modal Popup & Cart Bottom Sheet */
--shadow-drawer: 0 -8px 30px rgba(15, 23, 42, 0.12);
```

### 5.2 Big Screen Stage Elevation
```css
/* Efek kilau Podium Juara 1 (Emas) */
--glow-gold: 0 0 40px rgba(245, 158, 11, 0.40), 0 0 80px rgba(245, 158, 11, 0.15);

/* Efek kilau Kartu Apotek Naik Rank */
--glow-rank-up: 0 0 25px rgba(16, 185, 129, 0.35);

/* Glassmorphism Card Panggung */
--backdrop-glass: blur(16px);
```

---

## 6. Component-Specific Tokens (Gamifikasi & Ordering)

### 6.1 Dynamic Reward Progress Bar
* **Track Height:** `8px` (Mobile) / `12px` (Cart Review)
* **Track Background:** `var(--slate-200)`
* **Fill Gradient:** `linear-gradient(90deg, #10B981 0%, #1565C0 100%)`
* **Border Radius:** `var(--radius-full)`
* **Transition:** `width 600ms cubic-bezier(0.34, 1.56, 0.64, 1)` (bouncy smooth spring)

### 6.2 Quantity Stepper UI
* **Button Size:** `32×32px` (Touch target minimum `44×44px` via transparent padding)
* **Input Box Width:** `44px`
* **Typography:** `14px / SemiBold 600`
* **Border:** `1px solid var(--slate-200)`

### 6.3 Floating Bottom Checkout Pill
* **Height:** `56px`
* **Position:** Fixed `bottom: 16px`, `left: 16px`, `right: 16px`
* **Backdrop Blur:** `backdrop-filter: blur(12px)`
* **Background:** `rgba(15, 23, 42, 0.92)` (Dark slate luxury)
* **Text Color:** `#FFFFFF`

---

## 7. Motion & Transitions

* **Quick Feedback (Button press, tab switch):** `150ms ease-out`
* **Expanding Drawers / Modal:** `300ms cubic-bezier(0.16, 1, 0.3, 1)`
* **Leaderboard Rank Swap Animation:** `500ms cubic-bezier(0.4, 0, 0.2, 1)`
* **Live Ticker Marquee Speed:** Linear `25s` per loop

---

## 8. Layering & Z-Index System

Mencegah benturan layer antar komponen interaktif:

| Token Name | Z-Index Value | Elemen Pengguna |
| :--- | :--- | :--- |
| `z-base` | `0` | Card produk, list konten biasa |
| `z-sticky` | `20` | Sticky Top Bar apotek, Sticky horizontal tabs pabrik |
| `z-floating` | `40` | Floating Bottom Checkout Pill |
| `z-backdrop` | `50` | Overlay gelap saat keranjang / modal terbuka |
| `z-drawer` | `60` | Bottom Sheet keranjang belanja (`/cart`) |
| `z-modal` | `70` | Modal konfirmasi apotek, popup error koneksi |
| `z-toast` | `90` | Notifikasi alert singkat di pojok atas layar |

---

## 9. Iconography & Media Standards

### 9.1 Icon Tokens (Lucide React)
* **Icon Stroke Width:** `1.75px` (Konsisten dengan Poppins)
* **Ukuran Standar:**
  * `icon-xs`: `14×14px` (Indikator status panah, checkmark kecil)
  * `icon-sm`: `16×16px` (Icon dalam badge reward, pill category)
  * `icon-md`: `20×20px` (Icon keranjang belanja, stepper `-` dan `+`)
  * `icon-lg`: `24×24px` (Icon back navigation, search bar input)
  * `icon-xl`: `32×32px` (Icon trophy panggung, empty state illustration)

### 9.2 Media & Aspect Ratio
* **Product Thumbnail (Katalog):** Rasio `1:1` (Square, e.g. `120×120px` / responsive width).
* **Image Fit:** `object-contain` dengan background `var(--slate-100)` (standarisasi kemasan box obat vertikal/horizontal).
* **Principal Brand Logo:** Rasio `3:1` atau `16:9`, `object-contain`, max-height `28px`.

---

## 10. Form & Interactive State Tokens

* **Minimum Touch Target:** `44×44px` (Standar ergonomi layar sentuh mobile).
* **Focus Ring:** `outline: none; box-shadow: 0 0 0 3px rgba(21, 101, 192, 0.25);`
* **Disabled State:** `opacity: 0.45; cursor: not-allowed; pointer-events: none;`
* **Input Active/Default:** 
  * Background: `#FFFFFF`
  * Border: `1px solid var(--slate-200)`
  * Height: `48px`

---

## 11. Component Tokens: Badges & Gamification Tags

### 11.1 Status Order Badge
* **Submitted (Pending):** Background `var(--gold-100)`, Text `var(--gold-600)`, Border `1px solid var(--gold-300)`.
* **Verified (Leaderboard Sah):** Background `var(--emerald-50)`, Text `var(--emerald-700)`, Border `1px solid var(--emerald-200)`.
* **Cancelled:** Background `var(--red-50)`, Text `var(--red-600)`, Border `1px solid var(--red-200)`.

### 11.2 Rank Chips (Mobile Web)
* **Top 3 Highlight:** Background `linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%)`, Text `#B45309` (Bold 700), Border `1px solid #F59E0B`.
* **Rank 4+ (Regular):** Background `var(--slate-100)`, Text `var(--slate-700)` (SemiBold 600), Border `1px solid var(--slate-200)`.

---

## 12. Layout Containment (Desktop Fallback)

Jika URL mobile web dibuka di browser laptop atau tablet:
* **Max Width Wrapper:** `max-w-md` (`448px`)
* **Horizontal Alignment:** `mx-auto` (Center di tengah layar)
* **Canvas Background:** `#0F172A` (Desktop latar gelap dengan frame aplikasi mobile putih di tengah).

---

## 13. Tailwind CSS Configuration (`tailwind.config.js`)

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.jsx",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Poppins', 'sans-serif'],
      },
      colors: {
        brand: {
          DEFAULT: '#1565C0',
          dark: '#0D47A1',
          light: '#E3F2FD',
          container: '#1565C0',
          'on-container': '#DAE5FF',
        },
        gold: {
          100: '#FEF3C7',
          300: '#FCD34D',
          400: '#FBBF24',
          500: '#F59E0B',
          600: '#D97706',
        },
        stage: {
          bg: '#090D16',
          card: 'rgba(30, 41, 59, 0.70)',
        }
      },
      borderRadius: {
        '2xl': '20px',
        '3xl': '24px',
      },
      boxShadow: {
        'floating': '0 10px 25px -3px rgba(15, 23, 42, 0.15)',
        'drawer': '0 -8px 30px rgba(15, 23, 42, 0.12)',
        'glow-gold': '0 0 40px rgba(245, 158, 11, 0.40)',
        'glow-rank-up': '0 0 25px rgba(16, 185, 129, 0.35)',
      },
      zIndex: {
        'sticky': '20',
        'floating': '40',
        'backdrop': '50',
        'drawer': '60',
        'modal': '70',
        'toast': '90',
      }
    },
  },
  plugins: [],
}
```
