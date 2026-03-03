@extends('layouts.admin')

@section('title', $customer->name)
@section('header', 'Customer Details')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
            <i class="hgi-stroke hgi-arrow-left-01 text-[18px]"></i> Back to Customers
        </a>
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
            <i class="hgi-stroke hgi-edit-02 text-[18px]"></i> Edit
        </a>
    </div>

    @php
        $tierColors = [
            'bronze' => 'bg-orange-100 text-orange-700',
            'silver' => 'bg-gray-100 text-gray-700',
            'gold' => 'bg-yellow-100 text-yellow-700',
            'platinum' => 'bg-indigo-100 text-indigo-700',
        ];
        $tierColor = $tierColors[$customer->loyalty_tier] ?? 'bg-gray-100 text-gray-700';
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Sidebar --}}
        <div class="space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-4">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-3">
                        <i class="hgi-stroke hgi-user-02 text-[28px] text-indigo-600"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $customer->name }}</h2>
                    <span class="mt-1 px-3 py-0.5 text-xs font-semibold rounded-full {{ $tierColor }}">
                        {{ ucfirst($customer->loyalty_tier) }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500 flex items-center gap-1"><i
                                class="hgi-stroke hgi-call text-[16px]"></i> Phone</span>
                        <span class="text-sm text-gray-800 font-medium">{{ $customer->phone ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500 flex items-center gap-1"><i
                                class="hgi-stroke hgi-mail-01 text-[16px]"></i> Email</span>
                        <span class="text-sm text-gray-800">{{ $customer->email ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Spend</span>
                        <span class="text-sm font-bold text-indigo-700">RM {{ number_format($totalSpend, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Member Since</span>
                        <span class="text-sm text-gray-700">{{ $customer->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Loyalty Card --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-star text-[20px] text-yellow-500"></i>
                    <h3 class="text-md font-semibold text-gray-800">Loyalty</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Current Points</span>
                        <span
                            class="font-bold text-lg text-indigo-700">{{ number_format($customer->loyalty_points) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Earned</span>
                        <span
                            class="text-sm font-medium text-gray-800">{{ number_format($customer->total_points_earned) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Points Value</span>
                        <span class="text-sm font-medium text-green-700">≈ RM
                            {{ number_format($customer->getPointsValue(), 2) }}</span>
                    </div>
                    @if($customer->points_expiry_date)
                        <div class="flex justify-between py-2">
                            <span class="text-sm text-gray-500">Expiry Date</span>
                            <span class="text-sm text-gray-700">{{ $customer->points_expiry_date->format('d M Y') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Activity --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Recent Purchases --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-shopping-cart-01 text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Recent Purchases</h3>
                </div>
                @if($customer->sales->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-gray-400">No purchases yet</p>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sale #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($customer->sales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-medium text-gray-800">#{{ $sale->id }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $sale->created_at->format('d M Y, H:i') }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $sale->outlet->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-800">RM
                                        {{ number_format($sale->total_amount, 2) }}</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $sale->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($sale->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Loyalty Transactions --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-star text-[20px] text-yellow-500"></i>
                    <h3 class="text-md font-semibold text-gray-800">Loyalty Transactions</h3>
                </div>
                @if($transactions->isEmpty())
                    <p class="px-6 py-8 text-center text-sm text-gray-400">No loyalty transactions yet</p>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance After</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($transactions as $tx)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $tx->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $tx->type === 'earn' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                            {{ ucfirst($tx->type) }}
                                        </span>
                                    </td>
                                    <td
                                        class="px-6 py-3 text-sm font-semibold {{ $tx->points > 0 ? 'text-green-700' : 'text-orange-700' }}">
                                        {{ $tx->points > 0 ? '+' : '' }}{{ number_format($tx->points) }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ number_format($tx->points_balance_after) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500 truncate max-w-xs">{{ $tx->description ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection