/**
 * Data Placeholder Klinik Gigi Senyum Sehat
 * Sesuai PRD Section 4. Seluruh konten teks dan data klinik terpusat di file ini.
 */

export const clinicData = {
  name: "Klinik Gigi Senyum Sehat",
  shortName: "Senyum Sehat",
  tagline: "Perawatan gigi lembut, nyaman, dan terpercaya untuk seluruh keluarga.",
  address: "Jl. Contoh No. 12, Kota Contoh",
  hoursText: "Senin – Sabtu 09.00 – 19.00, Minggu tutup",
  schedule: [
    { days: "Senin – Jumat", hours: "09.00 – 19.00" },
    { days: "Sabtu", hours: "09.00 – 17.00" },
    { days: "Minggu / Hari Libur", hours: "Tutup" }
  ],
  whatsapp: "6281234567890",
  whatsappFormatted: "+62 812-3456-7890",
  email: "halo@senyumsehat.example",
  showDemoBanner: true,

  // Layanan dasar sesuai PRD
  services: [
    {
      id: "scaling",
      name: "Scaling Gigi",
      description: "Pembersihan karang gigi ultrasonik yang lembut dan menyegarkan napas.",
      duration: "45 menit",
      startingPrice: 300000,
      priceFormatted: "Mulai Rp 300.000",
      image: "/images/service-scaling.webp",
      aspectRatio: "4/3",
      width: 1200,
      height: 900,
      alt: "Prosedur scaling gigi ultrasonik higienis"
    },
    {
      id: "tambal",
      name: "Tambal Gigi",
      description: "Menambal gigi berlubang menggunakan resin komposit estetik warna alami.",
      duration: "30 menit",
      startingPrice: 250000,
      priceFormatted: "Mulai Rp 250.000",
      image: "/images/service-filling.webp",
      aspectRatio: "4/3",
      width: 1200,
      height: 900,
      alt: "Penambalan gigi estetik komposit"
    },
    {
      id: "cabut",
      name: "Cabut Gigi",
      description: "Pencabutan gigi aman dan minim ngilu dengan bius lokal modern terukur.",
      duration: "30 menit",
      startingPrice: 350000,
      priceFormatted: "Mulai Rp 350.000",
      image: "/images/service-extraction.webp",
      aspectRatio: "4/3",
      width: 1200,
      height: 900,
      alt: "Pencabutan gigi dengan bius lokal terukur"
    }
  ],

  // Dokter gigi sesuai PRD
  doctors: [
    {
      id: "drg-rina",
      name: "drg. Rina Pratama",
      title: "Dokter Gigi Umum",
      experience: "8 tahun",
      practiceDaysText: "Senin, Rabu, Jumat",
      practiceDays: [1, 3, 5], // 1: Mon, 3: Wed, 5: Fri
      image: "/images/doctor-rina.webp",
      aspectRatio: "3/4",
      width: 900,
      height: 1200,
      alt: "Potret drg. Rina Pratama tersenyum hangat"
    },
    {
      id: "drg-budi",
      name: "drg. Budi Santoso",
      title: "Dokter Gigi Umum",
      experience: "12 tahun",
      practiceDaysText: "Selasa, Kamis, Sabtu",
      practiceDays: [2, 4, 6], // 2: Tue, 4: Thu, 6: Sat
      image: "/images/doctor-budi.webp",
      aspectRatio: "3/4",
      width: 900,
      height: 1200,
      alt: "Potret drg. Budi Santoso ramah dan profesional"
    },
    {
      id: "drg-maya",
      name: "drg. Maya Lestari",
      title: "Dokter Gigi Umum",
      experience: "5 tahun",
      practiceDaysText: "Senin, Kamis, Sabtu",
      practiceDays: [1, 4, 6], // 1: Mon, 4: Thu, 6: Sat
      image: "/images/doctor-maya.webp",
      aspectRatio: "3/4",
      width: 900,
      height: 1200,
      alt: "Potret drg. Maya Lestari cermat dan teliti"
    }
  ],

  // 3 Testimonial sangat singkat (maks 15 kata per item sesuai PRD)
  testimonials: [
    {
      quote: "Scaling di sini sama sekali tidak ngilu. Dokternya sangat teliti dan ramah.",
      author: "Aditya N.",
      tag: "Pasien Scaling"
    },
    {
      quote: "Pelayanan tepat waktu, ruang kliniknya bersih sekali. Reservasi web sangat cepat.",
      author: "Siti Rahma",
      tag: "Pasien Tambal Gigi"
    },
    {
      quote: "Bius lokalnya halus, cabut gigi selesai tanpa rasa cemas berlebih.",
      author: "Hendra K.",
      tag: "Pasien Cabut Gigi"
    }
  ],

  // 5 FAQ (maks 2 baris pendek per jawaban sesuai PRD)
  faqs: [
    {
      question: "Bagaimana cara booking dan verifikasi WhatsApp?",
      answer: "Pilih layanan, dokter, serta jam di website, lalu kirim pesan WhatsApp otomatis untuk konfirmasi jadwal."
    },
    {
      question: "Kapan jam operasional klinik?",
      answer: "Klinik buka Senin hingga Sabtu pukul 09.00–19.00 WIB. Hari Minggu dan libur nasional tutup."
    },
    {
      question: "Metode pembayaran apa saja yang diterima?",
      answer: "Kami menerima pembayaran tunai, debit, kartu kredit, QRIS, dan transfer bank langsung di klinik."
    },
    {
      question: "Bisakah saya mengubah atau menjadwal ulang janji?",
      answer: "Bisa. Cukup balas pesan konfirmasi WhatsApp kami minimal 3 jam sebelum jadwal yang ditentukan."
    },
    {
      question: "Apa saja yang perlu dibawa saat kunjungan?",
      answer: "Cukup bawa kartu identitas (KTP) dan tunjukkan pesan verifikasi WhatsApp kepada resepsionis kami."
    }
  ],

  // 6 Foto Galeri Klinik
  gallery: [
    {
      id: "g1",
      title: "Ruang Tunggu Sejuk",
      image: "/images/gallery-waiting-room.webp",
      aspectRatio: "4/3",
      width: 1200,
      height: 900,
      alt: "Ruang tunggu klinik nyaman ber-AC"
    },
    {
      id: "g2",
      title: "Dental Unit Steril",
      image: "/images/gallery-treatment-room.webp",
      aspectRatio: "16/9",
      width: 1200,
      height: 675,
      alt: "Ruang dental unit higienis mutakhir"
    },
    {
      id: "g3",
      title: "Instrumen Medis Premium",
      image: "/images/gallery-equipment.webp",
      aspectRatio: "1/1",
      width: 900,
      height: 900,
      alt: "Instrumen dental stainless steel higienis"
    },
    {
      id: "g4",
      title: "Konsultasi Edukatif",
      image: "/images/gallery-consultation.webp",
      aspectRatio: "4/3",
      width: 1200,
      height: 900,
      alt: "Diskusi konsultasi model gigi bersama dokter"
    },
    {
      id: "g5",
      title: "Sudut Relaksasi",
      image: "/images/gallery-kids-corner.webp",
      aspectRatio: "1/1",
      width: 900,
      height: 900,
      alt: "Sudut santai klinik menenangkan pasien"
    },
    {
      id: "g6",
      title: "Standar Sterilisasi",
      image: "/images/gallery-sterilization.webp",
      aspectRatio: "4/3",
      width: 1200,
      height: 900,
      alt: "Autoclave sterilisasi instrumen dental"
    }
  ],

  // Gambar Penutup & Kontak
  heroImage: {
    src: "/images/hero-main.webp",
    aspectRatio: "16/9",
    width: 1920,
    height: 1080,
    alt: "Tim dokter gigi profesional tersenyum ramah di klinik modern"
  },
  closingBanner: {
    src: "/images/banner-closing.webp",
    aspectRatio: "21/9",
    width: 1920,
    height: 820,
    alt: "Klinik gigi Senyum Sehat ruang perawatan luas dan ramah"
  },
  contactExterior: {
    src: "/images/contact-exterior.webp",
    aspectRatio: "16/9",
    width: 1600,
    height: 900,
    alt: "Fasad depan Klinik Gigi Senyum Sehat"
  }
};

