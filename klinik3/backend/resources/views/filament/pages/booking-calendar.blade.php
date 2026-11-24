<x-filament-panels::page>
    <div class="space-y-4" x-data="calendarApp(@js($this->events), @entangle('selectedDoctorId'), @entangle('selectedStatus'))">
        <!-- Filter Bar Interaktif: Dokter & Status -->
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Filter per Dokter -->
                <div class="flex items-center gap-2">
                    <label for="doctorFilter" class="text-xs font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">
                        Dokter Gigi:
                    </label>
                    <select 
                        id="doctorFilter"
                        x-model="selectedDoctorId" 
                        @change="$wire.set('selectedDoctorId', selectedDoctorId ? parseInt(selectedDoctorId) : null)"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs focus:ring-primary-500 focus:border-primary-500 min-w-48 py-1.5"
                    >
                        <option value="">Semua Dokter Gigi</option>
                        @foreach($this->doctors as $doc)
                            <option value="{{ $doc->id }}">{{ $doc->name }} ({{ $doc->title }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter per Status Reservasi -->
                <div class="flex items-center gap-2">
                    <label for="statusFilter" class="text-xs font-semibold text-gray-700 dark:text-gray-200 whitespace-nowrap">
                        Status:
                    </label>
                    <select 
                        id="statusFilter"
                        x-model="selectedStatus" 
                        @change="$wire.set('selectedStatus', selectedStatus ? selectedStatus : null)"
                        class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-xs focus:ring-primary-500 focus:border-primary-500 min-w-44 py-1.5"
                    >
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Verifikasi</option>
                        <option value="verified">Terverifikasi</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                        <option value="no_show">Tidak Hadir (No-Show)</option>
                    </select>
                </div>
            </div>

            <!-- Petunjuk Warna Status (Legend) dengan Label Jelas dan Dot Warna -->
            <div class="flex flex-wrap items-center gap-3 text-xs pt-1 lg:pt-0">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Keterangan:</span>
                <span class="inline-flex items-center gap-1.5 font-medium text-gray-700 dark:text-white">
                    <span style="display:inline-block; width:10px; height:10px; border-radius:9999px; background-color:#f59e0b; box-shadow:0 0 0 1px rgba(0,0,0,0.1);"></span>
                    Menunggu Verifikasi
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-gray-700 dark:text-white">
                    <span style="display:inline-block; width:10px; height:10px; border-radius:9999px; background-color:#10b981; box-shadow:0 0 0 1px rgba(0,0,0,0.1);"></span>
                    Terverifikasi
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-gray-700 dark:text-white">
                    <span style="display:inline-block; width:10px; height:10px; border-radius:9999px; background-color:#3b82f6; box-shadow:0 0 0 1px rgba(0,0,0,0.1);"></span>
                    Selesai
                </span>
                <span class="inline-flex items-center gap-1.5 font-medium text-gray-700 dark:text-white">
                    <span style="display:inline-block; width:10px; height:10px; border-radius:9999px; background-color:#ef4444; box-shadow:0 0 0 1px rgba(0,0,0,0.1);"></span>
                    Dibatalkan / No-Show
                </span>
            </div>
        </div>

        <!-- Custom Styling untuk FullCalendar agar teks tidak terpotong dan tombol berjarak -->
        <style>
            /* Toolbar layout & jarak antar button kalender */
            .fc .fc-toolbar {
                flex-wrap: wrap !important;
                gap: 12px !important;
                margin-bottom: 1.25rem !important;
            }
            .fc .fc-toolbar-chunk {
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
            }
            .fc .fc-button-group {
                display: inline-flex !important;
                gap: 8px !important;
                margin: 0 !important;
            }
            .fc .fc-button-group > .fc-button {
                border-radius: 8px !important;
                margin: 0 !important;
            }
            .fc .fc-button-group > .fc-button:not(:first-child) {
                margin-left: 0 !important;
                border-top-left-radius: 8px !important;
                border-bottom-left-radius: 8px !important;
            }
            .fc .fc-button-group > .fc-button:not(:last-child) {
                border-top-right-radius: 8px !important;
                border-bottom-right-radius: 8px !important;
            }
            .fc .fc-today-button {
                border-radius: 8px !important;
                margin-left: 0 !important;
            }

            /* Buttons styling */
            .fc .fc-button-primary {
                background-color: #0d9488 !important;
                border-color: #0d9488 !important;
                color: #ffffff !important;
                font-size: 13px !important;
                font-weight: 500 !important;
                padding: 6px 14px !important;
                border-radius: 8px !important;
                box-shadow: 0 1px 2px rgba(0,0,0,0.1) !important;
                transition: all 0.15s ease !important;
            }
            .fc .fc-button-primary:hover {
                background-color: #0f766e !important;
                border-color: #0f766e !important;
            }
            .fc .fc-button-primary:active,
            .fc .fc-button-primary.fc-button-active {
                background-color: #115e59 !important;
                border-color: #115e59 !important;
                box-shadow: inset 0 2px 4px rgba(0,0,0,0.25) !important;
                font-weight: 700 !important;
            }

            /* FullCalendar responsive width & anti-cutoff for laptop screens */
            .fc {
                width: 100% !important;
                max-width: 100% !important;
            }
            .fc .fc-view-harness {
                width: 100% !important;
                max-width: 100% !important;
            }
            .fc .fc-scrollgrid {
                width: 100% !important;
                max-width: 100% !important;
                table-layout: fixed !important;
            }
            .fc table {
                width: 100% !important;
                table-layout: fixed !important;
            }
            .fc-col-header, .fc-daygrid-body, .fc-timegrid-body {
                width: 100% !important;
            }
            .fc-daygrid-body table, .fc-col-header table {
                width: 100% !important;
                table-layout: fixed !important;
            }

            .fc .fc-timegrid-slot {
                height: 48px !important;
            }
            .fc-timegrid-event {
                min-height: 48px !important;
                border-radius: 6px !important;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
                border: none !important;
                cursor: pointer !important;
            }
            .fc-daygrid-event {
                border-radius: 4px !important;
                margin-top: 2px !important;
                margin-bottom: 2px !important;
                cursor: pointer !important;
                border: none !important;
            }
            .fc-theme-standard td, .fc-theme-standard th {
                border-color: rgba(156, 163, 175, 0.25) !important;
            }
        </style>

        <!-- Kalender Visual Kontainer (Responsive auto width dengan overflow perlindungan) -->
        <div class="p-4 rounded-xl bg-white dark:bg-gray-800 shadow-xs border border-gray-200 dark:border-gray-700 min-h-[680px] overflow-x-auto w-full">
            <div id="fullcalendar" class="fc-theme-standard w-full"></div>
        </div>

        <!-- Modal Detail Reservasi -->
        <div 
            x-show="modalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div 
                @click.away="modalOpen = false"
                class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl border border-gray-200 dark:border-gray-700 space-y-4"
            >
                <div class="flex items-center justify-between border-b pb-3 border-gray-200 dark:border-gray-700">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Detail Reservasi</div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="activeEvent?.patient_name"></h3>
                    </div>
                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900/50 dark:text-primary-300 font-mono" x-text="activeEvent?.code"></span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Dokter Gigi</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="activeEvent?.doctor_name"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Layanan</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="activeEvent?.service_name"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Tanggal & Jam</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="`${activeEvent?.date} - ${activeEvent?.time}`"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Status</div>
                        <div class="font-semibold" :style="`color: ${activeEvent?.status_color || '#10b981'}`" x-text="activeEvent?.status"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Sumber Reservasi</div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="activeEvent?.source"></div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Nomor WhatsApp</div>
                        <div class="font-mono font-medium text-gray-900 dark:text-gray-100" x-text="activeEvent?.patient_whatsapp"></div>
                    </div>
                </div>

                <div class="text-sm bg-gray-50 dark:bg-gray-700/40 p-3 rounded-lg border border-gray-200/60 dark:border-gray-600/60">
                    <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">Catatan Pasien:</div>
                    <div class="text-gray-700 dark:text-gray-300" x-text="activeEvent?.notes"></div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                    <button 
                        type="button" 
                        @click="modalOpen = false" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 rounded-lg cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                    <!-- CTA Chat WA dengan background hijau WhatsApp asli (#25D366) -->
                    <a 
                        :href="`https://wa.me/${(activeEvent?.patient_whatsapp || '').replace(/[^0-9]/g, '')}`" 
                        target="_blank"
                        style="background-color: #25D366 !important; color: #ffffff !important; display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13.5px; text-decoration: none; box-shadow: 0 2px 4px rgba(37,211,102,0.3); transition: background-color 0.2s;"
                        onmouseover="this.style.backgroundColor='#1ebc57'"
                        onmouseout="this.style.backgroundColor='#25D366'"
                    >
                        <svg style="width: 17px; height: 17px; fill: currentColor;" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        Chat WA Pasien
                    </a>
                </div>
            </div>
        </div>

        <!-- Script FullCalendar 6 Local Bundle & Logic -->
        <script src="{{ asset('js/fullcalendar.min.js') }}"></script>
        <script src="{{ asset('js/booking-calendar.js') }}"></script>
    </div>
</x-filament-panels::page>
