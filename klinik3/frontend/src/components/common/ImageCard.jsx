import React from 'react';

/**
 * Komponen ImageCard dengan dukungan aspect ratio presisi & subtle micro-zoom.
 * Memenuhi House Rule 5: foto zoom 1.02-1.03x di dalam bingkai tanpa tumpah.
 */
export function ImageCard({
  src,
  alt,
  aspectRatio = '4/3', // '16/9' | '4/3' | '3/4' | '1/1' | '21/9'
  width,
  height,
  loading = 'lazy',
  className = '',
  overlay = false,
  title,
  subtitle,
  badge,
  children,
  onClick,
}) {
  const ratioClasses = {
    '16/9': 'aspect-[16/9]',
    '4/3': 'aspect-[4/3]',
    '3/4': 'aspect-[3/4]',
    '1/1': 'aspect-square',
    '21/9': 'aspect-[21/9]',
  };

  const ratioClass = ratioClasses[aspectRatio] || 'aspect-[4/3]';

  return (
    <div
      onClick={onClick}
      className={`group relative overflow-hidden rounded-card bg-clinic-surface border border-clinic-border/80 shadow-soft transition-all duration-200 hover:shadow-lift ${onClick ? 'cursor-pointer' : ''} ${className}`}
    >
      <div className={`w-full overflow-hidden ${ratioClass}`}>
        <img
          src={src}
          alt={alt}
          width={width}
          height={height}
          loading={loading}
          decoding="async"
          className="h-full w-full object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
        />
      </div>

      {overlay && (
        <div className="absolute inset-0 bg-gradient-to-t from-clinic-dark/85 via-clinic-dark/30 to-transparent pointer-events-none" />
      )}

      {badge && (
        <div className="absolute top-3 left-3 z-10">
          <span className="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-white/95 text-brand-700 shadow-sm backdrop-blur-sm">
            {badge}
          </span>
        </div>
      )}

      {(title || subtitle) && (
        <div className="absolute bottom-0 left-0 right-0 p-4 z-10 text-white">
          {title && <h3 className="text-lg font-semibold tracking-tight leading-snug">{title}</h3>}
          {subtitle && <p className="text-xs text-white/90 mt-0.5 line-clamp-1">{subtitle}</p>}
        </div>
      )}

      {children && (
        <div className="relative z-10 p-4 bg-white border-t border-clinic-border/50">
          {children}
        </div>
      )}
    </div>
  );
}

