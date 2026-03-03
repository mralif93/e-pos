<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ config('app.name', 'E-POS') }}</title>

    <!-- Tailwind CSS (Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/figtree.css') }}" />

    <!-- HugeIcons -->
    <link rel="stylesheet" href="{{ asset('assets/icons/hgi-stroke-rounded.css') }}" />

    <!-- Styles -->
    @yield('styles')
</head>

<body class="bg-gray-50 font-[Figtree] font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-100 flex flex-col shadow-sm">
            <!-- Logo -->
            <div class="h-16 flex items-center px-5 border-b border-gray-100 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 group">
                    <div
                        class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-xl flex items-center justify-center shadow-md shadow-indigo-200 group-hover:scale-105 transition-transform">
                        <i class="hgi-stroke hgi-store-01 text-white text-sm"></i>
                    </div>
                    <div>
                        <span class="font-bold text-gray-800 text-md leading-none block">E-POS</span>
                        <span class="text-[10px] text-gray-400 font-medium">Admin Panel</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
                <a href="{{ route('admin.dashboard', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-dashboard-square-01 shrink-0"></i>
                    <span>Dashboard</span>
                </a>

                <div class="pt-5 pb-1.5">
                    <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sales</p>
                </div>
                <a href="{{ route('admin.reports.sales', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.reports.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-chart-evaluation shrink-0"></i>
                    <span>Sales Reports</span>
                </a>

                <div class="pt-5 pb-1.5">
                    <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Management</p>
                </div>
                <a href="{{ route('admin.users.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-user-star-01 shrink-0"></i>
                    <span>Users</span>
                </a>
                <a href="{{ route('admin.customers.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.customers.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-user-multiple-02 shrink-0"></i>
                    <span>Customers</span>
                </a>
                <a href="{{ route('admin.outlets.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.outlets.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-store-01 shrink-0"></i>
                    <span>Outlets</span>
                </a>
                <a href="{{ route('admin.products.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.products.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-package shrink-0"></i>
                    <span>Products</span>
                </a>
                <a href="{{ route('admin.categories.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.categories.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-grid-view shrink-0"></i>
                    <span>Categories</span>
                </a>
                <div class="pt-5 pb-1.5">
                    <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Inventory</p>
                </div>
                <a href="{{ route('admin.inventory.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.inventory.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-warehouse shrink-0"></i>
                    <span>Inventory</span>
                </a>
                <a href="{{ route('admin.transfers.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.transfers.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-arrow-left-right shrink-0"></i>
                    <span>Transfers</span>
                </a>

                <div class="pt-5 pb-1.5">
                    <p class="px-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Operations</p>
                </div>
                <a href="{{ route('admin.shifts.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.shifts.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-calendar-01 shrink-0"></i>
                    <span>Shifts</span>
                </a>
                <a href="{{ route('admin.settings.index', request()->has('outlet_id') ? ['outlet_id' => request('outlet_id')] : []) }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                    {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300' : 'text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}">
                    <i class="hgi-stroke text-[18px] hgi-settings-01 shrink-0"></i>
                    <span>Settings</span>
                </a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            <header
                class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-6 shadow-sm shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-6 bg-gradient-to-b from-indigo-500 to-violet-500 rounded-full"></div>
                    <h1 class="text-md font-bold text-gray-800">@yield('header', 'Dashboard')</h1>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Outlet Switcher Badge -->
                    @php
                        $outlets = \App\Models\Outlet::orderBy('name')->get();
                        $selectedOutletId = request('outlet_id'); // middleware merges session value here
                        $selectedOutlet = $outlets->firstWhere('id', $selectedOutletId);
                        $currentOutletName = $selectedOutlet ? $selectedOutlet->name : 'All Outlets';
                    @endphp

                    <div class="relative" id="outletSwitcherWrapper">
                        @if(Auth::user()->role === 'Super Admin')
                            <!-- Badge Trigger Button for Super Admin -->
                            <button id="outletSwitcherBtn" type="button"
                                class="flex items-center gap-2 h-[38px] px-3.5 rounded-xl border transition-all focus:outline-none
                                                    {{ $selectedOutletId ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm shadow-indigo-300 hover:bg-indigo-700' : 'bg-white border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm' }}">
                                <i class="hgi-stroke hgi-building-03 text-[15px] shrink-0"></i>
                                <span class="text-sm font-semibold max-w-[120px] truncate">{{ $currentOutletName }}</span>
                                @if($selectedOutletId)
                                    <span class="w-1.5 h-1.5 rounded-full bg-white/70 shrink-0"></span>
                                @endif
                                <i class="hgi-stroke hgi-arrow-down-01 text-xs shrink-0 transition-transform"
                                    id="outletChevron"></i>
                            </button>
                        @else
                            <!-- Static Badge for Non-Super Admin -->
                            <div
                                class="flex items-center gap-2 h-[38px] px-3.5 rounded-xl border bg-indigo-50 border-indigo-100 text-indigo-700">
                                <i class="hgi-stroke hgi-building-03 text-[15px] shrink-0"></i>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold leading-tight max-w-[120px] truncate">
                                        {{ Auth::user()->outlet ? Auth::user()->outlet->name : 'No Outlet' }}
                                    </span>
                                </div>
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-300 shrink-0"></span>
                                <i class="hgi-stroke hgi-lock-01 text-xs shrink-0 opacity-70"></i>
                            </div>
                        @endif

                        @if(Auth::user()->role === 'Super Admin')
                            <!-- Popover Panel -->
                            <div id="outletSwitcherPanel"
                                class="hidden absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 z-50 overflow-hidden origin-top-left">
                                <!-- Panel Header -->
                                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/70">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Switch Outlet</p>
                                </div>
                                <!-- Outlet List -->
                                <div class="p-2 max-h-72 overflow-y-auto space-y-0.5">
                                    <!-- All Outlets Option -->
                                    <form method="GET" action="" class="outlet-form">
                                        @foreach(request()->except('outlet_id') as $key => $value)
                                            @if(is_array($value))
                                                @foreach($value as $v)
                                                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                                @endforeach
                                            @else
                                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                            @endif
                                        @endforeach
                                        {{-- Explicit empty value clears the session outlet --}}
                                        <input type="hidden" name="outlet_id" value="">
                                        <button type="submit"
                                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all
                                                                {{ !$selectedOutletId ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                            <div
                                                class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                                                    {{ !$selectedOutletId ? 'bg-indigo-600' : 'bg-gray-100' }}">
                                                <i
                                                    class="hgi-stroke hgi-grid-view text-sm {{ !$selectedOutletId ? 'text-white' : 'text-gray-400' }}"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-semibold truncate">All Outlets</p>
                                                <p class="text-xs text-gray-400">Show all data</p>
                                            </div>
                                            @if(!$selectedOutletId)
                                                <i
                                                    class="hgi-stroke hgi-checkmark-circle-01 text-indigo-600 text-base shrink-0"></i>
                                            @endif
                                        </button>
                                    </form>

                                    @foreach($outlets as $outlet)
                                        <form method="GET" action="" class="outlet-form">
                                            @foreach(request()->except('outlet_id') as $key => $value)
                                                @if(is_array($value))
                                                    @foreach($value as $v)
                                                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                                    @endforeach
                                                @else
                                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                                @endif
                                            @endforeach
                                            <input type="hidden" name="outlet_id" value="{{ $outlet->id }}">
                                            <button type="submit"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition-all
                                                                                        {{ $selectedOutletId == $outlet->id ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                                <div
                                                    class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                                                                            {{ $selectedOutletId == $outlet->id ? 'bg-indigo-600' : 'bg-gray-100' }}">
                                                    <span
                                                        class="text-xs font-black {{ $selectedOutletId == $outlet->id ? 'text-white' : 'text-gray-500' }}">
                                                        {{ substr($outlet->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-semibold truncate">{{ $outlet->name }}</p>
                                                    @if($outlet->address)
                                                        <p class="text-xs text-gray-400 truncate">{{ $outlet->address }}</p>
                                                    @else
                                                        <p class="text-xs text-gray-400">Outlet</p>
                                                    @endif
                                                </div>
                                                @if($selectedOutletId == $outlet->id)
                                                    <i
                                                        class="hgi-stroke hgi-checkmark-circle-01 text-indigo-600 text-base shrink-0"></i>
                                                @endif
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <a href="{{ route('pos.home') }}" target="_blank"
                        class="group inline-flex items-center gap-2 text-sm bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 text-white px-4 py-2 rounded-xl font-bold transition-all shadow-md shadow-indigo-300/50 h-[38px] hover:shadow-indigo-400/60 hover:-translate-y-0.5 active:translate-y-0 hover:from-indigo-700 hover:via-violet-700 hover:to-purple-700">
                        <i class="hgi-stroke hgi-computer text-sm shrink-0"></i>
                        <span>e-POS</span>
                        <i
                            class="hgi-stroke hgi-arrow-up-right-01 text-xs opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 transition-transform shrink-0"></i>
                    </a>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <button id="adminUserMenuBtn"
                            class="flex items-center gap-2.5 focus:outline-none hover:bg-gray-50 px-3 py-1.5 rounded-xl transition-all border border-transparent hover:border-gray-200 h-[38px] group">
                            <div
                                class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-violet-600 rounded-lg flex items-center justify-center shadow-sm">
                                <span class="text-white font-bold text-xs">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <div class="text-left hidden md:block">
                                <p class="text-sm font-semibold text-gray-800 leading-tight">{{ Auth::user()->name }}
                                </p>
                                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400">
                                    {{ Auth::user()->role ?? 'Admin' }}
                                </p>
                            </div>
                            <i
                                class="hgi-stroke hgi-arrow-down-01 text-gray-400 text-xs ml-0.5 transition-transform group-hover:rotate-180"></i>
                        </button>

                        <div id="adminUserMenuDropdown"
                            class="hidden absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50 origin-top-right">
                            <!-- User info header in dropdown -->
                            <div class="px-4 py-3 border-b border-gray-100 mb-1">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="{{ route('admin.profile') }}"
                                class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                <i class="hgi-stroke hgi-user-circle text-base"></i> My Profile
                            </a>
                            <div class="mx-3 my-1 border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                @csrf
                                <button type="button" onclick="confirmLogout()"
                                    class="w-full text-left flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                    <i class="hgi-stroke hgi-logout-01 text-base"></i> Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                    <div
                        class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm mb-5">
                        <i class="hgi-stroke hgi-checkmark-circle-01 text-lg shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div
                        class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm mb-5">
                        <i class="hgi-stroke hgi-alert-01 text-lg shrink-0"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>

    <!-- SweetAlert2 -->
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>

    <!-- Logout Confirmation -->
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Sign Out?',
                text: 'Are you sure you want to sign out?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Yes, sign out',
                cancelButtonText: 'Cancel',
                customClass: { popup: 'rounded-2xl shadow-xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }
    </script>

    <!-- User Dropdown Toggle -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userBtn = document.getElementById('adminUserMenuBtn');
            const userDropdown = document.getElementById('adminUserMenuDropdown');
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    userDropdown.classList.toggle('hidden');
                    // Close outlet panel if open
                    document.getElementById('outletSwitcherPanel')?.classList.add('hidden');
                });
                document.addEventListener('click', (e) => {
                    if (!userBtn.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.add('hidden');
                    }
                });
            }

            // Outlet Switcher
            const outletBtn = document.getElementById('outletSwitcherBtn');
            const outletPanel = document.getElementById('outletSwitcherPanel');
            const outletChevron = document.getElementById('outletChevron');

            if (outletBtn && outletPanel) {
                outletBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isHidden = outletPanel.classList.contains('hidden');
                    outletPanel.classList.toggle('hidden');
                    outletChevron?.classList.toggle('rotate-180', isHidden);
                    // Close user dropdown if open
                    userDropdown?.classList.add('hidden');
                });
                document.addEventListener('click', (e) => {
                    if (!outletBtn.contains(e.target) && !outletPanel.contains(e.target)) {
                        outletPanel.classList.add('hidden');
                        outletChevron?.classList.remove('rotate-180');
                    }
                });
            }
        });
    </script>
    @yield('scripts')
</body>

</html>