@extends('layouts.admin')

@section('title', 'Customers')
@section('header', 'Customer Management')

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
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="hgi-stroke hgi-search-01 text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search customers by name, phone or email..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none">
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <select name="tier"
                            class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">All Tiers</option>
                            <option value="platinum" {{ request('tier') == 'platinum' ? 'selected' : '' }}>Platinum</option>
                            <option value="gold" {{ request('tier') == 'gold' ? 'selected' : '' }}>Gold</option>
                            <option value="silver" {{ request('tier') == 'silver' ? 'selected' : '' }}>Silver</option>
                            <option value="bronze" {{ request('tier') == 'bronze' ? 'selected' : '' }}>Bronze</option>
                        </select>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
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
        <a href="{{ route('admin.customers.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-user-add-01"></i>
            Add Customer
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-user-multiple-02 text-indigo-600"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Customers List</h3>
                    <p class="text-sm text-gray-500">Manage your customer database</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tier</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center shrink-0">
                                    <span class="text-indigo-600 font-bold text-sm">{{ substr($customer->name, 0, 1) }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $customer->email ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">
                            {{ number_format($customer->loyalty_points ?? 0) }}
                        </td>
                        <td class="px-6 py-4">
                            <span
                                class="px-2 py-1 text-xs font-medium rounded-full 
                                                                                                                                                                    @if($customer->loyalty_tier === 'platinum') bg-purple-100 text-purple-700
                                                                                                                                                                    @elseif($customer->loyalty_tier === 'gold') bg-yellow-100 text-yellow-700
                                                                                                                                                                    @elseif($customer->loyalty_tier === 'silver') bg-gray-100 text-gray-700
                                                                                                                                                                    @else bg-orange-100 text-orange-700 @endif">
                                {{ ucfirst($customer->loyalty_tier ?? 'bronze') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.customers.points', $customer->id) }}"
                                    class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 hover:bg-yellow-200"
                                    title="View Points">
                                    <i class="hgi-stroke text-[20px] hgi-star text-sm"></i>
                                </a>
                                <a href="{{ route('admin.customers.show', $customer->id) }}"
                                    class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200">
                                    <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                </a>
                                <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                    class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                    <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                </a>
                                <button onclick="deleteCustomer({{ $customer->id }})"
                                    class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 hover:bg-red-200">
                                    <i class="hgi-stroke text-[20px] hgi-delete-01 text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">No customers found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($customers->total() <= $customers->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $customers->firstItem() ?? 0 }} &ndash; {{ $customers->lastItem() ?? 0 }} of
                    {{ $customers->total() }} results
                </p>
            @else
                {{ $customers->links() }}
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            async function deleteCustomer(id) {
                const { isConfirmed } = await Swal.fire({
                    title: 'Delete Customer',
                    text: 'Are you sure you want to delete this customer?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                });

                if (isConfirmed) {
                    try {
                        const response = await fetch(`/admin/customers/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        });

                        if (response.ok) {
                            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1500, showConfirmButton: false });
                            location.reload();
                        }
                    } catch (e) {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to delete customer' });
                    }
                }
            }
        </script>
    @endpush
@endsection