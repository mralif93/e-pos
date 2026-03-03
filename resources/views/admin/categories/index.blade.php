@extends('layouts.admin')

@section('title', 'Categories')
@section('header', 'Category Management')

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
                            placeholder="Search categories by name..."
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-sm transition-all outline-none">
                    </div>

                    <!-- Column 2: Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-4">
                        <select name="status"
                            class="flex-1 px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                            <option value="">Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-transparent text-gray-600 border-gray-200 hover:bg-gray-100">
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
    <div class="flex justify-end mb-4 mt-2">
        <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-add-01"></i>
            Add Category
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-grid-view text-indigo-600"></i>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Categories List</h3>
                    <p class="text-sm text-gray-500">Manage product categories</p>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center shrink-0">
                                    <i class="hgi-stroke text-[16px] hgi-grid-view text-purple-600"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $category->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $category->products_count ?? 0 }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Active</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $category->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.categories.show', $category->id) }}"
                                    class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center text-gray-600 hover:bg-gray-200">
                                    <i class="hgi-stroke text-[20px] hgi-view text-sm"></i>
                                </a>
                                <a href="{{ route('admin.categories.edit', $category->id) }}"
                                    class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 hover:bg-indigo-200">
                                    <i class="hgi-stroke text-[20px] hgi-edit-02 text-sm"></i>
                                </a>
                                <button onclick="deleteCategory({{ $category->id }})"
                                    class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600 hover:bg-red-200">
                                    <i class="hgi-stroke text-[20px] hgi-delete-01 text-sm"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No categories found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            @if($categories->total() <= $categories->perPage())
                <p class="text-sm text-gray-500">
                    Showing {{ $categories->firstItem() ?? 0 }} &ndash; {{ $categories->lastItem() ?? 0 }} of
                    {{ $categories->total() }} results
                </p>
            @else
                {{ $categories->links() }}
            @endif
        </div>
    </div>

    <!-- Modal -->
    <div id="category-modal" class="fixed inset-0 z-50 hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closeModal()"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h3 class="text-lg font-semibold mb-4" id="modal-title">Add Category</h3>
                <form id="category-form">
                    @csrf
                    <input type="hidden" id="category_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category Name</label>
                        <input type="text" id="category_name" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeModal()" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm flex-1">Cancel</button>
                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200 flex-1">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openModal() {
                document.getElementById('modal-title').innerText = 'Add Category';
                document.getElementById('category_id').value = '';
                document.getElementById('category_name').value = '';
                document.getElementById('category-modal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('category-modal').classList.add('hidden');
            }

            function editCategory(id, name) {
                document.getElementById('modal-title').innerText = 'Edit Category';
                document.getElementById('category_id').value = id;
                document.getElementById('category_name').value = name;
                document.getElementById('category-modal').classList.remove('hidden');
            }

            document.getElementById('category-form').addEventListener('submit', async function (e) {
                e.preventDefault();
                const id = document.getElementById('category_id').value;
                const url = id ? `/admin/categories/${id}` : '/admin/categories';
                const method = id ? 'PUT' : 'POST';

                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ name: document.getElementById('category_name').value })
                });

                if (response.ok) { closeModal(); location.reload(); }
            });

            async function deleteCategory(id) {
                const { isConfirmed } = await Swal.fire({
                    title: 'Delete Category',
                    text: 'Are you sure you want to delete this category?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                    cancelButtonText: 'Cancel'
                });

                if (isConfirmed) {
                    await fetch(`/admin/categories/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                    location.reload();
                }
            }
        </script>
    @endpush
@endsection