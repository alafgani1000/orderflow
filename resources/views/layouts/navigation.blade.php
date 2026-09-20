<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Logo & Desktop Nav -->
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 shrink-0 group">
                    <x-brand-mark class="w-8 h-8 shrink-0 transition-transform group-hover:scale-105" />
                    <span class="font-bold text-[15px] tracking-tight text-gray-900 leading-none">Order<span class="text-indigo-600">Flow</span></span>
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-0.5">
                    @php
                        $canFinance = Auth::user()->canViewFinances();
                        $navItems = [
                            ['route' => 'dashboard', 'label' => 'Dashboard', 'active' => request()->routeIs('dashboard')],
                            ['route' => 'orders.index', 'label' => 'Pesanan', 'active' => request()->routeIs('orders.index') || request()->routeIs('orders.show') || request()->routeIs('orders.edit') || request()->routeIs('orders.create')],
                            ['route' => 'orders.kanban', 'label' => 'Papan Kanban', 'active' => request()->routeIs('orders.kanban')],
                            ['route' => 'customers.index', 'label' => 'Pelanggan', 'active' => request()->routeIs('customers.*')],
                        ];

                        if ($canFinance) {
                            $navItems[] = ['route' => 'payments.index', 'label' => 'Pembayaran', 'active' => request()->routeIs('payments.*')];
                            $navItems[] = ['route' => 'reports.index', 'label' => 'Laporan', 'active' => request()->routeIs('reports.*')];
                        }

                        if (Auth::user()->isOwner() || Auth::user()->isSuperAdmin()) {
                            $navItems[] = ['route' => 'billing.index', 'label' => 'Langganan', 'active' => request()->routeIs('billing.*')];
                        }

                        $navItems[] = ['route' => 'settings.index', 'label' => 'Pengaturan', 'active' => request()->routeIs('settings.*')];
                    @endphp
                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="px-3 py-1.5 text-sm font-medium rounded-lg transition-all active:scale-95
                                  {{ $item['active']
                                      ? 'text-indigo-700 bg-indigo-50 font-semibold'
                                      : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 active:bg-gray-200' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Right: Create Button + User Dropdown -->
            <div class="hidden md:flex items-center gap-3">
                @if(Auth::user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-bold text-xs rounded-lg transition">
                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        Super Admin
                    </a>
                @endif
                <a href="{{ route('orders.create') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-lg shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Pesanan Baru
                </a>

                <x-dropdown align="right" width="72">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 px-3 py-1.5 text-sm rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-medium transition shadow-2xs">
                            @if(Auth::user()->avatar)
                                <img src="{{ Auth::user()->avatar }}" 
                                     alt="{{ Auth::user()->name }}" 
                                     referrerpolicy="no-referrer"
                                     loading="lazy"
                                     onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                     class="w-6 h-6 rounded-full object-cover shrink-0 shadow-2xs border border-gray-100" />
                                <div style="display: none;" class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @else
                                <div class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-2xs">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </div>
                            @endif
                            <span class="max-w-[120px] truncate text-xs font-semibold text-gray-800">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <!-- Profile & Role Info -->
                        <div class="p-3.5 border-b border-gray-100 bg-gray-50/60">
                            <div class="flex items-center gap-3">
                                @if(Auth::user()->avatar)
                                    <img src="{{ Auth::user()->avatar }}" 
                                         alt="{{ Auth::user()->name }}" 
                                         referrerpolicy="no-referrer"
                                         loading="lazy"
                                         onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';"
                                         class="w-9 h-9 rounded-xl object-cover shrink-0 shadow-xs border border-gray-100" />
                                    <div style="display: none;" class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-xs">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1.5">
                                        <p class="text-xs font-bold text-gray-900 truncate leading-snug">{{ Auth::user()->name }}</p>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider shrink-0 whitespace-nowrap {{ Auth::user()->isOwner() ? 'bg-indigo-100 text-indigo-700 border border-indigo-200/60' : (Auth::user()->isAdminCs() ? 'bg-emerald-100 text-emerald-700 border border-emerald-200/60' : 'bg-amber-100 text-amber-700 border border-amber-200/60') }}">
                                            {{ Auth::user()->short_role_label }}
                                        </span>
                                    </div>
                                    @if(Auth::user()->business_name)
                                        <p class="text-[11px] font-medium text-gray-600 truncate mt-0.5">{{ Auth::user()->business_name }}</p>
                                    @endif
                                    <p class="text-[11px] text-gray-400 truncate">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Links -->
                        <div class="py-1">
                            @if(Auth::user()->isOwner() || Auth::user()->isSuperAdmin())
                                <x-dropdown-link :href="route('billing.index')" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                    <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    <span>Langganan &amp; Kuota</span>
                                </x-dropdown-link>
                            @endif

                            @if(Auth::user()->isSuperAdmin())
                                <x-dropdown-link :href="route('admin.dashboard')" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-50 transition">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>Super Admin Platform</span>
                                </x-dropdown-link>
                            @endif

                            <x-dropdown-link :href="route('settings.index')" class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Pengaturan Akun &amp; Usaha</span>
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    <span>Keluar</span>
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Mobile Menu Button -->
            <button @click="open = !open" class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path :class="{'hidden': open}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    <path :class="{'hidden': !open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" class="md:hidden border-t border-gray-100 bg-white px-4 py-3 space-y-1">
        <a href="{{ route('orders.create') }}" class="flex items-center justify-center gap-2 py-2.5 mb-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl">
            + Buat Pesanan Baru
        </a>
        @foreach($navItems as $item)
            <x-responsive-nav-link :href="route($item['route'])" :active="$item['active']" class="active:scale-[0.99] active:bg-gray-100">
                {{ $item['label'] }}
            </x-responsive-nav-link>
        @endforeach
        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
            <div>
                <div class="flex items-center gap-1.5">
                    <p class="text-xs font-bold text-gray-900">{{ Auth::user()->name }}</p>
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ Auth::user()->isOwner() ? 'bg-indigo-100 text-indigo-700' : (Auth::user()->isAdminCs() ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ Auth::user()->short_role_label }}
                    </span>
                </div>
                @if(Auth::user()->business_name)
                    <p class="text-[11px] text-gray-500">{{ Auth::user()->business_name }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800">Logout</button>
            </form>
        </div>
    </div>
</nav>
