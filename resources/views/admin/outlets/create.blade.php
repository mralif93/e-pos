@extends('layouts.admin')

@section('title', 'Create Outlet')
@section('header', 'Create Outlet')

@section('content')
    <div class="w-full">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                <i class="hgi-stroke text-[20px] hgi-store-01 text-indigo-600"></i>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Create Outlet</h3>
                    <p class="text-xs text-gray-400">Add a physically new store location</p>
                </div>
            </div>
            
            <form action="{{ route('admin.outlets.store') }}" method="POST">
                @csrf
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
                            <h4 class="text-sm font-semibold text-gray-800 border-b pb-2 mb-4">Location Details</h4>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Outlet Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Outlet Code <span class="text-red-500">*</span></label>
                            <input type="text" name="outlet_code" value="{{ old('outlet_code') }}" required placeholder="e.g. HQ-01"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Address</label>
                            <textarea name="address" rows="3"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">{{ old('address') }}</textarea>
                        </div>
                        
                        <div class="md:col-span-2 mt-4">
                            <h4 class="text-sm font-semibold text-gray-800 border-b pb-2 mb-4">System Access</h4>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">Outlet is Active</label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="has_pos_access" id="has_pos_access" value="1" {{ old('has_pos_access', true) ? 'checked' : '' }}
                                class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                            <label for="has_pos_access" class="ml-2 block text-sm text-gray-700">Allow POS System Access</label>
                        </div>
                    </div>
                </div>
                
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 items-center">
                    <a href="{{ route('admin.outlets.index') }}"
                        class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-white text-gray-700 border-gray-300 hover:bg-gray-50 shadow-sm">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-2.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
                        Create Outlet
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
