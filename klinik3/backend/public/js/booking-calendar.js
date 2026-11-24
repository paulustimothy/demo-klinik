function calendarApp(initialEvents, doctorBinding, statusBinding) {
    return {
        calendar: null,
        events: initialEvents,
        selectedDoctorId: doctorBinding,
        selectedStatus: statusBinding,
        modalOpen: false,
        activeEvent: null,

        init() {
            const calendarEl = document.getElementById('fullcalendar');
            if (!calendarEl || typeof FullCalendar === 'undefined') return;

            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                handleWindowResize: true,
                windowResizeDelay: 50,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Hari Ini',
                    month: 'Bulan',
                    week: 'Pekan',
                    day: 'Hari'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '21:00:00',
                slotDuration: '00:30:00',
                allDaySlot: false,
                expandRows: true,
                eventDisplay: 'block',
                events: this.events,
                eventTimeFormat: {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                },
                // Custom DOM Renderer agar teks di bulan & pekan tidak terpotong
                eventContent: (arg) => {
                    const props = arg.event.extendedProps || {};
                    const time = props.time ? props.time.replace(' WIB', '') : (arg.timeText || '');
                    const patient = props.patient_name || '';
                    const doctor = props.doctor_short || props.doctor_name || '';
                    const service = props.service_name || '';

                    const container = document.createElement('div');
                    container.title = `${time} - ${patient} | ${props.doctor_name || doctor} (${service}) [${props.status || ''}]`;

                    if (arg.view.type === 'dayGridMonth') {
                        container.style.cssText = 'font-size: 11px; padding: 2px 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; display: flex; align-items: center; gap: 4px; line-height: 1.3;';
                        
                        const strong = document.createElement('strong');
                        strong.style.fontWeight = '700';
                        strong.textContent = time;
                        container.appendChild(strong);

                        const labelSpan = document.createElement('span');
                        labelSpan.style.cssText = 'overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
                        labelSpan.textContent = `${patient} (${doctor})`;
                        container.appendChild(labelSpan);

                        return { domNodes: [container] };
                    }

                    // Tampilan Pekan (timeGridWeek) dan Harian (timeGridDay)
                    container.style.cssText = 'font-size: 11px; line-height: 1.25; padding: 3px 5px; height: 100%; display: flex; flex-direction: column; justify-content: flex-start; overflow: hidden;';
                    
                    const titleDiv = document.createElement('div');
                    titleDiv.style.cssText = 'font-weight: 700; font-size: 11.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;';
                    titleDiv.textContent = `${time} ${patient}`;
                    container.appendChild(titleDiv);

                    const docDiv = document.createElement('div');
                    docDiv.style.cssText = 'font-size: 10.5px; opacity: 0.95; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;';
                    docDiv.textContent = `👨‍⚕️ ${doctor}`;
                    container.appendChild(docDiv);

                    if (service) {
                        const srvDiv = document.createElement('div');
                        srvDiv.style.cssText = 'font-size: 10px; opacity: 0.85; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;';
                        srvDiv.textContent = `🦷 ${service}`;
                        container.appendChild(srvDiv);
                    }

                    return { domNodes: [container] };
                },
                eventClick: (info) => {
                    this.activeEvent = info.event.extendedProps;
                    this.modalOpen = true;
                }
            });

            this.calendar.render();

            // ResizeObserver agar kalender selalu auto-fit 100% saat layout sidebar / layar laptop selesai render
            if (window.ResizeObserver) {
                const resizeObserver = new ResizeObserver(() => {
                    if (this.calendar) {
                        this.calendar.updateSize();
                    }
                });
                resizeObserver.observe(calendarEl);
                if (calendarEl.parentElement) {
                    resizeObserver.observe(calendarEl.parentElement);
                }
            }

            // Eksekusi updateSize beberapa kali saat pertama dibuka agar kolom "Sat" tidak terpotong
            requestAnimationFrame(() => {
                if (this.calendar) this.calendar.updateSize();
            });
            [50, 150, 300, 600, 1000].forEach((delay) => {
                setTimeout(() => {
                    if (this.calendar) {
                        this.calendar.updateSize();
                    }
                }, delay);
            });

            window.addEventListener('resize', () => {
                if (this.calendar) {
                    this.calendar.updateSize();
                }
            });

            // Re-render saat Livewire events berubah (filter dokter / status)
            this.$watch('events', (newEvents) => {
                if (this.calendar) {
                    this.calendar.removeAllEvents();
                    this.calendar.addEventSource(newEvents);
                    this.calendar.updateSize();
                }
            });

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', () => {
                    setTimeout(() => {
                        if (this.calendar && this.$wire) {
                            const freshEvents = this.$wire.get('events');
                            if (freshEvents) {
                                this.calendar.removeAllEvents();
                                this.calendar.addEventSource(freshEvents);
                                this.calendar.updateSize();
                            }
                        }
                    }, 50);
                });
            }
        }
    };
}
