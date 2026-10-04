@props(['status'])

@php
    $cfg = match($status) {
        'new'             => ['cls' => 'bg-gray-100 text-gray-600 border-gray-200',     'dot' => 'bg-gray-400',    'label' => __('Baru')],
        'waiting_design'  => ['cls' => 'bg-amber-50 text-amber-700 border-amber-200',   'dot' => 'bg-amber-500',   'label' => __('Menunggu Desain')],
        'design_approved' => ['cls' => 'bg-sky-50 text-sky-700 border-sky-200',         'dot' => 'bg-sky-500',     'label' => __('Desain Disetujui')],
        'production'      => ['cls' => 'bg-indigo-50 text-indigo-700 border-indigo-200','dot' => 'bg-indigo-600',  'label' => __('Produksi')],
        'completed'       => ['cls' => 'bg-emerald-50 text-emerald-700 border-emerald-200','dot' => 'bg-emerald-500','label' => __('Selesai')],
        'delivered'       => ['cls' => 'bg-teal-50 text-teal-700 border-teal-200',      'dot' => 'bg-teal-600',    'label' => __('Dikirim / Diambil')],
        'cancelled'       => ['cls' => 'bg-red-50 text-red-700 border-red-200',         'dot' => 'bg-red-500',     'label' => __('Dibatalkan')],
        default           => ['cls' => 'bg-gray-100 text-gray-600 border-gray-200',     'dot' => 'bg-gray-400',    'label' => $status],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-semibold border {$cfg['cls']}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $cfg['dot'] }}"></span>
    {{ $cfg['label'] }}
</span>
