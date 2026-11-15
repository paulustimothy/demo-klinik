import React, { useState } from 'react';
import { NavLink, Link, useLocation } from 'react-router-dom';
import { Menu, X, Calendar } from 'lucide-react';
import { useClinic } from '../../context/ClinicContext';
import { Button } from '../common/Button';

export function Navbar() {
  const [isOpen, setIsOpen] = useState(false);
  const location = useLocation();
  const { clinic } = useClinic();

  const navLinks = [
    { to: '/', label: 'Beranda' },
    { to: '/layanan', label: 'Layanan' },
    { to: '/dokter', label: 'Dokter' },
    { to: '/booking', label: 'Booking' },
    { to: '/kontak', label: 'Kontak' },
  ];

  const closeMenu = () => setIsOpen(false);

  return (
    <header className="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-clinic-border">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16 sm:h-20">
          
          {/* Logo & Brand Name */}
          <Link
            to="/"
            onClick={closeMenu}
            className="flex items-center gap-2.5 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 rounded-lg p-1"
          >
            <div className="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-200 transition-colors group-hover:bg-brand-500 group-hover:text-white">
              {/* Dental Icon SVG */}
              <svg className="w-5 h-5 transition-transform group-hover:scale-105" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M12 2C8.5 2 7 5 7 8c0 3.5 1.5 6 3 10 .8 2 2 4 2 4s1.2-2 2-4c1.5-4 3-6.5 3-10 0-3-1.5-6-5-6z" />
              </svg>
            </div>
            <div className="flex flex-col">
              <span className="font-bold text-base sm:text-lg text-clinic-ink tracking-tight leading-none">
                {clinic.name}
              </span>
              <span className="text-[11px] text-clinic-muted font-medium tracking-wide mt-1">
                Klinik Gigi Modern & Terpercaya
              </span>
            </div>
          </Link>

          {/* Desktop Navigation Links */}
          <nav aria-label="Navigasi Utama" className="hidden md:flex items-center gap-1 lg:gap-2">
            {navLinks.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                className={({ isActive }) =>
                  `px-3 py-2 rounded-lg text-sm font-medium transition-colors ${
                    isActive
                      ? 'text-brand-600 bg-brand-50/80 font-semibold'
                      : 'text-clinic-ink hover:text-brand-600 hover:bg-clinic-surface'
                  }`
                }
              >
                {link.label}
              </NavLink>
            ))}
          </nav>

          {/* Right Action: Primary CTA */}
          <div className="hidden md:flex items-center gap-3">
            <Button to="/booking" variant="primary" size="md" icon={Calendar}>
              Buat Janji
            </Button>
          </div>

          {/* Mobile Menu Button */}
          <div className="flex items-center md:hidden">
            <button
              type="button"
              onClick={() => setIsOpen(!isOpen)}
              className="p-2 rounded-lg text-clinic-ink hover:bg-clinic-surface focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 min-h-[44px] min-w-[44px] flex items-center justify-center"
              aria-expanded={isOpen}
              aria-controls="mobile-menu"
              aria-label={isOpen ? "Tutup menu" : "Buka menu navigasi"}
            >
              {isOpen ? <X className="w-6 h-6" /> : <Menu className="w-6 h-6" />}
            </button>
          </div>

        </div>
      </div>

      {/* Mobile Drawer Menu */}
      {isOpen && (
        <div id="mobile-menu" className="md:hidden border-b border-clinic-border bg-white px-4 pt-3 pb-6 space-y-2 animate-in slide-in-from-top-2 duration-150">
          <nav aria-label="Navigasi Mobile" className="space-y-1">
            {navLinks.map((link) => (
              <NavLink
                key={link.to}
                to={link.to}
                onClick={closeMenu}
                className={({ isActive }) =>
                  `block px-3 py-2.5 rounded-lg text-base font-medium transition-colors ${
                    isActive
                      ? 'bg-brand-50 text-brand-700 font-semibold'
                      : 'text-clinic-ink hover:bg-clinic-surface'
                  }`
                }
              >
                {link.label}
              </NavLink>
            ))}
          </nav>

          <div className="pt-3 border-t border-clinic-border/60">
            <Button
              to="/booking"
              variant="primary"
              size="lg"
              className="w-full"
              icon={Calendar}
              onClick={closeMenu}
            >
              Buat Janji Temu
            </Button>
          </div>
        </div>
      )}
    </header>
  );
}

