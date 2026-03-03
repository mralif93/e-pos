@extends('layouts.admin')

@section('title', 'Transfers')
@section('header', 'Inventory Transfers')

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
                    <!-- Column 1: Status -->
                    <div>
                        <select name="status"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <select name="outlet_id"
                            class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">Outlet</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}" {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.transfers.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
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
    <div class="flex justify-end mb-4 mt-2">
        <a href="{{ route('admin.transfers.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-add-01"></i>
            New Transfer
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-arrow-left-right text-indigo-600"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Transfers List</h3>
                    <p class="text-sm text-gray-500">Manage inventory transfers between outlets</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">#{{ $transfer->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->fromOutlet->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->toOutlet->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->items->count() }} items</td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($transfer->status === 'pending') bg-yellow-100 text-yellow-700
                                @elseif($transfer->status === 'approved') bg-blue-100 text-blue-700
                                @elseif($transfer->status === 'in_transit') bg-purple-100 text-purple-700
                                @elseif($transfer->status === 'completed') bg-green-100 text-green-700
                                @else bg-red-100 text-red-700 @endif">
                                {{ ucfirst(str_replace('_', ' ', $transfer->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transfer->requested_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.transfers.show', $transfer->id) }}" title="View"
                                    class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200">
                                    <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                </a>
                                @if($transfer->status === 'pending')
                                    <button onclick="approveTransfer({{ $transfer->id }})"
                                        class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 hover:bg-green-200"
                                        title="Approve">
                                        <i class="hgi-stroke text-[20px] hgi-checkmark-circle-01 text-sm"></i>
                                    </button>
                                    <button onclick="rejectTransfer({{ $transfer->id }})"
                                        class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 hover:bg-yellow-200"
                                        title="Reject">
                                        <i class="hgi-stroke text-[20px] hgi-cancel-01 text-sm"></i>
                                    </button>
                                    <a href="{{ route('admin.transfers.show', $transfer->id) }}"
                                        class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200"
                                        title="View">
                                        <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                    </a>
                                    <a href="{{ route('admin.transfers.edit', $transfer->id) }}"
                                        class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200"
                                        title="Edit Draft">
                                        <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                    </a>
                                    <form action="{{ route('admin.transfers.destroy', $transfer->id) }}" method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Are you sure you want to delete this pending transfer?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 hover:bg-red-200"
                                            title="Delete">
                                            <i class="hgi-stroke text-[20px] hgi-delete-01 text-sm"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($transfer->status === 'approved')
                                    <button onclick="markTransit({{ $transfer->id }})"
                                        class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 hover:bg-purple-200"
                                        title="Mark In Transit">
                                        <i class="hgi-stroke text-[20px] hgi-truck-01 text-sm"></i>
                                    </button>
                                @endif
                                @if($transfer->status === 'in_transit')
                                    <button onclick="receiveTransfer({{ $transfer->id }})"
                                        class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200"
                                        title="Receive">
                                        <i class="hgi-stroke text-[20px] hgi-warehouse text-sm"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No transfers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($transfers->total() <= $transfers->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $transfers->firstItem() ?? 0 }} &ndash; {{ $transfers->lastItem() ?? 0 }} of
                    {{ $transfers->total() }} results
                </p>
            @else
                {{ $transfers->links() }}
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            async function approveTransfer(id) {
                if (confirm('Approve this transfer?')) {
                    await fetch(`/admin/transfers/${id}/approve`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                    location.reload();
                }
            }

            async function rejectTransfer(id) {
                if (confirm('Reject this transfer?')) {
                    await fetch(`/admin/transfers/${id}/reject`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                    location.reload();
                }
            }

            async function markTransit(id) {
                await fetch(`/admin/transfers/${id}/transit`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                location.reload();
            }

            async function receiveTransfer(id) {
                await fetch(`/admin/transfers/${id}/receive`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                location.reload();
            }
        </script>
    @endpush
@endsection