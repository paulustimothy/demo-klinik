import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useClinic } from '../context/ClinicContext';
import { ImageCard } from '../components/common/ImageCard';
import { Button } from '../components/common/Button';
import { useBooking } from '../context/BookingContext';
import { Calendar, Award, CalendarDays, UserCheck, AlertCircle, MessageCircle } from 'lucide-react';

export function Doctors() {
  const navigate = useNavigate();
  const { updateBooking } = useBooking();
  const { doctors, clinic } = useClinic();

  const handleSelectDoctor = (doctorId) => {
    updateBooking({ doctorId });
    navigate(`/booking?doctor=${doctorId}`);
  };

  const whatsappInquiryUrl = `https://wa.me/${clinic.whatsapp}?text=${encodeURIComponent('Halo ' + clinic.name + ', saya ingin bertanya informasi jadwal praktek dokter.')}`;

  return (
    <div className="py-12 sm:py-16 space-y-12 sm:space-y-16 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      
      {/* Intro Header */}
      <div className="max-w-3xl">
        <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-brand-50 text-brand-700 border border-brand-200 mb-3">
          <UserCheck className="w-3.5 h-3.5" />
          <span>Tenaga Medis Berpengalaman</span>
        </div>
        <h1 className="text-3xl sm:text-4xl font-bold text-clinic-ink tracking-tight">
          Tim Dokter Gigi Kami
        </h1>
        <p className="mt-2 text-base text-clinic-muted leading-relaxed">
          Praktisi berdedikasi tinggi yang mengedepankan pendekatan ramah, teliti, dan minim rasa sakit.
        </p>
      </div>

      {/* Grid of large portrait cards (3:4) or Empty State */}
      {doctors.length === 0 ? (
        <div className="text-center py-12 px-6 rounded-2xl border border-clinic-border bg-white shadow-soft max-w-md mx-auto space-y-4">
          <div className="w-12 h-12 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center">
            <AlertCircle className="w-6 h-6" />
          </div>
          <div className="space-y-1">
            <h3 className="text-lg font-bold text-clinic-ink">Jadwal Dokter Sedang Diperbarui</h3>
            <p className="text-xs sm:text-sm text-clinic-muted">
              Saat ini belum ada jadwal dokter aktif yang tersedia untuk reservasi online. Silakan hubungi admin kami untuk jadwal konsultasi langsung.
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
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {doctors.map((doctor, index) => (
            <div
              key={doctor.id}
              className="flex flex-col rounded-2xl border border-clinic-border bg-white shadow-soft overflow-hidden transition-all duration-200 hover:shadow-lift"
            >
              {/* Portrait Image (3:4) */}
              <div className="w-full aspect-[3/4] overflow-hidden bg-clinic-surface">
                <img
                  src={doctor.image}
                  alt={doctor.alt}
                  width={doctor.width}
                  height={doctor.height}
                  loading={index === 0 ? 'eager' : 'lazy'}
                  className="w-full h-full object-cover transition-transform duration-300 ease-out hover:scale-[1.03]"
                />
              </div>

              {/* Doctor Info & Action */}
              <div className="p-6 flex flex-col flex-1 justify-between space-y-5">
                
                <div className="space-y-3">
                  <div>
                    <h2 className="text-xl font-bold text-clinic-ink tracking-tight">
                      {doctor.name}
                    </h2>
                    <p className="text-xs sm:text-sm font-medium text-brand-700 mt-0.5">
                      {doctor.title}
                    </p>
                  </div>

                  {/* Experience & Practice days as small chips */}
                  <div className="flex flex-col gap-2 pt-1">
                    <div className="inline-flex items-center gap-1.5 text-xs font-medium text-clinic-ink bg-clinic-surface px-3 py-1.5 rounded-lg border border-clinic-border/60">
                      <Award className="w-3.5 h-3.5 text-brand-600 shrink-0" />
                      <span>Pengalaman {doctor.experience}</span>
                    </div>

                    <div className="inline-flex items-center gap-1.5 text-xs font-medium text-clinic-ink bg-brand-50/80 px-3 py-1.5 rounded-lg border border-brand-200/80">
                      <CalendarDays className="w-3.5 h-3.5 text-brand-600 shrink-0" />
                      <span>Praktek: {doctor.practiceDaysText}</span>
                    </div>
                  </div>
                </div>

                {/* Action Button: Opens booking with doctor preselected */}
                <div className="pt-2">
                  <Button
                    onClick={() => handleSelectDoctor(doctor.id)}
                    variant="primary"
                    size="md"
                    icon={Calendar}
                    className="w-full"
                  >
                    Buat Janji
                  </Button>
                </div>

              </div>

            </div>
          ))}
        </div>
      )}

    </div>
  );
}
