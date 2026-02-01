document.addEventListener('DOMContentLoaded', function () {
    const schedules = window.schedules || [];

    function getStatus(schedule) {
        const now = new Date();
        const start = new Date(schedule.start_date);
        const end = new Date(schedule.end_date);

        if (now < start) return 'Upcoming';
        if (now >= start && now <= end) return 'Ongoing';
        return 'Completed';
    }

    const events = schedules.map(schedule => {
        let barangays = 'N/A';
        if (Array.isArray(schedule.barangay_names) && schedule.barangay_names.length > 0) {
            barangays = schedule.barangay_names.join(', ');
        }
        const status = getStatus(schedule);
        const colorClass = 'fc-event-' + status.toLowerCase();
        const programName = schedule.aid_program ? schedule.aid_program.aid_program_name : 'N/A';
        const beneficiaryType = schedule.beneficiary_type
            ? schedule.beneficiary_type.charAt(0).toUpperCase() + schedule.beneficiary_type.slice(1)
            : '';
        const start = new Date(schedule.start_date);
        const end = new Date(schedule.end_date);
        const time = start.toLocaleDateString() + ' - ' + end.toLocaleDateString();

        return {
            title: `${programName} (${beneficiaryType})`,
            start: schedule.start_date,
            end: schedule.end_date,
            classNames: [colorClass],
            extendedProps: {
                status,
                program: programName,
                beneficiary: beneficiaryType,
                time,
                barangays
            }
        };
    });

    const calendarEl = document.getElementById('scheduleCalendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        events: events,
        eventClick(info) {
            const props = info.event.extendedProps;
            document.getElementById('modalProgram').innerText = props.program;
            document.getElementById('modalBeneficiary').innerText = props.beneficiary;
            document.getElementById('modalTime').innerText = props.time;
            document.getElementById('modalStatus').innerText = props.status;
            document.getElementById('modalBarangays').innerText = props.barangays;

            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        },
        eventDidMount(info) {
            info.el.setAttribute('title', `${info.event.title}\n${info.event.extendedProps.time}\nBarangays: ${info.event.extendedProps.barangays}`);
        }
    });

    calendar.render();
});
