const ID_DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
const ID_MONTHS = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

/**
 * Menghasilkan daftar 14 hari ke depan dari hari ini
 */
export function getNext14Days() {
  const days = [];
  const now = new Date();

  for (let i = 0; i < 14; i++) {
    const d = new Date(now);
    d.setDate(now.getDate() + i);

    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const date = String(d.getDate()).padStart(2, '0');
    const dateString = `${year}-${month}-${date}`;

    days.push({
      dateObj: d,
      dateString,
      dayIndex: d.getDay(), // 0: Sunday, 1: Monday, ...
      dayName: ID_DAYS[d.getDay()],
      dayNumber: d.getDate(),
      monthName: ID_MONTHS[d.getMonth()].slice(0, 3),
      isToday: i === 0,
      isSunday: d.getDay() === 0,
    });
  }

  return days;
}

/**
 * Format tanggal ramah bahasa Indonesia (contoh: "Senin, 17 November 2026")
 */
export function formatIndonesianDate(dateString) {
  if (!dateString) return '';
  const [year, month, day] = dateString.split('-').map(Number);
  const d = new Date(year, month - 1, day);
  return `${ID_DAYS[d.getDay()]}, ${d.getDate()} ${ID_MONTHS[d.getMonth()]} ${d.getFullYear()}`;
}

