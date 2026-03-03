@extends('layouts.admin')

@section('title', 'Create Transfer')
@section('header', 'Create Inventory Transfer')

@section('content')
    <div class="w-full">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-arrow-left-right text-indigo-600"></i>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Create Transfer</h3>
                    <p class="text-xs text-gray-400">Transfer inventory between outlets</p>
                </div>
            </div>
            <form action="{{ route('admin.transfers.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    @if ($errors->any())
                        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Outlet</label>
                            <select name="from_outlet" id="from_outlet" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                                <option value="">Select Source Outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Outlet</label>
                            <select name="to_outlet" id="to_outlet" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                                <option value="">Select Destination Outlet</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Items</label>
                            <div id="transfer-items" class="border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto">
                                <p class="text-center text-gray-400 py-4">Select source outlet to view available products
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 items-center">
                    <a href="{{ route('admin.transfers.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
                        Create Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('from_outlet').addEventListener('change', async function () {
                const outletId = this.value;
                const toOutlet = document.getElementById('to_outlet');
                const itemsContainer = document.getElementById('transfer-items');

                if (!outletId) {
                    itemsContainer.innerHTML = '<p class="text-center text-gray-400 py-4">Select source outlet to view available products</p>';
                    return;
                }

                // Disable selected outlet in destination
                Array.from(toOutlet.options).forEach(option => {
                    if (option.value === outletId) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });

                // Fetch products from selected outlet
                itemsContainer.innerHTML = '<p class="text-center text-gray-400 py-4">Loading products...</p>';

                try {
                    const response = await fetch(`/admin/inventory/by-outlet/${outletId}`);
                    const products = await response.json();

                    if (products.length === 0) {
                        itemsContainer.innerHTML = '<p class="text-center text-gray-400 py-4">No products available in this outlet</p>';
                        return;
                    }

                    let html = '';
                    products.forEach(product => {
                        html += `
                                <div class="flex items-center gap-4 py-2 border-b border-gray-100 last:border-0">
                                    <input type="checkbox" name="items[][product_id]" value="${product.id}" class="product-checkbox rounded text-indigo-600">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">${product.name}</p>
                                        <p class="text-sm text-gray-500">Stock: ${product.stock_level}</p>
                                    </div>
                                    <input type="number" name="items[][quantity]" min="1" max="${product.stock_level}" placeholder="Qty" 
                                        class="w-20 px-2 py-1 border border-gray-300 rounded text-center" disabled>
                                </div>
                            `;
                    });
                    itemsContainer.innerHTML = html;

                    // Add event listeners for checkboxes
                    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', function () {
                            const qtyInput = this.closest('div').querySelector('input[type="number"]');
                            qtyInput.disabled = !this.checked;
                            if (!this.checked) qtyInput.value = '';
                        });
                    });
                } catch (e) {
                    itemsContainer.innerHTML = '<p class="text-center text-red-400 py-4">Error loading products</p>';
                }
            });
        </script>
    @endpush
@endsection