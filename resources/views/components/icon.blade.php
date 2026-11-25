@props(['name', 'class' => 'w-5 h-5'])

@php
    $raw = trim((string) $name);
    $aliases = [
        'check_plain' => 'check',
        'copy'        => 'content_copy',
        'eye'         => 'visibility',
        'eye_off'     => 'visibility_off',
        'calendar'    => 'calendar_month',
        'x'           => 'close',
        'x_circle'    => 'cancel',
        'alert'       => 'warning',
        'globe'       => 'public',
        'flight_takeoff' => 'flight',
        'delete_outline' => 'delete',
        'trash'       => 'delete',
        'plus'        => 'add',
        'phone'       => 'call',
        'key'         => 'lock',
        'arrow_left'  => 'arrow_back',
        'shield'      => 'verified_user',
        'wallet'      => 'payments',
    ];
    $glyph = $aliases[$raw] ?? $raw;

    $fontSize = '!text-[20px]';
    if (preg_match('/\bw-3(?:\s|$)/', $class)) {
        $fontSize = '!text-[14px]';
    } elseif (preg_match('/\bw-3\.5(?:\s|$)/', $class)) {
        $fontSize = '!text-[16px]';
    } elseif (preg_match('/\bw-4(?:\s|$)/', $class)) {
        $fontSize = '!text-[18px]';
    } elseif (preg_match('/\bw-5(?:\s|$)/', $class)) {
        $fontSize = '!text-[20px]';
    } elseif (preg_match('/\bw-6(?:\s|$)/', $class)) {
        $fontSize = '!text-[24px]';
    } elseif (preg_match('/\bw-7(?:\s|$)/', $class)) {
        $fontSize = '!text-[28px]';
    } elseif (preg_match('/\bw-8(?:\s|$)/', $class)) {
        $fontSize = '!text-[32px]';
    } elseif (preg_match('/\bw-9(?:\s|$)/', $class)) {
        $fontSize = '!text-[36px]';
    }
@endphp

<span {{ $attributes->merge(['class' => "material-symbols-outlined inline-flex items-center justify-center shrink-0 leading-none select-none {$fontSize} {$class}"]) }} aria-hidden="true">{{ $glyph }}</span>
