import React from 'react';
import { Check } from 'lucide-react';

const steps = [
  { number: 1, title: 'Layanan & Dokter' },
  { number: 2, title: 'Tanggal & Jam' },
  { number: 3, title: 'Data Diri & Ringkasan' },
];

export function Stepper({ currentStep = 1, onStepClick }) {
  return (
    <nav aria-label="Proses reservasi" className="w-full max-w-2xl mx-auto py-3">
      <ol className="flex items-center justify-between w-full relative">
        {/* Connecting track line */}
        <div className="absolute top-1/2 left-0 right-0 -translate-y-1/2 h-0.5 bg-clinic-border -z-0" />
        
        {/* Active progress fill */}
        <div
          className="absolute top-1/2 left-0 -translate-y-1/2 h-0.5 bg-brand-500 transition-all duration-300 -z-0"
          style={{
            width: currentStep === 1 ? '0%' : currentStep === 2 ? '50%' : '100%',
          }}
        />

        {steps.map((step) => {
          const isCompleted = currentStep > step.number;
          const isActive = currentStep === step.number;
          const isPending = currentStep < step.number;

          return (
            <li
              key={step.number}
              className="relative z-10 flex flex-col items-center"
              aria-current={isActive ? 'step' : undefined}
            >
              <button
                type="button"
                onClick={() => isCompleted && onStepClick && onStepClick(step.number)}
                disabled={!isCompleted || !onStepClick}
                className={`w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-200 border-2 select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 ${
                  isCompleted
                    ? 'bg-brand-500 border-brand-500 text-white cursor-pointer hover:bg-brand-600'
                    : isActive
                    ? 'bg-white border-brand-500 text-brand-600 ring-4 ring-brand-100 shadow-sm'
                    : 'bg-white border-clinic-border text-clinic-muted cursor-not-allowed'
                }`}
                aria-label={`Langkah ${step.number}: ${step.title}`}
              >
                {isCompleted ? (
                  <Check className="w-4 h-4 stroke-[2.5]" aria-hidden="true" />
                ) : (
                  <span>{step.number}</span>
                )}
              </button>

              <span
                className={`mt-2 text-xs font-medium tracking-tight text-center max-w-[100px] sm:max-w-none transition-colors duration-150 ${
                  isActive
                    ? 'text-brand-700 font-semibold'
                    : isCompleted
                    ? 'text-clinic-ink'
                    : 'text-clinic-muted'
                }`}
              >
                {step.title}
              </span>
            </li>
          );
        })}
      </ol>
    </nav>
  );
}

