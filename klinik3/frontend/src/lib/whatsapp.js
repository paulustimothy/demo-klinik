import { clinicData } from '../data/clinic';

/**
 * Validasi nomor WhatsApp format Indonesia (08xx atau 628xx atau +628xx)
 */
export function validateIndonesianPhone(phone) {
  if (!phone) return { valid: false, message: "Nomor WhatsApp wajib diisi." };
  
  // Bersihkan karakter non-digit selain '+'
  const cleaned = phone.replace(/[^0-9+]/g, "");
  
  // Regex: 08xx atau 628xx atau +628xx, panjang 10-14 digit
  const idRegex = /^(\+62|62|0)8[1-9][0-9]{6,11}$/;
  if (!idRegex.test(cleaned)) {
    return {
      valid: false,
      message: "Masukkan nomor WhatsApp yang valid (contoh: 08123456789 atau 628123456789)."
    };
  }
  return { valid: true, message: "" };
}

/**
 * Normalisasi nomor WhatsApp untuk wa.me (angka saja, pastikan awalan 62)
 */
function normalizeWaNumber(phone) {
  if (!phone) return clinicData.whatsapp;
  let cleaned = String(phone).replace(/[^0-9]/g, '');
  if (cleaned.startsWith('08')) {
    cleaned = '62' + cleaned.slice(1);
  }
  return cleaned || clinicData.whatsapp;
}

/**
 * Menghasilkan link WhatsApp untuk verifikasi booking
 * Format sesuai PRD:
 * Halo Klinik Gigi Senyum Sehat, saya {nama}. Saya ingin verifikasi janji temu dengan kode {kode}: Layanan {layanan}, Dokter {dokter}, Tanggal {tanggal}, Jam {jam}. Terima kasih.
 * Jika status dibatalkan, pesan disesuaikan: booking telah dibatalkan.
 */
export function getBookingWhatsAppUrl(
  { nama, kode, layanan, dokter, tanggal, jam, status },
  customWhatsapp = null,
  customClinicName = null
) {
  const targetClinic = customClinicName || clinicData.name;
  const targetPhone = normalizeWaNumber(customWhatsapp || clinicData.whatsapp);

  let message;
  if (status === 'Dibatalkan' || status === 'CANCELLED') {
    message = `Halo ${targetClinic}, booking telah dibatalkan. Reservasi janji temu dengan kode ${kode} telah dibatalkan. Terima kasih.`;
  } else {
    message = `Halo ${targetClinic}, saya ${nama}. Saya ingin verifikasi janji temu dengan kode ${kode}: Layanan ${layanan}, Dokter ${dokter}, Tanggal ${tanggal}, Jam ${jam}. Terima kasih.`;
  }

  return `https://wa.me/${targetPhone}?text=${encodeURIComponent(message)}`;
}

/**
 * Menghasilkan link WhatsApp untuk pertanyaan umum
 */
export function getGeneralInquiryWhatsAppUrl(customWhatsapp = null, customClinicName = null) {
  const targetClinic = customClinicName || clinicData.name;
  const targetPhone = normalizeWaNumber(customWhatsapp || clinicData.whatsapp);
  const message = `Halo ${targetClinic}, saya ingin bertanya.`;
  return `https://wa.me/${targetPhone}?text=${encodeURIComponent(message)}`;
}
