# PRD: Klinik Gigi Demo, Backend & Admin (Laravel + Filament)

Version 1.0 · Companion to the Frontend PRD · Full-stack demo used to pitch to dental clinics

## 1. Overview

**Goal.** Build the backend API and admin panel behind the existing frontend demo, so a clinic owner can see the whole flow: a patient books on the website, the booking lands in the admin panel as unverified, the patient verifies through WhatsApp, and the admin verifies it with one click.

**Success looks like.** An owner books on a phone, sees the booking appear in the admin panel within seconds, verifies it, and sees the slot become locked on the website.

**Out of scope.** Patient accounts or login, payments, WhatsApp API integration (messages are sent manually through `wa.me` links), email or SMS notifications, multiple clinics, medical records, real patient data.

## 2. Instructions for the AI Agent

- Read the Frontend PRD first. The frontend already exists. Do not redesign it. Its only integration point is `src/lib/api.js`, which switches from mock data to this API when `VITE_API_URL` is set.
- Read this whole PRD before writing code. If something is unclear, ask one short batch of questions (max 5, each with 2–4 options and your recommended default), then continue.
- Keep it simple. Do not add features, roles, packages, or pages that are not listed here.
- Token rules: short replies, no repeated code, edit only the files that need changes, do not re-read the whole project at each step.
- Follow the build order in section 10 and stop for my approval after step 1.

## 3. Locked Decisions

- **Backend:** latest stable Laravel with MySQL. Time zone `Asia/Jakarta`.
- **Admin:** Filament at `/admin`, one admin user, all labels in Indonesian.
- **API:** JSON REST under `/api`, no patient authentication.
- **Verification:** manual. The patient sends the WhatsApp message containing the booking code, and the admin clicks “Verifikasi”.
- **Slot hold:** a pending booking holds its slot for 2 hours, set by the `hold_minutes` setting (default 120).
- **Admin features:** booking list with verification, dashboard, doctor schedules, services and prices, doctor profiles, clinic settings.
- **Deployment:** one VPS. React build at `/`, API at `/api`, admin at `/admin`, all on the same domain.
- **Demo data:** fictional only, reset every night.

## 4. Data Model

| Table | Fields |
| --- | --- |
| `services` | name, slug, description (short), duration\_minutes, starting\_price (IDR, integer), image\_path, is\_active, sort\_order |
| `doctors` | name, title, years\_experience, photo\_path, is\_active |
| `doctor_schedules` | doctor\_id, weekday (1 = Monday to 6 = Saturday), start\_time, end\_time |
| `clinic_settings` | single row: clinic\_name, tagline, address, hours\_text, whatsapp\_number, email, hold\_minutes, slot\_minutes (default 60) |
| `bookings` | code (unique), service\_id, doctor\_id, date, start\_time, patient\_name, patient\_whatsapp, notes (max 200), status, slot\_key (nullable, unique), expires\_at, verified\_at, timestamps |

**Statuses** (`status` enum): `pending` (Menunggu verifikasi), `verified` (Terverifikasi), `expired` (Kedaluwarsa), `cancelled` (Dibatalkan), `completed` (Selesai).

Slots start every `slot_minutes` inside a doctor’s schedule window for that weekday. The clinic is closed on Sundays.

**Double-booking rule.** `slot_key` holds the doctor, date, and start time as one string only while a booking is `pending` or `verified`, and is set to null when it becomes expired, cancelled, or completed. A unique index on `slot_key` makes the database reject a second active booking for the same slot (MySQL allows many nulls). Creating a booking runs inside a transaction and returns 409 when the unique index rejects it.

## 5. Status Flow

1. Patient submits the form. Status `pending`, `expires_at` = now + `hold_minutes`, slot held.
2. Patient sends the WhatsApp message containing the booking code.
3. Admin finds the booking by code and clicks “Verifikasi”. Status `verified`, `verified_at` set, `expires_at` cleared, slot stays locked.
4. If `expires_at` passes while the booking is still `pending`, a scheduled job sets `expired` and releases the slot.
5. Admin can click “Batalkan” on a pending or verified booking. Status `cancelled`, slot released.
6. After the visit, admin clicks “Selesai” on a verified booking. Status `completed`.

Allowed transitions only: pending to verified, expired, or cancelled; verified to cancelled or completed.

## 6. Public API

| Method and path | Purpose |
| --- | --- |
| `GET /api/clinic` | Clinic name, address, hours, WhatsApp number, `hold_minutes` |
| `GET /api/services` | Active services in `sort_order` |
| `GET /api/doctors` | Active doctors with their practice weekdays |
| `GET /api/availability?doctor_id=&date=` | Start times for that day, each with `available` true or false |
| `POST /api/bookings` | Create a pending booking |

**`POST /api/bookings` body:** `service_id`, `doctor_id`, `date` (YYYY-MM-DD), `time` (HH:MM), `name`, `whatsapp`, `notes` (optional).

- **201:** `code`, `status`, `expires_at`, and a `booking` object (service name, doctor name, date, time, patient name).
- **422:** validation errors in Indonesian.
- **409:** slot already taken, with the message “Jam ini baru saja dipesan, silakan pilih jam lain.”
- **429:** too many requests.

**Validation.** Name required (max 100). WhatsApp required, accepts 08xx or 628xx, stored as 628xx. Notes max 200. Date within the next 14 days, not a Sunday, and a weekday the doctor practices. Time must be a valid slot. Service and doctor must be active.

**Booking code.** `KG-` plus 6 uppercase alphanumeric characters, generated on the server (skip ambiguous characters such as O, 0, I, 1), unique, retry on collision.

**Content split.** Hero text, testimonials, and FAQ stay in the frontend `src/data/clinic.js`. Clinic info, services, doctors, and prices come from the API.

No public endpoint returns patient data, and there is no “get booking by code” endpoint.

## 7. Admin Panel (Filament)

- **Login.** One admin user created by a seeder from the env values `ADMIN_EMAIL` and `ADMIN_PASSWORD`.
- **Dashboard.** Stat cards: “Booking hari ini”, “Menunggu verifikasi”, “Terverifikasi minggu ini”. Under them, a table of the 5 newest pending bookings with remaining hold time.
- **Booking.** Columns: Kode, Pasien, WhatsApp, Layanan, Dokter, Tanggal, Jam, Status (colored badge), Sisa waktu (pending only). Search by code and patient name. Filters: status, date, doctor. Default sort: newest first. Patient data is read-only. Row actions, each with a confirmation:
  - “Verifikasi” (pending only).
  - “Chat pasien”: opens `wa.me` with the patient’s number and the message: Halo {nama}, jadwal Anda di {klinik} pada {tanggal} jam {jam} sudah dikonfirmasi. Kode: {kode}.
  - “Batalkan” (pending or verified).
  - “Selesai” (verified only).
- **Layanan.** Create, edit, and deactivate services: name, description, duration, starting price, image, active, order.
- **Dokter.** Create, edit, and deactivate doctors: name, title, years of experience, photo, active. A relation manager edits the weekly schedule (weekday, start time, end time).
- **Pengaturan Klinik.** One settings page for the `clinic_settings` row.
- Never hard-delete a service or doctor that has bookings. Deactivate it instead.
- Show a notice in the panel: “Data demo, direset setiap malam.”

## 8. Security Requirements

- Validate everything on the server and never trust the frontend. Use Eloquent or the query builder only (parameterized queries).
- Rate limit `POST /api/bookings`: 5 requests per hour per IP, and at most 2 active pending bookings per WhatsApp number.
- Same-domain deployment needs no CORS. If the frontend is ever hosted on another domain, allow only that origin.
- Production: HTTPS only, `APP_DEBUG=false`, secrets only in `.env` (never committed), login throttling on the admin panel, strong admin password.
- Store only the patient name, WhatsApp number, and an optional note. Do not write full phone numbers to logs.
- Demo data is fictional, and visitors should not enter real personal data (the frontend shows a demo notice).

## 9. Demo Data and Reset

- Seeder: the same 3 services and 3 doctors as the frontend placeholders (with weekly schedules), clinic settings, the admin user, and about 8 sample bookings across statuses so the dashboard is not empty.
- Sample bookings use dates relative to today, so they never go stale.
- Command `php artisan demo:reset` restores the seed state and runs nightly at 03:00 through the scheduler.
- The scheduler also expires pending bookings every minute. The server needs one cron entry running `php artisan schedule:run` every minute.

## 10. Build Order

1. Project setup, MySQL, migrations, models, status enum, and seeders. Show the schema and **wait for my approval**.
2. Public API: clinic, services, doctors, availability, bookings. Include validation, slot rules, and rate limits, with automated tests for double-booking and expiry.
3. Scheduler: expire pending bookings, and `demo:reset`.
4. Filament: admin login, Booking resource with its actions, and the dashboard.
5. Filament: Layanan, Dokter with schedule, and Pengaturan Klinik.
6. Connect the frontend: implement the real calls in `src/lib/api.js` behind `VITE_API_URL`. Change nothing in the UI except handling a 409 (show the message and return to the time step) and a 429.
7. Deployment README: VPS setup (web server, PHP-FPM, MySQL, cron, HTTPS), building the React app and serving it from `/`, and environment variables.

After each step: a 2–3 line summary and how to test it. No long explanations.

## 11. Acceptance Checklist

- A patient books from the website and the booking appears in `/admin` as “Menunggu verifikasi” within seconds.
- Two simultaneous requests for the same doctor, date, and time: exactly one succeeds and the other gets 409 (automated test).
- A pending booking holds its slot, and the slot frees automatically after `hold_minutes` (test with a short value).
- “Verifikasi” changes the status to Terverifikasi and the slot stays locked. “Batalkan” frees the slot.
- “Chat pasien” opens WhatsApp with the correct prefilled message.
- Changing a service price, a doctor schedule, or a doctor profile in the admin panel shows on the website without code changes.
- The rate limit returns 429 after the limit, and invalid input returns Indonesian 422 messages.
- No public endpoint returns patient data, and production runs with `APP_DEBUG=false`.
- `demo:reset` restores the seed state, and the sample bookings have valid future dates.
- The frontend still works fully on mock data when `VITE_API_URL` is not set.

## 12. Future (Not Part of This Build)

Automatic verification through the WhatsApp Business API, admin notifications for new bookings, extra roles (receptionist, doctor), multiple clinics, patient accounts, payments, and patient reminders.
