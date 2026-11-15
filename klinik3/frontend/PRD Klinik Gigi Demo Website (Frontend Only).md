# PRD: Klinik Gigi Demo Website (Frontend Only)

Version 1.1 · Frontend / UI-UX only · Demo used to pitch online reservation to dental clinics

## 1. Overview

**Goal.** Build a polished, image-led, frontend-only website for a fictional dental clinic with a fast online reservation flow. A freelancer will show it to real dental and health clinic owners so they want a full version.

**Success looks like.** A clinic owner opens the link on a phone, finds the site trustworthy and good-looking, and finishes a demo booking in under one minute.

**Out of scope.** Backend, database, and admin panel (specified in a separate Backend & Admin PRD), patient login, payment, real email/SMS/WhatsApp sending, real patient data, analytics, blog, multi-language.

**Positioning.** Real clinics mostly take bookings by chatting on WhatsApp, not through their website. This demo must be image-led with very little text, and make booking on the site faster than chatting while still ending in WhatsApp, which patients already use. See section 12 for reference notes.

## 2. Instructions for the AI Agent

- Apply the `design-taste-frontend` skill (configured in `~/.gemini/config/skills.json`) for every UI and UX decision. If it conflicts with this PRD on visual taste, the skill wins. On functional requirements, this PRD wins.
- Read this whole PRD before writing code. If something is unclear, ask one short batch of questions (max 5, each with 2–4 options and your recommended default), then continue.
- Keep it simple. Do not add features, pages, or libraries that are not listed here.
- Token rules: short replies, no repeated code, edit only the files that need changes, do not re-read the whole project at each step.
- Follow the build order in section 9 and stop for my approval after the design system step.

## 3. Locked Decisions

- **Language:** Indonesian only. All visible text in Bahasa Indonesia.
- **Clinic:** fictional demo clinic with placeholder name and data.
- **Design approach:** image-first, minimal text. Large photos lead every section, and copy stays to one short headline and one or two short lines per block.
- **Mood:** calm and clinical. White base with soft blue/teal. Trustworthy, spacious, clean.
- **Pages:** Beranda (Home), Layanan (Services), Dokter (Doctors), Booking, Kontak (Contact).
- **Services:** basic only: scaling, fillings, extraction.
- **Booking:** WhatsApp-first. A fast 3-step flow that ends with a success screen showing the status “Menunggu verifikasi” and a WhatsApp verification button.
- **Stack:** React 18 + Vite + Tailwind CSS + Lucide Icons + React Router. Static build. The backend is built separately and connected later through a small API layer.

## 4. Placeholder Content

Everything below lives in ONE file, `src/data/clinic.js`, so it can be edited later without touching components. Treat all of it as placeholder.

**Clinic**

- Name: Klinik Gigi Senyum Sehat
- Tagline: one short Indonesian line about gentle, comfortable dental care
- Address: Jl. Contoh No. 12, Kota Contoh
- Hours: Senin–Sabtu 09.00–19.00, Minggu tutup
- WhatsApp: 6281234567890 · Email: halo@senyumsehat.example
- Flag: `showDemoBanner: true`

**Services** (name, short description, duration, starting price)

- Scaling Gigi: pembersihan karang gigi, 45 menit, mulai Rp 300.000
- Tambal Gigi: menambal gigi berlubang, 30 menit, mulai Rp 250.000
- Cabut Gigi: pencabutan gigi dengan bius lokal, 30 menit, mulai Rp 350.000

**Doctors** (name, title, years of experience, practice days)

- drg. Rina Pratama, Dokter Gigi Umum, 8 tahun, Senin / Rabu / Jumat
- drg. Budi Santoso, Dokter Gigi Umum, 12 tahun, Selasa / Kamis / Sabtu
- drg. Maya Lestari, Dokter Gigi Umum, 5 tahun, Senin / Kamis / Sabtu

**Other content**

- 3 very short testimonials (max 15 words each).
- 5 FAQ items (cara booking dan verifikasi WhatsApp, jam buka, pembayaran, jadwal ulang, apa yang perlu dibawa), answers max 2 short lines each, all in Indonesian.
- Image slots, all in `public/images` (see section 8): 1 hero (16:9), 3 service images (4:3, reused on Beranda and Layanan), 3 doctor portraits (3:4, reused on Beranda and Dokter), 6 clinic gallery images (mixed ratios), 1 closing banner (21:9), and 1 clinic exterior for Kontak. Total: 15 photos. Also 1 social preview image (1200×630) for link sharing, which can be a crop of the hero.

## 5. Pages and Requirements

**Global**

- Navbar: logo and name, links to the 5 pages, primary button “Buat Janji”. Mobile menu.
- Footer: clinic name, short description, hours, contact, page links.
- Floating WhatsApp button, bottom-right on every page, opening a general inquiry message: Halo Klinik Gigi Senyum Sehat, saya ingin bertanya.
- Demo ribbon, controlled by `showDemoBanner`.

**Beranda**

1. Hero: full-bleed photo, one headline (max 6 words), one short line, primary CTA “Buat Janji”, secondary CTA “Chat WhatsApp”.
2. Services: 3 large image cards with the service name over the photo and no description text, each linking to Layanan.
3. Clinic gallery: photo mosaic or horizontal scroller of 5–6 images (waiting room, treatment room, equipment, team). Captions max 3 words.
4. Doctors: 3 portrait cards with name and title only, linking to Dokter.
5. “Cara Booking”: 3 steps (Pilih jadwal, Isi data, Verifikasi via WhatsApp), each an icon plus 3 words.
6. Testimonials: 3 short quotes in a slim carousel.
7. FAQ: 5 short questions in an accordion, answers max 2 short lines.
8. Closing CTA: full-width photo banner with one line, the “Buat Janji” and “Chat WhatsApp” buttons, and opening hours and address on one line each.

**Layanan.** One-line intro, then 3 large alternating image sections (photo, service name, one short sentence, duration and starting price as small chips) with a button “Pesan Layanan Ini” that opens Booking with the service preselected.

**Dokter.** Grid of large portrait cards (3:4) with name and title, and experience and practice days as small chips. Button “Buat Janji” opens Booking with the doctor preselected.

**Booking.** Stepper, see section 6.

**Kontak.** Header photo of the clinic exterior, then address, WhatsApp button, email, hours table, and a static map placeholder (a styled box, no map API or keys). No contact form.

## 6. Booking Flow (the key feature)

Three steps with a visible progress indicator, built to finish in under 30 seconds on a phone. Choices use large image cards and chips instead of dropdowns. Selections are kept when moving between steps. Booking state lives in React state or context only (no browser storage).

1. **Layanan & Dokter.** Tap a service photo card, then a doctor portrait chip. Preselected when coming from the Layanan or Dokter pages.
2. **Tanggal & Jam.** Horizontal date strip for the next 14 days. Disable past dates, Sundays, and days the chosen doctor does not practice. Hour chips from 09.00 to 18.00, with some shown as “Penuh” (disabled). The full/available pattern is mock data, deterministic for a given doctor and date (no random changes on re-render).
3. **Data Diri & Ringkasan.** Fields: Nama lengkap (required), Nomor WhatsApp (required, valid Indonesian format 08xx or 628xx), Keluhan/catatan (optional, max 200 characters). A compact summary of the choices sits above the form, with “Ubah” links back to each step. Inline error messages in Indonesian. Small note: “Ini adalah website demo. Data tidak disimpan.” Button “Konfirmasi Janji” shows a 1-second loading state.

**Success screen**

- Confirmation message with a status badge “Menunggu verifikasi”, a generated booking code (format `KG-` plus 6 random alphanumeric characters), and the booking summary. The status never changes in this demo, because nothing is verified for real.
- A short “Langkah berikutnya” note: 1. Kirim pesan ke WhatsApp klinik. 2. Klinik memverifikasi dan mengonfirmasi jadwal. Below it, one line: “Jadwal ditahan sementara, segera kirim pesan agar tidak hangus.”
- Button “Verifikasi via WhatsApp”, the most prominent button on this screen: opens `https://wa.me/<number>?text=<encoded message>` in a new tab. Message template (Indonesian): Halo Klinik Gigi Senyum Sehat, saya {nama}. Saya ingin verifikasi janji temu dengan kode {kode}: Layanan {layanan}, Dokter {dokter}, Tanggal {tanggal}, Jam {jam}. Terima kasih.
- Buttons “Kembali ke Beranda” and “Buat Janji Lain” (resets the flow).

## 7. Design Direction

- Apply `design-taste-frontend`. Choose final colors, type, spacing, and radius through that skill, then define them as tokens in the Tailwind config before building any page.
- Mood: calm, clinical, trustworthy. White base, soft blue/teal primary, neutral grays, one clear CTA color, generous whitespace.
- Typography: max 2 font families with good Indonesian (Latin) support and a clear hierarchy.
- Icons: Lucide only. No emoji as icons.
- Avoid the generic AI look: no purple gradients, glassmorphism, gradient text, or repeated identical icon cards. No lorem ipsum or fake statistics.
- Motion: subtle only (150–250 ms) and respect `prefers-reduced-motion`.
- Mobile-first. Verify at 360, 768, and 1280 px. One primary CTA per section.
- Accessibility: WCAG AA contrast, visible focus states, labeled form fields, keyboard-operable stepper and accordion, alt text on images.

**Image-first rules**

- Large, high-quality photos lead every section: hero, services, doctors, clinic gallery, closing banner. Text supports the image, never the other way around.
- Copy limits: headlines up to 6 words, supporting lines up to 2 short lines, no paragraph longer than 2 lines on mobile.
- Photo style: bright, natural light, a real-feeling clinic interior, friendly dentists, relaxed patients. One consistent color grade that fits the soft blue/teal palette.
- Fixed aspect ratios (16:9 hero, 4:3 services, 3:4 doctors) and one corner radius for all images.
- Text over a photo only with a subtle dark overlay that keeps AA contrast.
- Avoid stock-photo clichés and cartoon illustrations.

## 8. Technical Requirements

- Stack as in section 3. Static build: `npm run build` outputs `dist`.
- Routes: `/`, `/layanan`, `/dokter`, `/booking`, `/kontak`.
- Suggested structure: `src/data/clinic.js`, `src/pages/`, `src/components/`, `src/context/BookingContext.jsx`, `src/lib/` (slots, validation, WhatsApp helpers).
- Add `public/_redirects` containing `/* /index.html 200` so page refreshes work on Netlify.
- No API keys, tracking, or remote image URLs. All data access goes through src/lib/api.js, which uses mock data by default and calls the real API when VITE\_API\_URL is set, so the backend can be connected later without touching the UI.
- Short README: how to run, how to edit `clinic.js`, how to deploy to Netlify or Cloudflare Pages.

**Images**

- Do not hotlink images. Store them in `public/images` as `.webp`, around 200 KB or less each. Load the hero eagerly and everything below the fold with `loading="lazy"`. Always set width and height to prevent layout shift.
- Before building pages, write `IMAGES.md` listing every image: filename, aspect ratio, and subject. I will download free-license stock photos to match it. Until then, show neutral placeholder blocks with the right aspect ratio and the filename as a label.

## 9. Build Order

1. Setup, design tokens, and shared components (Navbar, Footer, Button, ImageCard, Stepper) and the IMAGES.md shot list. Show a style-tile page and **wait for my approval**.
2. Beranda.
3. Layanan and Dokter.
4. Booking flow and success screen.
5. Kontak.
6. Polish: responsive checks, accessibility, demo ribbon, README.

After each step: a 2–3 line summary and how to run it. No long explanations.

## 10. Acceptance Checklist

- All 5 pages work at 360, 768, and 1280 px with no horizontal scrolling.
- Every page opens with a large image, and on mobile no text block is longer than about 2 lines (contact details excepted).
- A full booking can be completed from the navbar button in 3 steps, and validation errors are in Indonesian.
- Sundays, past dates, non-practice days, and “Penuh” slots cannot be selected.
- Preselection works from the Layanan and Dokter pages.
- The “Verifikasi via WhatsApp” button and the floating WhatsApp button open with the correct prefilled messages, and the success screen shows the status “Menunggu verifikasi”.
- All visible text is Indonesian, and all clinic content comes from `src/data/clinic.js`.
- Booking can be completed with the keyboard only, and focus is always visible.
- No broken or hotlinked images, and every image has alt text and explicit width and height.
- `npm run build` passes with no errors, and the app runs fully on mock data when VITE\_API\_URL is not set.
- `design-taste-frontend` was applied: no emoji icons, no lorem ipsum.

## 11. Future (Not Part of This Build)

Backend and admin panel (Laravel + Filament, specified in a separate Backend & Admin PRD), real notifications, payments, real map, and additional services.

**Planned booking flow (context only).** The patient submits the form and the booking is saved in the admin panel as “Menunggu verifikasi”. The patient verifies by sending the WhatsApp message that contains the booking code, and the admin then sets the status to “Terverifikasi”. The admin never retypes patient data.

## 12. Reference Notes (inspiration only)

Patterns seen on the homepage of a real multi-branch dental clinic, [Damessa](https://damessa.id), checked on 14 November 2026. Use them for structure only. Do not copy text, images, logos, or branding.

- The header has a prominent “Reservasi” button that opens WhatsApp with a prefilled message, next to a phone number. The homepage shows no multi-step booking form.
- The page runs: promo banner and short headline, a numbers strip, doctor specialties, insurance partner logos, a grid of service image cards, branch locations, reasons to choose the clinic, patient reviews, then a long text block.
- Each service is a clickable image card, and WhatsApp buttons (“Konsultasi Gratis”, “Reservasi”) repeat down the page.
- Weak spots to beat: long SEO text blocks, a very long page, and WhatsApp buttons that point to different numbers.
- Opportunity for this demo: keep the page short and image-led, and collect service, doctor, date, and time first, then send one structured WhatsApp message so the clinic needs no back-and-forth.
