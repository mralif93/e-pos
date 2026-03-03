<div class="space-y-5">
    {{-- Category Tabs --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 custom-scrollbar snap-x" x-data="{
        categories: [],
        activeId: null,
        async fetchCategories() {
            try {
                const response = await fetch('{{ route('api.v1.pos.categories') }}', {
                    headers: { 'Authorization': 'Bearer ' + window.apiToken, 'Accept': 'application/json' }
                });
                const data = await response.json();
                this.categories = data.data || [];
            } catch (e) { console.error('Error:', e); }
        },
        setCategory(id) {
            this.activeId = id;
            $wire.setCategory(id);
        }
    }" x-init="fetchCategories()">
        <button @click="setCategory(null)"
            :class="activeId === null ? 'bg-{{ $theme }}-600 text-white shadow-lg shadow-{{ $theme }}-200/50' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 hover:shadow-sm'"
            class="snap-start flex-shrink-0 px-5 py-2.5 rounded-xl text-xs font-bold tracking-wide transition-all duration-200">
            All Items
        </button>

        <template x-for="category in categories" :key="category.id">
            <button @click="setCategory(category.id)"
                :class="activeId === category.id ? 'bg-{{ $theme }}-600 text-white shadow-lg shadow-{{ $theme }}-200/50' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200 hover:shadow-sm'"
                class="snap-start flex-shrink-0 px-5 py-2.5 rounded-xl text-xs font-bold tracking-wide transition-all duration-200"
                x-text="category.name">
            </button>
        </template>
    </div>

    {{-- Search Bar --}}
    <div class="relative" x-data="{ hasSearch: false }">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search products..."
            class="w-full bg-slate-50 border border-slate-200 rounded-2xl py-3.5 pl-12 pr-10 text-sm font-medium text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-{{ $theme }}-500/20 focus:border-{{ $theme }}-400 transition-all"
            @input="hasSearch = $event.target.value.length > 0"
            @keydown.escape="$wire.set('search', ''); $el.value = ''; hasSearch = false">
        <!-- Clear Button -->
        <button type="button" x-show="hasSearch" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-75"
            @click="$wire.set('search', ''); $el.previousElementSibling.value = ''; hasSearch = false"
            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors">
            <span
                class="w-5 h-5 rounded-full bg-slate-200 hover:bg-slate-300 flex items-center justify-center transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
        </button>
    </div>


    {{-- Product Grid --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @forelse($products as $product)
            <div wire:key="product-{{ $product->id }}" wire:click="selectProduct({{ $product->id }})" x-data="{ 
                            count: 0,
                            updateCount(cart) {
                                this.count = cart.filter(i => i.id == {{ $product->id }}).reduce((sum, i) => sum + i.quantity, 0);
                            }
                        }" x-init="updateCount(JSON.parse(localStorage.getItem('pos_cart') || '[]'))"
                @cart-updated.window="updateCount($event.detail.cart)"
                class="group relative bg-white rounded-2xl border border-slate-100 overflow-hidden cursor-pointer hover:shadow-lg hover:shadow-slate-200/50 hover:border-{{ $theme }}-200 transition-all duration-300 active:scale-[0.97]">

                {{-- Item Counter Badge --}}
                <div x-show="count > 0" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-75" x-transition:enter-end="opacity-100 scale-100"
                    class="absolute top-2.5 left-2.5 z-10">
                    <span
                        class="flex items-center justify-center min-w-[24px] h-6 px-1.5 bg-{{ $theme }}-600 text-white text-[10px] font-bold rounded-full shadow-lg shadow-{{ $theme }}-300/50 ring-2 ring-white"
                        x-text="count"></span>
                </div>

                {{-- Product Image Area --}}
                <div class="aspect-[4/3] w-full bg-slate-50 relative overflow-hidden">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="w-full h-full flex items-center justify-center"
                            style="background-color: {{ $product->fallback_color }}; color: {{ $product->fallback_text_color }};">
                            <span
                                class="text-lg font-bold tracking-wider select-none opacity-70">{{ $product->initials }}</span>
                        </div>
                    @endif
                    {{-- Stock Badge --}}
                    <div class="absolute top-2.5 right-2.5">
                        <span
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-[10px] font-semibold backdrop-blur-md {{ $product->stock_level <= 5 ? 'bg-rose-500/10 text-rose-600 ring-1 ring-rose-500/20' : 'bg-emerald-500/10 text-emerald-600 ring-1 ring-emerald-500/20' }}">
                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            {{ $product->stock_level }}
                        </span>
                    </div>
                </div>

                {{-- Product Info --}}
                <div class="p-3.5">
                    <h3
                        class="text-[13px] font-semibold text-slate-800 group-hover:text-{{ $theme }}-600 transition-colors line-clamp-1 leading-snug mb-0.5">
                        {{ $product->name }}
                    </h3>
                    <p class="text-[10px] text-slate-400 font-medium mb-3">{{ $product->sku }}</p>

                    <div class="flex items-center justify-between">
                        <span
                            class="text-base font-bold text-slate-900">{{ $outletSettings['currency_symbol'] ?? 'RM' }}{{ number_format($product->price, 2) }}</span>
                        <div
                            class="w-8 h-8 rounded-xl bg-{{ $theme }}-50 text-{{ $theme }}-500 flex items-center justify-center group-hover:bg-{{ $theme }}-600 group-hover:text-white transition-all duration-200 group-hover:shadow-lg group-hover:shadow-{{ $theme }}-200/50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <p class="text-sm font-medium text-slate-400">No products found</p>
            </div>
        @endforelse
    </div>
</div>