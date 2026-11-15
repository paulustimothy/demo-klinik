import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useClinic } from '../context/ClinicContext';
import { ImageCard } from '../components/common/ImageCard';
import { Button } from '../components/common/Button';
import { useBooking } from '../context/BookingContext';
import { Calendar, Award, CalendarDays, UserCheck } from 'lucide-react';

export function Doctors() {
  const navigate = useNavigate();
  const { updateBooking } = useBooking();
  const { doctors } = useClinic();

  const handleSelectDoctor = (doctorId) => {
    updateBooking({ doctorId });
    navigate(`/booking?doctor=${doctorId}`);
  };

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

      {/* Grid of large portrait cards (3:4) */}
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

    </div>
  );
}
