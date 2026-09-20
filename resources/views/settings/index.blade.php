<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl mx-auto">
            <h1 class="text-xl font-bold text-gray-900">Pengaturan Akun & Usaha</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola identitas usaha, keamanan, dan staf tim OrderFlow Anda.</p>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">
        <!-- Business Identity -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Identitas Pemilik & Usaha</h2>
                <p class="text-xs text-gray-500 mt-0.5">Nama usaha akan otomatis muncul di template pesan WhatsApp.</p>
            </div>

            <form method="POST" action="{{ route('settings.profile') }}" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Nama Pemilik / Admin <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Usaha / Toko</label>
                        <input type="text" name="business_name" value="{{ old('business_name', $user->business_name) }}"
                               placeholder="Sablon Juara / Percetakan Berkah"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Email (Login) <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nomor WhatsApp Usaha</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               placeholder="08123456789"
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm rounded-xl shadow-xs transition">
                        Simpan Identitas
                    </button>
                </div>
            </form>
        </div>

        <!-- Change Password -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-900">Keamanan & Ganti Password</h2>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui password akun Anda secara berkala.</p>
            </div>

            <form method="POST" action="{{ route('settings.password') }}" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Password Saat Ini <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="current_password" required
                           class="w-full max-w-sm rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-[11px] text-gray-400 mt-1">Minimal 8 karakter.</p>
                        @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required
                               class="w-full rounded-xl border border-gray-300 px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-5 py-2.5 bg-gray-900 hover:bg-gray-800 text-white font-semibold text-sm rounded-xl shadow-xs transition">
                        Perbarui Password
                    </button>
                </div>
            </form>
        </div>

        @if($user->isOwner())
            <!-- Kelola Tim & Karyawan (Khusus Owner) -->
            <div x-data="{ showAddModal: false }" class="bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-sm font-bold text-gray-900">Kelola Tim & Staf Karyawan</h2>
                            <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 text-[10px] font-bold">Khusus Owner</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Beri akses login untuk Kasir/CS atau Operator Produksi sablon Anda.</p>
                    </div>
                    <button type="button" @click="showAddModal = true"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow-xs transition self-start sm:self-auto">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Staf
                    </button>
                </div>

                <!-- Role Explanation Pills -->
                <div class="bg-gray-50/70 px-6 py-3 border-b border-gray-100 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-600">
                    <div class="flex items-start gap-2">
                        <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] shrink-0">Kasir / Admin CS</span>
                        <span>Bisa input pesanan, catat pembayaran & lihat laporan omset.</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold text-[10px] shrink-0">Desainer / Operator</span>
                        <span>Akses papan Kanban & rincian pesanan. <strong>Nominal uang disembunyikan</strong>.</span>
                    </div>
                </div>

                <!-- Staff List -->
                @if($employees->isEmpty())
                    <div class="p-8 text-center">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center mx-auto mb-2.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-800">Belum ada staf terdaftar</p>
                        <p class="text-xs text-gray-400 mt-1">Tambahkan akun staf agar tim workshop dapat berkolaborasi langsung.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($employees as $employee)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-gray-50/50 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($employee->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $employee->name }}</p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider {{ $employee->isAdminCs() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                                {{ $employee->role_label }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-400 truncate">{{ $employee->email }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                                          onsubmit="return confirm('Cabut akses dan hapus akun staf {{ $employee->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus Staf">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Modal Tambah Staf Baru -->
                <div x-show="showAddModal" x-cloak
                     class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4">
                    <div @click.outside="showAddModal = false"
                         class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 transform transition-all">
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                            <h3 class="text-base font-bold text-gray-900">Tambah Akun Staf Baru</h3>
                            <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" action="{{ route('employees.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap Staf <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Budi (Operator Sablon)"
                                       class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                                <input type="email" name="email" required placeholder="staf@tokoanda.com"
                                       class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Peran / Hak Akses <span class="text-red-500">*</span></label>
                                <select name="role" required class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="admin_cs">Kasir / Admin CS (Akses Penuh Pesanan & Keuangan)</option>
                                    <option value="production">Desainer / Operator Sablon (Khusus Produksi & Kanban, Keuangan Disembunyikan)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Password Awal <span class="text-red-500">*</span></label>
                                <input type="password" name="password" required placeholder="Minimal 8 karakter"
                                       class="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                                <button type="button" @click="showAddModal = false"
                                        class="px-4 py-2 text-xs font-semibold text-gray-600 hover:bg-gray-100 rounded-xl transition">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-xs transition">
                                    Simpan Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            <!-- Info Status Akun Karyawan -->
            <div class="bg-indigo-50/60 border border-indigo-100 rounded-2xl p-5 flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900">Hak Akses Karyawan</h3>
                    <p class="text-xs text-indigo-800 mt-1">
                        Anda saat ini login dengan hak akses <strong>{{ $user->role_label }}</strong> pada toko <strong>{{ $user->owner?->business_name ?? $user->owner?->name }}</strong>. Pengaturan toko dan manajemen tim hanya dapat dikelola oleh pemilik toko (Owner).
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
