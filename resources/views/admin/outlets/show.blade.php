@extends('layouts.admin')

@section('title', $outlet->name)
@section('header', 'Outlet Details')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.outlets.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
            <i class="hgi-stroke hgi-arrow-left-01 text-[18px]"></i> Back to Outlets
        </a>
        <a href="{{ route('admin.outlets.edit', $outlet->id) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
            <i class="hgi-stroke hgi-edit-02 text-[18px]"></i> Edit
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Sidebar --}}
        <div class="space-y-6">
            {{-- Outlet Info --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-5">
                    <div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="hgi-stroke hgi-building-03 text-[26px] text-indigo-600"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $outlet->name }}</h2>
                    <span
                        class="mt-1 px-3 py-0.5 text-xs font-semibold rounded-full font-mono text-indigo-700 bg-indigo-50">
                        {{ $outlet->outlet_code }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Phone</span>
                        <span class="text-sm text-gray-800">{{ $outlet->phone ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Status</span>
                        <span
                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $outlet->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $outlet->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">POS Access</span>
                        <span
                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $outlet->has_pos_access ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $outlet->has_pos_access ? 'Enabled' : 'Disabled' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">This Month Sales</span>
                        <span class="text-sm font-bold text-indigo-700">RM {{ number_format($monthSales, 2) }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Total Staff</span>
                        <span class="text-sm font-bold text-gray-800">{{ $outlet->users->count() }}</span>
                    </div>
                    @if($outlet->address)
                        <div class="py-2">
                            <p class="text-sm text-gray-500 mb-1">Address</p>
                            <p class="text-sm text-gray-700 bg-gray-50 p-2 rounded-lg">{{ $outlet->address }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Staff List --}}
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-user-multiple-02 text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Staff ({{ $outlet->users->count() }})</h3>
                </div>
                @if($outlet->users->isEmpty())
                    <p class="text-sm text-gray-400 text-center py-6">No staff assigned</p>
                @else
                    <div class="p-6 space-y-2">
                        @foreach($outlet->users as $user)
                            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ ucfirst($user->role) }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="px-2 py-0.5 text-xs rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                        {{ $user->is_active ? 'Active' : 'Off' }}
                                    </span>
                                    <a href="{{ route('admin.users.show', $user->id) }}"
                                        class="text-gray-400 hover:text-indigo-600 transition-colors">
                                        <i class="hgi-stroke hgi-view text-[16px]"></i>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Recent Shifts --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <i class="hgi-stroke hgi-clock-01 text-[20px] text-indigo-600"></i>
                    <h3 class="text-md font-semibold text-gray-800">Recent Shifts</h3>
                </div>
                @if($recentShifts->isEmpty())
                    <p class="px-6 py-10 text-center text-sm text-gray-400">No shifts recorded for this outlet</p>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Opened</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Closed</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentShifts as $shift)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-3 text-sm font-mono text-gray-700">{{ $shift->shift_number }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $shift->user->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $shift->opened_at?->format('d M, H:i') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $shift->closed_at?->format('d M, H:i') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $shift->status === 'open' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                            {{ ucfirst($shift->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route('admin.shifts.show', $shift->id) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 text-gray-500 bg-white hover:bg-indigo-50 hover:border-indigo-300 hover:text-indigo-600 transition-all">
                                            <i class="hgi-stroke hgi-view text-[16px]"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
@endsection