@props(['name' => 'circle', 'size' => 24])

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.7"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
    {{ $attributes }}
>
    @switch($name)
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break

        @case('close')
            <path d="m6 6 12 12M18 6 6 18" />
            @break

        @case('arrow-right')
            <path d="M5 12h14M13 6l6 6-6 6" />
            @break

        @case('arrow-up-right')
            <path d="M7 17 17 7M8 7h9v9" />
            @break

        @case('expand')
            <path d="M8 3H5a2 2 0 0 0-2 2v3M16 3h3a2 2 0 0 1 2 2v3M8 21H5a2 2 0 0 1-2-2v-3M16 21h3a2 2 0 0 0 2-2v-3" />
            @break

        @case('quran')
            <path d="M12 6.5C10 4.8 7.5 4 4 4v15c3.5 0 6 .8 8 2.5 2-1.7 4.5-2.5 8-2.5V4c-3.5 0-6 .8-8 2.5Z" />
            <path d="M12 6.5v15" />
            @break

        @case('heart')
            <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z" />
            @break

        @case('book')
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
            @break

        @case('users')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
            <circle cx="9" cy="7" r="4" />
            <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
            @break

        @case('home')
            <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V10Z" />
            @break

        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M16 3v4M8 3v4M3 10h18" />
            @break

        @case('sparkle')
            <path d="m12 3 1.4 4.6L18 9l-4.6 1.4L12 15l-1.4-4.6L6 9l4.6-1.4L12 3Z" />
            <path d="m19 15 .7 2.3L22 18l-2.3.7L19 21l-.7-2.3L16 18l2.3-.7L19 15Z" />
            @break

        @case('map-pin')
            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
            <circle cx="12" cy="10" r="2.5" />
            @break

        @case('phone')
            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2Z" />
            @break

        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
            @break

        @case('clock')
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
            @break

        @case('instagram')
            <rect x="3" y="3" width="18" height="18" rx="5" />
            <circle cx="12" cy="12" r="4" />
            <path d="M17.5 6.5h.01" />
            @break

        @case('tiktok')
            <path d="M15.5 4c.5 2.3 2.3 4.2 4.5 4.5" />
            <path d="M15.5 4v11" />
            <circle cx="11" cy="15" r="4.5" />
            @break

        @case('youtube')
            <path d="M22 12s0-3.2-.4-4.7a3 3 0 0 0-2.1-2.1C17.9 4.8 12 4.8 12 4.8s-5.9 0-7.5.4a3 3 0 0 0-2.1 2.1C2 8.8 2 12 2 12s0 3.2.4 4.7a3 3 0 0 0 2.1 2.1c1.6.4 7.5.4 7.5.4s5.9 0 7.5-.4a3 3 0 0 0 2.1-2.1C22 15.2 22 12 22 12Z" />
            <path d="m10 9 5 3-5 3V9Z" />
            @break

        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break

        @case('quote')
            <path d="M9 11H5a4 4 0 0 1 4-4V5a6 6 0 0 0-6 6v6h6v-6Z" />
            <path d="M21 11h-4a4 4 0 0 1 4-4V5a6 6 0 0 0-6 6v6h6v-6Z" />
            @break

        @default
            <circle cx="12" cy="12" r="8" />
    @endswitch
</svg>
