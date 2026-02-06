@extends('layouts.admin')

@section('title', 'Events Calendar')
@section('page-title', 'Events Calendar')

@section('content')
<div class="rounded-3xl bg-white dark:bg-[#1A1A1A] shadow-sm border border-gray-200 dark:border-[#272B30] overflow-hidden p-6">
    <div id="calendar"></div>
</div>

@push('scripts')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        events: '{{ route("admin.events.calendar.data") ?? "/admin/events/calendar/data" }}',
        eventClick: function(info) {
            window.location.href = '/admin/events/' + info.event.id + '/edit';
        },
        eventClassNames: function(arg) {
            return ['fc-event-custom'];
        },
        height: 'auto',
        themeSystem: 'standard',
    });
    calendar.render();

    // Dark mode support
    if (document.documentElement.classList.contains('dark')) {
        calendarEl.classList.add('fc-dark');
    }
});
</script>
<style>
.fc-event-custom {
    cursor: pointer;
    border-radius: 8px;
    padding: 4px 8px;
}
.fc-dark {
    --fc-border-color: #272B30;
    --fc-page-bg-color: #1A1A1A;
    --fc-neutral-bg-color: #0B0B0B;
    --fc-neutral-text-color: #FCFCFC;
    --fc-today-bg-color: rgba(37, 99, 235, 0.1);
}
</style>
@endpush
@endsection
