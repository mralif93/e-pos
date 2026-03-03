@extends('layouts.admin')

@section('title', 'Users')
@section('header', 'User Management')

@section('content')



    <!-- Filter Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="hgi-stroke text-[20px] hgi-settings-02 text-indigo-600"></i>
            <h3 class="text-md font-semibold text-gray-800">Search & Filter</h3>
        </div>
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                    <!-- Column 1: Search -->
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="hgi-stroke hgi-search-01 text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search users by name, email or staff ID..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[140px]">
                            <select name="role"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer hover:bg-gray-50">
                                <option value="">All Roles</option>
                                <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Manager" {{ request('role') == 'Manager' ? 'selected' : '' }}>Manager</option>
                                <option value="Cashier" {{ request('role') == 'Cashier' ? 'selected' : '' }}>Cashier</option>
                                <option value="viewer" {{ request('role') == 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>
                        </div>

                        <div class="flex-1 min-w-[140px]">
                            <select name="outlet_id"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm bg-white transition-all cursor-pointer hover:bg-gray-50">
                                <option value="">All Outlets</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ (request('outlet') == $outlet->id || request('outlet_id') == $outlet->id) ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
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

    <!-- Table Card -->
    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-add-01"></i>
            Create User
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-user-multiple-02 text-indigo-600"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Users List</h3>
                    <p class="text-sm text-gray-500">Manage user accounts and roles</p>
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
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name / Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Staff ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
                                        <i class="hgi-stroke text-[16px] hgi-user-star-01 text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->staff_id }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full {{ $user->role === 'Admin' || $user->role === 'Super Admin' ? 'bg-purple-100 text-purple-700' : ($user->role === 'Manager' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->outlet->name ?? 'All Outlets' }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.show', $user->id) }}" title="View"
                                        class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200">
                                        <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" title="Edit"
                                        class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                        <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline"
                                            onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete"
                                                class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 hover:bg-red-200">
                                                <i class="hgi-stroke text-[20px] hgi-delete-01 text-sm"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">No users found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection