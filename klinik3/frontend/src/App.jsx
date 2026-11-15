import React, { useEffect } from 'react';
import { Routes, Route, useLocation } from 'react-router-dom';
import { ClinicProvider } from './context/ClinicContext';
import { BookingProvider } from './context/BookingContext';
import { DemoBanner } from './components/common/DemoBanner';
import { Navbar } from './components/layout/Navbar';
import { Footer } from './components/layout/Footer';
import { FloatingWhatsApp } from './components/common/FloatingWhatsApp';
import { Home } from './pages/Home';
import { Services } from './pages/Services';
import { Doctors } from './pages/Doctors';
import { Booking } from './pages/Booking';
import { Contact } from './pages/Contact';
import { StyleTile } from './pages/StyleTile';

function ScrollToTop() {
  const { pathname } = useLocation();

  useEffect(() => {
    window.scrollTo(0, 0);
  }, [pathname]);

  return null;
}

export default function App() {
  return (
    <ClinicProvider>
      <BookingProvider>
        <div className="min-h-screen flex flex-col bg-clinic-bg text-clinic-ink selection:bg-brand-100 selection:text-brand-900">
          <ScrollToTop />
          <DemoBanner />
          <Navbar />
          <main className="flex-1" id="main-content">
            <Routes>
              <Route path="/" element={<Home />} />
              <Route path="/layanan" element={<Services />} />
              <Route path="/dokter" element={<Doctors />} />
              <Route path="/booking" element={<Booking />} />
              <Route path="/kontak" element={<Contact />} />
              <Route path="/style-tile" element={<StyleTile />} />
            </Routes>
          </main>
          <Footer />
          <FloatingWhatsApp />
        </div>
      </BookingProvider>
    </ClinicProvider>
  );
}
