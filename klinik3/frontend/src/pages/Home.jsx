import React from "react";
import { Link } from "react-router-dom";
import { clinicData } from "../data/clinic";
import { useClinic } from "../context/ClinicContext";
import { Button } from "../components/common/Button";
import { ImageCard } from "../components/common/ImageCard";
import { TestimonialCarousel } from "../components/home/TestimonialCarousel";
import { FAQAccordion } from "../components/home/FAQAccordion";
import { getGeneralInquiryWhatsAppUrl } from "../lib/whatsapp";
import {
  Calendar,
  MessageCircle,
  CalendarDays,
  FileText,
  CheckCircle2,
  ArrowRight,
  Star,
  ShieldCheck,
} from "lucide-react";

export function Home() {
  const { clinic, services, doctors } = useClinic();
  const whatsappUrl = getGeneralInquiryWhatsAppUrl(clinic.whatsapp, clinic.name);

  return (
    <div className="space-y-16 sm:space-y-24 pb-16 overflow-x-hidden">
      {/* 1. HERO SECTION (Full Section Image, Fokus ke Gambar, Minimalis Teks & 2 CTA) */}
      <section className="relative overflow-hidden w-full min-h-[480px] sm:min-h-[540px] lg:min-h-[600px] flex items-center">
        {/* Foto Full Section 100% Terang & Tajam, gradient halus hanya di area teks */}
        <div className="absolute inset-0 z-0">
          <img
            src={clinicData.heroImage.src}
            alt={clinicData.heroImage.alt}
            width={clinicData.heroImage.width}
            height={clinicData.heroImage.height}
            loading="eager"
            className="absolute inset-0 w-full h-full object-cover object-center"
          />
          {/* Subtle gradient: hanya di sisi kiri teks agar teks kontras tinggi, sisi kanan gambar 100% jernih dan terbuka */}
          <div className="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent sm:bg-gradient-to-r sm:from-black/75 sm:via-black/30 sm:to-transparent" />
        </div>

        {/* Konten Hero: Minimalis, langsung to the point tanpa banyak teks */}
        <div className="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24 w-full">
          <div className="max-w-xl space-y-4 sm:space-y-5">
            {/* Headline max 6 kata */}
            <h1 className="text-3xl sm:text-5xl lg:text-6xl font-bold text-white tracking-tight leading-[1.15] drop-shadow-sm">
              Perawatan Gigi Nyaman & Terpercaya
            </h1>

            {/* 1 kalimat pendek */}
            <p className="text-base sm:text-lg text-white/90 leading-relaxed max-w-lg drop-shadow-sm">
              Reservasi online cepat dan mudah, langsung terhubung ke WhatsApp klinik tanpa antre.
            </p>

            {/* CTA Tunggal: Fokus & Tegas */}
            <div className="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
              <Button
                to="/booking"
                variant="primary"
                size="lg"
                icon={Calendar}
                className="sm:w-auto shadow-lg text-base font-semibold px-7 py-3.5 bg-brand-500 hover:bg-brand-600 border border-brand-400/40"
              >
                Pilih Jadwal Temu
              </Button>
            </div>

            {/* Mini Trust Bar ala Klinik Indonesia */}
            <div className="pt-5 border-t border-white/20 flex flex-wrap items-center gap-y-2.5 gap-x-4 sm:gap-x-6 text-xs sm:text-sm text-white/90">
              <div className="flex items-center gap-1.5 font-medium">
                <div className="flex items-center text-amber-300">
                  <Star className="w-4 h-4 fill-amber-400 stroke-amber-400" />
                </div>
                <span>
                  <strong className="font-semibold text-white">4.9/5</strong> (1.200+ Pasien)
                </span>
              </div>
              <span className="text-white/30 hidden sm:inline" aria-hidden="true">•</span>
              <div className="flex items-center gap-1.5">
                <ShieldCheck className="w-4 h-4 text-emerald-400 shrink-0" />
                <span>Dokter Gigi Berpengalaman</span>
              </div>
              <span className="text-white/30 hidden sm:inline" aria-hidden="true">•</span>
              <div className="flex items-center gap-1.5">
                <CheckCircle2 className="w-4 h-4 text-emerald-400 shrink-0" />
                <span>Konfirmasi via WhatsApp</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* 2. SERVICES SECTION (3 large image cards, name over photo, no description text, link to Layanan) */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
          <div>
            <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
              Layanan Perawatan Gigi
            </h2>
            <p className="text-sm text-clinic-muted mt-1">
              Perawatan terukur dengan kenyamanan maksimal untuk pasien.
            </p>
          </div>
          <Link
            to="/layanan"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 group"
          >
            <span>Semua Layanan</span>
            <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {services.slice(0, 3).map((service) => (
            <Link
              key={service.id}
              to="/layanan"
              className="group block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 rounded-card"
            >
              <ImageCard
                src={service.image}
                alt={service.alt}
                aspectRatio="4/3"
                width={service.width}
                height={service.height}
                overlay={true}
                badge={service.priceFormatted}
                title={service.name}
              />
            </Link>
          ))}
        </div>
      </section>

      {/* 3. CLINIC GALLERY (Photo mosaic / scroller 5-6 images, captions max 3 words) */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="mb-8">
          <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
            Fasilitas & Ruang Klinik
          </h2>
          <p className="text-sm text-clinic-muted mt-1">
            Lingkungan bersih dan tenang dengan standar sterilisasi medis ketat.
          </p>
        </div>

        <div className="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
          {clinicData.gallery.map((item) => (
            <div
              key={item.id}
              className="relative group overflow-hidden rounded-card border border-clinic-border bg-white shadow-soft"
            >
              <div className="w-full aspect-[4/3] overflow-hidden">
                <img
                  src={item.image}
                  alt={item.alt}
                  width={item.width}
                  height={item.height}
                  loading="lazy"
                  className="w-full h-full object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
                />
              </div>
              <div className="p-3 bg-white border-t border-clinic-border/60">
                {/* Caption maks 3 kata sesuai PRD */}
                <span className="text-xs font-semibold text-clinic-ink tracking-tight block truncate">
                  {item.title}
                </span>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* 4. DOCTORS SECTION (3 portrait cards 3:4, name & title only, link to Dokter) */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
          <div>
            <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
              Tim Dokter Kami
            </h2>
            <p className="text-sm text-clinic-muted mt-1">
              Praktisi gigi berdedikasi yang mengutamakan kenyamanan Anda.
            </p>
          </div>
          <Link
            to="/dokter"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-800 group"
          >
            <span>Profil Dokter</span>
            <ArrowRight className="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
          {doctors.slice(0, 3).map((doctor) => (
            <Link
              key={doctor.id}
              to="/dokter"
              className="group block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 rounded-card"
            >
              <ImageCard
                src={doctor.image}
                alt={doctor.alt}
                aspectRatio="3/4"
                width={doctor.width}
                height={doctor.height}
              >
                {/* Hanya Nama dan Gelar saja sesuai PRD */}
                <div className="space-y-0.5">
                  <h3 className="text-base sm:text-lg font-semibold text-clinic-ink group-hover:text-brand-600 transition-colors">
                    {doctor.name}
                  </h3>
                  <p className="text-xs text-clinic-muted font-medium">
                    {doctor.title}
                  </p>
                </div>
              </ImageCard>
            </Link>
          ))}
        </div>
      </section>

      {/* 5. CARA BOOKING (3 steps: icon + 3 kata) */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="bg-white rounded-2xl border border-clinic-border p-6 sm:p-10 shadow-soft">
          <div className="text-center max-w-xl mx-auto mb-10">
            <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
              Cara Booking Cepat
            </h2>
            <p className="text-sm text-clinic-muted mt-1">
              Tiga langkah mudah reservasi jadwal tanpa perlu telepon manual.
            </p>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-3 gap-8 relative">
            {/* Step 1 */}
            <div className="flex flex-col items-center text-center space-y-3">
              <div className="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-200">
                <CalendarDays className="w-7 h-7" />
              </div>
              <span className="text-xs font-mono font-semibold text-brand-700 tracking-wider">
                LANGKAH 01
              </span>
              <h3 className="text-base sm:text-lg font-bold text-clinic-ink">
                Pilih Jadwal Temu
              </h3>
            </div>

            {/* Step 2 */}
            <div className="flex flex-col items-center text-center space-y-3">
              <div className="w-14 h-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-200">
                <FileText className="w-7 h-7" />
              </div>
              <span className="text-xs font-mono font-semibold text-brand-700 tracking-wider">
                LANGKAH 02
              </span>
              <h3 className="text-base sm:text-lg font-bold text-clinic-ink">
                Isi Data Pasien
              </h3>
            </div>

            {/* Step 3 */}
            <div className="flex flex-col items-center text-center space-y-3">
              <div className="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                <MessageCircle className="w-7 h-7" />
              </div>
              <span className="text-xs font-mono font-semibold text-emerald-700 tracking-wider">
                LANGKAH 03
              </span>
              <h3 className="text-base sm:text-lg font-bold text-clinic-ink">
                Verifikasi via WhatsApp
              </h3>
            </div>
          </div>

          {/* Action to Booking */}
          <div className="mt-10 text-center">
            <Button to="/booking" variant="primary" size="md" icon={Calendar}>
              Mulai Reservasi Sekarang
            </Button>
          </div>
        </div>
      </section>

      {/* 6. TESTIMONIALS (Carousel 3 items, photo + quote max 15 words) */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center max-w-xl mx-auto mb-8">
          <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
            Kata Pasien Kami
          </h2>
          <p className="text-sm text-clinic-muted mt-1">
            Pengalaman nyata perawatan gigi yang ramah dan bebas cemas.
          </p>
        </div>

        <TestimonialCarousel />
      </section>

      {/* 7. FAQ ACCORDION (4-5 common questions) */}
      <section className="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="text-center mb-8">
          <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
            Pertanyaan Umum
          </h2>
          <p className="text-sm text-clinic-muted mt-1">
            Informasi praktis seputar booking, biaya, dan kunjungan pertama Anda.
          </p>
        </div>

        <FAQAccordion />
      </section>

      {/* 8. CLOSING CTA BANNER (Full-width photo banner with 1 line, Buat Janji & Chat WhatsApp, hours & address) */}
      <section className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="relative overflow-hidden rounded-2xl bg-clinic-dark text-white p-8 sm:p-12 lg:p-16 text-center">
          {/* Banner image background */}
          <div className="absolute inset-0 z-0">
            <img
              src={clinicData.closingBanner.src}
              alt={clinicData.closingBanner.alt}
              width={clinicData.closingBanner.width}
              height={clinicData.closingBanner.height}
              loading="lazy"
              className="w-full h-full object-cover opacity-25"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-clinic-dark via-clinic-dark/80 to-clinic-dark/60" />
          </div>

          <div className="relative z-10 max-w-2xl mx-auto space-y-6">
            {/* One line headline */}
            <h2 className="text-2xl sm:text-4xl font-bold tracking-tight text-white leading-tight">
              Wujudkan Senyum Sehat Bersama Kami
            </h2>

            {/* Buttons: Buat Janji & Chat WhatsApp */}
            <div className="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
              <Button
                to="/booking"
                variant="primary"
                size="lg"
                icon={Calendar}
                className="w-full sm:w-auto"
              >
                Buat Janji
              </Button>
              <Button
                href={whatsappUrl}
                variant="whatsapp"
                size="lg"
                icon={MessageCircle}
                className="w-full sm:w-auto"
              >
                Chat WhatsApp
              </Button>
            </div>

            {/* Hours and address on one line each */}
            <div className="pt-4 border-t border-white/15 space-y-1 text-xs sm:text-sm text-white/80">
              <p>Buka {clinic.hoursText}</p>
              <p>{clinic.address}</p>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
