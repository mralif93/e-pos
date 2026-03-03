@extends('layouts.admin')

@section('title', 'Settings')
@section('header', 'System Settings')

@section('content')
<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PUT')

    @if(session('success'))
        <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-xl mb-6 text-sm">
            <i class="hgi-stroke text-[20px] hgi-tick-circle text-green-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl mb-6 text-sm">
            <i class="hgi-stroke text-[20px] hgi-cancel-circle text-red-500 mt-0.5"></i>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">

        {{-- ── General Settings ── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[18px] hgi-settings-02 text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">General Settings</h3>
                    <p class="text-xs text-gray-400">Basic application configuration</p>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">App Name</label>
                        <input type="text" name="app_name" value="{{ config('app.name') }}"
                            placeholder="e.g. My POS System"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        <p class="text-xs text-gray-400 mt-1">Displayed in page titles and receipt headers.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                        <select name="currency" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none bg-white">
                            <option value="MYR" {{ config('settings.currency', 'MYR') === 'MYR' ? 'selected' : '' }}>MYR – Malaysian Ringgit (RM)</option>
                            <option value="USD" {{ config('settings.currency') === 'USD' ? 'selected' : '' }}>USD – US Dollar ($)</option>
                            <option value="SGD" {{ config('settings.currency') === 'SGD' ? 'selected' : '' }}>SGD – Singapore Dollar (S$)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                        <div class="relative">
                            <input type="number" name="tax_rate" value="{{ config('settings.tax_rate', 0) }}" step="0.01" min="0" max="100"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none pr-10">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">%</span>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Applied to all sales transactions (0 to disable).</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Inventory Settings ── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[18px] hgi-package text-orange-600"></i>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Inventory</h3>
                    <p class="text-xs text-gray-400">Stock management and alert thresholds</p>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Default Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" value="{{ config('settings.low_stock_threshold', 10) }}" min="0"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        <p class="text-xs text-gray-400 mt-1">Products below this quantity trigger a low-stock alert.</p>
                    </div>
                    <div class="flex items-start gap-4 pt-6">
                        <div class="flex items-center h-10">
                            <input type="checkbox" name="enable_low_stock_alerts" id="enable_low_stock_alerts" value="1"
                                {{ config('settings.enable_low_stock_alerts', true) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        </div>
                        <label for="enable_low_stock_alerts" class="cursor-pointer">
                            <span class="block text-sm font-medium text-gray-700">Enable Low Stock Alerts</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Show alerts in the dashboard when stock falls below threshold.</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Loyalty Settings ── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[18px] hgi-star text-yellow-600"></i>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Loyalty Points</h3>
                    <p class="text-xs text-gray-400">Customer reward points earning and redemption rules</p>
                </div>
            </div>
            <div class="p-6 space-y-6">
                {{-- Earning rules --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Points Earned per RM 1 Spent</label>
                        <input type="number" name="loyalty_points_per_ringgit"
                            value="{{ config('settings.loyalty_points_per_ringgit', 1) }}" step="1" min="0"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        <p class="text-xs text-gray-400 mt-1">Number of points awarded per RM 1 of spend.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Spend for Points (RM)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">RM</span>
                            <input type="number" name="loyalty_min_spend"
                                value="{{ config('settings.loyalty_min_spend', 1) }}" step="0.01" min="0"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Transactions below this amount do not earn points.</p>
                    </div>
                </div>

                {{-- Tier redemption values --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-1">Point Redemption Value by Tier</h4>
                    <p class="text-xs text-gray-400 mb-4">RM value of each loyalty point redeemed, based on customer tier.</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-600 inline-block"></span>
                                <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide">Bronze</span>
                            </div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Value per Point (RM)</label>
                            <input type="number" name="loyalty_bronze"
                                value="{{ config('settings.loyalty_points_value_bronze', 0.025) }}" step="0.001" min="0"
                                class="w-full px-3 py-1.5 border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-400 text-sm bg-white">
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-400 inline-block"></span>
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Silver</span>
                            </div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Value per Point (RM)</label>
                            <input type="number" name="loyalty_silver"
                                value="{{ config('settings.loyalty_points_value_silver', 0.030) }}" step="0.001" min="0"
                                class="w-full px-3 py-1.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-400 text-sm bg-white">
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500 inline-block"></span>
                                <span class="text-xs font-semibold text-yellow-700 uppercase tracking-wide">Gold</span>
                            </div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Value per Point (RM)</label>
                            <input type="number" name="loyalty_gold"
                                value="{{ config('settings.loyalty_points_value_gold', 0.035) }}" step="0.001" min="0"
                                class="w-full px-3 py-1.5 border border-yellow-200 rounded-lg focus:ring-2 focus:ring-yellow-400 text-sm bg-white">
                        </div>
                        <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 inline-block"></span>
                                <span class="text-xs font-semibold text-cyan-700 uppercase tracking-wide">Platinum</span>
                            </div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Value per Point (RM)</label>
                            <input type="number" name="loyalty_platinum"
                                value="{{ config('settings.loyalty_points_value_platinum', 0.040) }}" step="0.001" min="0"
                                class="w-full px-3 py-1.5 border border-cyan-200 rounded-lg focus:ring-2 focus:ring-cyan-400 text-sm bg-white">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Payment Settings ── --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="hgi-stroke text-[18px] hgi-credit-card text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-md font-semibold text-gray-800">Payment Gateway</h3>
                    <p class="text-xs text-gray-400">DuitNow QR integration credentials</p>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">DuitNow Merchant ID</label>
                        <input type="text" name="duitnow_merchant_id"
                            value="{{ config('settings.duitnow_merchant_id') }}"
                            placeholder="e.g. MY123456789"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-sm outline-none">
                        <p class="text-xs text-gray-400 mt-1">Provided by your DuitNow payment provider.</p>
                    </div>
                    <div class="flex items-start gap-4 pt-6">
                        <div class="flex items-center h-10">
                            <input type="checkbox" name="duitnow_production" id="duitnow_production" value="1"
                                {{ config('settings.duitnow_production', false) ? 'checked' : '' }}
                                class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">
                        </div>
                        <label for="duitnow_production" class="cursor-pointer">
                            <span class="block text-sm font-medium text-gray-700">Production Mode</span>
                            <span class="block text-xs text-gray-400 mt-0.5">Enable for live payments. Leave unchecked for sandbox/testing.</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- end space-y-6 --}}

    {{-- Save Bar --}}
    <div class="mt-6 bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-4 flex items-center justify-between">
        <p class="text-sm text-gray-500">Changes are saved immediately and take effect on the next page load.</p>
        <button type="submit" class="inline-flex items-center justify-center gap-1.5 rounded-lg text-sm font-medium transition-all active:scale-95 border focus:outline-none px-4 py-0.5 bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 shadow-sm shadow-indigo-200">
            <i class="hgi-stroke text-[20px] hgi-floppy-disk-01"></i>
            Save Settings
        </button>
    </div>

</form>
@endsection
