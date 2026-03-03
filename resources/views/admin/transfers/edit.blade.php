@extends('layouts.admin')

@section('title', 'Edit Transfer')
@section('header', 'Edit Inventory Transfer: ' . $transfer->transfer_number)

@section('content')
    <div class="w-full">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-edit-02 text-indigo-600"></i>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Edit Transfer</h3>
                    <p class="text-xs text-gray-400">Modify pending inventory transfer details</p>
                </div>
            </div>
            
            <form action="{{ route('admin.transfers.update', $transfer->id) }}" method="POST">
                @csrf
                @method('PUT')
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
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none bg-gray-50 text-gray-500" readonly>
                                @foreach($outlets as $outlet)
                                    @if($outlet->id == $transfer->from_outlet_id)
                                        <option value="{{ $outlet->id }}" selected>{{ $outlet->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <span class="text-xs text-gray-400 mt-1 block">Source outlet cannot be changed. Create a new transfer instead.</span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Outlet</label>
                            <select name="to_outlet" id="to_outlet" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                                <option value="">Select Destination Outlet</option>
                                @foreach($outlets as $outlet)
                                    @if($outlet->id != $transfer->from_outlet_id)
                                        <option value="{{ $outlet->id }}" {{ $transfer->to_outlet_id == $outlet->id ? 'selected' : '' }}>
                                            {{ $outlet->name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Transfer Items</label>
                            <div id="transfer-items" class="border border-gray-200 rounded-lg p-4 max-h-64 overflow-y-auto">
                                <p class="text-center text-gray-400 py-4">Loading products...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 items-center">
                    <a href="{{ route('admin.transfers.index') }}"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
                        Update Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', async function () {
                const outletId = document.getElementById('from_outlet').value;
                const itemsContainer = document.getElementById('transfer-items');
                
                // Pre-populate existing items from server
                const existingItems = @json($transfer->items->map(function($i) {
                    return [
                        'id' => $i->product_id,
                        'qty' => $i->quantity
                    ];
                }));

                try {
                    const response = await fetch(`/admin/inventory/by-outlet/${outletId}`);
                    const products = await response.json();

                    if (products.length === 0) {
                        itemsContainer.innerHTML = '<p class="text-center text-gray-400 py-4">No products available in this outlet</p>';
                        return;
                    }

                    let html = '';
                    products.forEach(product => {
                        const existingEntry = existingItems.find(item => item.id == product.id);
                        const isChecked = existingEntry ? 'checked' : '';
                        const qtyValue = existingEntry ? existingEntry.qty : '';

                        html += `
                        <div class="flex items-center gap-4 py-2 border-b border-gray-100 last:border-0">
                            <input type="checkbox" name="items[][product_id]" value="${product.id}" class="product-checkbox rounded text-indigo-600" ${isChecked}>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">${product.name}</p>
                                <p class="text-xs text-gray-500">Available: ${product.stock_level ?? 0}</p>
                            </div>
                            <input type="number" name="items[][quantity]" min="1" max="${product.stock_level ?? 9999}" 
                                placeholder="Qty" value="${qtyValue}"
                                class="w-24 px-3 py-1 border border-gray-300 rounded focus:ring-2 focus:ring-indigo-500 qty-input" ${!isChecked ? 'disabled' : ''}>
                        </div>
                        `;
                    });

                    itemsContainer.innerHTML = html;

                    // Add event listeners to new checkboxes
                    document.querySelectorAll('.product-checkbox').forEach(cb => {
                        cb.addEventListener('change', function() {
                            const qtyInput = this.closest('div').querySelector('.qty-input');
                            qtyInput.disabled = !this.checked;
                            if(this.checked && !qtyInput.value) qtyInput.value = 1;
                        });
                    });

                } catch (error) {
                    itemsContainer.innerHTML = '<p class="text-center text-red-500 py-4">Failed to load products</p>';
                }
            });
        </script>
    @endpush
@endsection
