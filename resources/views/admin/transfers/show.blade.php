@extends('layouts.admin')

@section('title', 'Transfer ' . $transfer->transfer_number)
@section('header', 'Transfer Details')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.transfers.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
            <i class="hgi-stroke hgi-arrow-left-01 text-[18px]"></i> Back to Transfers
        </a>
        @if(in_array($transfer->status, ['pending', 'approved']))
        <a href="{{ route('admin.transfers.edit', $transfer->id) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
            <i class="hgi-stroke hgi-edit-02 text-[18px]"></i> Edit
        </a>
        @endif
    </div>

    @php
        $statusConfig = [
            'pending'    => ['bg-yellow-100 text-yellow-700', 'Pending'],
            'approved'   => ['bg-blue-100 text-blue-700', 'Approved'],
            'in_transit' => ['bg-orange-100 text-orange-700', 'In Transit'],
            'received'   => ['bg-green-100 text-green-700', 'Received'],
            'rejected'   => ['bg-red-100 text-red-700', 'Rejected'],
            'cancelled'  => ['bg-gray-100 text-gray-500', 'Cancelled'],
        ];
        [$statusClass, $statusLabel] = $statusConfig[$transfer->status] ?? ['bg-gray-100 text-gray-500', ucfirst($transfer->status)];
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Details --}}
        <div class="space-y-6">
            {{-- Transfer Info --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Transfer #</p>
                        <h2 class="text-lg font-bold font-mono text-gray-900">{{ $transfer->transfer_number }}</h2>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">From</span>
                        <span class="text-sm font-medium text-gray-800">{{ $transfer->fromOutlet->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">To</span>
                        <span class="text-sm font-medium text-gray-800">{{ $transfer->toOutlet->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Items</span>
                        <span class="text-sm font-bold text-indigo-700">{{ $transfer->items->count() }} products</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Qty</span>
                        <span class="text-sm font-bold text-gray-800">{{ $transfer->items->sum('quantity_sent') }}</span>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-time-01 text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Timeline</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Requested by {{ $transfer->requestedBy->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $transfer->requested_at?->format('d M Y, H:i') ?? '—' }}</p>
                        </div>
                    </div>
                    @if($transfer->approved_at)
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-{{ $transfer->status === 'rejected' ? 'red' : 'green' }}-500 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">{{ $transfer->status === 'rejected' ? 'Rejected' : 'Approved' }} by {{ $transfer->approvedBy->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $transfer->approved_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($transfer->in_transit_at)
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-orange-500 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Dispatched (In Transit)</p>
                            <p class="text-xs text-gray-500">{{ $transfer->in_transit_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                    @if($transfer->received_at)
                    <div class="flex gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-600 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Received by {{ $transfer->receivedBy->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $transfer->received_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($transfer->notes)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-note-01 text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Notes</h3>
                </div>
                <div class="p-6">
                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $transfer->notes }}</p>
                </div>
            </div>
            @endif

            @if($transfer->rejection_reason)
            <div class="bg-red-50 rounded-xl border border-red-100 p-6">
                <h3 class="font-semibold text-red-700 mb-2 flex items-center gap-1">
                    <i class="hgi-stroke hgi-cancel-01 text-[16px]"></i> Rejection Reason
                </h3>
                <p class="text-sm text-red-600">{{ $transfer->rejection_reason }}</p>
            </div>
            @endif
        </div>

        {{-- Right: Items Table --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-package text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Transfer Items</h3>
                </div>
                @if($transfer->items->isEmpty())
                    <p class="px-6 py-10 text-center text-sm text-gray-400">No items in this transfer</p>
                @else
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Sent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($transfer->items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-sm font-medium text-gray-800">{{ $item->product->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm font-mono text-gray-500">{{ $item->product->sku ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-800">{{ $item->quantity_sent }}</td>
                            <td class="px-6 py-3 text-sm {{ $item->quantity_received !== null ? ($item->quantity_received == $item->quantity_sent ? 'text-green-700 font-semibold' : 'text-orange-700 font-semibold') : 'text-gray-400' }}">
                                {{ $item->quantity_received ?? 'Pending' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t border-gray-100">
                        <tr>
                            <td class="px-6 py-3 text-sm font-semibold text-gray-700" colspan="2">Total</td>
                            <td class="px-6 py-3 text-sm font-bold text-gray-900">{{ $transfer->items->sum('quantity_sent') }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-green-700">{{ $transfer->items->whereNotNull('quantity_received')->sum('quantity_received') ?: '—' }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif
            </div>
        </div>
    </div>
@endsection
