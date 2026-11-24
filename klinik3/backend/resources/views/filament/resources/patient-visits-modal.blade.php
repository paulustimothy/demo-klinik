<div class="patient-visits-container">
    <style>
        .patient-visits-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            font-family: inherit;
        }

        /* 1. Header Hero Card */
        .pvc-hero {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }
        .pvc-hero-top {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .pvc-hero-top {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .pvc-patient-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .pvc-avatar {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background: rgba(13, 148, 136, 0.25);
            border: 1.5px solid rgba(45, 212, 191, 0.5);
            color: #2dd4bf;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            flex-shrink: 0;
            text-transform: uppercase;
        }
        .pvc-name-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .pvc-patient-name {
            font-size: 1.25rem;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
            letter-spacing: -0.01em;
        }
        .pvc-status-tag {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            background: rgba(45, 212, 191, 0.2);
            color: #5eead4;
            border: 1px solid rgba(45, 212, 191, 0.4);
        }
        .pvc-meta-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 0.35rem;
            font-size: 0.8rem;
            color: #cbd5e1;
            flex-wrap: wrap;
        }
        .pvc-phone-link {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-family: monospace;
            font-weight: 600;
            color: #f1f5f9;
        }
        .pvc-wa-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background-color: #25D366;
            color: #ffffff !important;
            font-size: 0.825rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.15s ease, transform 0.15s ease;
            box-shadow: 0 2px 6px rgba(37, 211, 102, 0.3);
            white-space: nowrap;
        }
        .pvc-wa-cta:hover {
            background-color: #1ebc57;
            transform: translateY(-1px);
        }

        /* Hero Stats Cards */
        .pvc-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
            padding-top: 0.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .pvc-stat-box {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .pvc-stat-box-lbl {
            font-size: 0.7rem;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .pvc-stat-box-val {
            font-size: 1rem;
            font-weight: 700;
            color: #ffffff;
            margin-top: 0.15rem;
        }

        /* 2. Daftar Kunjungan (Visit Cards) */
        .pvc-list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.25rem;
        }
        .pvc-list-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #111827;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .dark .pvc-list-title {
            color: #f9fafb;
        }

        .pvc-empty-box {
            text-align: center;
            padding: 3rem 1.5rem;
            border-radius: 12px;
            border: 1.5px dashed #cbd5e1;
            background: #f8fafc;
            color: #64748b;
        }
        .dark .pvc-empty-box {
            border-color: #334155;
            background: rgba(30, 41, 59, 0.4);
            color: #94a3b8;
        }

        .pvc-visits-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        /* Visit Card */
        .pvc-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 1.15rem 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .dark .pvc-card {
            border-color: #27272a;
            background: #18181b;
            box-shadow: none;
        }
        .pvc-card:hover {
            border-color: #0d9488;
            box-shadow: 0 3px 10px rgba(13, 148, 136, 0.08);
        }
        .dark .pvc-card:hover {
            border-color: #14b8a6;
        }

        /* Card Header */
        .pvc-card-header {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        @media (min-width: 640px) {
            .pvc-card-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }
        }
        .dark .pvc-card-header {
            border-bottom-color: #27272a;
        }
        .pvc-date-badge {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            flex-wrap: wrap;
        }
        .pvc-date-text {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
        }
        .dark .pvc-date-text {
            color: #f8fafb;
        }
        .pvc-time-pill {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            background: #f1f5f9;
            color: #334155;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        .dark .pvc-time-pill {
            background: #27272a;
            color: #cbd5e1;
        }
        .pvc-code-pill {
            font-family: monospace;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .dark .pvc-code-pill {
            background: rgba(13, 148, 136, 0.15);
            color: #2dd4bf;
            border-color: rgba(45, 212, 191, 0.3);
        }

        /* Status Badges */
        .pvc-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            white-space: nowrap;
        }
        .pvc-status-dot {
            width: 7px;
            height: 7px;
            border-radius: 9999px;
        }

        /* Status Colors - Light & Dark */
        .pvc-st-verified {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .pvc-st-verified .pvc-status-dot { background: #10b981; }
        .dark .pvc-st-verified {
            background: rgba(16, 185, 129, 0.18);
            color: #6ee7b7;
            border-color: rgba(52, 211, 153, 0.4);
        }

        .pvc-st-completed {
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .pvc-st-completed .pvc-status-dot { background: #3b82f6; }
        .dark .pvc-st-completed {
            background: rgba(59, 130, 246, 0.18);
            color: #93c5fd;
            border-color: rgba(96, 165, 250, 0.4);
        }

        .pvc-st-pending {
            background: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .pvc-st-pending .pvc-status-dot { background: #f59e0b; }
        .dark .pvc-st-pending {
            background: rgba(245, 158, 11, 0.18);
            color: #fde68a;
            border-color: rgba(251, 191, 36, 0.4);
        }

        .pvc-st-cancelled, .pvc-st-no_show {
            background: #fff1f2;
            color: #9f1239;
            border: 1px solid #fecdd3;
        }
        .pvc-st-cancelled .pvc-status-dot, .pvc-st-no_show .pvc-status-dot { background: #f43f5e; }
        .dark .pvc-st-cancelled, .dark .pvc-st-no_show {
            background: rgba(244, 63, 94, 0.18);
            color: #fda4af;
            border-color: rgba(251, 113, 133, 0.4);
        }

        .pvc-st-expired {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .pvc-st-expired .pvc-status-dot { background: #94a3b8; }
        .dark .pvc-st-expired {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5e1;
            border-color: rgba(148, 163, 184, 0.3);
        }

        /* Card Body Grid */
        .pvc-card-body {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            padding: 0.75rem 0;
        }
        .pvc-item-box {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }
        .pvc-item-label {
            font-size: 0.7rem;
            color: #64748b;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .dark .pvc-item-label {
            color: #94a3b8;
        }
        .pvc-item-val {
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }
        .dark .pvc-item-val {
            color: #e2e8f0;
        }

        /* Source Pills */
        .pvc-src-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.15rem 0.6rem;
            border-radius: 9999px;
            width: fit-content;
        }
        .pvc-src-website {
            background: #f0f9ff;
            color: #0369a1;
            border: 1px solid #bae6fd;
        }
        .dark .pvc-src-website {
            background: rgba(14, 165, 233, 0.15);
            color: #7dd3fc;
            border-color: rgba(56, 189, 248, 0.3);
        }
        .pvc-src-whatsapp {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .dark .pvc-src-whatsapp {
            background: rgba(16, 185, 129, 0.15);
            color: #6ee7b7;
            border-color: rgba(52, 211, 153, 0.3);
        }
        .pvc-src-phone {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .dark .pvc-src-phone {
            background: rgba(245, 158, 11, 0.15);
            color: #fde68a;
            border-color: rgba(251, 191, 36, 0.3);
        }
        .pvc-src-walk_in {
            background: #faf5ff;
            color: #7e22ce;
            border: 1px solid #e9d5ff;
        }
        .dark .pvc-src-walk_in {
            background: rgba(168, 85, 247, 0.15);
            color: #d8b4fe;
            border-color: rgba(192, 132, 252, 0.3);
        }

        /* Notes Box */
        .pvc-notes-box {
            margin-top: 0.25rem;
            padding: 0.6rem 0.85rem;
            border-radius: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            font-size: 0.8rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .dark .pvc-notes-box {
            background: #202023;
            border-color: #2e2e33;
        }
        .pvc-notes-lbl {
            font-weight: 600;
            color: #475569;
            white-space: nowrap;
        }
        .dark .pvc-notes-lbl {
            color: #94a3b8;
        }
        .pvc-notes-txt {
            color: #334155;
            line-height: 1.4;
        }
        .dark .pvc-notes-txt {
            color: #cbd5e1;
        }
    </style>

    <!-- 1. Hero Card: Profil & Kontak Pasien -->
    @php
        $waRaw = preg_replace('/[^\d]/', '', (string) $patient->whatsapp);
        if (str_starts_with($waRaw, '08')) {
            $waFormatted = '62' . substr($waRaw, 1);
        } else {
            $waFormatted = $waRaw;
        }
        $initials = collect(explode(' ', trim($patient->name)))
            ->map(fn($part) => substr($part, 0, 1))
            ->take(2)
            ->implode('');
        if (empty($initials)) {
            $initials = 'PS';
        }
    @endphp
    <div class="pvc-hero">
        <div class="pvc-hero-top">
            <div class="pvc-patient-info">
                <div class="pvc-avatar">
                    {{ strtoupper($initials) }}
                </div>
                <div>
                    <div class="pvc-name-row">
                        <span class="pvc-patient-name">{{ $patient->name }}</span>
                        <span class="pvc-status-tag">Pasien Terdaftar</span>
                    </div>
                    <div class="pvc-meta-row">
                        <span class="pvc-phone-link">
                            <svg style="width: 13px; height: 13px; fill: currentColor;" viewBox="0 0 24 24">
                                <path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                            </svg>
                            {{ $patient->whatsapp }}
                        </span>
                        <span>•</span>
                        <span>Terdaftar: {{ \Carbon\Carbon::parse($patient->created_at)->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- CTA Chat WA -->
            <div>
                <a 
                    href="https://wa.me/{{ $waFormatted }}?text={{ urlencode('Halo ' . $patient->name . ', salam hangat dari Klinik Gigi Senyum Sehat.') }}" 
                    target="_blank"
                    class="pvc-wa-cta"
                    title="Buka WhatsApp Pasien"
                >
                    <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                    </svg>
                    Chat WA Pasien
                </a>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="pvc-stats-grid">
            <div class="pvc-stat-box">
                <div class="pvc-stat-box-lbl">Total Kunjungan</div>
                <div class="pvc-stat-box-val" style="color: #2dd4bf;">{{ $bookings->count() }} Kali</div>
            </div>
            <div class="pvc-stat-box">
                <div class="pvc-stat-box-lbl">Kunjungan Terakhir</div>
                <div class="pvc-stat-box-val" style="font-size: 0.9rem;">
                    {{ $patient->last_visit ?? ($bookings->first() ? \Carbon\Carbon::parse($bookings->first()->date)->translatedFormat('d M Y') : '-') }}
                </div>
            </div>
            <div class="pvc-stat-box">
                <div class="pvc-stat-box-lbl">Status Pasien</div>
                <div class="pvc-stat-box-val" style="color: #6ee7b7; font-size: 0.9rem;">Aktif</div>
            </div>
        </div>
    </div>

    <!-- 2. Section: Daftar Kunjungan / Reservasi Pasien -->
    <div>
        <div class="pvc-list-header">
            <h4 class="pvc-list-title">
                <svg style="width: 18px; height: 18px; color: #0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Riwayat Reservasi & Kunjungan ({{ $bookings->count() }})
            </h4>
        </div>

        @if($bookings->isEmpty())
            <div class="pvc-empty-box">
                <svg style="width: 40px; height: 40px; margin: 0 auto; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div style="font-weight: 700; font-size: 1rem; margin-top: 0.75rem;">Belum Ada Riwayat Kunjungan</div>
                <p style="font-size: 0.85rem; margin-top: 0.25rem;">Pasien ini belum memiliki jadwal atau riwayat kunjungan di klinik.</p>
            </div>
        @else
            <div class="pvc-visits-list">
                @foreach($bookings as $b)
                    @php
                        $statusVal = $b->status->value;
                        $sourceVal = $b->source?->value ?? 'website';
                    @endphp
                    <div class="pvc-card">
                        <!-- Card Header: Tanggal, Waktu, Kode & Status -->
                        <div class="pvc-card-header">
                            <div class="pvc-date-badge">
                                <span class="pvc-date-text">
                                    {{ \Carbon\Carbon::parse($b->date)->translatedFormat('l, d F Y') }}
                                </span>
                                <span class="pvc-time-pill">
                                    <svg style="width: 13px; height: 13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ substr($b->start_time, 0, 5) }} WIB
                                </span>
                                <span class="pvc-code-pill" title="Kode Booking">
                                    #{{ $b->code }}
                                </span>
                            </div>

                            <!-- Status Pill -->
                            <div>
                                <span class="pvc-status-pill pvc-st-{{ $statusVal }}">
                                    <span class="pvc-status-dot"></span>
                                    {{ $b->status->label() }}
                                </span>
                            </div>
                        </div>

                        <!-- Card Body: Layanan, Dokter & Sumber -->
                        <div class="pvc-card-body">
                            <!-- Layanan -->
                            <div class="pvc-item-box">
                                <span class="pvc-item-label">Layanan Gigi</span>
                                <span class="pvc-item-val">
                                    <span>🦷</span>
                                    <span>{{ $b->service?->name ?? 'Layanan Umum' }}</span>
                                </span>
                            </div>

                            <!-- Dokter -->
                            <div class="pvc-item-box">
                                <span class="pvc-item-label">Dokter Gigi</span>
                                <span class="pvc-item-val">
                                    <span>👨‍⚕️</span>
                                    <span>{{ $b->doctor?->name ?? 'Dokter Jaga' }}</span>
                                </span>
                            </div>

                            <!-- Sumber Reservasi -->
                            <div class="pvc-item-box">
                                <span class="pvc-item-label">Sumber Reservasi</span>
                                <div>
                                    <span class="pvc-src-pill pvc-src-{{ $sourceVal }}">
                                        @if($sourceVal === 'whatsapp')
                                            <span>💬 WhatsApp</span>
                                        @elseif($sourceVal === 'phone')
                                            <span>📞 Telepon</span>
                                        @elseif($sourceVal === 'walk_in')
                                            <span>🚶 Walk-in</span>
                                        @else
                                            <span>🌐 Website</span>
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Pasien (Jika Ada) -->
                        @if($b->notes && $b->notes !== '-')
                            <div class="pvc-notes-box">
                                <span class="pvc-notes-lbl">Catatan:</span>
                                <span class="pvc-notes-txt">{{ $b->notes }}</span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
