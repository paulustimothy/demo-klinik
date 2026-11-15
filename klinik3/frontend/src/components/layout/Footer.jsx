import React from 'react';
import { Link } from 'react-router-dom';
import { useClinic } from '../../context/ClinicContext';
import { MapPin, Phone, Mail, Clock } from 'lucide-react';

export function Footer() {
  const { clinic } = useClinic();

  return (
    <footer className="bg-clinic-surface border-t border-clinic-border mt-auto text-clinic-ink">
      <div className="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div className="grid grid-cols-1 md:grid-cols-4 gap-8 lg:gap-12">
          
          {/* Brand & Tagline */}
          <div className="md:col-span-2 space-y-4">
            <div className="flex items-center gap-2.5">
              <div className="w-8 h-8 rounded-lg bg-brand-500 text-white flex items-center justify-center">
                <svg className="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M12 2C8.5 2 7 5 7 8c0 3.5 1.5 6 3 10 .8 2 2 4 2 4s1.2-2 2-4c1.5-4 3-6.5 3-10 0-3-1.5-6-5-6z" />
                </svg>
              </div>
              <span className="font-bold text-lg tracking-tight text-clinic-ink">{clinic.name}</span>
            </div>
            
            <p className="text-sm text-clinic-muted max-w-sm leading-relaxed">
              {clinic.tagline}
            </p>

            <div className="pt-2 text-xs text-clinic-muted">
              Sistem reservasi terpadu online & WhatsApp.
            </div>
          </div>

          {/* Nav Links */}
          <div className="space-y-3">
            <h4 className="text-sm font-semibold text-clinic-ink tracking-tight uppercase">Menu Halaman</h4>
            <ul className="space-y-2 text-sm text-clinic-muted">
              <li>
                <Link to="/" className="hover:text-brand-600 transition-colors">Beranda</Link>
              </li>
              <li>
                <Link to="/layanan" className="hover:text-brand-600 transition-colors">Layanan Dental</Link>
              </li>
              <li>
                <Link to="/dokter" className="hover:text-brand-600 transition-colors">Tim Dokter</Link>
              </li>
              <li>
                <Link to="/booking" className="hover:text-brand-600 transition-colors">Reservasi Online</Link>
              </li>
              <li>
                <Link to="/kontak" className="hover:text-brand-600 transition-colors">Kontak & Lokasi</Link>
              </li>
              <li>
                <Link to="/style-tile" className="text-brand-600/80 hover:text-brand-700 transition-colors text-xs font-mono">
                  → Preview Style Tile
                </Link>
              </li>
            </ul>
          </div>

          {/* Jam & Kontak */}
          <div className="space-y-3">
            <h4 className="text-sm font-semibold text-clinic-ink tracking-tight uppercase">Jam & Kontak</h4>
            <div className="space-y-2 text-sm text-clinic-muted">
              <div className="flex items-start gap-2">
                <Clock className="w-4 h-4 text-brand-600 shrink-0 mt-0.5" aria-hidden="true" />
                <span>{clinic.hoursText}</span>
              </div>
              <div className="flex items-start gap-2">
                <MapPin className="w-4 h-4 text-brand-600 shrink-0 mt-0.5" aria-hidden="true" />
                <span>{clinic.address}</span>
              </div>
              <div className="flex items-center gap-2">
                <Phone className="w-4 h-4 text-brand-600 shrink-0" aria-hidden="true" />
                <span>WhatsApp: {clinic.whatsappFormatted}</span>
              </div>
              <div className="flex items-center gap-2">
                <Mail className="w-4 h-4 text-brand-600 shrink-0" aria-hidden="true" />
                <span>{clinic.email}</span>
              </div>
            </div>
          </div>

        </div>

        {/* Bottom Bar: Copyright */}
        <div className="mt-12 pt-6 border-t border-clinic-border/80 flex flex-col sm:flex-row items-center justify-between text-xs text-clinic-muted gap-4">
          <p>© {new Date().getFullYear()} {clinic.name}. Hak Cipta Dilindungi.</p>
          <p className="text-[11px] text-clinic-muted/80">
            Aset visual placeholder terstruktur disiapkan untuk foto dental berlisensi bebas.
          </p>
        </div>
      </div>
    </footer>
  );
}
