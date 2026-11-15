import { clinicData } from '../data/clinic';

const API_URL = import.meta.env.VITE_API_URL || null;

// Mapping fallback jika ID masih berupa string slug di mock frontend
const doctorIdMap = {
  'drg-rina': 1,
  'drg-budi': 2,
  'drg-maya': 3,
};

const serviceIdMap = {
  'scaling': 1,
  'tambal': 2,
  'cabut': 3,
};

function formatWaNumber(phone) {
  if (!phone) return clinicData.whatsappFormatted;
  const digits = String(phone).replace(/[^0-9]/g, '');
  if (digits.startsWith('62') && digits.length >= 10) {
    return `+62 ${digits.slice(2, 5)}-${digits.slice(5, 9)}-${digits.slice(9)}`;
  }
  if (digits.startsWith('08') && digits.length >= 10) {
    return `+62 ${digits.slice(1, 4)}-${digits.slice(4, 8)}-${digits.slice(8)}`;
  }
  return phone;
}

/**
 * Layer akses data terpusat.
 * Menggunakan mock data secara default, dan memanggil API riil jika VITE_API_URL dikonfigurasi.
 */
export const api = {
  async getClinicInfo() {
    if (API_URL) {
      try {
        const res = await fetch(`${API_URL}/clinic`);
        if (res.ok) {
          const raw = await res.json();
          const whatsappNumber = raw.whatsapp_number || clinicData.whatsapp;
          return {
            ...clinicData,
            name: raw.clinic_name || clinicData.name,
            clinic_name: raw.clinic_name || clinicData.name,
            shortName: raw.clinic_name ? raw.clinic_name.replace(/^Klinik Gigi\s*/i, '') : clinicData.shortName,
            tagline: raw.tagline || clinicData.tagline,
            address: raw.address || clinicData.address,
            hoursText: raw.hours_text || clinicData.hoursText,
            hours_text: raw.hours_text || clinicData.hoursText,
            whatsapp: whatsappNumber,
            whatsapp_number: whatsappNumber,
            whatsappFormatted: formatWaNumber(whatsappNumber),
            email: raw.email || clinicData.email,
            hold_minutes: raw.hold_minutes ?? clinicData.hold_minutes,
            slot_minutes: raw.slot_minutes ?? clinicData.slot_minutes,
          };
        }
      } catch (e) {
        console.warn('Gagal memuat pengaturan klinik dari API, beralih ke fallback:', e);
      }
    }
    return clinicData;
  },

  async getServices() {
    if (API_URL) {
      try {
        const res = await fetch(`${API_URL}/services`);
        if (res.ok) {
          const rawServices = await res.json();
          if (Array.isArray(rawServices) && rawServices.length > 0) {
            return rawServices.map((s, idx) => ({
              id: s.id,
              slug: s.slug || `service-${s.id}`,
              name: s.name,
              description: s.description,
              durationMinutes: s.duration_minutes || 30,
              duration: s.duration_minutes ? `${s.duration_minutes} menit` : '30 menit',
              startingPrice: s.starting_price || 0,
              starting_price: s.starting_price || 0,
              priceFormatted: s.starting_price ? `Mulai Rp ${Number(s.starting_price).toLocaleString('id-ID')}` : 'Hubungi Kami',
              image: s.image_path || '/images/service-scaling.webp',
              image_path: s.image_path || '/images/service-scaling.webp',
              aspectRatio: '4/3',
              width: 1200,
              height: 900,
              alt: `Prosedur ${s.name} higienis`,
              sort_order: s.sort_order ?? idx,
              is_active: s.is_active ?? true,
            }));
          }
        }
      } catch (e) {
        console.warn('Gagal memuat layanan dari API, beralih ke fallback:', e);
      }
    }
    return clinicData.services;
  },

  async getDoctors() {
    if (API_URL) {
      try {
        const res = await fetch(`${API_URL}/doctors`);
        if (res.ok) {
          const rawDoctors = await res.json();
          if (Array.isArray(rawDoctors) && rawDoctors.length > 0) {
            return rawDoctors.map((d, idx) => {
              const practiceDays = d.practice_days || (d.schedules ? d.schedules.map(sc => sc.weekday) : [1, 3, 5]);
              return {
                id: d.id,
                name: d.name,
                title: d.title || 'Dokter Gigi Umum',
                experience: d.years_experience !== undefined ? `${d.years_experience} tahun` : (d.experience || '5 tahun'),
                years_experience: d.years_experience,
                practiceDays: practiceDays,
                practice_days: practiceDays,
                practiceDaysText: d.practice_days_text || 'Senin – Jumat',
                practice_days_text: d.practice_days_text || 'Senin – Jumat',
                image: d.photo_path || '/images/doctor-rina.webp',
                photo_path: d.photo_path || '/images/doctor-rina.webp',
                aspectRatio: '3/4',
                width: 900,
                height: 1200,
                alt: `Potret ${d.name} ramah dan profesional`,
                schedules: d.schedules || [],
                is_active: d.is_active ?? true,
              };
            });
          }
        }
      } catch (e) {
        console.warn('Gagal memuat dokter dari API, beralih ke fallback:', e);
      }
    }
    return clinicData.doctors;
  },

  async getDoctorSlots(doctorId, dateString) {
    if (API_URL) {
      try {
        const numericDoctorId = Number(doctorIdMap[doctorId] || doctorId);
        const res = await fetch(`${API_URL}/availability?doctor_id=${numericDoctorId}&date=${dateString}`);
        if (res.ok) {
          return await res.json();
        }
      } catch (err) {
        console.warn('Gagal memuat ketersediaan jam dari API, beralih ke deterministik mock:', err);
      }
    }

    // Deterministik mock slots (09.00 - 18.00)
    const baseHours = [
      "09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00", "17:00"
    ];
    
    // Hash deterministik berdasarkan doctorId + dateString agar tidak berubah saat re-render
    const hash = (doctorId + dateString).split("").reduce((acc, char) => acc + char.charCodeAt(0), 0);
    
    return baseHours.map((time, index) => {
      // Slot terisi deterministik jika indeks match dengan modulo hash
      const isFull = (hash + index) % 4 === 0;
      return {
        time,
        available: !isFull
      };
    });
  },

  async submitBooking(bookingPayload) {
    if (API_URL) {
      const numericServiceId = Number(serviceIdMap[bookingPayload.serviceId] || bookingPayload.serviceId);
      const numericDoctorId = Number(doctorIdMap[bookingPayload.doctorId] || bookingPayload.doctorId);

      const body = {
        service_id: numericServiceId,
        doctor_id: numericDoctorId,
        date: bookingPayload.date,
        time: bookingPayload.time,
        name: bookingPayload.patientName,
        whatsapp: bookingPayload.patientPhone,
        notes: bookingPayload.patientNotes || '',
      };

      const res = await fetch(`${API_URL}/bookings`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify(body),
      });

      const data = await res.json().catch(() => ({}));

      if (!res.ok) {
        const error = new Error(data.message || 'Terjadi kesalahan sistem.');
        error.status = res.status;
        error.data = data;
        throw error;
      }

      return {
        success: true,
        code: data.code,
        status: data.status,
        expiresAt: data.expires_at,
        patientName: data.booking?.patient_name || bookingPayload.patientName,
        serviceName: data.booking?.service_name || bookingPayload.serviceName,
        doctorName: data.booking?.doctor_name || bookingPayload.doctorName,
        date: data.booking?.date || bookingPayload.date,
        dateFormatted: bookingPayload.dateFormatted,
        time: data.booking?.time || bookingPayload.time,
        patientPhone: bookingPayload.patientPhone,
        patientNotes: bookingPayload.patientNotes,
      };
    }

    // Simulasi delay 1 detik untuk loading state
    await new Promise((resolve) => setTimeout(resolve, 1000));
    
    // Generate kode KG-XXXXXX
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    let code = "KG-";
    for (let i = 0; i < 6; i++) {
      code += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    return {
      success: true,
      code,
      status: "Menunggu verifikasi",
      createdAt: new Date().toISOString(),
      ...bookingPayload
    };
  }
};
