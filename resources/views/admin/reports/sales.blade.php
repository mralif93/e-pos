@extends('layouts.admin')

@section('title', 'Sales Reports')
@section('header', 'Sales Reports')

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[20px] hgi-cash-01 text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Total Sales</p>
                    <p class="text-xl font-bold text-gray-800">RM {{ number_format($totalSales, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[20px] hgi-invoice-01 text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Transactions</p>
                    <p class="text-xl font-bold text-gray-800">{{ number_format($transactions) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[20px] hgi-chart-increase text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Average Sale</p>
                    <p class="text-xl font-bold text-gray-800">RM
                        {{ number_format($transactions > 0 ? $totalSales / $transactions : 0, 2) }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[20px] hgi-package text-orange-600"></i>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500">Items Sold</p>
                    <p class="text-xl font-bold text-gray-800">{{ number_format($itemsSold) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="hgi-stroke text-[20px] hgi-settings-02 text-indigo-600"></i>
            <h3 class="text-md font-semibold text-gray-800">Search & Filter</h3>
        </div>
        <form method="GET">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <!-- Column 1: Date Range -->
                    <div class="flex items-center gap-4">
                        <input type="date" name="date_from"
                            value="{{ request('date_from', now()->startOfMonth()->format('Y-m-d')) }}" title="Date From"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        <span class="text-gray-400">-</span>
                        <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}"
                            title="Date To"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <select name="outlet_id"
                            class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">All Outlets</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.sales') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
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

    <!-- Sales Table -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-computer text-indigo-600"></i>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Sales Records</h3>
                    <p class="text-xs text-gray-400">View all sales transactions</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">#{{ $sale->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->outlet->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $sale->saleItems->count() }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">RM {{ number_format($sale->total_amount, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full {{ $sale->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No sales found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($sales->total() <= $sales->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $sales->firstItem() ?? 0 }} &ndash; {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }}
                    results
                </p>
            @else
                {{ $sales->links() }}
            @endif
        </div>
    </div>
@endsection