@extends('layouts.app')

@section('content')
    @php $theme = $outletSettings['pos_theme_color'] ?? 'indigo'; @endphp
    <script>window.apiToken = '{{ $apiToken }}';</script>

    <div x-data="posAppData()"
        class="flex flex-col h-screen bg-slate-100 font-sans antialiased text-slate-800 overflow-hidden relative">
        <style>
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: scale(0.98) translateY(6px);
                }

                to {
                    opacity: 1;
                    transform: scale(1) translateY(0);
                }
            }

            .modal-dialog {
                backface-visibility: hidden;
                -webkit-backface-visibility: hidden;
                transform-style: preserve-3d;
            }

            @keyframes pulse-soft {

                0%,
                100% {
                    opacity: 1;
                }

                50% {
                    opacity: .5;
                }
            }

            .animate-standard {
                animation: fadeIn 0.25s ease-out forwards;
            }

            .animate-pulse-soft {
                animation: pulse-soft 2s ease-in-out infinite;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 4px;
            }

            .custom-scrollbar::-webkit-scrollbar-track {
                background: transparent;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background: #e2e8f0;
                border-radius: 10px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                background: #cbd5e1;
            }

            [x-cloak] {
                display: none !important;
            }

            .pos-btn-primary {
                @apply bg-{{ $theme }}-600 hover:bg-{{ $theme }}-700 text-white font-bold px-6 py-4 rounded-2xl transition-all duration-200 active:scale-[0.97] shadow-lg shadow-{{ $theme }}-200/50 flex items-center justify-center gap-3;
            }
        </style>

        <!-- SHIFT GUARD OVERLAY -->
        <div id="shift-guard-overlay"
            class="fixed inset-0 z-[100000] bg-slate-900/50 backdrop-blur-xl flex items-center justify-center p-6 {{ $hasOpenShift ? 'hidden' : '' }}">
            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl p-10 text-center animate-standard">
                <div
                    class="w-16 h-16 bg-gradient-to-br from-{{ $theme }}-500 to-{{ $theme }}-700 rounded-2xl flex items-center justify-center text-white mx-auto mb-6 shadow-lg shadow-{{ $theme }}-200/50">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Start Your Shift</h2>
                <p class="text-slate-500 text-sm mb-8 leading-relaxed">Enter the opening float amount to begin your terminal
                    session.</p>
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-100 mb-6">
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-3">Opening
                        Cash</label>
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-xl font-bold text-slate-300">{{ $outletSettings['currency_symbol'] ?? '$' }}</span>
                        <input type="number" id="guard-opening-cash"
                            class="block w-full bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-{{ $theme }}-500/20 focus:border-{{ $theme }}-400 text-3xl font-bold text-slate-900 text-center py-4 placeholder:text-slate-200 transition-all"
                            placeholder="0.00" step="0.01" autofocus>
                    </div>
                </div>
                <button onclick="posApp.openShiftFromGuard()" class="pos-btn-primary w-full !text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    Start Session
                </button>
            </div>
        </div>

        <!-- Main Header -->
        <header
            class="h-14 bg-white border-b border-slate-200/80 flex justify-between items-center px-5 z-30 flex-shrink-0 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 bg-gradient-to-br from-{{ $theme }}-500 to-{{ $theme }}-700 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm">
                        P</div>
                    <h1 class="text-base font-bold text-slate-900">e-POS</h1>
                </div>
                <div class="h-5 w-px bg-slate-200"></div>
                <div class="text-xs font-medium text-slate-400" id="current-date-time"></div>
            </div>

            <div class="flex items-center gap-2">
                <div class="flex items-center bg-slate-100/80 p-1 rounded-xl gap-0.5">
                    <button onclick="posApp.openHistory()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-[11px] font-semibold text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        History
                    </button>
                    <button onclick="posApp.openShiftModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-[11px] font-semibold text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Shift
                    </button>
                    <button onclick="posApp.openTransferModal()"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-[11px] font-semibold text-slate-500 hover:bg-white hover:text-slate-800 hover:shadow-sm rounded-lg transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Stock
                    </button>
                </div>
                <div class="h-5 w-px bg-slate-200 mx-1"></div>
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-full bg-{{ $theme }}-50 border border-{{ $theme }}-100 flex items-center justify-center text-{{ $theme }}-600 text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(Auth::user()->name, strpos(Auth::user()->name, ' ') + 1, 1)) }}
                    </div>
                    <div class="text-right hidden sm:block">
                        <p class="text-xs font-semibold text-slate-700 leading-none mb-0.5">{{ Auth::user()->name }}</p>
                        <p class="text-[10px] font-medium text-slate-400">{{ Auth::user()->outlet->name ?? 'HQ' }}</p>
                    </div>
                    <button onclick="confirmLogout()"
                        class="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all"
                        title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow flex overflow-hidden p-3 gap-3 bg-slate-100">
            <!-- Left Panel: Catalog -->
            <section
                class="flex-grow flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden"
                @product-selected.window="addToCart($event.detail.product)">
                <div class="flex-grow overflow-y-auto p-5 custom-scrollbar">
                    <livewire:pos.product-picker :theme="$theme" />
                </div>
            </section>

            <!-- Right Panel: Cart -->
            <section class="w-[380px] flex-shrink-0 flex flex-col h-full overflow-hidden">
                <div
                    class="flex-grow flex flex-col bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">

                    <!-- Cart Header with Customer Integration -->
                    <div class="px-5 pt-5 pb-4 border-b border-slate-100 flex-shrink-0">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex items-center gap-2.5">
                                <h2 class="text-sm font-bold text-slate-800">Current Order</h2>
                                <span x-show="cart.length > 0" x-transition
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[10px] font-bold bg-{{ $theme }}-100 text-{{ $theme }}-700 rounded-full"
                                    x-text="cart.reduce((s,i) => s + i.quantity, 0)"></span>
                            </div>
                            <button onclick="posApp.clearCart()"
                                class="p-1.5 text-slate-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-all"
                                title="Clear Order">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>

                        <!-- Customer Selection Display -->
                        <div
                            class="group flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 transition-all">
                            <div class="flex items-center gap-2.5 overflow-hidden flex-grow cursor-pointer"
                                onclick="posApp.openCustomerModal()">
                                <div
                                    class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 group-hover:text-{{ $theme }}-600 group-hover:border-{{ $theme }}-100 transition-all shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div class="truncate">
                                    <p class="text-xs font-semibold text-slate-700"
                                        x-text="cartCustomer ? cartCustomer.name : 'Walk-in Customer'"></p>
                                    <p class="text-[10px] font-medium text-slate-400"
                                        x-text="cartCustomer ? cartCustomer.phone : 'Tap to assign customer'"></p>
                                </div>
                            </div>

                            <div class="flex items-center">
                                <button type="button" x-show="cartCustomer"
                                    onclick="event.stopPropagation(); posApp.selectCustomer(null);"
                                    class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors focus:outline-none">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                                <div onclick="event.stopPropagation(); posApp.openCustomerModal();"
                                    class="p-2 cursor-pointer text-slate-300 group-hover:text-{{ $theme }}-500 transition-colors"
                                    x-show="!cartCustomer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scrollable Cart Items -->
                    <div id="cart-items" class="flex-grow overflow-y-auto px-5 py-3 custom-scrollbar space-y-2">
                        <template x-for="(item, index) in cart" :key="item.cartItemId">
                            <div
                                class="group p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:bg-slate-100 hover:border-slate-200 transition-all duration-150 animate-standard">

                                <!-- Row 1: Badge + Name + Unit Price + Remove -->
                                <div class="flex items-start gap-2.5 mb-2.5">
                                    <div class="w-5 h-5 mt-0.5 rounded-md bg-{{ $theme }}-100 flex items-center justify-center text-[10px] font-bold text-{{ $theme }}-600 flex-shrink-0 transition-all"
                                        x-text="index + 1"></div>
                                    <div class="flex-grow min-w-0">
                                        <p class="text-[13px] font-bold text-slate-800 leading-snug line-clamp-2"
                                            x-text="item.name"></p>
                                        <p class="text-[11px] font-medium text-slate-400 mt-0.5"
                                            x-text="formatPrice(item.unitPrice || item.price) + ' / unit'"></p>
                                    </div>
                                    <!-- Remove Item Button -->
                                    <button @click="deleteFromCart(item.cartItemId)"
                                        class="ml-auto flex-shrink-0 w-6 h-6 rounded-md flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-100 transition-all"
                                        title="Remove item">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Row 2: Qty Stepper + Line Total -->
                                <div class="flex items-center justify-between pl-7">
                                    <div class="flex items-center gap-1.5">
                                        <button @click="removeItemFromCart(item.cartItemId)"
                                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 hover:border-rose-200 transition-all shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="text-sm font-extrabold text-slate-800 w-7 text-center tabular-nums"
                                            x-text="item.quantity"></span>
                                        <button @click="addItemToCart({cartItemId: item.cartItemId, id: item.id})"
                                            class="w-7 h-7 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:bg-{{ $theme }}-50 hover:text-{{ $theme }}-600 hover:border-{{ $theme }}-200 transition-all shadow-sm">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                    d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p class="text-sm font-extrabold text-slate-900 tabular-nums"
                                        x-text="formatPrice((item.unitPrice || item.price) * item.quantity)"></p>
                                </div>
                            </div>
                        </template>
                        <!-- Empty State -->
                        <div x-show="cart.length === 0" class="h-full flex flex-col items-center justify-center py-16">
                            <div
                                class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mb-4">
                                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <p class="text-sm font-semibold text-slate-400 mb-1">No items yet</p>
                            <p class="text-xs text-slate-300">Tap products to add them here</p>
                        </div>
                    </div>

                    <!-- Cart Footer / Totals -->
                    <div class="px-5 py-4 bg-slate-50/80 border-t border-slate-100 flex-shrink-0">
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between items-center text-xs text-slate-500">
                                <span class="font-medium">Subtotal</span>
                                <span class="font-semibold text-slate-700" x-text="formatPrice(cartSubtotal)"></span>
                            </div>
                            <div class="flex justify-between items-center text-xs text-slate-500">
                                <span class="font-medium">Tax (<span x-text="taxRate"></span>%)</span>
                                <span class="font-semibold text-slate-700" x-text="formatPrice(cartTax)"></span>
                            </div>
                            <div class="flex justify-between items-center border-t border-dashed border-slate-200 pt-3">
                                <span class="text-sm font-bold text-slate-700">Total</span>
                                <span class="text-2xl font-bold text-{{ $theme }}-600"
                                    x-text="formatPrice(cartTotal)"></span>
                            </div>
                        </div>
                        <button type="button" @click="redirectToCheckout()"
                            :class="cart.length > 0 ? 'bg-{{ $theme }}-600 hover:bg-{{ $theme }}-700 text-white shadow-lg shadow-{{ $theme }}-200/50' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                            class="w-full py-4 rounded-xl font-bold text-lg transition-all duration-200 active:scale-[0.98] flex items-center justify-center gap-2.5">
                            <span>Checkout</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </button>
                    </div>
                </div>
            </section>
        </main>

        {{-- ============================================================ --}}
        {{-- STANDARD MODALS --}}
        {{-- Consistent pattern: overlay > centered dialog --}}
        {{-- - Header: icon + title + close button --}}
        {{-- - Body: scrollable content --}}
        {{-- - Footer (where needed): action buttons --}}
        {{-- ============================================================ --}}

        {{-- ── HISTORY MODAL ────────────────────────────────────────── --}}
        {{-- ── HISTORY MODAL ─────────────────────────────────────────── --}}
        <div id="history-modal" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="posApp.closeHistory()"></div>
            <div class="fixed inset-0 flex items-start justify-center p-4 pt-8 pointer-events-none">
                <div
                    class="relative w-full max-w-5xl bg-white rounded-2xl shadow-2xl pointer-events-auto flex max-h-[88vh] animate-standard overflow-hidden outline-none">

                    {{-- LEFT: Transaction List --}}
                    <div class="flex flex-col flex-grow min-w-0 border-r border-slate-100">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-100 flex-shrink-0">
                            <div
                                class="w-9 h-9 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-grow">
                                <h2 class="text-sm font-bold text-slate-900">Transaction History</h2>
                                <p class="text-xs text-slate-400">Click a row to view the receipt</p>
                            </div>
                            <button onclick="posApp.loadHistory()" title="Refresh"
                                class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            <button onclick="posApp.closeHistory()"
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex-grow overflow-y-auto custom-scrollbar">
                            <table class="w-full text-left">
                                <thead
                                    class="sticky top-0 bg-slate-50 text-[10px] font-semibold text-slate-500 uppercase tracking-wide border-b border-slate-100 z-10">
                                    <tr>
                                        <th class="px-4 py-3">Order #</th>
                                        <th class="px-4 py-3">Date &amp; Time</th>
                                        <th class="px-4 py-3">Customer</th>
                                        <th class="px-4 py-3">Cashier</th>
                                        <th class="px-4 py-3">Status</th>
                                        <th class="px-4 py-3 text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="history-list-body" class="divide-y divide-slate-50 text-sm">
                                    <tr>
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- RIGHT: Receipt Panel --}}
                    <div class="w-72 flex-shrink-0 flex flex-col bg-slate-50/50">
                        <div class="px-5 py-4 border-b border-slate-100 flex-shrink-0">
                            <h3 class="text-sm font-bold text-slate-900">Order Receipt</h3>
                            <p class="text-xs text-slate-400">Select a transaction to view</p>
                        </div>
                        <div id="history-receipt-body"
                            class="flex-grow overflow-y-auto custom-scrollbar p-4 flex flex-col items-center justify-center text-center text-slate-400">
                            <svg class="w-10 h-10 mb-3 text-slate-200" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-sm font-medium">No order selected</p>
                            <p class="text-xs mt-1">Click any row on the left</p>
                        </div>
                        <div id="history-receipt-footer" class="px-4 py-4 border-t border-slate-100 flex-shrink-0 hidden">
                            <button id="history-void-btn" onclick="posApp.voidSale(posApp._selectedSaleId)"
                                class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold text-sm flex items-center justify-center gap-2 transition-all active:scale-[0.97] shadow-md shadow-rose-200/50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Void This Order
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        {{-- ── SHIFT MODAL ──────────────────────────────────────────── --}}
        <div id="shift-modal" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="posApp.closeShiftModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div
                    class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl pointer-events-auto flex flex-col max-h-[90vh] animate-standard overflow-hidden outline-none">
                    {{-- Header --}}
                    <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-100 flex-shrink-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="flex-grow">
                            <h2 class="text-base font-bold text-slate-900 leading-tight">Shift Dashboard</h2>
                            <p class="text-xs text-slate-400">Session <span id="current-shift-number-header"
                                    class="font-semibold text-slate-600">-</span></p>
                        </div>
                        <button onclick="posApp.closeShiftModal()"
                            class="flex-shrink-0 p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    {{-- Body --}}
                    <div class="flex-grow overflow-y-auto custom-scrollbar p-6">
                        <div id="shift-current-info" class="hidden animate-standard space-y-6">
                            {{-- Stats Row --}}
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-5">
                                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide mb-1">Gross
                                        Sales</p>
                                    <p class="text-2xl font-bold text-slate-900" id="shift-total-sales-display">-</p>
                                </div>
                                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5">
                                    <p class="text-[11px] font-semibold text-emerald-500 uppercase tracking-wide mb-1">
                                        Expected Cash</p>
                                    <p class="text-2xl font-bold text-emerald-600" id="shift-expected-cash-display">-</p>
                                </div>
                                <div class="bg-amber-50 border border-amber-100 rounded-xl p-5">
                                    <p class="text-[11px] font-semibold text-amber-500 uppercase tracking-wide mb-1">
                                        Non-Cash</p>
                                    <p class="text-2xl font-bold text-amber-600" id="shift-card-sales-display">-</p>
                                </div>
                            </div>
                            {{-- Staff Table + Close Form --}}
                            <div class="grid grid-cols-5 gap-5">
                                {{-- Staff Breakdown --}}
                                <div class="col-span-3 bg-white border border-slate-200 rounded-xl overflow-hidden">
                                    <div class="px-5 py-3 bg-slate-50 border-b border-slate-100">
                                        <h4 class="text-[11px] font-semibold uppercase text-slate-500 tracking-wide">Staff
                                            Contribution</h4>
                                    </div>
                                    <table class="w-full text-left text-sm">
                                        <thead
                                            class="bg-slate-50/50 text-[11px] font-semibold text-slate-400 border-b border-slate-100">
                                            <tr>
                                                <th class="px-5 py-3">User</th>
                                                <th class="px-5 py-3 text-right">Cash</th>
                                                <th class="px-5 py-3 text-right">Net Sales</th>
                                            </tr>
                                        </thead>
                                        <tbody id="shift-user-breakdown" class="divide-y divide-slate-50"></tbody>
                                    </table>
                                </div>
                                {{-- Close Form --}}
                                <div class="col-span-2 bg-rose-50 border border-rose-100 rounded-xl p-5 flex flex-col">
                                    <h4 class="text-sm font-bold text-slate-900 mb-1">Close This Shift</h4>
                                    <p class="text-xs text-slate-500 mb-4">Enter the physical cash in the drawer for
                                        reconciliation.</p>
                                    <div class="relative mb-4">
                                        <span
                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-base font-bold text-slate-400">{{ $outletSettings['currency_symbol'] ?? 'RM' }}</span>
                                        <input type="number" id="closing-cash"
                                            class="w-full pl-8 pr-3 py-3.5 text-2xl font-bold text-center bg-white border border-rose-200 rounded-xl focus:ring-2 focus:ring-rose-400/20 focus:border-rose-400 transition-all"
                                            placeholder="0.00">
                                    </div>
                                    <button onclick="posApp.closeShift()"
                                        class="mt-auto w-full py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-semibold text-sm shadow-md shadow-rose-200/50 active:scale-[0.97] transition-all flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Close & Reconcile
                                    </button>
                                </div>
                            </div>
                        </div>
                        {{-- No active shift placeholder (hidden when shift is active) --}}
                        <div id="shift-no-info" class="py-12 text-center text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-sm font-medium">No active shift data</p>
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="px-6 py-3 border-t border-slate-100 flex justify-end flex-shrink-0 bg-slate-50/50">
                        <button onclick="posApp.closeShiftModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Dismiss</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── STOCK & TRANSFER MODAL (Tabbed) ─────────────────────────── --}}
        {{-- ── STOCK & TRANSFER MODAL (Tabbed) ─────────────────────────── --}}
        <div id="transfer-modal" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true" x-data="{
                                            tab: 'stock',
                                            stockItems: [], stockLoading: true,
                                            outlets: [],
                                            productSearch: '', productSearchResults: [], selectedProduct: null,
                                            transferOutletId: '', transferQty: '', transferNotes: '', transferring: false,
                                            _searchTimer: null,


                                            async loadStock() {
                                                this.stockLoading = true;
                                                try {
                                                    const r = await fetch(posApp.apiBaseUrl + '/pos/inventory/low-stock', { headers: { 'Authorization': 'Bearer ' + posApp.apiToken } });
                                                    const d = await r.json();
                                                    this.stockItems = d.data || [];
                                                } catch(e) { console.error(e); }
                                                this.stockLoading = false;
                                            },

                                            async loadOutlets() {
                                                if (this.outlets.length) return;
                                                try {
                                                    const r = await fetch(posApp.apiBaseUrl + '/pos/outlets', { headers: { 'Authorization': 'Bearer ' + posApp.apiToken } });
                                                    const d = await r.json();
                                                    this.outlets = d.data || [];
                                                } catch(e) { console.error(e); }
                                            },

                                            async searchProducts() {
                                                if (this.productSearch.length < 2) { this.productSearchResults = []; return; }
                                                try {
                                                    const r = await fetch(posApp.apiBaseUrl + '/pos/products?query=' + encodeURIComponent(this.productSearch), { headers: { 'Authorization': 'Bearer ' + posApp.apiToken } });
                                                    const d = await r.json();
                                                    this.productSearchResults = (d.data || []).slice(0, 8);
                                                } catch(e) { console.error(e); this.productSearchResults = []; }
                                            },

                                            selectProduct(p) {
                                                this.selectedProduct = p;
                                                this.productSearch = p.name;
                                                this.productSearchResults = [];
                                            },

                                            async submitTransfer() {
                                                if (!this.transferOutletId) return Swal.fire('Missing', 'Please select a destination outlet.', 'warning');
                                                if (!this.selectedProduct) return Swal.fire('Missing', 'Please select a product.', 'warning');
                                                if (!this.transferQty || this.transferQty < 1) return Swal.fire('Missing', 'Enter a valid quantity.', 'warning');
                                                this.transferring = true;
                                                try {
                                                    const res = await fetch(posApp.apiBaseUrl + '/pos/transfers', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + posApp.apiToken, 'Accept': 'application/json' },
                                                        body: JSON.stringify({ to_outlet_id: this.transferOutletId, items: [{ product_id: this.selectedProduct.id, quantity: parseInt(this.transferQty) }], notes: this.transferNotes })
                                                    });
                                                    const data = await res.json();
                                                    if (data.success) {
                                                        Swal.fire('Done', 'Transfer request created successfully.', 'success');
                                                        this.selectedProduct = null; this.productSearch = ''; this.transferQty = ''; this.transferNotes = ''; this.transferOutletId = '';
                                                        this.tab = 'stock'; this.loadStock();
                                                    } else { Swal.fire('Error', data.message, 'error'); }
                                                } catch(e) { Swal.fire('Error', 'Failed to submit transfer.', 'error'); }
                                                this.transferring = false;
                                            }
                                        }">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="posApp.closeTransferModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div
                    class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl pointer-events-auto flex flex-col max-h-[88vh] animate-standard overflow-hidden outline-none">

                    {{-- Header --}}
                    <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-100 flex-shrink-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center text-violet-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="flex-grow">
                            <h2 class="text-base font-bold text-slate-900 leading-tight">Stock Management</h2>
                            <p class="text-xs text-slate-400">Monitor levels &amp; transfer stock between outlets</p>
                        </div>
                        <button onclick="posApp.closeTransferModal()"
                            class="flex-shrink-0 p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Tab Bar --}}
                    <div class="flex border-b border-slate-100 px-4 flex-shrink-0 bg-slate-50/50">
                        <button @click="tab = 'stock'"
                            :class="tab === 'stock' ? 'border-b-2 border-violet-600 text-violet-600 font-semibold' : 'text-slate-400 hover:text-slate-600'"
                            class="py-3 px-4 text-sm transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Stock Levels
                            <span x-show="stockItems.length > 0" x-text="stockItems.length"
                                class="text-[10px] font-bold bg-rose-100 text-rose-600 rounded-full px-1.5 leading-5"></span>
                        </button>
                        <button @click="tab = 'transfer'; loadOutlets()"
                            :class="tab === 'transfer' ? 'border-b-2 border-violet-600 text-violet-600 font-semibold' : 'text-slate-400 hover:text-slate-600'"
                            class="py-3 px-4 text-sm transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Transfer
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="flex-grow overflow-y-auto custom-scrollbar">

                        {{-- Stock Levels Tab --}}
                        <div x-show="tab === 'stock'" class="p-5">
                            <div x-show="stockLoading"
                                class="flex flex-col items-center justify-center py-12 text-slate-400">
                                <svg class="w-8 h-8 animate-spin mb-3 text-violet-400" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                                </svg>
                                <p class="text-sm">Fetching stock data...</p>
                            </div>
                            <div x-show="!stockLoading && stockItems.length === 0"
                                class="flex flex-col items-center justify-center py-12">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
                                    <svg class="w-7 h-7 text-emerald-500" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 mb-1">All Stock Levels OK</p>
                                <p class="text-xs text-slate-400 text-center">No products are currently below their low
                                    stock threshold.</p>
                            </div>
                            <div x-show="!stockLoading && stockItems.length > 0" class="space-y-2.5">
                                <p class="text-xs font-semibold text-rose-500 uppercase tracking-wide mb-3"><span
                                        x-text="stockItems.length"></span> product(s) need attention</p>
                                <template x-for="item in stockItems" :key="item.id">
                                    <div
                                        class="flex items-center gap-3 p-4 bg-slate-50 border border-slate-100 rounded-xl hover:bg-white hover:shadow-sm transition-all">
                                        <div
                                            class="w-9 h-9 rounded-xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-xs font-bold text-rose-600"
                                                x-text="item.name.slice(0,2).toUpperCase()"></span>
                                        </div>
                                        <div class="flex-grow min-w-0">
                                            <p class="text-sm font-semibold text-slate-800 truncate" x-text="item.name"></p>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <div class="flex-grow bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                                    <div class="h-1.5 rounded-full transition-all"
                                                        :class="item.stock_level <= 2 ? 'bg-rose-500' : item.stock_level <= 5 ? 'bg-amber-400' : 'bg-emerald-400'"
                                                        :style="'width: ' + Math.min(100, Math.round((item.stock_level / (item.threshold || 10)) * 100)) + '%'">
                                                    </div>
                                                </div>
                                                <span
                                                    class="text-[11px] font-semibold text-slate-500 whitespace-nowrap"><span
                                                        x-text="item.stock_level"></span> / <span
                                                        x-text="item.threshold"></span></span>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg flex-shrink-0"
                                            :class="item.stock_level <= 2 ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-600'"
                                            x-text="item.stock_level <= 2 ? 'Critical' : 'Low'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Transfer Tab --}}
                        <div x-show="tab === 'transfer'" class="p-5 space-y-4" style="overflow: visible;">
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Destination
                                    Outlet</label>
                                <select x-model="transferOutletId"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 focus:bg-white transition-all">
                                    <option value="">Select outlet...</option>
                                    <template x-for="outlet in outlets" :key="outlet.id">
                                        <option :value="outlet.id" x-text="outlet.name"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Product</label>
                                <input type="text" x-model="productSearch" oninput="posTransferSearch(this.value)"
                                    placeholder="Type at least 2 characters to search..."
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 focus:bg-white transition-all placeholder:text-slate-400">
                                {{-- Inline results list (no absolute positioning - works inside scroll containers) --}}
                                <div x-show="productSearchResults.length > 0" id="transfer-product-results"
                                    class="mt-1 bg-white border border-slate-200 rounded-xl shadow-md overflow-hidden">
                                    <template x-for="p in productSearchResults" :key="p.id">
                                        <button
                                            @click="selectProduct(p); $el.closest('[id=transfer-modal]') && (document.querySelector('#transfer-modal input[x-model=productSearch]').value = p.name)"
                                            class="w-full flex items-center justify-between px-4 py-3 hover:bg-violet-50 transition-all text-left border-b border-slate-50 last:border-0">
                                            <div>
                                                <span class="text-sm font-medium text-slate-800" x-text="p.name"></span>
                                                <span class="text-xs text-slate-400 block"
                                                    x-text="p.category ? p.category.name : ''"></span>
                                            </div>
                                            <span class="text-xs font-semibold px-2 py-1 rounded-lg ml-3 flex-shrink-0"
                                                :class="(p.stock_level ?? 0) <= 5 ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600'"
                                                x-text="'Stock: ' + (p.stock_level ?? '?')"></span>
                                        </button>
                                    </template>
                                </div>
                                {{-- Selected product chip --}}
                                <div x-show="selectedProduct && productSearchResults.length === 0"
                                    class="mt-2 flex items-center gap-2 px-3 py-2.5 bg-violet-50 border border-violet-100 rounded-xl">
                                    <svg class="w-4 h-4 text-violet-500 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4" />
                                    </svg>
                                    <div class="flex-grow min-w-0">
                                        <p class="text-xs font-semibold text-violet-800 truncate"
                                            x-text="selectedProduct ? selectedProduct.name : ''"></p>
                                        <p class="text-[10px] text-violet-500"
                                            x-text="selectedProduct ? 'Available: ' + (selectedProduct.stock_level ?? '?') + ' units' : ''">
                                        </p>
                                    </div>
                                    <button @click="selectedProduct = null; productSearch = ''"
                                        class="text-violet-400 hover:text-violet-700 flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Quantity
                                    to Transfer</label>
                                <input type="number" x-model="transferQty" min="1"
                                    :max="selectedProduct ? selectedProduct.stock_level : ''" placeholder="0"
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 focus:bg-white transition-all">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Notes
                                    <span class="text-slate-300 lowercase font-normal">(optional)</span></label>
                                <textarea x-model="transferNotes" rows="2" placeholder="Reason for transfer..."
                                    class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-700 focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 focus:bg-white transition-all resize-none placeholder:text-slate-400"></textarea>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t border-slate-100 flex items-center gap-3 flex-shrink-0 bg-slate-50/50">
                        <button x-show="tab === 'stock'" @click="loadStock()"
                            class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-violet-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh
                        </button>
                        <div class="flex-grow"></div>
                        <button onclick="posApp.closeTransferModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Close</button>
                        <button x-show="tab === 'transfer'" @click="submitTransfer()" :disabled="transferring"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-violet-600 hover:bg-violet-700 rounded-xl shadow-md shadow-violet-200/50 active:scale-[0.97] transition-all flex items-center gap-2 disabled:opacity-60">
                            <svg x-show="!transferring" class="w-4 h-4" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <svg x-show="transferring" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                            </svg>
                            <span x-text="transferring ? 'Submitting...' : 'Submit Transfer'"></span>
                        </button>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── CUSTOMER MODAL ───────────────────────────────────────── --}}
        <div id="customer-modal" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" onclick="posApp.closeCustomerModal()"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div
                    class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl pointer-events-auto flex flex-col max-h-[85vh] animate-standard overflow-hidden outline-none">
                    {{-- Header --}}
                    <div class="flex items-center gap-4 px-6 py-4 border-b border-slate-100 flex-shrink-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-{{ $theme }}-50 flex items-center justify-center text-{{ $theme }}-600 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="flex-grow">
                            <h2 class="text-base font-bold text-slate-900 leading-tight">Assign Customer</h2>
                            <p class="text-xs text-slate-400">Search existing or create a new profile</p>
                        </div>
                        <button onclick="posApp.closeCustomerModal()"
                            class="flex-shrink-0 p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    {{-- Body --}}
                    <div class="flex-grow overflow-y-auto custom-scrollbar p-6 space-y-4">
                        {{-- Search --}}
                        <div class="relative">
                            <input type="text" id="customer-search-input" oninput="posApp.searchCustomer()"
                                class="w-full pl-4 pr-10 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-{{ $theme }}-500/20 focus:border-{{ $theme }}-400 focus:bg-white transition-all placeholder:text-slate-400"
                                placeholder="Name or phone number...">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                        {{-- Results --}}
                        <div id="customer-search-results" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar"></div>
                        {{-- Divider --}}
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-slate-100"></div>
                            </div>
                            <div class="relative flex justify-center"><span
                                    class="bg-white px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wide">Or
                                    Create New</span></div>
                        </div>
                        {{-- New Customer Form --}}
                        <div class="space-y-3">
                            <input type="text" id="new-customer-name" placeholder="Full Name"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-{{ $theme }}-400 focus:bg-white focus:ring-2 focus:ring-{{ $theme }}-500/20 outline-none transition-all placeholder:text-slate-400">
                            <input type="tel" id="new-customer-phone" placeholder="Phone Number"
                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:border-{{ $theme }}-400 focus:bg-white focus:ring-2 focus:ring-{{ $theme }}-500/20 outline-none transition-all placeholder:text-slate-400">
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div
                        class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 flex-shrink-0 bg-slate-50/50">
                        <button onclick="posApp.closeCustomerModal()"
                            class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-all">Cancel</button>
                        <button onclick="posApp.createCustomer()"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-{{ $theme }}-600 hover:bg-{{ $theme }}-700 rounded-xl shadow-md shadow-{{ $theme }}-200/50 active:scale-[0.97] transition-all">Register
                            & Select</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // ── Global product search for transfer modal (bypasses Alpine reactivity) ──
            let _transferSearchTimer = null;
            function posTransferSearch(val) {
                // sync to Alpine x-model if needed
                clearTimeout(_transferSearchTimer);
                const modal = document.getElementById('transfer-modal');
                if (!modal || !modal._x_dataStack) return;
                const alpine = modal._x_dataStack[0];
                if (!alpine) return;

                // Clear if too short
                if (!val || val.length < 2) {
                    alpine.productSearchResults = [];
                    if (val === '' || val.length < 2) { alpine.selectedProduct = null; }
                    return;
                }
                // If user changed text after selecting a product, clear selection
                if (alpine.selectedProduct && val !== alpine.selectedProduct.name) {
                    alpine.selectedProduct = null;
                }
                _transferSearchTimer = setTimeout(async () => {
                    try {
                        const res = await fetch(
                            posApp.apiBaseUrl + '/pos/products?query=' + encodeURIComponent(val),
                            { headers: { 'Authorization': 'Bearer ' + posApp.apiToken } }
                        );
                        const data = await res.json();
                        if (modal._x_dataStack && modal._x_dataStack[0]) {
                            modal._x_dataStack[0].productSearchResults = (data.data || []).slice(0, 8);
                        }
                    } catch (e) { console.error('product search error:', e); }
                }, 350);
            }

            function posTransferSelect(productJson) {
                const p = JSON.parse(decodeURIComponent(productJson));
                const modal = document.getElementById('transfer-modal');
                if (!modal || !modal._x_dataStack || !modal._x_dataStack[0]) return;
                const alpine = modal._x_dataStack[0];
                alpine.selectedProduct = p;
                alpine.productSearch = p.name;
                alpine.productSearchResults = [];
            }

            function posAppData() {
                return {
                    apiToken: '{{ $apiToken }}', apiBaseUrl: '{{ $apiBaseUrl }}', isOnline: navigator.onLine,
                    forcedOffline: localStorage.getItem('pos_forced_offline') === 'true',
                    cart: JSON.parse(localStorage.getItem('pos_cart') || '[]'),
                    cartCustomer: JSON.parse(localStorage.getItem('pos_customer') || 'null'),
                    currency: '{{ $outletSettings['currency_symbol'] ?? '$' }}',
                    taxRate: {{ $outletSettings['tax_rate'] ?? 0 }}, activeShiftId: null,

                    init() {
                        window.posApp = this; this.checkShiftStatus();
                        setInterval(() => { const el = document.getElementById('current-date-time'); if (el) el.innerText = new Date().toLocaleString(); }, 1000);
                    },

                    get cartSubtotal() { return this.cart.reduce((sum, item) => sum + ((item.unitPrice || item.price) * item.quantity), 0); },
                    get cartTax() { return this.cartSubtotal * (this.taxRate / 100); },
                    get cartTotal() { return this.cartSubtotal + this.cartTax; },

                    formatPrice(amount) {
                        const val = parseFloat(amount);
                        if (isNaN(val)) return this.currency + '0.00';
                        return this.currency + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    async checkShiftStatus() {
                        try {
                            const res = await fetch(`${this.apiBaseUrl}/pos/shift/current`, { headers: { 'Authorization': 'Bearer ' + this.apiToken } });
                            const data = await res.json(); this.updateShiftUI(data.data?.shift, data.data?.summary);
                        } catch (e) { console.error('Shift error:', e); }
                    },

                    updateShiftUI(shift, summary) {
                        const guard = document.getElementById('shift-guard-overlay');
                        const info = document.getElementById('shift-current-info');
                        if (shift) {
                            if (guard) guard.classList.add('hidden'); if (info) info.classList.remove('hidden');
                            this.activeShiftId = shift.id;
                            document.getElementById('current-shift-number-header').innerText = shift.shift_number;
                            document.getElementById('shift-total-sales-display').innerText = this.formatPrice(summary.total_sales);
                            document.getElementById('shift-expected-cash-display').innerText = this.formatPrice(parseFloat(shift.opening_cash) + parseFloat(summary.cash_total));
                            document.getElementById('shift-card-sales-display').innerText = this.formatPrice(summary.card_total);
                            if (summary?.user_breakdown) {
                                document.getElementById('shift-user-breakdown').innerHTML = summary.user_breakdown.map(u => `
                                                                                                                    <tr><td class="px-5 py-3.5 text-sm font-medium text-slate-700">${u.user_name}</td><td class="px-5 py-3.5 text-sm font-semibold text-right text-emerald-600">${this.formatPrice(u.cash_total)}</td><td class="px-5 py-3.5 text-sm font-semibold text-right text-slate-900">${this.formatPrice(u.total_sales)}</td></tr>
                                                                                                                `).join('');
                            }
                        } else { if (guard) guard.classList.remove('hidden'); if (info) info.classList.add('hidden'); }
                    },

                    async openShiftFromGuard() {
                        const amount = document.getElementById('guard-opening-cash').value;
                        if (!amount) return Swal.fire('Amount Required', 'Please enter opening float', 'warning');
                        try {
                            const res = await fetch(`{{ route('api.v1.pos.shift.open') }}`, {
                                method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + this.apiToken, 'Accept': 'application/json' },
                                body: JSON.stringify({ opening_cash: amount })
                            });
                            const data = await res.json(); if (data.success) location.reload(); else Swal.fire('Access Denied', data.message, 'error');
                        } catch (e) { console.error(e); }
                    },

                    async closeShift() {
                        const amount = document.getElementById('closing-cash').value;
                        if (!amount) return Swal.fire('Incomplete', 'Actual cash count is required', 'warning');
                        const { value: pin } = await Swal.fire({ title: 'Manager PIN', input: 'password', inputAttributes: { maxlength: 4, autocomplete: 'new-password', style: 'text-align: center' }, showCancelButton: true, confirmButtonText: 'Verify & Close' });
                        if (pin) {
                            try {
                                const res = await fetch(`${this.apiBaseUrl}/pos/shift/${this.activeShiftId}/close`, {
                                    method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + this.apiToken, 'Accept': 'application/json' },
                                    body: JSON.stringify({ closing_cash: amount, pin: pin })
                                });
                                const data = await res.json(); if (data.success) { await Swal.fire('Session Finalized', 'The shift has been successfully closed.', 'success'); location.reload(); }
                                else Swal.fire('Error', data.message, 'error');
                            } catch (e) { console.error(e); }
                        }
                    },

                    addToCart(p) { this.addItemToCart(p); },
                    addItemToCart(p) {
                        let id = p.cartItemId || (p.id + '-base');
                        let idx = this.cart.findIndex(i => i.cartItemId === id);
                        if (idx > -1) this.cart[idx].quantity++;
                        else this.cart.push({ cartItemId: id, id: p.id, name: p.name, price: parseFloat(p.price), unitPrice: parseFloat(p.price), quantity: 1 });
                        this.cart = [...this.cart]; this.saveCart();
                    },
                    removeItemFromCart(id) {
                        let idx = this.cart.findIndex(i => i.cartItemId === id);
                        if (idx > -1) { if (this.cart[idx].quantity > 1) this.cart[idx].quantity--; else this.cart.splice(idx, 1); }
                        this.cart = [...this.cart]; this.saveCart();
                    },
                    saveCart() { localStorage.setItem('pos_cart', JSON.stringify(this.cart)); window.dispatchEvent(new CustomEvent('cart-updated', { detail: { cart: this.cart } })); },
                    deleteFromCart(id) { this.cart = this.cart.filter(i => i.cartItemId !== id); this.saveCart(); },
                    redirectToCheckout() { if (this.cart.length > 0) window.location.href = '{{ route('pos.checkout') }}'; },
                    clearCart() {
                        Swal.fire({
                            title: 'Clear Order?',
                            text: 'All items in the current order will be removed.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e11d48',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Yes, Clear All',
                            cancelButtonText: 'Cancel',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.cart = [];
                                this.saveCart();
                            }
                        });
                    },
                    openHistory() { document.getElementById('history-modal').classList.remove('hidden'); this.loadHistory(); },
                    closeHistory() { document.getElementById('history-modal').classList.add('hidden'); },
                    async loadHistory() {
                        const body = document.getElementById('history-list-body');
                        body.innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs"><svg class=\"w-5 h-5 animate-spin mx-auto mb-2 text-slate-300\" fill=\"none\" viewBox=\"0 0 24 24\"><circle class=\"opacity-25\" cx=\"12\" cy=\"12\" r=\"10\" stroke=\"currentColor\" stroke-width=\"4\"/><path class=\"opacity-75\" fill=\"currentColor\" d=\"M4 12a8 8 0 018-8v8z\"/></svg>Loading...</td></tr>';
                        try {
                            const res = await fetch(`${this.apiBaseUrl}/pos/history`, { headers: { 'Authorization': 'Bearer ' + this.apiToken } });
                            const data = await res.json();
                            if (data.success && data.data && data.data.length > 0) {
                                body.innerHTML = data.data.map(s => {
                                    const statusClass = s.status === 'void' ? 'bg-rose-50 text-rose-500' : 'bg-emerald-50 text-emerald-600';
                                    const customerName = s.customer ? s.customer.name : 'Walk-in';
                                    const cashierName = s.user ? s.user.name : '-';
                                    const date = new Date(s.created_at);
                                    const dateStr = date.toLocaleDateString('en-MY', { day: '2-digit', month: 'short' });
                                    const timeStr = date.toLocaleTimeString('en-MY', { hour: '2-digit', minute: '2-digit' });
                                    return `<tr onclick="posApp.viewReceipt(${JSON.stringify(s).replace(/"/g, '&quot;')})"
                                                                                                class="hover:bg-indigo-50/50 cursor-pointer transition-colors border-b border-slate-50">
                                                                                                <td class="px-4 py-3 font-bold text-slate-800 text-xs">#${s.id}</td>
                                                                                                <td class="px-4 py-3 text-xs text-slate-500">${dateStr}<br><span class="text-slate-400">${timeStr}</span></td>
                                                                                                <td class="px-4 py-3 text-xs font-medium text-slate-700">${customerName}</td>
                                                                                                <td class="px-4 py-3 text-xs text-slate-500">${cashierName}</td>
                                                                                                <td class="px-4 py-3"><span class="text-[10px] font-bold px-2 py-1 rounded-lg ${statusClass}">${s.status}</span></td>
                                                                                                <td class="px-4 py-3 text-right text-sm font-bold text-slate-900">${this.formatPrice(s.total_amount)}</td>
                                                                                            </tr>`;
                                }).join('');
                            } else {
                                body.innerHTML = '<tr><td colspan="6" class="px-4 py-12 text-center text-slate-400 text-sm">No transactions found.</td></tr>';
                            }
                        } catch (e) {
                            console.error(e);
                            body.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-rose-400 text-sm">Failed to load history.</td></tr>';
                        }
                    },
                    viewReceipt(sale) {
                        this._selectedSaleId = sale.id;
                        const isVoid = sale.status === 'void';
                        const items = (sale.sale_items || []).map(i =>
                            `<div class="flex justify-between items-start text-xs py-1.5 border-b border-slate-100 last:border-0">
                                                                                        <div><p class="font-medium text-slate-800">${i.product ? i.product.name : ('Item #' + i.product_id)}</p>
                                                                                        <p class="text-slate-400">${i.quantity} &times; ${this.formatPrice(i.unit_price)}</p></div>
                                                                                        <span class="font-semibold text-slate-900 ml-2">${this.formatPrice(i.subtotal)}</span>
                                                                                    </div>`
                        ).join('');
                        const customerName = sale.customer ? sale.customer.name : 'Walk-in Customer';
                        const cashierName = sale.user ? sale.user.name : '-';
                        const date = new Date(sale.created_at).toLocaleString('en-MY');
                        const discount = parseFloat(sale.discount_amount || 0);
                        const tax = parseFloat(sale.tax_amount || 0);
                        document.getElementById('history-receipt-body').innerHTML = `
                                                                                    <div class="w-full text-left space-y-4">
                                                                                        <div class="text-center pb-3 border-b border-dashed border-slate-200">
                                                                                            <p class="text-xs text-slate-400">${date}</p>
                                                                                            <p class="text-lg font-bold text-slate-900 mt-1">#${sale.id}</p>
                                                                                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-lg ${isVoid ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600'}">${sale.status.toUpperCase()}</span>
                                                                                        </div>
                                                                                        <div class="grid grid-cols-2 gap-2 text-xs">
                                                                                            <div><p class="text-slate-400">Customer</p><p class="font-semibold text-slate-800">${customerName}</p></div>
                                                                                            <div><p class="text-slate-400">Cashier</p><p class="font-semibold text-slate-800">${cashierName}</p></div>
                                                                                        </div>
                                                                                        <div class="space-y-0">${items || '<p class="text-xs text-slate-400 text-center py-4">No items</p>'}</div>
                                                                                        <div class="pt-2 border-t border-dashed border-slate-200 space-y-1.5 text-xs">
                                                                                            <div class="flex justify-between text-slate-500"><span>Subtotal</span><span>${this.formatPrice(sale.subtotal_amount || sale.total_amount)}</span></div>
                                                                                            ${discount > 0 ? `<div class="flex justify-between text-rose-500"><span>Discount</span><span>-${this.formatPrice(discount)}</span></div>` : ''}
                                                                                            ${tax > 0 ? `<div class="flex justify-between text-slate-500"><span>Tax</span><span>${this.formatPrice(tax)}</span></div>` : ''}
                                                                                            <div class="flex justify-between font-bold text-slate-900 text-sm pt-1 border-t border-slate-200"><span>Total</span><span>${this.formatPrice(sale.total_amount)}</span></div>
                                                                                        </div>
                                                                                    </div>`;
                        const footer = document.getElementById('history-receipt-footer');
                        if (isVoid) {
                            footer.classList.add('hidden');
                        } else {
                            footer.classList.remove('hidden');
                        }
                    },
                    async voidSale(id) {
                        if (!id) return;
                        const { value: pin } = await Swal.fire({ title: 'Manager Authorization', text: 'Enter manager PIN to void this order', input: 'password', inputAttributes: { maxlength: 4, style: 'text-align:center;letter-spacing:0.3em' }, showCancelButton: true, confirmButtonText: 'Void Order', confirmButtonColor: '#e11d48' });
                        if (pin) {
                            try {
                                const res = await fetch(`${this.apiBaseUrl}/pos/sales/${id}/void`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Authorization': 'Bearer ' + this.apiToken }, body: JSON.stringify({ pin: pin }) });
                                const data = await res.json();
                                if (data.success) {
                                    Swal.fire('Order Voided', `Sale #${id} has been voided.`, 'success');
                                    document.getElementById('history-receipt-footer').classList.add('hidden');
                                    this.loadHistory();
                                } else { Swal.fire('Error', data.message, 'error'); }
                            } catch (e) { Swal.fire('Error', 'Could not void the order.', 'error'); }
                        }
                    },
                    openShiftModal() { document.getElementById('shift-modal').classList.remove('hidden'); },
                    closeShiftModal() { document.getElementById('shift-modal').classList.add('hidden'); },
                    openTransferModal() {
                        const el = document.getElementById('transfer-modal');
                        el.classList.remove('hidden');
                        setTimeout(() => {
                            if (el._x_dataStack && el._x_dataStack[0]) {
                                const a = el._x_dataStack[0];
                                if (a.loadStock) a.loadStock();
                                if (a.loadOutlets) a.loadOutlets();
                            }
                        }, 80);
                    },
                    closeTransferModal() { document.getElementById('transfer-modal').classList.add('hidden'); },
                    openCustomerModal() { document.getElementById('customer-modal').classList.remove('hidden'); },
                    closeCustomerModal() { document.getElementById('customer-modal').classList.add('hidden'); },
                    async searchCustomer() {
                        const q = document.getElementById('customer-search-input').value; if (q.length < 2) return;
                        const res = await fetch(`${this.apiBaseUrl}/pos/customers?query=${q}`, { headers: { 'Authorization': 'Bearer ' + this.apiToken } });
                        const data = await res.json(); const custs = data.data || data;
                        document.getElementById('customer-search-results').innerHTML = custs.map(c => `
                                                                                                            <div onclick="posApp.selectCustomer(${JSON.stringify(c).replace(/"/g, '&quot;')})" class="p-4 bg-slate-50 border border-slate-100 rounded-xl hover:bg-white hover:border-{{ $theme }}-200 hover:shadow-sm cursor-pointer transition-all"><p class="font-semibold text-sm text-slate-800">${c.name}</p><p class="text-xs text-slate-400 mt-0.5">${c.phone}</p></div>
                                                                                                        `).join('');
                    },
                    selectCustomer(c) { this.cartCustomer = c; localStorage.setItem('pos_customer', JSON.stringify(c)); this.closeCustomerModal(); },
                    async createCustomer() {
                        const name = document.getElementById('new-customer-name').value.trim();
                        const phone = document.getElementById('new-customer-phone').value.trim();
                        if (!name) return Swal.fire('Missing Details', 'Please enter a customer name', 'warning');

                        try {
                            const res = await fetch(`${this.apiBaseUrl}/pos/customers`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + this.apiToken
                                },
                                body: JSON.stringify({ name, phone })
                            });
                            const data = await res.json();
                            if (res.ok && data.success) {
                                this.selectCustomer(data.customer || data.data);
                                Swal.fire('Success', 'Customer registered successfully', 'success');
                                document.getElementById('new-customer-name').value = '';
                                document.getElementById('new-customer-phone').value = '';
                            } else {
                                Swal.fire('Error', data.message || 'Could not register customer', 'error');
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire('Error', 'Network error occurred', 'error');
                        }
                    },
                    lockScreen() { window.location.href = '{{ route('pos.lock') }}'; },
                    toggleOfflineMode() { this.forcedOffline = !this.forcedOffline; localStorage.setItem('pos_forced_offline', this.forcedOffline); },
                };
            }
            function confirmLogout() { Swal.fire({ title: 'Logout?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#ef4444', confirmButtonText: 'Logout' }).then(r => { if (r.isConfirmed) document.getElementById('pos-logout-form').submit(); }); }
        </script>
    @endpush
@endsection