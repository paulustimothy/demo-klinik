import React from 'react';
import { MessageCircle } from 'lucide-react';
import { useClinic } from '../../context/ClinicContext';
import { getGeneralInquiryWhatsAppUrl } from '../../lib/whatsapp';

export function FloatingWhatsApp() {
  const { clinic } = useClinic();
  const url = getGeneralInquiryWhatsAppUrl(clinic.whatsapp, clinic.name);

  return (
    <aside aria-label="Bantuan WhatsApp" className="fixed bottom-6 right-6 z-50">
      <a
        href={url}
        target="_blank"
        rel="noopener noreferrer"
        aria-label={`Chat WhatsApp ${clinic.name}`}
        className="group flex items-center gap-2 bg-[#25D366] hover:bg-[#20ba5a] active:bg-[#1caa52] text-white px-4 py-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#25D366] focus-visible:ring-offset-2 min-h-[44px]"
      >
        <MessageCircle className="w-5 h-5 fill-current shrink-0" aria-hidden="true" />
        <span className="text-xs sm:text-sm font-semibold tracking-wide pr-1">Chat WhatsApp</span>
      </a>
    </aside>
  );
}
