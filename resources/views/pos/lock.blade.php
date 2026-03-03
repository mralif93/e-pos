@extends('layouts.app')

@section('content')
    @php $theme = $outletSettings['pos_theme_color'] ?? 'indigo'; @endphp
    <div x-data="lockScreen"
        class="h-screen w-full bg-slate-900 flex flex-col items-center justify-center overflow-hidden font-sans antialiased">

        <!-- Glow Effects -->
        <div class="fixed top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-20 -left-20 w-96 h-96 rounded-full mix-blend-screen opacity-10 blur-3xl"
                style="background-color: {{ $theme }};"></div>
            <div
                class="absolute bottom-0 right-0 w-96 h-96 bg-purple-900 rounded-full mix-blend-screen opacity-10 blur-3xl">
            </div>
        </div>

        <!-- Floating Switch User Button -->
        <div class="fixed top-6 right-6 z-50">
            <form id="switch-user-form" action="{{ route('pos.logout') }}" method="POST">
                @csrf
                <button type="button" @click="confirmSwitch" title="Switch User"
                    class="group w-12 h-12 flex items-center justify-center rounded-full bg-slate-800/50 border border-slate-700/50 text-slate-400 hover:text-white hover:bg-red-600/80 hover:border-red-500 hover:shadow-[0_0_20px_rgba(220,38,38,0.4)] transition-all duration-300 backdrop-blur-md shadow-lg active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform group-hover:rotate-180"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </form>
        </div>

        <div class="relative z-10 flex flex-col items-center justify-center w-full max-w-md p-6">

            <!-- User Info -->
            <div class="text-center mb-10 w-full">
                <div class="relative inline-block mb-6">
                    <div
                        class="w-32 h-32 rounded-full p-1 shadow-2xl flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700">
                        <span
                            class="text-4xl font-bold text-white tracking-widest">{{ substr(Auth::user()->name, 0, 2) }}</span>
                    </div>
                    <div class="absolute bottom-2 right-2 w-8 h-8 bg-amber-500 border-4 border-slate-900 rounded-full flex items-center justify-center"
                        title="Locked">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-900" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <h2 class="text-3xl font-bold text-white tracking-tight mb-2">{{ Auth::user()->name }}</h2>
                <div class="flex items-center justify-center gap-2 text-slate-400 text-sm font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <span>{{ Auth::user()->outlet->name ?? 'Headquarters' }}</span>
                </div>
            </div>

            <!-- PIN Display -->
            <div class="mb-10 w-full flex justify-center gap-6">
                <template x-for="i in 4">
                    <div class="w-5 h-5 rounded-full border-2 transition-all duration-200"
                        :class="pinInput.length >= i ? 'bg-white border-white shadow-[0_0_15px_rgba(255,255,255,0.6)] scale-110' : 'border-slate-600 bg-transparent'">
                    </div>
                </template>
            </div>

            <!-- Numpad -->
            <div class="grid grid-cols-3 gap-6 w-full max-w-xs mb-10">
                <template x-for="n in [1,2,3,4,5,6,7,8,9]">
                    <button @click="handleLockPin(n)"
                        class="h-20 w-20 mx-auto rounded-full bg-slate-800/50 hover:bg-slate-700 active:bg-slate-600 border border-slate-700/50 text-3xl font-medium text-white transition-all duration-150 flex items-center justify-center shadow-lg active:scale-95 backdrop-blur-sm">
                        <span x-text="n"></span>
                    </button>
                </template>

                <div class="h-20 w-20 invisible"></div>

                <button @click="handleLockPin(0)"
                    class="h-20 w-20 mx-auto rounded-full bg-slate-800/50 hover:bg-slate-700 active:bg-slate-600 border border-slate-700/50 text-3xl font-medium text-white transition-all duration-150 flex items-center justify-center shadow-lg active:scale-95 backdrop-blur-sm">0</button>

                <button @click="handleLockPin('back')"
                    class="h-20 w-20 mx-auto rounded-full bg-transparent hover:bg-slate-800/30 text-slate-500 hover:text-white transition-all duration-150 flex items-center justify-center active:scale-95 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 transition-transform group-hover:-translate-x-1"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9.75L14.25 12m0 0l2.25 2.25M14.25 12l2.25-2.25M14.25 12L12 14.25m-2.58 4.92l-6.375-6.375a1.125 1.125 0 010-1.59L9.42 4.83c.211-.211.498-.33.796-.33H19.5a2.25 2.25 0 012.25 2.25v10.5a2.25 2.25 0 01-2.25 2.25h-9.284c-.298 0-.585-.119-.796-.33z" />
                    </svg>
                </button>
            </div>


        </div>
    </div>

    @push('scripts')
        <!-- Alpine.js -->
        <script defer src="{{ asset('assets/js/alpine.min.js') }}"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('lockScreen', () => ({
                    pinInput: '',
                    apiToken: '{{ $apiToken }}',

                    init() {
                        // Prevent back button
                        history.pushState(null, null, location.href);
                        window.onpopstate = function () {
                            history.go(1);
                        };
                    },

                    handleLockPin(val) {
                        if (val === 'back') {
                            this.pinInput = this.pinInput.slice(0, -1);
                            return;
                        }

                        if (this.pinInput.length < 4) {
                            this.pinInput += val;
                        }

                        if (this.pinInput.length === 4) {
                            this.verifyUnlock();
                        }
                    },

                    verifyUnlock() {
                        if (!navigator.onLine) {
                            Swal.fire({
                                icon: 'error', title: 'Offline', text: 'Cannot verify PIN while offline.',
                                toast: true, position: 'top', timer: 2000, showConfirmButton: false
                            });
                            this.pinInput = '';
                            return;
                        }

                        // Check PIN via web route to ensure session is cleared
                        fetch('{{ route('pos.verify-pin') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ pin: this.pinInput })
                        })
                        .then(async res => {
                            if (res.status === 401) {
                                window.location.href = '{{ route('login') }}';
                                return;
                            }
                            
                            const data = await res.json();
                            if (res.ok) {
                                window.location.href = '{{ route('pos.home') }}';
                            } else {
                                Swal.fire({
                                    icon: 'error', title: data.message || 'Invalid PIN',
                                    toast: true, position: 'top', timer: 1500, showConfirmButton: false,
                                    customClass: { popup: 'rounded-xl shadow-lg' }
                                });
                                this.pinInput = '';
                            }
                        })
                        .catch(err => {
                            console.error('Unlock error:', err);
                            this.pinInput = '';
                            Swal.fire({ icon: 'error', title: 'Connection Error', text: 'Failed to verify PIN.' });
                        });
                    },


                    confirmSwitch() {
                        Swal.fire({
                            title: 'Switch User?',
                            text: "You will be logged out to switch accounts.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#334155',
                            confirmButtonText: 'Yes, Switch',
                            cancelButtonText: 'Cancel',
                            background: '#1e293b',
                            color: '#fff'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('switch-user-form').submit();
                            }
                        })
                    }
                }))
            });
        </script>
    @endpush
@endsection