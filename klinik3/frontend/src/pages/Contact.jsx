import React from 'react';
import { clinicData } from '../data/clinic';
import { useClinic } from '../context/ClinicContext';
import { Button } from '../components/common/Button';
import { getGeneralInquiryWhatsAppUrl } from '../lib/whatsapp';
import {
  MapPin,
  Clock,
  Phone,
  Mail,
  MessageCircle,
  Navigation,
  CheckCircle,
} from 'lucide-react';

export function Contact() {
  const { clinic } = useClinic();
  const whatsappUrl = getGeneralInquiryWhatsAppUrl(clinic.whatsapp, clinic.name);

  return (
    <div className="py-12 sm:py-16 space-y-12 sm:space-y-16 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      
      {/* 1. Header Photo of Clinic Exterior (16:9) */}
      <section className="relative overflow-hidden rounded-2xl border border-clinic-border bg-clinic-surface shadow-soft">
        <div className="w-full aspect-[16/9] max-h-[460px] overflow-hidden relative">
          <img
            src={clinicData.contactExterior.src}
            alt={clinicData.contactExterior.alt}
            width={clinicData.contactExterior.width}
            height={clinicData.contactExterior.height}
            loading="eager"
            className="w-full h-full object-cover"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-clinic-dark/80 via-clinic-dark/30 to-transparent flex items-end p-6 sm:p-10">
            <div className="text-white space-y-1">
              <span className="text-xs font-semibold uppercase tracking-wider text-brand-200">
                Lokasi & Kunjungan
              </span>
              <h1 className="text-2xl sm:text-4xl font-bold tracking-tight">
                {clinic.name}
              </h1>
              <p className="text-xs sm:text-sm text-white/90">
                {clinic.address}
              </p>
            </div>
          </div>
        </div>
      </section>

      {/* 2. Contact Details & Hours Table & Static Map */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {/* Info & Hours Table (7 cols) */}
        <div className="lg:col-span-7 space-y-8">
          
          {/* Quick Contact Cards */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <div className="p-5 rounded-2xl border border-clinic-border bg-white shadow-soft space-y-2">
              <div className="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-200">
                <MapPin className="w-5 h-5" />
              </div>
              <h3 className="font-bold text-sm text-clinic-ink">Alamat Klinik</h3>
              <p className="text-xs sm:text-sm text-clinic-muted leading-relaxed">
                {clinic.address}
              </p>
            </div>

            <div className="p-5 rounded-2xl border border-clinic-border bg-white shadow-soft space-y-2">
              <div className="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center border border-emerald-200">
                <MessageCircle className="w-5 h-5" />
              </div>
              <h3 className="font-bold text-sm text-clinic-ink">WhatsApp Resmi</h3>
              <p className="text-xs sm:text-sm text-clinic-muted">
                {clinic.whatsappFormatted}
              </p>
              <div className="pt-1">
                <Button href={whatsappUrl} variant="whatsapp" size="sm" icon={MessageCircle} className="w-full text-xs">
                  Chat WhatsApp
                </Button>
              </div>
            </div>

            <div className="p-5 rounded-2xl border border-clinic-border bg-white shadow-soft space-y-2 sm:col-span-2">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-xl bg-clinic-surface text-clinic-ink flex items-center justify-center border border-clinic-border">
                  <Mail className="w-5 h-5 text-brand-600" />
                </div>
                <div>
                  <h3 className="font-bold text-sm text-clinic-ink">Email Pertanyaan</h3>
                  <p className="text-xs sm:text-sm text-clinic-muted font-mono">{clinic.email}</p>
                </div>
              </div>
            </div>

          </div>

          {/* Jam Operasional Table */}
          <div className="rounded-2xl border border-clinic-border bg-white shadow-soft overflow-hidden">
            <div className="p-5 border-b border-clinic-border bg-clinic-surface/50 flex items-center gap-2">
              <Clock className="w-4 h-4 text-brand-600" />
              <h3 className="font-bold text-sm sm:text-base text-clinic-ink">
                Jadwal & Jam Operasional
              </h3>
            </div>

            <div className="divide-y divide-clinic-border/60 text-xs sm:text-sm">
              {(clinic.schedule || clinicData.schedule).map((item, idx) => (
                <div key={idx} className="p-4 flex justify-between items-center hover:bg-clinic-surface/30">
                  <span className="font-medium text-clinic-ink">{item.days}</span>
                  <span className={`font-semibold ${item.hours === 'Tutup' ? 'text-red-600' : 'text-brand-700'}`}>
                    {item.hours}
                  </span>
                </div>
              ))}
            </div>
            {clinic.hoursText && (
              <div className="p-4 bg-clinic-surface/30 text-xs text-clinic-muted border-t border-clinic-border/40">
                <span>Keterangan operasional: {clinic.hoursText}</span>
              </div>
            )}
          </div>

        </div>

        {/* 3. Static Map Placeholder (5 cols) */}
        <div className="lg:col-span-5 space-y-4">
          <div className="rounded-2xl border border-clinic-border bg-white p-6 shadow-soft space-y-4">
            <div className="flex items-center justify-between">
              <h3 className="font-bold text-base text-clinic-ink">
                Peta Lokasi Klinik
              </h3>
              <span className="text-[11px] font-mono bg-clinic-surface px-2 py-0.5 rounded text-clinic-muted">
                Static Preview
              </span>
            </div>

            {/* Styled Map Box (no external API or keys) */}
            <div className="w-full aspect-[4/3] rounded-xl border border-clinic-border bg-[#eef3f5] relative overflow-hidden flex flex-col items-center justify-center p-6 text-center shadow-inner">
              
              {/* Subtle road map grid lines */}
              <div className="absolute inset-0 opacity-20 pointer-events-none">
                <svg className="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                  <defs>
                    <pattern id="map-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                      <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#0d7480" strokeWidth="1" />
                    </pattern>
                  </defs>
                  <rect width="100%" height="100%" fill="url(#map-grid)" />
                  <path d="M 0 60 Q 150 90 350 40 T 600 120" fill="none" stroke="#ffffff" strokeWidth="14" />
                  <path d="M 120 0 L 140 300" fill="none" stroke="#ffffff" strokeWidth="10" />
                </svg>
              </div>

              {/* Pin indicator */}
              <div className="relative z-10 flex flex-col items-center space-y-2">
                <div className="w-12 h-12 rounded-full bg-brand-500 text-white flex items-center justify-center shadow-lift ring-4 ring-white animate-bounce duration-1000">
                  <MapPin className="w-6 h-6 fill-current" />
                </div>
                <div className="bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-lg border border-clinic-border shadow-sm">
                  <p className="text-xs font-bold text-clinic-ink">{clinic.name}</p>
                  <p className="text-[10px] text-clinic-muted">{clinic.address}</p>
                </div>
              </div>

            </div>

            <p className="text-xs text-clinic-muted text-center leading-relaxed">
              Lokasi strategis di pusat kota dengan area parkir pasien yang luas dan akses transportasi umum mudah.
            </p>

            <Button
              href={`https://maps.google.com/?q=${encodeURIComponent(clinic.name + ' ' + clinic.address)}`}
              variant="outline"
              size="sm"
              icon={Navigation}
              className="w-full text-xs"
            >
              Buka di Google Maps
            </Button>
          </div>
        </div>

      </div>

    </div>
  );
}
