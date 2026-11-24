@props(['panel' => 'cashier'])

<a
    href="{{ url('/'.$panel.'/panduan-perangkat') }}"
    title="Panduan Alat Scan & Cetak Struk"
    style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(16, 185, 129, 0.35); background: rgba(16, 185, 129, 0.1); color: #059669; margin-right: 0.5rem;"
>
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
        <path d="M12 17h.01"/>
    </svg>
    <span>Panduan Alat</span>
</a>
