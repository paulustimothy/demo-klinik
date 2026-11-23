import React from "react";
import { useClinic } from "../../context/ClinicContext";
import { Info } from "lucide-react";

export function DemoBanner() {
  const { clinic } = useClinic();
  if (!clinic.showDemoBanner) return null;

  return (
    <aside
      aria-label="Informasi website demo"
      className="bg-brand-900 text-brand-100 text-xs py-1.5 px-4 text-center font-medium border-b border-brand-800 flex items-center justify-center gap-2"
    >
      <Info
        className="w-3.5 h-3.5 shrink-0 text-brand-300"
        aria-hidden="true"
      />
      <span>
        <strong>Website Demo:</strong> Desain prototipe untuk klinik gigi.
        Reservasi langsung terhubung ke WhatsApp dan panel admin.
      </span>
      <a href="/admin">Admin Page</a>
    </aside>
  );
}
