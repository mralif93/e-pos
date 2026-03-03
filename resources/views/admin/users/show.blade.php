@extends('layouts.admin')

@section('title', $user->name)
@section('header', 'User Details')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
            <i class="hgi-stroke hgi-arrow-left-01 text-[18px]"></i> Back to Users
        </a>
        <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 text-sm bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
            <i class="hgi-stroke hgi-edit-02 text-[18px]"></i> Edit
        </a>
    </div>

    @php
        $roleColors = [
            'superuser' => 'bg-purple-100 text-purple-700',
            'admin' => 'bg-indigo-100 text-indigo-700',
            'manager' => 'bg-blue-100 text-blue-700',
            'cashier' => 'bg-gray-100 text-gray-700',
        ];
        $roleColor = $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700';
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Sidebar --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-5">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mb-3">
                        <i class="hgi-stroke hgi-user-02 text-[28px] text-indigo-600"></i>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                    <span class="mt-1 px-3 py-0.5 text-xs font-semibold rounded-full {{ $roleColor }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Staff ID</span>
                        <span class="text-sm font-mono text-gray-800">{{ $user->staff_id ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Email</span>
                        <span class="text-sm text-gray-800 truncate max-w-[140px]">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Outlet</span>
                        <span class="text-sm text-gray-800">{{ $user->outlet->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50">
                        <span class="text-sm text-gray-500">Status</span>
                        <span
                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-gray-500">Joined</span>
                        <span class="text-sm text-gray-700">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                </div>
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
                    <p class="px-6 py-10 text-center text-sm text-gray-400">No shifts recorded for this user</p>
                @else
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shift #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
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
                                    <td class="px-6 py-3 text-sm text-gray-600">{{ $shift->outlet->name ?? '—' }}</td>
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