import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useClinic } from '../context/ClinicContext';
import { ImageCard } from '../components/common/ImageCard';
import { Button } from '../components/common/Button';
import { useBooking } from '../context/BookingContext';
import { Clock, Calendar, CheckCircle, AlertCircle, MessageCircle } from 'lucide-react';

export function Services() {
  const navigate = useNavigate();
  const { updateBooking } = useBooking();
  const { services, clinic } = useClinic();

  const handleSelectService = (serviceId) => {
    updateBooking({ serviceId });
    navigate(`/booking?service=${serviceId}`);
  };

  const whatsappInquiryUrl = `https://wa.me/${clinic.whatsapp}?text=${encodeURIComponent('Halo ' + clinic.name + ', saya ingin bertanya mengenai paket layanan perawatan gigi.')}`;

  return (
    <div className="py-12 sm:py-16 space-y-12 sm:space-y-16 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      
      {/* Intro Header: One-line intro as required by PRD */}
      <div className="max-w-3xl">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200 mb-3">
          <CheckCircle className="w-3.5 h-3.5" />
          <span>Perawatan Medis Berstandar</span>
        </div>
        <h1 className="text-3xl sm:text-4xl font-bold text-clinic-ink tracking-tight">
          Layanan Dental Unggulan
        </h1>
        {/* One-line intro */}
        <p className="mt-2 text-base text-clinic-muted leading-relaxed">
          Perawatan gigi menyeluruh dengan teknologi modern dan prosedur yang nyaman bagi seluruh keluarga.
        </p>
      </div>

      {/* Large Alternating Image Sections or Empty State */}
      {services.length === 0 ? (
        <div className="text-center py-12 px-6 rounded-2xl border border-clinic-border bg-white shadow-soft max-w-md mx-auto space-y-4">
          <div className="w-12 h-12 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center">
            <AlertCircle className="w-6 h-6" />
          </div>
          <div className="space-y-1">
            <h3 className="text-lg font-bold text-clinic-ink">Daftar Layanan Sedang Diperbarui</h3>
            <p className="text-xs sm:text-sm text-clinic-muted">
              Saat ini belum ada layanan aktif yang tersedia untuk reservasi online. Silakan hubungi admin kami untuk informasi paket perawatan.
            </p>
          </div>
          <Button
            href={whatsappInquiryUrl}
            variant="whatsapp"
            size="md"
            icon={MessageCircle}
            className="w-full sm:w-auto mx-auto"
          >
            Hubungi WhatsApp Klinik
          </Button>
        </div>
      ) : (
        <div className="space-y-12 sm:space-y-16">
          {services.map((service, index) => {
            const isReversed = index % 2 === 1;

            return (
              <section
                key={service.id}
                className={`rounded-2xl border border-clinic-border bg-white p-6 sm:p-8 lg:p-10 shadow-soft grid grid-cols-1 lg:grid-cols-12 gap-8 items-center ${
                  isReversed ? 'lg:grid-flow-dense' : ''
                }`}
              >
                
                {/* Photo Column (4:3 ratio) */}
                <div className={`lg:col-span-7 ${isReversed ? 'lg:col-start-6' : ''}`}>
                  <ImageCard
                    src={service.image}
                    alt={service.alt}
                    aspectRatio="4/3"
                    width={service.width}
                    height={service.height}
                    loading={index === 0 ? 'eager' : 'lazy'}
                  />
                </div>

                {/* Text & Action Column */}
                <div className={`lg:col-span-5 space-y-5 ${isReversed ? 'lg:col-start-1' : ''}`}>
                  
                  <div className="space-y-2">
                    <span className="text-xs font-mono font-semibold text-brand-700 tracking-wider uppercase">
                      Layanan 0{index + 1}
                    </span>
                    <h2 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
                      {service.name}
                    </h2>
                    {/* One short sentence */}
                    <p className="text-sm sm:text-base text-clinic-muted leading-relaxed">
                      {service.description}
                    </p>
                  </div>

                  {/* Duration & starting price as small chips */}
                  <div className="flex flex-wrap items-center gap-2 pt-1">
                    <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-clinic-surface text-clinic-ink border border-clinic-border">
                      <Clock className="w-3.5 h-3.5 text-brand-600" />
                      <span>Durasi {service.duration}</span>
                    </span>
                    
                    <span className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200">
                      <span>{service.priceFormatted}</span>
                    </span>
                  </div>

                  {/* Primary Action Button */}
                  <div className="pt-2">
                    <Button
                      onClick={() => handleSelectService(service.id)}
                      variant="primary"
                      size="md"
                      icon={Calendar}
                      className="w-full sm:w-auto"
                    >
                      Pesan Layanan Ini
                    </Button>
                  </div>

                </div>

              </section>
            );
          })}
        </div>
      )}

    </div>
  );
}
