@props(['name', 'class' => 'w-5 h-5'])

@php
    $icon = trim((string) $name);
@endphp

<svg {{ $attributes->merge(['class' => $class . ' inline-block shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($icon)
        @case('home')
            <path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V9.5"/>
            @break

        @case('home_work')
            <path d="M2 12l5.5-4.5L13 12v8H2v-8z"/><path d="M6 20v-4h3v4"/><path d="M10 9V4h11v16h-8"/><path d="M14 8h2M17.5 8h1M14 12h2M17.5 12h1"/>
            @break

        @case('person')
            <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>
            @break

        @case('account_circle')
            <circle cx="12" cy="12" r="9.5"/><circle cx="12" cy="9.2" r="2.8"/><path d="M6.8 18.6c1.1-2.3 3-3.6 5.2-3.6s4.1 1.3 5.2 3.6"/>
            @break

        @case('account_box')
            <rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="12" cy="9.5" r="2.7"/><path d="M7.2 18.5c1-2.2 2.8-3.5 4.8-3.5s3.8 1.3 4.8 3.5"/>
            @break

        @case('person_add')
            <circle cx="9.5" cy="8" r="3.5"/><path d="M3 20c0-3.5 3-5.5 6.5-5.5S16 16.5 16 20"/><path d="M19 8v6M16 11h6"/>
            @break

        @case('person_off')
            <circle cx="10" cy="8" r="3.5"/><path d="M3.5 20c0-3.3 2.9-5.5 6.5-5.5 1.3 0 2.6.3 3.6.8"/><path d="M17 14l4 4m0-4l-4 4"/>
            @break

        @case('group')
        @case('groups')
        @case('supervisor_account')
            <circle cx="9" cy="8" r="3.2"/><path d="M2.5 19.5c0-3.2 2.9-5.2 6.5-5.2s6.5 2 6.5 5.2"/><circle cx="16.5" cy="9.5" r="2.5"/><path d="M16.5 14.5c2.7 0 5 1.5 5 4.5"/>
            @break

        @case('manage_accounts')
            <circle cx="10" cy="8" r="3.5"/><path d="M3 20c0-3.5 3.1-5.5 7-5.5 1 0 2 .2 2.8.5"/><circle cx="17.5" cy="16.5" r="2.5"/><path d="M17.5 12.8v1.2m0 5v1.2m3.7-3.7H20m-5 0h-1.2m6-2.6l-.9.9m-3.6 3.6l-.9.9m5.4 0l-.9-.9m-3.6-3.6l-.9-.9"/>
            @break

        @case('admin_panel_settings')
        @case('shield')
        @case('shield_person')
        @case('verified_user')
            <path d="M12 2.5L4.5 5.8v5.7c0 5 3.2 8.7 7.5 10 4.3-1.3 7.5-5 7.5-10V5.8L12 2.5z"/><path d="M9 12.2l2 2 4-4"/>
            @break

        @case('description')
        @case('document_scanner')
        @case('receipt_long')
        @case('fact_check')
        @case('checklist')
        @case('route')
            <path d="M7 3h7l5 5v13a1 1 0 01-1 1H7a1 1 0 01-1-1V4a1 1 0 011-1z"/><path d="M14 3v5h5"/><path d="M9.5 13h5M9.5 17h3.5"/>
            @break

        @case('badge')
        @case('work')
            <rect x="3" y="6" width="18" height="13" rx="2"/><circle cx="9" cy="12" r="2"/><path d="M6.5 16.5c.6-1.3 1.5-2 2.5-2s1.9.7 2.5 2"/><path d="M14 11h4.5M14 14.5h3.5"/>
            @break

        @case('account_balance')
        @case('domain')
            <path d="M3 10l9-6 9 6"/><path d="M4 10h16"/><path d="M6 10v8M10 10v8M14 10v8M18 10v8"/><path d="M3 18h18M2 21h20"/>
            @break

        @case('payments')
        @case('monetization_on')
        @case('wallet')
            <rect x="2.5" y="6" width="19" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/><path d="M6 9.5h.01M18 14.5h.01"/>
            @break

        @case('calendar_month')
        @case('edit_calendar')
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/>
            @break

        @case('schedule')
        @case('timer')
        @case('pending')
            <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>
            @break

        @case('check_circle')
        @case('verified')
        @case('check')
        @case('mark_email_read')
            <circle cx="12" cy="12" r="9"/><path d="M8.5 12.2l2.3 2.3 4.8-4.8"/>
            @break

        @case('check_plain')
            <path d="M5 12.5l4.2 4.2L19 7"/>
            @break

        @case('close')
        @case('x')
        @case('cancel')
            <path d="M6 6l12 12M18 6L6 18"/>
            @break

        @case('x_circle')
            <circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/>
            @break

        @case('warning')
        @case('error')
        @case('alert')
            <path d="M10.3 3.9L2.4 17.5A1.8 1.8 0 004 20h16a1.8 1.8 0 001.6-2.5L13.7 3.9a1.9 1.9 0 00-3.4 0z"/><path d="M12 9v4M12 16.5h.01"/>
            @break

        @case('info')
            <circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>
            @break

        @case('logout')
            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/><path d="M10 17l5-5-5-5M15 12H3"/>
            @break

        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16"/>
            @break

        @case('public')
        @case('globe')
            <circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9s1.3-6.3 3.8-9z"/>
            @break

        @case('hotel')
        @case('night_shelter')
            <path d="M3 19V7M3 14h18v5M21 19v-3a3 3 0 00-3-3h-7v-3H7a2 2 0 00-2 2v2"/><circle cx="7.5" cy="9.5" r="1.5"/>
            @break

        @case('flight')
        @case('flight_takeoff')
            <path d="M21 16v-2l-8-5V3.5a1.5 1.5 0 00-3 0V9l-8 5v2l8-2.5V19l-2 1.5V22l3.5-1 3.5 1v-1.5L13 19v-5.5l8 2.5z"/>
            @break

        @case('mosque')
            <path d="M12 3c1.5 1.5 4 3 4 5.5H8C8 6 10.5 4.5 12 3z"/><path d="M5 10h14v10H5z"/><path d="M10 20v-4a2 2 0 114 0v4"/><path d="M3 12v8M21 12v8"/>
            @break

        @case('inventory_2')
            <path d="M3 7h18v13a1 1 0 01-1 1H4a1 1 0 01-1-1V7z"/><path d="M2 4h20v3H2z"/><path d="M10 12h4"/>
            @break

        @case('edit')
        @case('save_as')
            <path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 013 3L7 19l-4 1 1-4 12.5-12.5z"/>
            @break

        @case('delete')
        @case('delete_outline')
        @case('trash')
            <path d="M3 6h18M8 6V4h8v2M6 6l1 14a1 1 0 001 1h8a1 1 0 001-1l1-14"/><path d="M10 11v6M14 11v6"/>
            @break

        @case('add')
        @case('add_box')
        @case('plus')
            <path d="M12 5v14M5 12h14"/>
            @break

        @case('search')
            <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>
            @break

        @case('call')
        @case('phone')
            <path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>
            @break

        @case('location_on')
        @case('pin_drop')
            <path d="M12 21s-6-5.5-6-11a6 6 0 1112 0c0 5.5-6 11-6 11z"/><circle cx="12" cy="10" r="2.2"/>
            @break

        @case('lock_reset')
        @case('lock')
        @case('key')
            <rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>
            @break

        @case('send')
            <path d="M22 2L11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>
            @break

        @case('upload_file')
            <path d="M14 3H7a1 1 0 00-1 1v16a1 1 0 001 1h10a1 1 0 001-1V8l-4-5z"/><path d="M14 3v5h4M12 17v-6m-2.5 2.5L12 11l2.5 2.5"/>
            @break

        @case('download')
            <path d="M12 3v12m-4-4l4 4 4-4"/><path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
            @break

        @case('save')
            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/>
            @break

        @case('visibility')
        @case('eye')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
            @break

        @case('eye_off')
            <path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8"/><path d="M9.9 5.1A10.6 10.6 0 0112 5c6.5 0 10 7 10 7a16.7 16.7 0 01-3.2 4.3M6.5 6.5A16.5 16.5 0 002 12s3.5 7 10 7a9.7 9.7 0 004.3-1"/>
            @break

        @case('notifications')
            <path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 01-3.4 0"/>
            @break

        @case('star')
        @case('stars')
            <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9L12 2.5z"/>
            @break

        @case('arrow_back')
        @case('arrow_left')
            <path d="M19 12H5m6 6l-6-6 6-6"/>
            @break

        @case('arrow_right')
            <path d="M5 12h14m-6-6l6 6-6 6"/>
            @break

        @case('north_east')
            <path d="M7 17L17 7M7 7h10v10"/>
            @break

        @case('arrow_drop_down')
            <path d="M6 9l6 6 6-6"/>
            @break

        @case('school')
            <path d="M12 3L2 8l10 5 10-5-10-5z"/><path d="M6 10.5V16c0 1.7 2.7 3 6 3s6-1.3 6-3v-5.5"/>
            @break

        @case('image')
            <rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="M21 15l-5-5-11 10"/>
            @break

        @case('settings')
            <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06a1.65 1.65 0 001.82.33h.09a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v.09a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>
            @break

        @case('copy')
            <rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
            @break

        @case('print')
            <path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="9" rx="2"/><path d="M6 14h12v7H6v-7z"/>
            @break

        @case('id_card')
            <rect x="2.5" y="5" width="19" height="14" rx="2"/><circle cx="8" cy="10.5" r="2"/><path d="M5.5 15.5c.6-1.4 1.5-2 2.5-2s1.9.6 2.5 2"/><path d="M13.5 10h5M13.5 14h4"/>
            @break

        @case('passport')
            <rect x="5" y="2.5" width="14" height="19" rx="2"/><circle cx="12" cy="10.5" r="3.5"/><path d="M8.5 10.5h7M12 7c1.2 1.1 1.6 2.3 1.6 3.5S13.2 13 12 14c-1.2-1-1.6-2.3-1.6-3.5S10.8 8.1 12 7z"/><path d="M9 17.5h6"/>
            @break

        @case('photo_camera_front')
            <path d="M4 7h3l1.5-2.5h7L17 7h3a1.5 1.5 0 011.5 1.5v10A1.5 1.5 0 0120 20H4a1.5 1.5 0 01-1.5-1.5v-10A1.5 1.5 0 014 7z"/><circle cx="12" cy="12" r="2.2"/><path d="M8.5 17.5c.8-1.6 2-2.4 3.5-2.4s2.7.8 3.5 2.4"/>
            @break

        @case('menu_book')
        @case('auto_stories')
            <path d="M2 5.5A2.5 2.5 0 014.5 3H11v16H4.5A2.5 2.5 0 002 21.5V5.5z"/><path d="M22 5.5A2.5 2.5 0 0019.5 3H13v16h6.5A2.5 2.5 0 0122 21.5V5.5z"/>
            @break

        @case('scan')
            <path d="M4 8V5a1 1 0 011-1h3M16 4h3a1 1 0 011 1v3M4 16v3a1 1 0 001 1h3M16 20h3a1 1 0 001-1v-3"/><path d="M4 12h16"/>
            @break

        @case('money_bag')
            <path d="M9 4l1.5 3h3L15 4H9z"/><path d="M8 9.5C6 11.2 5 13.7 5 16a5 5 0 005 5h4a5 5 0 005-5c0-2.3-1-4.8-3-6.5l-1.5-2.5h-5L8 9.5z"/><path d="M12 11.5v6m-1.8-4.5c0-.8.8-1.3 1.8-1.3s1.8.5 1.8 1.3-.8 1.2-1.8 1.4-1.8.6-1.8 1.4.8 1.3 1.8 1.3 1.8-.5 1.8-1.3"/>
            @break

        @case('box_edit')
            <path d="M12 3L4 7.5v9L12 21l3.5-2"/><path d="M12 3l8 4.5V12"/><path d="M4 7.5l8 4.5 8-4.5"/><path d="M12 12v9"/><path d="M16 20.5l4.5-4.5 1.5 1.5-4.5 4.5H16v-1.5z"/>
            @break

        @case('flights_and_hotels')
            <path d="M3 20V7h7v13M6 10h1M6 13h1M6 16h1"/><path d="M14 13l7-4-1.5-1.5-4 1.8L12 6l-1.5 1 2.2 3.5-3.2 1.5-2-.8-1 1 2.5 2-.5 2.3 1.5.5 1.8-2 3.5 1.5 1-1.5-2.3-2z"/>
            @break

        @case('travel')
            <circle cx="12" cy="12" r="9"/><path d="M3.6 9h16.8M3.6 15h16.8"/><path d="M10.5 7l4.5 5-4.5 5"/>
            @break

        @case('checked_bag')
            <rect x="6" y="7" width="12" height="14" rx="2"/><path d="M9 7V5a2 2 0 012-2h2a2 2 0 012 2v2"/><path d="M9.5 14l1.8 1.8 3.5-3.6"/>
            @break

        @case('edit_note')
            <path d="M4 7h11M4 11h11M4 15h6"/><path d="M14 19l5.5-5.5 1.5 1.5-5.5 5.5H14V19z"/>
            @break

        @case('volunteer_activism')
            <path d="M12 11.5C12 9 9.5 7.5 8 8.8c-1.2 1-1 2.8.3 4l3.7 3.2 3.7-3.2c1.3-1.2 1.5-3 .3-4-1.5-1.3-4 .2-4 2.7z"/><path d="M3 16l4 2.5h9l4.5-2"/>
            @break

        @case('date_range')
            <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/><path d="M8 14h2M12 14h4"/>
            @break

        @case('download_2')
            <path d="M12 3v11m-4.5-4.5L12 14l4.5-4.5"/><path d="M5 18h14M5 21h14"/>
            @break

        @case('cloud_upload')
            <path d="M17.5 18H19a3.5 3.5 0 00.5-6.95A5.5 5.5 0 008.7 9.4 4.5 4.5 0 006.5 18H8"/><path d="M12 13v8m-3-5l3-3 3 3"/>
            @break

        @case('assignment')
            <rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4.5a2 2 0 016 0"/><path d="M8.5 10h7M8.5 14h7M8.5 18h4"/>
            @break

        @case('task_alt')
            <circle cx="12" cy="12" r="9"/><path d="M8.5 12.2l2.3 2.3 4.8-4.8"/>
            @break

        @case('keyboard_arrow_down')
        @case('dropdown_menu')
            <path d="M6 9l6 6 6-6"/>
            @break

        @case('hourglass_top')
            <path d="M6 3h12M6 21h12M7 3v4l5 5 5-5V3M7 21v-4l5-5 5 5v4"/>
            @break

        @default
            <circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="2"/>
    @endswitch
</svg>
