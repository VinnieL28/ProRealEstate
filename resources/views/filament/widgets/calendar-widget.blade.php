<x-filament-widgets::widget>
    <x-filament::section heading="Calendar" icon="heroicon-o-calendar-days">

        @once
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
            <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>

            <style>
                .fc { font-family: inherit; }
                .fc-toolbar-title { font-size: 1rem !important; font-weight: 600; }
                .fc-button { background: #374151 !important; border-color: #4b5563 !important; color: #f9fafb !important; font-size: 0.75rem !important; padding: 0.3rem 0.6rem !important; }
                .fc-button:hover { background: #4b5563 !important; }
                .fc-button-active { background: #f59e0b !important; border-color: #f59e0b !important; }
                .fc-daygrid-day { background: transparent; }
                .fc-daygrid-day-number { color: #9ca3af; font-size: 0.75rem; }
                .fc-col-header-cell-cushion { color: #6b7280; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; }
                .fc-event { font-size: 0.72rem; border-radius: 4px; padding: 1px 4px; cursor: pointer; }
                .fc-popover { background: #1f2937 !important; border-color: #374151 !important; }
                .fc-popover-title { background: #374151 !important; color: #f9fafb !important; }
                .fc-daygrid-more-link { color: #f59e0b; font-size: 0.7rem; }
                .fc-theme-standard td, .fc-theme-standard th { border-color: #374151; }
                .fc-scrollgrid { border-color: #374151 !important; }
            </style>
        @endonce

        <div
            wire:ignore
            x-data="{
                calendar: null,
                init() {
                    const events = @js($this->getEvents());
                    const el = this.$el.querySelector('#fc-cal-{{ $this->getId() }}');
                    if (!el || typeof FullCalendar === 'undefined') return;
                    this.calendar = new FullCalendar.Calendar(el, {
                        initialView: 'dayGridMonth',
                        events: events,
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listWeek'
                        },
                        height: 'auto',
                        dayMaxEvents: 3,
                        eventClick: function(info) {
                            const p = info.event.extendedProps;
                            let detail = info.event.title + '\n';
                            if (p.type === 'task')        detail += 'Priority: ' + p.priority + '\nAssigned: ' + p.assigned;
                            if (p.type === 'deal')        detail += 'Stage: ' + p.stage + '\nProperty: ' + p.property;
                            if (p.type === 'appointment') detail += 'Phone: ' + p.phone;
                            alert(detail);
                        },
                    });
                    this.calendar.render();
                }
            }"
            class="min-h-[480px]"
        >
            <div id="fc-cal-{{ $this->getId() }}"></div>
        </div>

        {{-- Legend --}}
        <div class="mt-3 flex flex-wrap gap-3 text-xs text-slate-400">
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded" style="background:#ef4444"></span> High-priority task</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded" style="background:#f59e0b"></span> Medium task / today</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded" style="background:#8b5cf6"></span> Deal closing date</span>
            <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded" style="background:#10b981"></span> Appointment</span>
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
