import React, { useState } from 'react';
import { clinicData } from '../../data/clinic';
import { ChevronLeft, ChevronRight, Quote } from 'lucide-react';

export function TestimonialCarousel() {
  const [current, setCurrent] = useState(0);
  const items = clinicData.testimonials;

  const next = () => setCurrent((prev) => (prev + 1) % items.length);
  const prev = () => setCurrent((prev) => (prev - 1 + items.length) % items.length);

  return (
    <div className="relative max-w-3xl mx-auto bg-white rounded-card border border-clinic-border p-6 sm:p-8 shadow-soft">
      <div className="flex items-center justify-between mb-4">
        <div className="flex items-center gap-2 text-brand-600">
          <Quote className="w-5 h-5 fill-current opacity-80" aria-hidden="true" />
          <span className="text-xs font-semibold uppercase tracking-wider text-clinic-muted">Ulasan Pasien</span>
        </div>
        
        {/* Carousel controls */}
        <div className="flex items-center gap-1.5">
          <button
            type="button"
            onClick={prev}
            aria-label="Testimoni sebelumnya"
            className="p-1.5 rounded-lg border border-clinic-border text-clinic-ink hover:bg-clinic-surface active:scale-95 transition-all min-h-[36px] min-w-[36px] flex items-center justify-center focus-visible:ring-2 focus-visible:ring-brand-500"
          >
            <ChevronLeft className="w-4 h-4" />
          </button>
          <button
            type="button"
            onClick={next}
            aria-label="Testimoni berikutnya"
            className="p-1.5 rounded-lg border border-clinic-border text-clinic-ink hover:bg-clinic-surface active:scale-95 transition-all min-h-[36px] min-w-[36px] flex items-center justify-center focus-visible:ring-2 focus-visible:ring-brand-500"
          >
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* Quote text (maks 15 kata per item) */}
      <div className="min-h-[84px] flex flex-col justify-center">
        <blockquote className="text-base sm:text-lg text-clinic-ink font-medium leading-snug">
          "{items[current].quote}"
        </blockquote>
        <div className="mt-3 flex items-center gap-2">
          <span className="text-xs font-bold text-clinic-ink">{items[current].author}</span>
          <span className="text-[11px] text-brand-700 bg-brand-50 px-2 py-0.5 rounded font-medium border border-brand-200">
            {items[current].tag}
          </span>
        </div>
      </div>

      {/* Dots indicator */}
      <div className="flex justify-center gap-1.5 mt-4">
        {items.map((_, idx) => (
          <button
            key={idx}
            onClick={() => setCurrent(idx)}
            aria-label={`Beralih ke ulasan ${idx + 1}`}
            className={`h-1.5 rounded-full transition-all duration-200 ${
              current === idx ? 'w-6 bg-brand-500' : 'w-1.5 bg-clinic-border hover:bg-slate-300'
            }`}
          />
        ))}
      </div>
    </div>
  );
}

