import React, { useState, useEffect } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { useClinic } from '../context/ClinicContext';
import { useBooking } from '../context/BookingContext';
import { api } from '../lib/api';
import { validateIndonesianPhone, getBookingWhatsAppUrl } from '../lib/whatsapp';
import { getNext14Days, formatIndonesianDate } from '../lib/dateUtils';
import { Stepper } from '../components/common/Stepper';
import { Button } from '../components/common/Button';
import {
  Check,
  Calendar,
  Clock,
  User,
  Phone,
  FileText,
  AlertCircle,
  MessageCircle,
  CheckCircle2,
  ArrowRight,
  ArrowLeft,
  CalendarDays,
  ShieldCheck,
  RefreshCw,
} from 'lucide-react';

export function Booking() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { bookingData, updateBooking, bookingResult, setBookingResult, resetBooking } = useBooking();
  const { clinic, services, doctors } = useClinic();

  const [currentStep, setCurrentStep] = useState(1);
  const [slots, setSlots] = useState([]);
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [formErrors, setFormErrors] = useState({});
  const [conflictError, setConflictError] = useState(null);
  const [submitError, setSubmitError] = useState(null);

  // Helper: pindah step + scroll ke atas
  const goToStep = (step) => {
    setCurrentStep(step);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // 1. Tangani preselection dari URL query params (?service=... atau ?doctor=...)
  useEffect(() => {
    const serviceParam = searchParams.get('service');
    const doctorParam = searchParams.get('doctor');

    if (serviceParam || doctorParam) {
      const updates = {};
      if (serviceParam) {
        const found = services.find(s => String(s.id) === serviceParam || s.slug === serviceParam);
        if (found) updates.serviceId = found.id;
      }
      if (doctorParam) {
        const found = doctors.find(d => String(d.id) === doctorParam);
        if (found) updates.doctorId = found.id;
      }
      if (Object.keys(updates).length > 0) {
        updateBooking(updates);
      }
    }
  }, [searchParams, services, doctors]);

  // Dapatkan entitas terpilih
  const selectedService = services.find(s => String(s.id) === String(bookingData.serviceId) || s.slug === bookingData.serviceId);
  const selectedDoctor = doctors.find(d => String(d.id) === String(bookingData.doctorId));

  // 2. Fetch slot deterministik saat dokter dan tanggal berubah
  useEffect(() => {
    if (bookingData.doctorId && bookingData.date) {
      setLoadingSlots(true);
      api.getDoctorSlots(bookingData.doctorId, bookingData.date).then((res) => {
        setSlots(res);
        setLoadingSlots(false);
        // Jika jam yang dipilih sebelumnya tidak valid atau penuh pada tanggal/dokter ini, reset jam
        if (bookingData.time) {
          const slotValid = res.find(s => s.time === bookingData.time && s.available);
          if (!slotValid) {
            updateBooking({ time: null });
          }
        }
      });
    }
  }, [bookingData.doctorId, bookingData.date]);

  // Validasi form Data Diri (Step 3)
  const validateForm = () => {
    const errors = {};
    if (!bookingData.patientName.trim()) {
      errors.patientName = 'Nama lengkap wajib diisi.';
    } else if (bookingData.patientName.trim().length < 2) {
      errors.patientName = 'Masukkan nama lengkap minimal 2 karakter.';
    }

    const phoneValidation = validateIndonesianPhone(bookingData.patientPhone);
    if (!phoneValidation.valid) {
      errors.patientPhone = phoneValidation.message;
    }

    if (bookingData.patientNotes && bookingData.patientNotes.length > 200) {
      errors.patientNotes = 'Catatan maksimal 200 karakter.';
    }

    setFormErrors(errors);
    return Object.keys(errors).length === 0;
  };

  // Submit booking
  const handleConfirmBooking = async (e) => {
    e.preventDefault();
    if (!validateForm()) return;

    setSubmitting(true);
    setSubmitError(null);
    try {
      const payload = {
        serviceId: bookingData.serviceId,
        serviceName: selectedService?.name,
        doctorId: bookingData.doctorId,
        doctorName: selectedDoctor?.name,
        date: bookingData.date,
        dateFormatted: formatIndonesianDate(bookingData.date),
        time: bookingData.time,
        patientName: bookingData.patientName.trim(),
        patientPhone: bookingData.patientPhone.trim(),
        patientNotes: bookingData.patientNotes.trim(),
      };

      const result = await api.submitBooking(payload);
      setBookingResult(result);
    } catch (err) {
      console.error(err);
      if (err.status === 409) {
        setConflictError(err.message || 'Jam ini baru saja dipesan, silakan pilih jam lain.');
        updateBooking({ time: null });
        goToStep(2);
      } else if (err.status === 429) {
        setSubmitError('Terlalu banyak permintaan reservasi. Silakan coba beberapa saat lagi.');
      } else {
        setSubmitError(err.message || 'Terjadi kesalahan saat memproses reservasi.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  // Restart flow
  const handleRestart = () => {
    resetBooking();
    goToStep(1);
  };

  // Jika sudah sukses booking, tampilkan layar sukses
  if (bookingResult) {
    const whatsappVerificationUrl = getBookingWhatsAppUrl({
      nama: bookingResult.patientName,
      kode: bookingResult.code,
      layanan: bookingResult.serviceName,
      dokter: bookingResult.doctorName,
      tanggal: bookingResult.dateFormatted,
      jam: `${bookingResult.time} WIB`,
      status: bookingResult.status,
    }, clinic.whatsapp, clinic.name);

    return (
      <div className="py-12 sm:py-16 max-w-2xl mx-auto px-4 sm:px-6">
        <div className="bg-white rounded-2xl border border-clinic-border p-6 sm:p-10 shadow-lift text-center space-y-6">
          
          {/* Success Check Icon */}
          <div className="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 mx-auto flex items-center justify-center">
            <Check className="w-8 h-8 stroke-[3]" />
          </div>

          <div className="space-y-2">
            {/* Status badge: Menunggu verifikasi */}
            <div className="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
              <span className="w-2 h-2 rounded-full bg-amber-500 animate-pulse" />
              <span>{bookingResult.status}</span>
            </div>

            <h1 className="text-2xl sm:text-3xl font-bold text-clinic-ink tracking-tight">
              Reservasi Berhasil Dibuat
            </h1>
            <p className="text-sm text-clinic-muted">
              Kode Booking Anda:
            </p>
            <div className="inline-block bg-clinic-surface px-4 py-2 rounded-xl border border-clinic-border text-2xl font-mono font-bold tracking-wider text-brand-700">
              {bookingResult.code}
            </div>
          </div>

          {/* Booking Summary Box */}
          <div className="rounded-xl border border-clinic-border bg-clinic-bg/70 p-4 sm:p-5 text-left text-xs sm:text-sm space-y-2.5">
            <div className="flex justify-between border-b border-clinic-border/60 pb-2">
              <span className="text-clinic-muted">Layanan</span>
              <span className="font-semibold text-clinic-ink">{bookingResult.serviceName}</span>
            </div>
            <div className="flex justify-between border-b border-clinic-border/60 pb-2">
              <span className="text-clinic-muted">Dokter</span>
              <span className="font-semibold text-clinic-ink">{bookingResult.doctorName}</span>
            </div>
            <div className="flex justify-between border-b border-clinic-border/60 pb-2">
              <span className="text-clinic-muted">Jadwal</span>
              <span className="font-semibold text-clinic-ink">{bookingResult.dateFormatted} ({bookingResult.time} WIB)</span>
            </div>
            <div className="flex justify-between border-b border-clinic-border/60 pb-2">
              <span className="text-clinic-muted">Nama Pasien</span>
              <span className="font-semibold text-clinic-ink">{bookingResult.patientName}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-clinic-muted">WhatsApp Pasien</span>
              <span className="font-semibold text-clinic-ink">{bookingResult.patientPhone}</span>
            </div>
          </div>

          {/* Langkah berikutnya note */}
          <div className="rounded-xl bg-brand-50/80 border border-brand-200/90 p-4 sm:p-5 text-left space-y-2">
            <h4 className="text-xs font-bold text-brand-900 uppercase tracking-wider">
              Langkah Berikutnya:
            </h4>
            <ol className="list-decimal list-inside text-xs sm:text-sm text-brand-900 space-y-1">
              <li>Kirim pesan konfirmasi ke WhatsApp klinik.</li>
              <li>Klinik memverifikasi dan mengonfirmasi jadwal Anda.</li>
            </ol>
            <p className="text-[12px] text-amber-800 font-semibold pt-1">
              Jadwal ditahan sementara, segera kirim pesan agar tidak hangus.
            </p>
          </div>

          {/* Prominent WhatsApp Verification Button */}
          <div className="space-y-3 pt-2">
            <Button
              href={whatsappVerificationUrl}
              variant="whatsapp"
              size="lg"
              icon={MessageCircle}
              className="w-full text-base font-bold shadow-lift py-4"
            >
              Verifikasi via WhatsApp
            </Button>

            <div className="flex flex-col sm:flex-row items-center justify-center gap-2 pt-2">
              <Button
                variant="ghost"
                size="sm"
                onClick={handleRestart}
                icon={RefreshCw}
                className="w-full sm:w-auto text-xs"
              >
                Buat Janji Lain
              </Button>
              <Button
                variant="outline"
                size="sm"
                onClick={() => navigate('/')}
                className="w-full sm:w-auto text-xs"
              >
                Kembali ke Beranda
              </Button>
            </div>
          </div>

        </div>
      </div>
    );
  }

  // Next 14 days list for Step 2
  const next14Days = getNext14Days();

  return (
    <div className="py-10 sm:py-14 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
      
      {/* Header & Stepper */}
      <div className="text-center space-y-2">
        <h1 className="text-2xl sm:text-4xl font-bold text-clinic-ink tracking-tight">
          Reservasi Online Klinik
        </h1>
        <p className="text-sm text-clinic-muted">
          Pilih layanan, tentukan jam praktek, dan verifikasi via WhatsApp dalam 30 detik.
        </p>
      </div>

      <Stepper
        currentStep={currentStep}
        onStepClick={(step) => {
          // Hanya izinkan navigasi mundur atau ke langkah yang datanya sudah siap
          if (step < currentStep) goToStep(step);
          if (step === 2 && bookingData.serviceId && bookingData.doctorId) goToStep(2);
          if (step === 3 && bookingData.serviceId && bookingData.doctorId && bookingData.date && bookingData.time) goToStep(3);
        }}
      />

      {/* =========================================
          LANGKAH 1: LAYANAN & DOKTER
         ========================================= */}
      {currentStep === 1 && (
        <div className="space-y-8 animate-in fade-in duration-200">
          
          {/* Sub-step A: Pilih Layanan */}
          <section className="space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-lg sm:text-xl font-bold text-clinic-ink tracking-tight">
                1. Pilih Layanan Gigi
              </h2>
              <span className="text-xs text-clinic-muted">Pilih salah satu layanan</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              {services.map((service) => {
                const isSelected = String(bookingData.serviceId) === String(service.id) || bookingData.serviceId === service.slug;

                return (
                  <button
                    key={service.id}
                    type="button"
                    onClick={() => updateBooking({ serviceId: service.id })}
                    className={`group text-left rounded-card overflow-hidden border-2 transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 ${
                      isSelected
                        ? 'border-brand-500 ring-2 ring-brand-200 bg-brand-50/30 shadow-md'
                        : 'border-clinic-border bg-white hover:border-brand-300 shadow-soft'
                    }`}
                  >
                    <div className="aspect-[4/3] w-full overflow-hidden bg-clinic-surface relative">
                      <img
                        src={service.image}
                        alt={service.alt}
                        width={service.width}
                        height={service.height}
                        className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                      />
                      {isSelected && (
                        <div className="absolute top-2.5 right-2.5 w-6 h-6 rounded-full bg-brand-500 text-white flex items-center justify-center shadow-md">
                          <Check className="w-4 h-4 stroke-[3]" />
                        </div>
                      )}
                    </div>
                    <div className="p-3.5 space-y-1">
                      <div className="font-bold text-sm sm:text-base text-clinic-ink">
                        {service.name}
                      </div>
                      <div className="text-xs font-semibold text-brand-700">
                        {service.priceFormatted}
                      </div>
                      <div className="text-[11px] text-clinic-muted flex items-center gap-1 pt-0.5">
                        <Clock className="w-3 h-3 text-clinic-muted" />
                        <span>{service.duration}</span>
                      </div>
                    </div>
                  </button>
                );
              })}
            </div>
          </section>

          {/* Sub-step B: Pilih Dokter */}
          <section className="space-y-4 pt-4 border-t border-clinic-border/60">
            <div className="flex items-center justify-between">
              <h2 className="text-lg sm:text-xl font-bold text-clinic-ink tracking-tight">
                2. Pilih Dokter Gigi
              </h2>
              <span className="text-xs text-clinic-muted">Tersedia {doctors.length} dokter</span>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
              {doctors.map((doctor) => {
                const isSelected = String(bookingData.doctorId) === String(doctor.id);

                return (
                  <button
                    key={doctor.id}
                    type="button"
                    onClick={() => updateBooking({ doctorId: doctor.id })}
                    className={`group text-left p-3.5 rounded-card border-2 transition-all duration-150 flex items-center gap-3 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 ${
                      isSelected
                        ? 'border-brand-500 ring-2 ring-brand-200 bg-brand-50/40 shadow-md'
                        : 'border-clinic-border bg-white hover:border-brand-300 shadow-soft'
                    }`}
                  >
                    <div className="w-14 h-18 sm:w-16 sm:h-20 rounded-lg overflow-hidden shrink-0 bg-clinic-surface relative border border-clinic-border">
                      <img
                        src={doctor.image}
                        alt={doctor.alt}
                        width={doctor.width}
                        height={doctor.height}
                        className="w-full h-full object-cover"
                      />
                    </div>
                    <div className="flex-1 min-w-0 space-y-0.5">
                      <div className="font-bold text-sm text-clinic-ink truncate group-hover:text-brand-600 transition-colors">
                        {doctor.name}
                      </div>
                      <div className="text-xs text-clinic-muted">
                        Pengalaman {doctor.experience}
                      </div>
                      <div className="text-[11px] font-medium text-brand-700 bg-brand-50 px-2 py-0.5 rounded border border-brand-200/60 inline-block truncate max-w-full">
                        {doctor.practiceDaysText}
                      </div>
                    </div>
                    {isSelected && (
                      <div className="w-5 h-5 rounded-full bg-brand-500 text-white flex items-center justify-center shrink-0">
                        <Check className="w-3.5 h-3.5 stroke-[3]" />
                      </div>
                    )}
                  </button>
                );
              })}
            </div>
          </section>

          {/* Navigation */}
          <div className="pt-4 flex justify-end">
            <Button
              variant="primary"
              size="lg"
              disabled={!bookingData.serviceId || !bookingData.doctorId}
              onClick={() => goToStep(2)}
              icon={ArrowRight}
              className="w-full sm:w-auto"
            >
              Lanjut ke Tanggal & Jam
            </Button>
          </div>

        </div>
      )}

      {/* =========================================
          LANGKAH 2: TANGGAL & JAM
         ========================================= */}
      {currentStep === 2 && (
        <div className="space-y-8 animate-in fade-in duration-200">
          
          {/* Conflict 409 Alert */}
          {conflictError && (
            <div className="bg-amber-50 border border-amber-300 text-amber-900 px-4 py-3 rounded-xl text-sm flex items-center gap-2.5 shadow-soft">
              <AlertCircle className="w-5 h-5 shrink-0 text-amber-600" />
              <span>{conflictError}</span>
            </div>
          )}

          {/* Sub-step A: Horizontal Date Strip (14 hari) */}
          <section className="space-y-4">
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-lg sm:text-xl font-bold text-clinic-ink tracking-tight">
                  Pilih Tanggal Kunjungan
                </h2>
                <p className="text-xs text-clinic-muted mt-0.5">
                  14 hari ke depan. Minggu klinik libur; hari non-praktek dinonaktifkan otomatis.
                </p>
              </div>
              {selectedDoctor && (
                <span className="text-xs text-brand-700 font-semibold bg-brand-50 px-2.5 py-1 rounded-lg border border-brand-200">
                  {selectedDoctor.name} ({selectedDoctor.practiceDaysText})
                </span>
              )}
            </div>

            {/* Horizontal date scroll */}
            <div className="flex gap-2.5 overflow-x-auto pb-3 pt-1 scrollbar-thin focus-visible:outline-none">
              {next14Days.map((d) => {
                // Nonaktifkan hari Minggu
                const isSunday = d.isSunday;
                // Nonaktifkan hari yang bukan jadwal praktek dokter terpilih
                const isPracticeDay = selectedDoctor ? selectedDoctor.practiceDays.includes(d.dayIndex) : true;
                const isDisabled = isSunday || !isPracticeDay;
                const isSelected = bookingData.date === d.dateString;

                return (
                  <button
                    key={d.dateString}
                    type="button"
                    disabled={isDisabled}
                    onClick={() => updateBooking({ date: d.dateString })}
                    className={`flex flex-col items-center justify-center shrink-0 w-20 py-3 rounded-xl border-2 transition-all duration-150 min-h-[82px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 ${
                      isDisabled
                        ? 'opacity-40 bg-clinic-surface border-clinic-border cursor-not-allowed text-clinic-muted'
                        : isSelected
                        ? 'bg-brand-500 border-brand-500 text-white shadow-md ring-2 ring-brand-200'
                        : 'bg-white border-clinic-border hover:border-brand-300 text-clinic-ink shadow-soft'
                    }`}
                  >
                    <span className={`text-[11px] font-semibold uppercase tracking-wider ${isSelected ? 'text-white' : 'text-clinic-muted'}`}>
                      {d.isToday ? 'Hari Ini' : d.dayName}
                    </span>
                    <span className={`text-xl font-bold mt-0.5 ${isSelected ? 'text-white' : 'text-clinic-ink'}`}>
                      {d.dayNumber}
                    </span>
                    <span className={`text-[11px] font-medium ${isSelected ? 'text-brand-100' : 'text-clinic-muted'}`}>
                      {isDisabled ? (isSunday ? 'Libur' : 'Off') : d.monthName}
                    </span>
                  </button>
                );
              })}
            </div>
          </section>

          {/* Sub-step B: Hour Chips (09:00 - 18:00) */}
          <section className="space-y-4 pt-4 border-t border-clinic-border/60">
            <div className="flex items-center justify-between">
              <div>
                <h2 className="text-lg sm:text-xl font-bold text-clinic-ink tracking-tight">
                  Pilih Jam Praktek
                </h2>
                <p className="text-xs text-clinic-muted mt-0.5">
                  Slot dengan tanda "Penuh" tidak dapat dipilih.
                </p>
              </div>
              {bookingData.date && (
                <span className="text-xs font-medium text-clinic-muted">
                  {formatIndonesianDate(bookingData.date)}
                </span>
              )}
            </div>

            {!bookingData.date ? (
              <div className="rounded-xl border border-dashed border-clinic-border p-6 text-center text-sm text-clinic-muted">
                Silakan pilih salah satu tanggal praktek di atas terlebih dahulu.
              </div>
            ) : loadingSlots ? (
              <div className="py-8 text-center text-sm text-clinic-muted flex items-center justify-center gap-2">
                <span className="w-4 h-4 rounded-full border-2 border-brand-500 border-t-transparent animate-spin" />
                <span>Memuat ketersediaan slot...</span>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                {slots.map((slot) => {
                  const isSelected = bookingData.time === slot.time;
                  const isAvailable = slot.available;

                  return (
                    <button
                      key={slot.time}
                      type="button"
                      disabled={!isAvailable}
                      onClick={() => {
                        setConflictError(null);
                        updateBooking({ time: slot.time });
                      }}
                      className={`py-3 px-4 rounded-xl border-2 font-semibold text-sm transition-all duration-150 flex items-center justify-between min-h-[46px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 ${
                        !isAvailable
                          ? 'bg-clinic-surface/70 border-clinic-border text-clinic-muted/70 cursor-not-allowed line-through'
                          : isSelected
                          ? 'bg-brand-500 border-brand-500 text-white shadow-md'
                          : 'bg-white border-clinic-border hover:border-brand-300 text-clinic-ink shadow-soft'
                      }`}
                    >
                      <span className="flex items-center gap-1.5">
                        <Clock className={`w-3.5 h-3.5 ${isSelected ? 'text-white' : 'text-brand-600'}`} />
                        <span>{slot.time} WIB</span>
                      </span>
                      <span className="text-[11px] font-normal">
                        {!isAvailable ? 'Penuh' : isSelected ? 'Dipilih' : 'Tersedia'}
                      </span>
                    </button>
                  );
                })}
              </div>
            )}
          </section>

          {/* Navigation */}
          <div className="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <Button
              variant="outline"
              size="md"
              onClick={() => goToStep(1)}
              icon={ArrowLeft}
              className="w-full sm:w-auto"
            >
              Ubah Layanan & Dokter
            </Button>
            <Button
              variant="primary"
              size="lg"
              disabled={!bookingData.date || !bookingData.time}
              onClick={() => goToStep(3)}
              icon={ArrowRight}
              className="w-full sm:w-auto"
            >
              Lanjut ke Data Diri
            </Button>
          </div>

        </div>
      )}

      {/* =========================================
          LANGKAH 3: DATA DIRI & RINGKASAN
         ========================================= */}
      {currentStep === 3 && (
        <form onSubmit={handleConfirmBooking} className="space-y-8 animate-in fade-in duration-200">
          
          {/* General Submit Error (e.g. 429) */}
          {submitError && (
            <div className="bg-red-50 border border-red-300 text-red-900 px-4 py-3 rounded-xl text-sm flex items-center gap-2.5 shadow-soft">
              <AlertCircle className="w-5 h-5 shrink-0 text-red-600" />
              <span>{submitError}</span>
            </div>
          )}
          
          {/* Ringkasan Pilihan di Atas Form (Compact Summary) */}
          <section className="bg-white rounded-2xl border border-clinic-border p-5 sm:p-6 shadow-soft space-y-4">
            <div className="flex items-center justify-between border-b border-clinic-border/60 pb-3">
              <h3 className="font-bold text-base text-clinic-ink">
                Ringkasan Pilihan Jadwal
              </h3>
              <button
                type="button"
                onClick={() => goToStep(1)}
                className="text-xs font-semibold text-brand-700 hover:text-brand-900 underline"
              >
                Ubah Semua
              </button>
            </div>

            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
              <div className="space-y-1">
                <span className="text-clinic-muted block">Layanan Terpilih:</span>
                <div className="flex items-center justify-between">
                  <strong className="text-clinic-ink">{selectedService?.name}</strong>
                  <button
                    type="button"
                    onClick={() => goToStep(1)}
                    className="text-xs text-brand-700 hover:underline"
                  >
                    Ubah
                  </button>
                </div>
                <span className="text-xs text-brand-700 font-semibold">{selectedService?.priceFormatted}</span>
              </div>

              <div className="space-y-1">
                <span className="text-clinic-muted block">Dokter:</span>
                <div className="flex items-center justify-between">
                  <strong className="text-clinic-ink">{selectedDoctor?.name}</strong>
                  <button
                    type="button"
                    onClick={() => goToStep(1)}
                    className="text-xs text-brand-700 hover:underline"
                  >
                    Ubah
                  </button>
                </div>
                <span className="text-xs text-clinic-muted">{selectedDoctor?.title}</span>
              </div>

              <div className="space-y-1 sm:col-span-2 border-t border-clinic-border/40 pt-2 flex items-center justify-between">
                <div>
                  <span className="text-clinic-muted block">Jadwal Janji Temu:</span>
                  <strong className="text-clinic-ink">
                    {formatIndonesianDate(bookingData.date)}, Pukul {bookingData.time} WIB
                  </strong>
                </div>
                <button
                  type="button"
                  onClick={() => goToStep(2)}
                  className="text-xs text-brand-700 hover:underline font-medium"
                >
                  Ubah Jadwal
                </button>
              </div>
            </div>
          </section>

          {/* Form Data Diri Pasien */}
          <section className="bg-white rounded-2xl border border-clinic-border p-6 sm:p-8 shadow-soft space-y-5">
            <h3 className="font-bold text-lg text-clinic-ink tracking-tight">
              Data Pasien
            </h3>

            {/* Nama Lengkap */}
            <div className="space-y-1.5">
              <label htmlFor="patientName" className="block text-xs sm:text-sm font-semibold text-clinic-ink">
                Nama Lengkap Pasien <span className="text-red-500">*</span>
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-clinic-muted">
                  <User className="w-4 h-4" />
                </div>
                <input
                  id="patientName"
                  type="text"
                  required
                  placeholder="Contoh: Budi Pratama"
                  value={bookingData.patientName}
                  onChange={(e) => {
                    updateBooking({ patientName: e.target.value });
                    if (formErrors.patientName) setFormErrors({ ...formErrors, patientName: null });
                  }}
                  className={`w-full pl-10 pr-4 py-2.5 rounded-lg border text-sm text-clinic-ink bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 min-h-[44px] ${
                    formErrors.patientName ? 'border-red-500 ring-1 ring-red-300' : 'border-clinic-border'
                  }`}
                />
              </div>
              {formErrors.patientName && (
                <p className="text-xs text-red-600 flex items-center gap-1 mt-1">
                  <AlertCircle className="w-3.5 h-3.5" />
                  <span>{formErrors.patientName}</span>
                </p>
              )}
            </div>

            {/* Nomor WhatsApp */}
            <div className="space-y-1.5">
              <label htmlFor="patientPhone" className="block text-xs sm:text-sm font-semibold text-clinic-ink">
                Nomor WhatsApp Aktif <span className="text-red-500">*</span>
              </label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-clinic-muted">
                  <Phone className="w-4 h-4" />
                </div>
                <input
                  id="patientPhone"
                  type="tel"
                  required
                  placeholder="08123456789 atau 628123456789"
                  value={bookingData.patientPhone}
                  onChange={(e) => {
                    updateBooking({ patientPhone: e.target.value });
                    if (formErrors.patientPhone) setFormErrors({ ...formErrors, patientPhone: null });
                  }}
                  className={`w-full pl-10 pr-4 py-2.5 rounded-lg border text-sm text-clinic-ink bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 min-h-[44px] ${
                    formErrors.patientPhone ? 'border-red-500 ring-1 ring-red-300' : 'border-clinic-border'
                  }`}
                />
              </div>
              {formErrors.patientPhone ? (
                <p className="text-xs text-red-600 flex items-center gap-1 mt-1">
                  <AlertCircle className="w-3.5 h-3.5" />
                  <span>{formErrors.patientPhone}</span>
                </p>
              ) : (
                <p className="text-[11px] text-clinic-muted">
                  Nomor ini digunakan untuk verifikasi dan konfirmasi janji via WhatsApp.
                </p>
              )}
            </div>

            {/* Keluhan / Catatan Pasien (Optional max 200 chars) */}
            <div className="space-y-1.5">
              <div className="flex justify-between items-center">
                <label htmlFor="patientNotes" className="block text-xs sm:text-sm font-semibold text-clinic-ink">
                  Keluhan atau Catatan Tambahan (Opsional)
                </label>
                <span className="text-[11px] text-clinic-muted font-mono">
                  {bookingData.patientNotes.length}/200
                </span>
              </div>
              <textarea
                id="patientNotes"
                rows={3}
                maxLength={200}
                placeholder="Ceritakan keluhan gigi Anda (contoh: gigi geraham kanan ngilu saat minum dingin)..."
                value={bookingData.patientNotes}
                onChange={(e) => {
                  updateBooking({ patientNotes: e.target.value });
                  if (formErrors.patientNotes) setFormErrors({ ...formErrors, patientNotes: null });
                }}
                className={`w-full p-3 rounded-lg border text-sm text-clinic-ink bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 ${
                  formErrors.patientNotes ? 'border-red-500' : 'border-clinic-border'
                }`}
              />
            </div>

            {/* Demo Notice Note Sesuai PRD */}
            <div className="pt-2 text-xs text-clinic-muted bg-clinic-surface p-3 rounded-lg border border-clinic-border/60">
              ℹ️ <strong>Catatan:</strong> Ini adalah website demo. Data tidak disimpan ke server permanen.
            </div>

            {/* Navigation and Submission */}
            <div className="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
              <Button
                variant="outline"
                size="md"
                onClick={() => goToStep(2)}
                icon={ArrowLeft}
                className="w-full sm:w-auto"
                disabled={submitting}
              >
                Ubah Tanggal & Jam
              </Button>
              <Button
                type="submit"
                variant="primary"
                size="lg"
                loading={submitting}
                className="w-full sm:w-auto font-bold"
              >
                Konfirmasi Janji
              </Button>
            </div>

          </section>

        </form>
      )}

    </div>
  );
}
