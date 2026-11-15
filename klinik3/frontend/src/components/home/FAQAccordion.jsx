import React, { useState } from 'react';
import { clinicData } from '../../data/clinic';
import { ChevronDown } from 'lucide-react';

export function FAQAccordion() {
  const [openIndex, setOpenIndex] = useState(0); // Default item 1 open
  const faqs = clinicData.faqs;

  const toggle = (idx) => {
    setOpenIndex(openIndex === idx ? null : idx);
  };

  return (
    <div className="max-w-3xl mx-auto space-y-3">
      {faqs.map((faq, idx) => {
        const isOpen = openIndex === idx;
        const id = `faq-${idx}`;

        return (
          <div
            key={idx}
            className="rounded-xl border border-clinic-border bg-white shadow-soft overflow-hidden transition-colors"
          >
            <button
              type="button"
              onClick={() => toggle(idx)}
              aria-expanded={isOpen}
              aria-controls={id}
              className="w-full flex items-center justify-between p-4 sm:p-5 text-left font-semibold text-clinic-ink text-sm sm:text-base hover:text-brand-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 min-h-[48px]"
            >
              <span>{faq.question}</span>
              <ChevronDown
                className={`w-5 h-5 text-brand-600 shrink-0 ml-3 transition-transform duration-200 ${
                  isOpen ? 'rotate-180' : ''
                }`}
                aria-hidden="true"
              />
            </button>

            {isOpen && (
              <div
                id={id}
                className="px-4 pb-4 sm:px-5 sm:pb-5 text-xs sm:text-sm text-clinic-muted leading-relaxed border-t border-clinic-border/40 pt-3"
              >
                <p>{faq.answer}</p>
              </div>
            )}
          </div>
        );
      })}
    </div>
  );
}

