@extends('layouts.admin')

@section('title', 'Products')
@section('header', 'Product Management')

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
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex-1 min-w-[130px]">
                            <select name="outlet_id"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer hover:bg-gray-50">
                                <option value="">All Outlets</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[130px]">
                            <select name="category_id"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer hover:bg-gray-50">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 min-w-[110px]">
                            <select name="status"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer hover:bg-gray-50">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
                                <i class="hgi-stroke text-[20px] hgi-setup-01"></i>
                                Reset
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
                                <i class="hgi-stroke text-[20px] hgi-filter"></i>
                                Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Create Button -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-add-01"></i>
            Add Product
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-package text-indigo-600"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Products List</h3>
                    <p class="text-sm text-gray-500">Manage your product inventory</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlets</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Base Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center shrink-0">
                                    <i class="hgi-stroke text-[16px] hgi-package text-indigo-600"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->sku }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400 font-mono">{{ $product->sku }}</td>
                        <td class="px-6 py-4">
                            @if($product->category)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700">
                                    {{ $product->category->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if(request('outlet_id'))
                                {{-- Filtered by outlet: show ONLY that outlet's stock & price --}}
                                @php $op = $product->prices->where('outlet_id', request('outlet_id'))->first(); @endphp
                                @if($op)
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full {{ ($op->stock_level ?? 0) > 10 ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                            <span class="text-xs font-semibold text-gray-700">Stock: {{ $op->stock_level ?? 0 }}</span>
                                        </div>
                                        <p class="text-[11px] text-gray-400">RM {{ number_format($op->price, 2) }}</p>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            @else
                                {{-- No filter: show count pill + short list --}}
                                @php $count = $product->prices->count(); @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full text-[11px] font-medium max-w-full truncate">
                                    <i class="hgi-stroke hgi-store-01 text-[12px] shrink-0"></i>
                                    <span class="truncate">
                                        @if($count === 1)
                                            {{ $product->prices->first()->outlet->name ?? '1 outlet' }}
                                        @else
                                            {{ $count }} outlets
                                        @endif
                                    </span>
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-800">RM {{ number_format($product->price, 2) }}</td>
                        <td class="px-6 py-4">
                            @if($product->is_active)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.products.show', $product->id) }}"
                                    class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200">
                                    <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product->id) }}"
                                    class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                    <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                </a>
                                <button onclick="deleteProduct({{ $product->id }})"
                                    class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 hover:bg-red-200">
                                    <i class="hgi-stroke text-[20px] hgi-delete-01 text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No products found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($products->total() <= $products->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $products->firstItem() ?? 0 }} &ndash; {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} results
                </p>
            @else
                {{ $products->links() }}
            @endif
        </div>
    </div>

    <!-- Product Modal -->
    <div id="product-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeProductModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="text-lg font-semibold" id="modal-title">Add Product</h3>
                    <button onclick="closeProductModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="hgi-stroke text-[20px] hgi-cancel-01"></i>
                    </button>
                </div>
                <form id="product-form" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" id="product_id">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                        <input type="text" id="product_name" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" id="product_sku" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <select id="product_category" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (RM)</label>
                            <input type="number" id="product_price" step="0.01" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Level</label>
                            <input type="number" id="product_stock" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="closeProductModal()"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm flex-1">Cancel</button>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200 flex-1">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let products = @json(\Illuminate\Support\Arr::keyBy($products->items(), 'id'));

            function openProductModal() {
                document.getElementById('modal-title').innerText = 'Add Product';
                document.getElementById('product-form').reset();
                document.getElementById('product_id').value = '';
                document.getElementById('product-modal').classList.remove('hidden');
            }

            function closeProductModal() {
                document.getElementById('product-modal').classList.add('hidden');
            }

            function editProduct(id) {
                const product = products[id];
                if (product) {
                    document.getElementById('modal-title').innerText = 'Edit Product';
                    document.getElementById('product_id').value = product.id;
                    document.getElementById('product_name').value = product.name;
                    document.getElementById('product_sku').value = product.sku;
                    document.getElementById('product_category').value = product.category_id;
                    document.getElementById('product_price').value = product.price;
                    document.getElementById('product_stock').value = product.stock_level;
                    document.getElementById('product-modal').classList.remove('hidden');
                }
            }

            document.getElementById('product-form').addEventListener('submit', async function (e) {
                e.preventDefault();

                const id = document.getElementById('product_id').value;
                const url = id ? `/admin/products/${id}` : '/admin/products';
                const method = id ? 'PUT' : 'POST';

                const data = {
                    name: document.getElementById('product_name').value,
                    sku: document.getElementById('product_sku').value,
                    category_id: document.getElementById('product_category').value,
                    price: document.getElementById('product_price').value,
                    stock_level: document.getElementById('product_stock').value,
                };

                try {
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });

                    if (response.ok) {
                        Swal.fire({ icon: 'success', title: 'Success', timer: 1500, showConfirmButton: false });
                        closeProductModal();
                        location.reload();
                    } else {
                        const err = await response.json();
                        Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Failed to save product' });
                    }
                } catch (e) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save product' });
                }
            });

            async function deleteProduct(id) {
                const { isConfirmed } = await Swal.fire({
                    title: 'Delete Product',
                    text: 'Are you sure you want to delete this product?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                });

                if (isConfirmed) {
                    try {
                        const response = await fetch(`/admin/products/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });

                        if (response.ok) {
                            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                            location.reload();
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete product' });
                    }
                }
            }
        </script>
    @endpush
@endsection