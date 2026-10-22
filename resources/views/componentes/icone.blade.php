<svg class="icone" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($nome ?? 'caixa')
@case('usuarios') <circle cx="9" cy="7" r="4"/><path d="M2 21v-2a7 7 0 0 1 14 0v2M17 4a4 4 0 0 1 0 8m2 3a6 6 0 0 1 3 6"/> @break
@case('painel') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
@case('reparo') <path d="M14 6a5 5 0 0 0-6 6L3 17a3 3 0 0 0 4 4l5-5a5 5 0 0 0 6-6l-3 3-4-4 3-3Z"/> @break
@case('mais') <path d="M12 5v14M5 12h14"/> @break
@case('seta') <path d="M5 12h14m-5-5 5 5-5 5"/> @break
@case('busca') <circle cx="10" cy="10" r="7"/><path d="m15 15 6 6"/> @break
@case('check') <circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/> @break
@case('alerta') <path d="m12 3 10 18H2L12 3Z M12 9v4m0 4h.01"/> @break
@case('qr') <path d="M3 3h6v6H3zM15 3h6v6h-6zM3 15h6v6H3zM15 15h3v3h3v3h-6zM21 12v3M12 3v3M12 12v6M3 12h5"/> @break
@case('escola') <path d="m3 10 9-7 9 7v11H3V10Zm6 11v-7h6v7M7 10h.01M17 10h.01"/> @break
@case('local') <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z"/><circle cx="12" cy="10" r="2"/> @break
@case('impressora') <path d="M7 8V3h10v5M7 17H3V8h18v9h-4M7 14h10v7H7zM17 11h.01"/> @break
@case('sair') <path d="M9 4H4v16h5M10 12h11m-4-4 4 4-4 4"/> @break
@case('menu') <path d="M3 6h18M3 12h18M3 18h18"/> @break
@case('monitor') <rect x="3" y="4" width="18" height="13" rx="2"/><path d="M8 21h8m-4-4v4"/> @break
@default <path d="m12 3 9 5v9l-9 5-9-5V8l9-5Zm0 10 9-5M12 13v9M3 8l9 5M7 5l9 5"/>
@endswitch
</svg>
