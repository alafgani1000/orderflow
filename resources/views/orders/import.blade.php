<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="mb-1 text-[10px] font-black uppercase tracking-wider text-indigo-600">{{ __('Aktivasi Toko') }}</p>
                <h1 class="text-xl font-bold text-gray-900">{{ __('Import Pesanan') }}</h1>
                <p class="mt-0.5 text-xs text-gray-500">{{ __('Masukkan pesanan lama atau data awal dari CSV dan Excel.') }}</p>
            </div>
            <a href="{{ route('orders.index') }}" class="text-xs font-semibold text-gray-500 hover:text-indigo-600">
                &larr; {{ __('Kembali ke daftar pesanan') }}
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-6xl space-y-5">
        @if(!isset($token))
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h2 class="text-sm font-bold text-gray-900">{{ __('1. Unggah File Pesanan') }}</h2>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('Baris pertama harus berisi judul kolom. Maksimal 1.000 baris dan ukuran file 5 MB.') }}</p>
                </div>

                <form method="POST" action="{{ route('orders.import.preview') }}" enctype="multipart/form-data" class="space-y-5 p-6">
                    @csrf
                    <label for="file" class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-indigo-200 bg-indigo-50/40 px-6 py-10 text-center transition hover:border-indigo-400 hover:bg-indigo-50">
                        <svg class="mb-3 h-9 w-9 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5.002 5.002 0 0115.9 6L16 6a5 5 0 011 9.9M12 12v9m0-9l-3 3m3-3l3 3"/>
                        </svg>
                        <span class="text-sm font-bold text-gray-800">{{ __('Pilih file pesanan CSV atau XLSX') }}</span>
                        <span class="mt-1 text-xs text-gray-500">{{ __('Klik untuk memilih file dari perangkat Anda') }}</span>
                        <input id="file" type="file" name="file" accept=".csv,.txt,.xlsx" required class="sr-only" onchange="document.getElementById('selected-file').textContent = this.files[0]?.name || ''">
                        <span id="selected-file" class="mt-3 text-xs font-semibold text-indigo-700"></span>
                    </label>
                    @error('file') <p class="text-sm text-rose-600">{{ $message }}</p> @enderror

                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-900">
                        {{ __('Pelanggan harus sudah terdaftar dan akan dicocokkan menggunakan nomor WhatsApp, lalu nama pelanggan.') }}
                        <a href="{{ route('customers.import.create') }}" class="font-bold underline">{{ __('Import pelanggan terlebih dahulu') }}</a>.
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-xs leading-relaxed text-gray-500">
                            <p>{{ __('Format deadline: YYYY-MM-DD, DD/MM/YYYY, atau DD-MM-YYYY.') }}</p>
                            <p>{{ __('Total dapat dikosongkan dan akan dihitung dari jumlah × harga satuan.') }}</p>
                        </div>
                        <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
                            {{ __('Lihat Preview') }} &rarr;
                        </button>
                    </div>
                </form>
            </section>

            <section class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">{{ __('Gunakan format siap pakai') }}</h2>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('Template berisi seluruh kolom dan satu contoh pesanan.') }}</p>
                </div>
                <a href="{{ route('orders.import.template') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs font-bold text-gray-700 transition hover:border-indigo-300 hover:text-indigo-700">
                    {{ __('Unduh Template Pesanan') }}
                </a>
            </section>
        @else
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xs">
                <div class="flex flex-col gap-2 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">{{ __('2. Cocokkan Kolom Pesanan') }}</h2>
                        <p class="mt-0.5 text-xs text-gray-500">{{ __('File :file • :count baris data', ['file' => $fileName, 'count' => $totalRows]) }}</p>
                    </div>
                    <a href="{{ route('orders.import.create') }}" class="text-xs font-semibold text-indigo-600 hover:underline">{{ __('Ganti file') }}</a>
                </div>

                <form method="POST" action="{{ route('orders.import.store') }}" class="space-y-5 p-6">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    @error('columns') <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700">{{ $message }}</div> @enderror
                    @error('file') <div class="rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700">{{ $message }}</div> @enderror

                    @php
                        $mappings = [
                            ['field' => 'customer_name_column', 'label' => __('Nama Pelanggan'), 'suggestion' => $suggested['customer_name'], 'required' => true],
                            ['field' => 'customer_phone_column', 'label' => __('WhatsApp Pelanggan'), 'suggestion' => $suggested['customer_phone'], 'required' => false],
                            ['field' => 'order_number_column', 'label' => __('Nomor Pesanan'), 'suggestion' => $suggested['order_number'], 'required' => false],
                            ['field' => 'name_column', 'label' => __('Nama Pesanan'), 'suggestion' => $suggested['name'], 'required' => true],
                            ['field' => 'quantity_column', 'label' => __('Jumlah'), 'suggestion' => $suggested['quantity'], 'required' => true],
                            ['field' => 'price_column', 'label' => __('Harga Satuan'), 'suggestion' => $suggested['price'], 'required' => true],
                            ['field' => 'total_column', 'label' => __('Total'), 'suggestion' => $suggested['total'], 'required' => false],
                            ['field' => 'deadline_column', 'label' => __('Deadline'), 'suggestion' => $suggested['deadline'], 'required' => false],
                            ['field' => 'status_column', 'label' => __('Status'), 'suggestion' => $suggested['status'], 'required' => false],
                            ['field' => 'description_column', 'label' => __('Deskripsi'), 'suggestion' => $suggested['description'], 'required' => false],
                            ['field' => 'notes_column', 'label' => __('Catatan'), 'suggestion' => $suggested['notes'], 'required' => false],
                        ];
                    @endphp
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach($mappings as $mapping)
                            <div>
                                <label for="{{ $mapping['field'] }}" class="mb-1.5 block text-xs font-bold text-gray-700">
                                    {{ $mapping['label'] }} @if($mapping['required'])<span class="text-rose-500">*</span>@endif
                                </label>
                                <select id="{{ $mapping['field'] }}" name="{{ $mapping['field'] }}" @required($mapping['required'])
                                        class="w-full rounded-xl border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">{{ $mapping['required'] ? __('Pilih kolom') : __('Tidak diimpor') }}</option>
                                    @foreach($headers as $header)
                                        <option value="{{ $header }}" @selected(old($mapping['field'], $mapping['suggestion']) === $header)>{{ $header }}</option>
                                    @endforeach
                                </select>
                                @error($mapping['field']) <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>

                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3">
                            <h3 class="text-xs font-bold text-gray-700">{{ __('Preview 10 baris pertama') }}</h3>
                            <span class="text-[11px] text-gray-400">{{ trans_choice(':count baris|:count baris', $totalRows, ['count' => $totalRows]) }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-left text-xs">
                                <thead class="border-b border-gray-100 bg-white text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2.5">#</th>
                                        @foreach($headers as $header)
                                            <th class="whitespace-nowrap px-3 py-2.5">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($previewRows as $row)
                                        <tr>
                                            <td class="px-3 py-2.5 font-semibold text-gray-400">{{ $loop->iteration + 1 }}</td>
                                            @foreach($headers as $header)
                                                <td class="max-w-64 truncate whitespace-nowrap px-3 py-2.5 text-gray-700" title="{{ $row[$header] }}">{{ $row[$header] ?: '—' }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-[11px] leading-relaxed text-gray-500">{{ __('Nomor pesanan kosong akan dibuat otomatis. Nomor yang sudah ada akan dilewati sebagai duplikat.') }}</p>
                        <button type="submit" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700">
                            {{ __('Import :count Pesanan', ['count' => $totalRows]) }}
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-app-layout>
