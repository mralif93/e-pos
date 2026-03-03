@extends('layouts.admin')

@section('title', $product->name)
@section('header', 'Product Details')

@section('content')
    {{-- Back & Actions --}}
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.products.index') }}"
            class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
            <i class="hgi-stroke hgi-arrow-left-01 text-[18px]"></i> Back to Products
        </a>
        <a href="{{ route('admin.products.edit', $product->id) }}"
            class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
            <i class="hgi-stroke hgi-edit-02 text-[18px]"></i> Edit
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Product Info Card --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $product->name }}</h2>
                        <p class="text-sm text-gray-500 mt-0.5">SKU: <span
                                class="font-mono text-gray-700">{{ $product->sku ?? '—' }}</span></p>
                    </div>
                    <span
                        class="px-3 py-1 text-xs font-semibold rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Category</p>
                        <p class="font-medium text-gray-900">{{ $product->category->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Barcode</p>
                        <p class="font-mono text-gray-700">{{ $product->barcode ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Base Price</p>
                        <p class="font-semibold text-gray-900">RM {{ number_format($product->price, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Cost Price</p>
                        <p class="font-medium text-gray-700">RM {{ number_format($product->cost ?? 0, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Stock Level</p>
                        <p class="font-semibold {{ $product->stock_level <= 5 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $product->stock_level }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Total Sold</p>
                        <p class="font-semibold text-indigo-700">{{ number_format($totalSold) }} units</p>
                    </div>
                </div>
                @if($product->description)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Description</p>
                        <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $product->description }}</p>
                    </div>
                @endif
            </div>

            {{-- Outlet Pricing --}}
            @if($product->prices->count())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i class="hgi-stroke hgi-tag-01 text-[20px] text-indigo-600"></i>
                        <h3 class="text-md font-semibold text-gray-800">Outlet Pricing</h3>
                    </div>
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Compare Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($product->prices as $price)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $price->outlet->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">RM {{ number_format($price->price, 2) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500">
                                        {{ $price->compare_price ? 'RM ' . number_format($price->compare_price, 2) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Recent Sales --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-invoice-01 text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Recent Sales</h3>
                </div>
                @if($recentSales->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-gray-400">No sales recorded yet</p>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentSales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $sale->outlet_name }}</td>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $sale->quantity }}</td>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-800">RM
                                        {{ number_format($sale->price * $sale->quantity, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Right: Stock & Variants --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-warehouse text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Stock Overview</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Current Stock</span>
                        <span
                            class="font-bold text-lg {{ $product->stock_level <= 5 ? 'text-red-600' : 'text-gray-900' }}">{{ $product->stock_level }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Units Sold</span>
                        <span class="font-bold text-lg text-indigo-700">{{ number_format($totalSold) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-sm text-gray-500">Has Variants</span>
                        <span class="text-sm font-medium text-gray-900">{{ $product->has_variants ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>

            @if($product->variants->count())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                        <i class="hgi-stroke hgi-layers-01 text-[20px] text-indigo-600"></i>
                        <h3 class="text-md font-semibold text-gray-800">Variants</h3>
                    </div>
                    <div class="p-6 space-y-2">
                        @foreach($product->variants as $variant)
                            <div class="flex justify-between items-center py-2 border-b border-gray-50 last:border-0">
                                <span class="text-sm text-gray-700">{{ $variant->name }}</span>
                                <span class="text-sm text-gray-500 font-mono">{{ $variant->sku ?? '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-information-circle text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Details</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Created</span>
                        <span class="text-gray-700">{{ $product->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Last Updated</span>
                        <span class="text-gray-700">{{ $product->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection