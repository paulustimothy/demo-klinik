import React, { useState } from 'react';
import { Button } from '../components/common/Button';
import { ImageCard } from '../components/common/ImageCard';
import { Stepper } from '../components/common/Stepper';
import { clinicData } from '../data/clinic';
import { Calendar, MessageCircle, Clock, ShieldCheck, CheckCircle2, ArrowRight } from 'lucide-react';
import { Link } from 'react-router-dom';

export function StyleTile() {
  const [activeStep, setActiveStep] = useState(1);
  const [btnLoading, setBtnLoading] = useState(false);

  const toggleLoading = () => {
    setBtnLoading(true);
    setTimeout(() => setBtnLoading(false), 1500);
  };

  return (
    <div className="py-10 px-4 sm:px-6 lg:px-8 max-w-6xl mx-auto space-y-16">
      
      {/* Page Header */}
      <div className="border-b border-clinic-border pb-8">
        <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200 mb-3">
          <ShieldCheck className="w-3.5 h-3.5" />
          <span>Design System & Style Tile — Step 1</span>
        </div>
        <h1 className="text-3xl sm:text-4xl font-bold text-clinic-ink tracking-tight">
          Panduan Visual & Token Desain
        </h1>
        <p className="mt-2 text-base text-clinic-muted max-w-2xl leading-relaxed">
          Kombinasi palet <span className="font-semibold text-brand-700">Cool-White-Slate</span> dari taste-skill dan tipografi <span className="font-semibold text-brand-700">Plus Jakarta Sans</span>. Dirancang untuk citra klinik gigi modern, higienis, dan terpercaya.
        </p>
      </div>

      {/* 1. Color Palette Tokens */}
      <section className="space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold text-clinic-ink tracking-tight">1. Palet Warna (Color Tokens)</h2>
          <span className="text-xs font-mono text-clinic-muted">tasteskill: cool-white-slate</span>
        </div>

        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
          <div className="rounded-xl border border-clinic-border overflow-hidden bg-white shadow-soft p-3">
            <div className="h-16 rounded-lg bg-brand-500 mb-2.5 shadow-inner" />
            <div className="text-xs font-bold text-clinic-ink">Primary Teal</div>
            <div className="text-[11px] font-mono text-clinic-muted">#0d7480</div>
            <div className="text-[10px] text-brand-700 mt-1">Aksi & branding utama</div>
          </div>

          <div className="rounded-xl border border-clinic-border overflow-hidden bg-white shadow-soft p-3">
            <div className="h-16 rounded-lg bg-brand-50 mb-2.5 border border-brand-200" />
            <div className="text-xs font-bold text-clinic-ink">Teal Tint</div>
            <div className="text-[11px] font-mono text-clinic-muted">#f0f9fa</div>
            <div className="text-[10px] text-clinic-muted mt-1">Background aktif/chip</div>
          </div>

          <div className="rounded-xl border border-clinic-border overflow-hidden bg-white shadow-soft p-3">
            <div className="h-16 rounded-lg bg-[#25D366] mb-2.5 shadow-inner" />
            <div className="text-xs font-bold text-clinic-ink">WhatsApp Green</div>
            <div className="text-[11px] font-mono text-clinic-muted">#25D366</div>
            <div className="text-[10px] text-emerald-800 mt-1">Verifikasi & Chat WA</div>
          </div>

          <div className="rounded-xl border border-clinic-border overflow-hidden bg-white shadow-soft p-3">
            <div className="h-16 rounded-lg bg-clinic-bg mb-2.5 border border-clinic-border" />
            <div className="text-xs font-bold text-clinic-ink">Frost Ground</div>
            <div className="text-[11px] font-mono text-clinic-muted">#f7f9fa</div>
            <div className="text-[10px] text-clinic-muted mt-1">Dasar canvas klinis</div>
          </div>

          <div className="rounded-xl border border-clinic-border overflow-hidden bg-white shadow-soft p-3">
            <div className="h-16 rounded-lg bg-clinic-surface mb-2.5 border border-clinic-border" />
            <div className="text-xs font-bold text-clinic-ink">Slate Surface</div>
            <div className="text-[11px] font-mono text-clinic-muted">#ecf0f2</div>
            <div className="text-[10px] text-clinic-muted mt-1">Border & Footer ground</div>
          </div>

          <div className="rounded-xl border border-clinic-border overflow-hidden bg-white shadow-soft p-3">
            <div className="h-16 rounded-lg bg-clinic-ink mb-2.5 shadow-inner" />
            <div className="text-xs font-bold text-clinic-ink">Slate Ink</div>
            <div className="text-[11px] font-mono text-clinic-muted">#1f3038</div>
            <div className="text-[10px] text-clinic-muted mt-1">Teks kontras AA</div>
          </div>
        </div>
      </section>

      {/* 2. Typography Hierarchy */}
      <section className="space-y-4">
        <h2 className="text-xl font-bold text-clinic-ink tracking-tight">2. Tipografi (Plus Jakarta Sans)</h2>
        <div className="bg-white rounded-card border border-clinic-border p-6 space-y-6 shadow-soft">
          
          <div className="border-b border-clinic-border/60 pb-4">
            <span className="text-xs font-mono text-clinic-muted uppercase">Hero Headline (Display 36px–44px Bold)</span>
            <div className="text-3xl sm:text-4xl font-bold text-clinic-ink tracking-tight mt-1">
              Senyum Sehat Dimulai dari Sini
            </div>
          </div>

          <div className="border-b border-clinic-border/60 pb-4">
            <span className="text-xs font-mono text-clinic-muted uppercase">Section Title (24px–28px Bold)</span>
            <div className="text-2xl font-bold text-clinic-ink tracking-tight mt-1">
              Layanan Perawatan Gigi Unggulan
            </div>
          </div>

          <div className="border-b border-clinic-border/60 pb-4">
            <span className="text-xs font-mono text-clinic-muted uppercase">Card & Subsection Heading (18px Semibold)</span>
            <div className="text-lg font-semibold text-clinic-ink mt-1">
              Scaling Gigi Ultrasonik Tanpa Rasa Ngilu
            </div>
          </div>

          <div>
            <span className="text-xs font-mono text-clinic-muted uppercase">Body & Lead (15px–16px Regular, maks 2 baris)</span>
            <p className="text-base text-clinic-muted mt-1 max-w-xl leading-relaxed">
              Pembersihan karang gigi dengan teknologi getaran ultrasonik modern. Perawatan lembut, higienis, dan nyaman untuk kesehatan gusi Anda.
            </p>
          </div>

        </div>
      </section>

      {/* 3. Button Component States */}
      <section className="space-y-4">
        <h2 className="text-xl font-bold text-clinic-ink tracking-tight">3. Komponen Tombol (Button States & Variants)</h2>
        <div className="bg-white rounded-card border border-clinic-border p-6 space-y-6 shadow-soft">
          
          <div className="space-y-2">
            <span className="text-xs font-mono text-clinic-muted">Varian Tombol (Tap target min 44px, micro-lift 2px on hover)</span>
            <div className="flex flex-wrap items-center gap-3">
              <Button variant="primary" icon={Calendar}>
                Primary: Buat Janji
              </Button>
              <Button variant="whatsapp" icon={MessageCircle}>
                WhatsApp: Chat Kami
              </Button>
              <Button variant="secondary">
                Secondary Tonal
              </Button>
              <Button variant="outline">
                Outline
              </Button>
              <Button variant="ghost">
                Ghost Link
              </Button>
            </div>
          </div>

          <div className="space-y-2 pt-2 border-t border-clinic-border/60">
            <span className="text-xs font-mono text-clinic-muted">Status State (Loading & Disabled)</span>
            <div className="flex flex-wrap items-center gap-3">
              <Button variant="primary" loading={btnLoading} onClick={toggleLoading}>
                {btnLoading ? 'Memproses...' : 'Klik untuk Tes Loading (1s)'}
              </Button>
              <Button variant="primary" disabled>
                Disabled State
              </Button>
              <Button variant="whatsapp" disabled>
                Disabled WhatsApp
              </Button>
            </div>
          </div>

        </div>
      </section>

      {/* 4. Stepper Component */}
      <section className="space-y-4">
        <div className="flex items-center justify-between">
          <h2 className="text-xl font-bold text-clinic-ink tracking-tight">4. Komponen Stepper Alur Booking</h2>
          <div className="flex gap-2">
            {[1, 2, 3].map((step) => (
              <button
                key={step}
                onClick={() => setActiveStep(step)}
                className={`text-xs px-2.5 py-1 rounded font-medium ${
                  activeStep === step
                    ? 'bg-brand-500 text-white'
                    : 'bg-clinic-surface text-clinic-ink hover:bg-slate-200'
                }`}
              >
                Langkah {step}
              </button>
            ))}
          </div>
        </div>

        <div className="bg-white rounded-card border border-clinic-border p-6 shadow-soft">
          <Stepper currentStep={activeStep} onStepClick={setActiveStep} />
          <div className="mt-6 text-center text-xs text-clinic-muted">
            Status simulasi: <strong className="text-brand-700">Langkah {activeStep} aktif</strong>. Tombol langkah di atas dapat diklik untuk pratinjau transisi.
          </div>
        </div>
      </section>

      {/* 5. ImageCard Components */}
      <section className="space-y-4">
        <h2 className="text-xl font-bold text-clinic-ink tracking-tight">5. Komponen Gambar & Card (ImageCard)</h2>
        <p className="text-sm text-clinic-muted">
          Rasio aspek konsisten: 4:3 (Layanan), 3:4 (Dokter), 16:9 (Hero/Ruangan). Zoom mikro 1.02–1.03x pada hover tanpa tumpah bingkai.
        </p>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          
          {/* Card Layanan (4:3) */}
          <div className="space-y-2">
            <span className="text-xs font-mono text-clinic-muted">Rasio 4:3 — Layanan Card (Beranda / Layanan)</span>
            <ImageCard
              src={clinicData.services[0].image}
              alt={clinicData.services[0].alt}
              aspectRatio="4/3"
              width={1200}
              height={900}
              badge={clinicData.services[0].priceFormatted}
              overlay={true}
              title={clinicData.services[0].name}
              subtitle={clinicData.services[0].description}
            />
          </div>

          {/* Card Dokter (3:4) */}
          <div className="space-y-2">
            <span className="text-xs font-mono text-clinic-muted">Rasio 3:4 — Dokter Card (Beranda / Dokter)</span>
            <ImageCard
              src={clinicData.doctors[0].image}
              alt={clinicData.doctors[0].alt}
              aspectRatio="3/4"
              width={900}
              height={1200}
            >
              <div className="space-y-1">
                <div className="text-base font-semibold text-clinic-ink">{clinicData.doctors[0].name}</div>
                <div className="text-xs text-clinic-muted">{clinicData.doctors[0].title}</div>
                <div className="flex items-center gap-2 pt-2 text-[11px] text-brand-700 font-medium">
                  <span className="bg-brand-50 px-2 py-0.5 rounded border border-brand-200">Pengalaman {clinicData.doctors[0].experience}</span>
                  <span className="bg-clinic-surface px-2 py-0.5 rounded">{clinicData.doctors[0].practiceDaysText}</span>
                </div>
              </div>
            </ImageCard>
          </div>

          {/* Card Ruangan / Galeri (16:9) */}
          <div className="space-y-2">
            <span className="text-xs font-mono text-clinic-muted">Rasio 16:9 — Galeri / Fasilitas Medis</span>
            <ImageCard
              src={clinicData.gallery[1].image}
              alt={clinicData.gallery[1].alt}
              aspectRatio="16/9"
              width={1200}
              height={675}
              overlay={true}
              title={clinicData.gallery[1].title}
              subtitle="Dental chair ergonomis steril"
            />
          </div>

        </div>
      </section>

      {/* Step 1 Approval Box */}
      <div className="bg-brand-50 rounded-2xl border border-brand-200 p-6 sm:p-8 space-y-4">
        <div className="flex items-start gap-3">
          <CheckCircle2 className="w-6 h-6 text-brand-600 shrink-0 mt-0.5" />
          <div>
            <h3 className="text-lg font-bold text-brand-900">Step 1 Selesai: Sistem Desain & Komponen Bersama Siap</h3>
            <p className="text-sm text-brand-800/90 mt-1 leading-relaxed">
              Token warna, font Plus Jakarta Sans, shared components (Navbar, Footer, Button, ImageCard, Stepper), file shot list <code className="font-mono bg-white px-1.5 py-0.5 rounded border border-brand-200">IMAGES.md</code>, dan 16 placeholder WebP terstruktur di <code className="font-mono bg-white px-1.5 py-0.5 rounded border border-brand-200">public/images/</code> telah selesai dibangun dan berfungsi.
            </p>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-3 pt-2">
          <Link
            to="/"
            className="inline-flex items-center gap-1.5 text-sm font-semibold text-brand-700 hover:text-brand-900 bg-white px-4 py-2 rounded-lg border border-brand-300 shadow-sm"
          >
            <span>Lihat Navigasi Global Beranda</span>
            <ArrowRight className="w-4 h-4" />
          </Link>
        </div>
      </div>

    </div>
  );
}

