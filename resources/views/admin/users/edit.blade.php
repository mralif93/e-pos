@extends('layouts.admin')

@section('title', 'Edit User')
@section('header', 'Edit User: ' . $user->name)

@section('content')
    <div class="w-full">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-edit-02 text-indigo-600"></i>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Edit Staff User</h3>
                    <p class="text-xs text-gray-400">Update account details and access</p>
                </div>
            </div>

            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6">
                    @if ($errors->any())
                        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-semibold text-gray-800 border-b pb-2 mb-4">Personal Details</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span
                                    class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Staff ID <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="staff_id" value="{{ old('staff_id', $user->staff_id) }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>

                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-sm font-semibold text-gray-800 border-b pb-2 mb-4">Access & Security</h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">System Role <span
                                    class="text-red-500">*</span></label>
                            <select name="role" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none bg-white"
                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                <option value="Cashier" {{ old('role', $user->role) == 'Cashier' ? 'selected' : '' }}>Cashier
                                    (POS Only)</option>
                                <option value="Manager" {{ old('role', $user->role) == 'Manager' ? 'selected' : '' }}>Manager
                                    (Branch Admin)</option>
                                <option value="Admin" {{ old('role', $user->role) == 'Admin' ? 'selected' : '' }}>Admin (Full
                                    System)</option>
                            </select>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="role" value="{{ $user->role }}">
                                <p class="text-xs text-gray-500 mt-1">You cannot change your own role.</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned Outlet</label>
                            <select name="outlet_id"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none bg-white">
                                <option value="">All Outlets (HQ)</option>
                                @foreach($outlets as $outlet)
                                    <option value="{{ $outlet->id }}" {{ old('outlet_id', $user->outlet_id) == $outlet->id ? 'selected' : '' }}>
                                        {{ $outlet->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center mt-6">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Account is Active</label>
                            @if($user->id === auth()->id())
                                <input type="hidden" name="is_active" value="1">
                            @endif
                        </div>

                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-sm font-semibold text-gray-800 border-b pb-2 mb-4">Credentials Update <span
                                    class="text-xs font-normal text-gray-500 ml-2">(Leave blank to keep current
                                    password)</span></h4>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="password" minlength="8"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation" minlength="8"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">POS PIN (4 Digits)</label>
                            <input type="text" name="pin" value="{{ old('pin', $user->pin) }}" maxlength="4" pattern="\d{4}"
                                placeholder="------"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none tracking-widest text-center">
                            <p class="text-xs text-gray-500 mt-1">Leave blank if POS access is not required.</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 items-center">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection