import React from 'react';
import { Link } from 'react-router-dom';

/**
 * Komponen Tombol Terpadu
 * Menerapkan prinsip tasteskill: hover lift 2px, press active scale, focus-visible jelas.
 */
export function Button({
  children,
  variant = 'primary', // 'primary' | 'secondary' | 'outline' | 'whatsapp' | 'ghost'
  size = 'md',        // 'sm' | 'md' | 'lg'
  to,
  href,
  onClick,
  disabled = false,
  loading = false,
  className = '',
  icon: Icon,
  type = 'button',
  ...props
}) {
  const baseStyles = "inline-flex items-center justify-center font-medium rounded-lg transition-all duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2 active:scale-[0.98] disabled:opacity-60 disabled:pointer-events-none select-none";

  const sizeStyles = {
    sm: "text-xs px-3 py-1.5 gap-1.5 min-h-[36px]",
    md: "text-sm px-4 py-2.5 gap-2 min-h-[44px]", // Minimum 44px tap target
    lg: "text-base px-6 py-3.5 gap-2.5 min-h-[48px]",
  };

  const variantStyles = {
    primary: "bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white shadow-soft hover:shadow-lift hover:-translate-y-0.5",
    secondary: "bg-brand-50 text-brand-700 hover:bg-brand-100 active:bg-brand-200 border border-brand-200",
    outline: "border border-brand-500 text-brand-600 hover:bg-brand-50 active:bg-brand-100",
    whatsapp: "bg-[#25D366] hover:bg-[#20ba5a] active:bg-[#1caa52] text-white shadow-soft hover:shadow-lift hover:-translate-y-0.5",
    ghost: "text-clinic-ink hover:bg-clinic-surface active:bg-slate-200",
  };

  const combinedClasses = `${baseStyles} ${sizeStyles[size]} ${variantStyles[variant]} ${className}`;

  const content = (
    <>
      {loading ? (
        <svg className="animate-spin -ml-1 mr-2 h-4 w-4 text-current" fill="none" viewBox="0 0 24 24">
          <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
          <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
      ) : Icon ? (
        <Icon className={`shrink-0 ${size === 'sm' ? 'w-4 h-4' : 'w-5 h-5'}`} aria-hidden="true" />
      ) : null}
      <span>{children}</span>
    </>
  );

  if (to) {
    return (
      <Link to={to} className={combinedClasses} {...props}>
        {content}
      </Link>
    );
  }

  if (href) {
    return (
      <a href={href} target="_blank" rel="noopener noreferrer" className={combinedClasses} {...props}>
        {content}
      </a>
    );
  }

  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled || loading}
      className={combinedClasses}
      {...props}
    >
      {content}
    </button>
  );
}

