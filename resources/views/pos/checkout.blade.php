<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - E-POS</title>
    @php $theme = $outletSettings['pos_theme_color'] ?? 'indigo'; @endphp
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/fonts/figtree.css') }}" />
    <style>
        body {
            font-family: 'Figtree', sans-serif;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
    <script defer src="{{ asset('assets/js/alpine.min.js') }}"></script>
</head>

<body class="bg-slate-100 min-h-screen antialiased">
    <div x-data="checkoutApp()" x-init="init()" class="w-full h-screen flex overflow-hidden">

        {{-- ── LEFT: Order Summary ─────────────────────────────── --}}
        <div class="w-[380px] flex-shrink-0 bg-white border-r border-slate-200 flex flex-col shadow-lg z-10">

            {{-- Header --}}
            <div class="h-16 flex items-center px-5 border-b border-slate-100 gap-3 flex-shrink-0">
                <button onclick="window.location.href='{{ route('pos.home') }}'"
                    class="p-2 hover:bg-slate-100 rounded-xl transition-all text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-base font-bold text-slate-900 leading-none">Order Summary</h1>
                    <p class="text-[11px] text-slate-400 mt-0.5" x-text="itemCount + ' item(s)'"></p>
                </div>
                <span
                    class="ml-auto text-[10px] font-bold bg-{{ $theme }}-100 text-{{ $theme }}-700 px-2.5 py-1.5 rounded-lg"
                    x-text="itemCount + ' items'"></span>
            </div>

            {{-- Cart Items --}}
            <div class="flex-grow overflow-y-auto px-4 py-3 custom-scrollbar space-y-1.5">
                <template x-for="(item, index) in cart" :key="item.cartItemId">
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div class="w-7 h-7 rounded-lg bg-{{ $theme }}-100 flex items-center justify-center text-[11px] font-extrabold text-{{ $theme }}-700 flex-shrink-0"
                            x-text="index + 1"></div>
                        <div class="flex-grow min-w-0">
                            <p class="text-[13px] font-bold text-slate-800 line-clamp-1 leading-snug"
                                x-text="item.name"></p>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5"
                                x-text="'@ ' + formatPrice(item.price) + ' × ' + item.quantity"></p>
                        </div>
                        <span class="text-sm font-extrabold text-slate-900 tabular-nums flex-shrink-0"
                            x-text="formatPrice(item.price * item.quantity)"></span>
                    </div>
                </template>
            </div>

            {{-- Totals Footer --}}
            <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex-shrink-0 space-y-2">
                <div class="flex justify-between text-xs text-slate-500">
                    <span class="font-medium">Subtotal</span>
                    <span class="font-semibold text-slate-700 tabular-nums" x-text="formatPrice(subtotal)"></span>
                </div>
                <div class="flex justify-between text-xs text-slate-500">
                    <span class="font-medium">Tax (<span x-text="taxRate"></span>%)</span>
                    <span class="font-semibold text-slate-700 tabular-nums" x-text="formatPrice(taxAmount)"></span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-dashed border-slate-200">
                    <span class="text-sm font-bold text-slate-700">Total</span>
                    <span class="text-2xl font-extrabold text-{{ $theme }}-600 tabular-nums"
                        x-text="formatPrice(total)"></span>
                </div>
            </div>
        </div>

        {{-- ── RIGHT: Payment Panel ─────────────────────────────── --}}
        <div class="flex-1 flex flex-col bg-slate-100 overflow-hidden">

            {{-- Payment Header Bar --}}
            <div
                class="h-16 bg-white border-b border-slate-200 px-8 flex items-center justify-between flex-shrink-0 shadow-sm">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-{{ $theme }}-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-{{ $theme }}-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-800">Payment</h2>
                </div>
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Received</p>
                        <p class="text-lg font-bold text-emerald-600 tabular-nums" x-text="formatPrice(totalPaid)"></p>
                    </div>
                    <div class="w-px h-8 bg-slate-200"></div>
                    <div class="text-right">
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Balance Due
                        </p>
                        <p class="text-lg font-bold text-rose-600 tabular-nums" x-text="formatPrice(remainingDue)"></p>
                    </div>
                </div>
            </div>

            {{-- Main Payment Area --}}
            <div class="flex-grow p-6 overflow-y-auto flex gap-5 custom-scrollbar">

                {{-- Left: Payment Method + Numpad --}}
                <div class="flex-1 max-w-2xl flex flex-col gap-4">

                    {{-- Payment Method Tabs --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="grid grid-cols-3">
                            <template
                                x-for="m in [{key:'cash', label:'Cash', icon:'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'}, {key:'card', label:'Card', icon:'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'}, {key:'qr', label:'QR Pay', icon:'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 4h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'}]"
                                :key="m.key">
                                <button @click="paymentMethod = m.key" :class="paymentMethod === m.key
                                        ? 'bg-{{ $theme }}-600 text-white shadow-md'
                                        : 'text-slate-500 hover:bg-slate-50 hover:text-slate-700'"
                                    class="flex flex-col items-center justify-center gap-1.5 py-4 text-xs font-bold uppercase tracking-wide transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            :d="m.icon" />
                                    </svg>
                                    <span x-text="m.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Amount Display --}}
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 text-right">
                        <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Amount to Pay
                        </p>
                        <div class="flex justify-end items-baseline gap-2">
                            <span
                                class="text-lg font-bold text-slate-300">{{ $outletSettings['currency_symbol'] ?? 'RM' }}</span>
                            <span class="text-5xl font-extrabold text-slate-900 tracking-tight tabular-nums"
                                x-text="tenderAmountDisplay || '0.00'"></span>
                        </div>
                    </div>

                    {{-- Numpad Grid --}}
                    <div class="grid grid-cols-4 gap-3">
                        {{-- Digits --}}
                        <div class="col-span-3 grid grid-cols-3 gap-3">
                            <template x-for="n in [1,2,3,4,5,6,7,8,9,'.',0]">
                                <button @click="appendNumber(n)" x-text="n"
                                    class="h-14 rounded-xl bg-white border border-slate-200 text-xl font-bold text-slate-800 shadow-sm hover:border-{{ $theme }}-300 hover:bg-{{ $theme }}-50 hover:text-{{ $theme }}-700 active:scale-[0.97] transition-all duration-150"></button>
                            </template>
                            <button @click="backspace()"
                                class="h-14 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-rose-50 hover:text-rose-500 hover:border-rose-200 active:scale-[0.97] transition-all shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                                </svg>
                            </button>
                        </div>
                        {{-- Quick Amounts --}}
                        <div class="flex flex-col gap-3">
                            <button @click="setExact()"
                                class="flex-1 rounded-xl bg-{{ $theme }}-50 border-2 border-{{ $theme }}-200 text-{{ $theme }}-700 font-extrabold text-xs uppercase tracking-wide hover:bg-{{ $theme }}-100 active:scale-[0.97] transition-all">Exact</button>
                            <button @click="addAmount(10)"
                                class="flex-1 rounded-xl bg-white border border-slate-200 font-extrabold text-slate-700 hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97] transition-all shadow-sm">+10</button>
                            <button @click="addAmount(50)"
                                class="flex-1 rounded-xl bg-white border border-slate-200 font-extrabold text-slate-700 hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97] transition-all shadow-sm">+50</button>
                            <button @click="addAmount(100)"
                                class="flex-1 rounded-xl bg-white border border-slate-200 font-extrabold text-slate-700 hover:bg-slate-50 hover:border-slate-300 active:scale-[0.97] transition-all shadow-sm">+100</button>
                        </div>
                    </div>

                    {{-- Add Payment Button --}}
                    <button @click="addPayment()" :disabled="tenderAmount <= 0"
                        class="w-full py-4 rounded-xl font-bold text-base transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-3 shadow-lg"
                        :class="tenderAmount > 0
                            ? 'bg-slate-900 hover:bg-slate-800 text-white shadow-slate-300/50'
                            : 'bg-slate-100 text-slate-400 cursor-not-allowed'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Add Payment</span>
                    </button>
                </div>

                {{-- Right: Applied Payments --}}
                <div class="w-64 flex-shrink-0 flex flex-col gap-4">
                    <div
                        class="bg-white rounded-2xl border border-slate-200 shadow-sm flex-grow overflow-hidden flex flex-col">
                        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Applied Payments
                            </h3>
                        </div>
                        <div class="flex-grow p-4 space-y-2.5 overflow-y-auto custom-scrollbar">
                            <template x-for="(p, i) in payments" :key="i">
                                <div
                                    class="p-3.5 bg-slate-50 rounded-xl border border-slate-100 flex justify-between items-center group hover:bg-white hover:shadow-sm transition-all">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"
                                            x-text="p.method"></p>
                                        <p class="font-extrabold text-slate-800 text-sm tabular-nums"
                                            x-text="formatPrice(p.amount)"></p>
                                    </div>
                                    <button @click="removePayment(i)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                            <div x-show="payments.length === 0"
                                class="flex flex-col items-center justify-center py-10 text-slate-300">
                                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-xs font-medium">No payments yet</p>
                            </div>
                        </div>

                        {{-- Change Due (shows when overpaid) --}}
                        <div x-show="changeAmount > 0.01" x-transition
                            class="mx-4 mb-4 p-3 bg-emerald-50 border border-emerald-100 rounded-xl text-center">
                            <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wider mb-0.5">Change</p>
                            <p class="text-lg font-extrabold text-emerald-700 tabular-nums"
                                x-text="formatPrice(changeAmount)"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Finalize Footer --}}
            <div class="px-6 py-4 bg-white border-t border-slate-200 flex-shrink-0">
                <button @click="processPayment()" :disabled="remainingDue > 0.01"
                    :class="remainingDue > 0.01
                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                        : 'bg-{{ $theme }}-600 hover:bg-{{ $theme }}-700 text-white shadow-lg shadow-{{ $theme }}-200/50'"
                    class="w-full py-4 rounded-xl font-bold text-lg transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-3">
                    <span
                        x-text="remainingDue > 0.01 ? 'Balance Due: ' + formatPrice(remainingDue) : 'Finalize Transaction'"></span>
                    <svg x-show="remainingDue <= 0.01" class="w-6 h-6" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        function checkoutApp() {
            return {
                apiToken: '{{ $apiToken }}',
                apiBaseUrl: '{{ $apiBaseUrl }}',
                userId: {{ auth()->id() }},
                outletId: {{ auth()->user()->outlet_id }},
                cart: [],
                currency: '{{ $outletSettings['currency_symbol'] ?? 'RM' }}',
                taxRate: {{ $outletSettings['tax_rate'] ?? 0 }},
                subtotal: 0, taxAmount: 0, total: 0,
                paymentMethod: 'cash',
                tenderAmount: 0, tenderAmountDisplay: '',
                payments: [],

                get itemCount() { return this.cart.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0); },
                get totalPaid() { return this.payments.reduce((sum, p) => sum + p.amount, 0); },
                get remainingDue() { return Math.max(0, parseFloat((this.total - this.totalPaid).toFixed(2))); },
                get changeAmount() { return Math.max(0, parseFloat((this.totalPaid - this.total).toFixed(2))); },

                init() {
                    this.checkShiftStatus();
                    this.cart = JSON.parse(localStorage.getItem('pos_cart') || '[]');
                    this.calculateTotals();
                    this.setExact();
                },

                formatPrice(a) {
                    return this.currency + parseFloat(a).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },

                calculateTotals() {
                    this.subtotal = this.cart.reduce((s, i) => s + (parseFloat(i.price) * i.quantity), 0);
                    this.taxAmount = parseFloat((this.subtotal * (this.taxRate / 100)).toFixed(2));
                    this.total = parseFloat(Math.max(0, this.subtotal + this.taxAmount).toFixed(2));
                },

                appendNumber(n) {
                    if (n === '.' && this.tenderAmountDisplay.includes('.')) return;
                    this.tenderAmountDisplay += n.toString();
                    this.tenderAmount = parseFloat(this.tenderAmountDisplay);
                },

                backspace() {
                    this.tenderAmountDisplay = this.tenderAmountDisplay.slice(0, -1);
                    this.tenderAmount = parseFloat(this.tenderAmountDisplay || 0);
                },

                setExact() {
                    this.tenderAmount = this.remainingDue;
                    this.tenderAmountDisplay = this.remainingDue.toFixed(2);
                },

                addAmount(a) {
                    this.tenderAmount += a;
                    this.tenderAmountDisplay = this.tenderAmount.toFixed(2);
                },

                addPayment() {
                    if (this.tenderAmount <= 0) return;
                    this.payments.push({ method: this.paymentMethod, amount: this.tenderAmount });
                    this.tenderAmount = 0;
                    this.tenderAmountDisplay = '';
                    if (this.remainingDue > 0) this.setExact();
                },

                removePayment(i) {
                    this.payments.splice(i, 1);
                    this.setExact();
                },

                async checkShiftStatus() {
                    const res = await fetch(`${this.apiBaseUrl}/pos/shift/current`, {
                        headers: { 'Authorization': 'Bearer ' + this.apiToken }
                    });
                    const data = await res.json();
                    if (!data.success || !data.data.shift) window.location.href = '{{ route('pos.home') }}';
                },

                async processPayment() {
                    const breakdown = this.payments.map(p =>
                        `<div class="flex justify-between text-sm py-1 border-b border-slate-100 last:border-0">
                            <span class="font-medium text-slate-500 capitalize">${p.method}</span>
                            <span class="font-bold text-slate-800">${this.formatPrice(p.amount)}</span>
                        </div>`
                    ).join('');
                    const changeHtml = this.changeAmount > 0.01
                        ? `<div class="mt-3 p-3 bg-emerald-50 rounded-xl border border-emerald-100 text-sm font-bold text-emerald-700">
                            Change Due: ${this.formatPrice(this.changeAmount)}
                           </div>`
                        : '';

                    const { isConfirmed } = await Swal.fire({
                        title: 'Confirm Payment',
                        html: `
                            <div class="text-left space-y-3">
                                <div class="text-center py-2">
                                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Charged</p>
                                    <p class="text-4xl font-extrabold text-slate-900">${this.formatPrice(this.total)}</p>
                                </div>
                                <div class="bg-slate-50 rounded-xl p-3 border border-slate-100">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Payment Breakdown</p>
                                    ${breakdown}
                                </div>
                                ${changeHtml}
                            </div>`,
                        icon: null,
                        showCancelButton: true,
                        confirmButtonText: 'Confirm Payment',
                        cancelButtonText: 'Go Back',
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#64748b',
                        customClass: { popup: 'rounded-2xl' }
                    });

                    if (!isConfirmed) return;

                    Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    const payload = {
                        outlet_id: this.outletId,
                        user_id: this.userId,
                        total_amount: this.total,
                        tax_amount: this.taxAmount,
                        status: 'completed',
                        items: this.cart.map(i => ({ product_id: i.id, quantity: i.quantity, price: i.price })),
                        payments: this.payments.map(p => ({ amount: p.amount, payment_method: p.method }))
                    };
                    try {
                        const res = await fetch(`${this.apiBaseUrl}/pos/sales`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.apiToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            localStorage.removeItem('pos_cart');
                            localStorage.removeItem('pos_customer');
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Complete!',
                                text: this.changeAmount > 0.01 ? 'Change due: ' + this.formatPrice(this.changeAmount) : 'Thank you!',
                                timer: 2500,
                                showConfirmButton: false
                            }).then(() => window.location.href = '{{ route('pos.home') }}');
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        Swal.fire('Error', 'Network error. Please try again.', 'error');
                    }
                }
            };
        }
    </script>
</body>

</html>