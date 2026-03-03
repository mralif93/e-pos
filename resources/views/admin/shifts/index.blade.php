@extends('layouts.admin')

@section('title', 'Shifts')
@section('header', 'Shift Management')

@section('content')
    <!-- Open/Closed Shifts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-calendar-01 text-green-600"></i>
                <h3 class="text-md font-semibold text-gray-800">Open Shifts</h3>
            </div>
            <div class="p-6">
                @forelse($openShifts as $shift)
                    <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="hgi-stroke text-[18px] hgi-calendar-01 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $shift->user->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400">{{ $shift->outlet->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">Started: {{ $shift->opened_at->format('H:i') }}</p>
                            <form action="{{ route('admin.shifts.close', $shift->id) }}" method="POST" class="mt-1">
                                @csrf
                                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Close
                                    Shift</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-4">No open shifts</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-calendar-lock-01 text-gray-600"></i>
                <h3 class="text-md font-semibold text-gray-800">Recent Closed Shifts</h3>
            </div>
            <div class="p-6">
                @forelse($closedShifts as $shift)
                    <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="hgi-stroke text-[18px] hgi-calendar-lock-01 text-gray-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $shift->user->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-400">{{ $shift->outlet->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">{{ $shift->opened_at->format('M d, H:i') }} -
                                {{ $shift->closed_at ? $shift->closed_at->format('H:i') : '-' }}
                            </p>
                            <p class="text-xs font-medium text-gray-600">RM {{ number_format($shift->cash_in_hand ?? 0, 2) }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-400 py-4">No closed shifts</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Search & Filter Shifts -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="hgi-stroke text-[20px] hgi-search-01 text-indigo-600"></i>
            <h3 class="text-md font-semibold text-gray-800">Search & Filter Shifts</h3>
        </div>
        <form method="GET">
            <div class="p-6">
                <div class="flex flex-wrap items-center justify-between gap-6">
                    <!-- Column 1: Date Range -->
                    <div class="flex items-center gap-4 flex-1">
                        <input type="datetime-local" name="date_from" value="{{ request('date_from') }}"
                            title="Start Date & Time"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        <span class="text-gray-400">-</span>
                        <input type="datetime-local" name="date_to" value="{{ request('date_to') }}" title="End Date & Time"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.shifts.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
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
        <a href="{{ route('admin.shifts.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-add-01"></i>
            Create Shift
        </a>
    </div>

    <!-- All Shifts -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-list text-indigo-600"></i>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">All Shifts</h3>
                    <p class="text-xs text-gray-400">View all shift records</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ended</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cash In Hand</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($shifts as $shift)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-800">{{ $shift->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $shift->outlet->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $shift->opened_at->format('M d, H:i') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $shift->closed_at ? $shift->closed_at->format('M d, H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-800">RM {{ number_format($shift->cash_in_hand ?? 0, 2) }}</td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full {{ $shift->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($shift->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.shifts.show', $shift->id) }}" title="View"
                                    class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 hover:bg-blue-200">
                                    <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                </a>
                                <a href="{{ route('admin.shifts.edit', $shift->id) }}" title="Edit"
                                    class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                    <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                </a>
                                <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Are you sure you want to delete this shift? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 hover:bg-red-200"
                                        title="Delete">
                                        <i class="hgi-stroke text-[20px] hgi-delete-01 text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-400">No shifts found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($shifts->total() <= $shifts->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $shifts->firstItem() ?? 0 }} &ndash; {{ $shifts->lastItem() ?? 0 }} of {{ $shifts->total() }}
                    results
                </p>
            @else
                {{ $shifts->links() }}
            @endif
        </div>
    </div>
@endsection