<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/figtree.css') }}" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 CSS -->
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <script>
        if ('serviceWorker' in navigator && false) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/serviceworker.js')
                    .then(registration => console.log('SW registered:', registration))
                    .catch(err => console.log('SW registration failed:', err));
            });
        }
    </script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @isset($slot)
            {{ $slot }}
        @else
            @yield('content')
        @endisset
    </div>


    <!-- SweetAlert2 JS -->
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You will be logged out from the POS terminal!",
                icon: 'warning',
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Yes, logout!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'rounded-3xl',
                    actions: 'gap-2',
                    confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-red-200 transition-transform transform hover:scale-105',
                    cancelButton: 'bg-white hover:bg-slate-50 text-slate-500 border border-slate-200 font-bold py-3 px-6 rounded-xl transition-colors'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('pos-logout-form');
                    if (form) form.submit();
                }
            })
        }
    </script>
    @stack('scripts')
</body>

</html>