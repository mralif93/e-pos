@extends('layouts.admin')

@section('title', 'Inventory')
@section('header', 'Inventory Management')

@section('content')
    <!-- Filter Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="hgi-stroke text-[20px] hgi-settings-02 text-indigo-600"></i>
            <h3 class="text-md font-semibold text-gray-800">Search & Filter</h3>
        </div>
        <form method="GET">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <!-- Column 1: Search -->
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="hgi-stroke hgi-search-01 text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search products by name or SKU..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[160px]">
                            <select name="outlet_id"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer hover:bg-gray-50">
                                <option value="">Outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.inventory.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
                                <i class="hgi-stroke hgi-setup-01 text-[20px]"></i>
                                Reset
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
                                <i class="hgi-stroke hgi-filter text-[20px]"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Adjust Button -->
    <div class="flex justify-end mb-4 mt-2">
        <button onclick="openAdjustModal()" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-settings-02"></i>
            Adjust Stock
        </button>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-warehouse text-indigo-600"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Inventory List</h3>
                    <p class="text-sm text-gray-500">Track stock levels across outlets</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Show</span>
                <form method="GET">
                    @foreach(request()->except('per_page') as $key => $value)
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endforeach
                    <div class="relative">
                        <select name="per_page"
                            class="appearance-none pl-3 pr-8 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer text-gray-700 font-medium"
                            onchange="this.form.submit()">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </form>
                <span class="text-sm text-gray-500">entries</span>
            </div>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">{{ $item->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">-</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $item->stock_level ?? 0 }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $item->low_stock_threshold ?? 10 }}</td>
                        <td class="px-6 py-4">
                            @if(($item->stock_level ?? 0) <= 0)
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Out of Stock</span>
                            @elseif(($item->stock_level ?? 0) <= ($item->low_stock_threshold ?? 10))
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">Low
                                    Stock</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">In Stock</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button onclick="adjustStock({{ $item->id }})"
                                    class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                    <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No inventory items found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($products->total() <= $products->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $products->firstItem() ?? 0 }} &ndash; {{ $products->lastItem() ?? 0 }} of
                    {{ $products->total() }} results
                </p>
            @else
                {{ $products->links() }}
            @endif
        </div>
    </div>

    <!-- Adjust Stock Modal -->
    <div id="adjust-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeAdjustModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Adjust Stock</h3>
                    <button onclick="closeAdjustModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="hgi-stroke text-[20px] hgi-cancel-01"></i>
                    </button>
                </div>
                <form id="adjust-form" class="p-6 space-y-4">
                    @csrf
                    <!-- If opened from top menu, user must pick product. If opened from row, this will be hidden & populated -->
                    <div id="product-select-container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Select Product</label>
                        <select id="inventory_id"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                            <option value="">-- Choose Product --</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adjustment Type</label>
                    <select id="adjust_type" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        <option value="add">Add Stock</option>
                        <option value="remove">Remove Stock</option>
                        <option value="set">Set Stock</option>
                    </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                <input type="number" id="adjust_quantity" required min="0"
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeAdjustModal()"
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
            </form>
        </div>
    </div>
    </div>

    @push('scripts')
        <script>
            function openAdjustModal() {
                document.getElementById('adjust-form').reset();

                // Show product select since we don't have a specific ID
                const productSelect = document.getElementById('inventory_id');
                const container = document.getElementById('product-select-container');
                productSelect.value = '';
                productSelect.required = true;
                container.style.display = 'block';

                document.getElementById('adjust-modal').classList.remove('hidden');
            }

            function closeAdjustModal() {
                document.getElementById('adjust-modal').classList.add('hidden');
            }

            function adjustStock(id) {
                document.getElementById('adjust-form').reset();

                // Hide and pre-fill product select since clicked from specific row
                const productSelect = document.getElementById('inventory_id');
                const container = document.getElementById('product-select-container');
                productSelect.value = id;
                productSelect.required = false; // Hidden, so don't require user interaction
                container.style.display = 'none';

                document.getElementById('adjust-modal').classList.remove('hidden');
            }

            document.getElementById('adjust-form').addEventListener('submit', async function (e) {
                e.preventDefault();
                const inventoryId = document.getElementById('inventory_id').value;
                if (!inventoryId) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Please select a product first.' });
                    return;
                }

                const data = {
                    type: document.getElementById('adjust_type').value,
                    quantity: parseInt(document.getElementById('adjust_quantity').value),
                    reason: 'Manual adjustment',
                };
                try {
                    const response = await fetch(`/admin/inventory/adjust/${inventoryId}`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify(data)
                    });
                    if (response.ok) {
                        Swal.fire({ icon: 'success', title: 'Success', timer: 1500, showConfirmButton: false });
                        closeAdjustModal();
                        location.reload();
                    }
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to adjust stock' });
                }
            });
        </script>
    @endpush
@endsection