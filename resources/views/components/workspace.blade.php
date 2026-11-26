@props(['title' => 'Your dental care', 'subtitle' => ''])

@php
$role = auth()->user()?->role;

$links = match($role) {
'patient' => [
['Overview','patient.dashboard','home'],
['Find dentists','dentists.index','people'],
['Appointments','patient.workspace','calendar'],
['Smile AI','smile.ai.create','spark'],
['Profile','patient.profile.show','user']
],
'doctor' => [
['Overview','doctor.dashboard','home'],
['My schedule','doctor.schedules.index','calendar'],
['Appointments','doctor.workspace','list'],
['Services','doctor.services.index','spark'],
['My profile','doctor.profile.show','user']
],
'admin' => [
['Overview','admin.dashboard','home'],
['Users','admin.users.index','people'],
['Doctors','admin.doctors.index','user'],
['Specialities','admin.specialities.index','spark'],
['Appointment types','admin.types.workspace','list']
],
default => [
['Home','landing','home'],
['Find dentists','dentists.index','people'],
['Smile AI','smile.ai.create','spark'],
['Sign in','login','user']
],
};
@endphp

<!doctype html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __($title) }} {{ __('| TAQWEEM Dental') }}</title>

    <!-- Existing Workspace CSS -->
    <link rel="stylesheet" href="{{ asset('css/workspace.css') }}">

    <!-- TAQWEEM Chatbot CSS -->
    <link rel="stylesheet" href="{{ asset('css/medassist-chatbot.css') }}">

    @stack('styles')

    <script type="application/json" id="taqweem-ui-config">
        {
            !!json_encode([
                'submitting' => __('Submitting…'),
                'invalidImage' => __('Choose a JPEG, PNG or WebP image up to 8 MB'),
                'processing' => __('Processing securely…')
            ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!
        }
    </script>


    <script>
        window.TaqweemUI = JSON.parse(
            document.getElementById('taqweem-ui-config').textContent
        );
    </script>


    <script src="{{ asset('js/workspace.js') }}" defer></script>
</head>

<body class="workspace">

    <a class="skip-link" href="#main">
        {{ __('Skip to content') }}
    </a>

    <header class="mobile-header">
        <a class="brand" href="{{ route('landing') }}">
            <x-icon name="tooth" />
            {{ __('TAQWEEM') }}
        </a>

        <x-language-switch />

        <button class="secondary"
            type="button"
            aria-expanded="false"
            aria-controls="workspace-sidebar"
            data-menu>
            {{ __('Menu') }}
        </button>
    </header>

    <aside class="workspace-sidebar" id="workspace-sidebar">

        <a class="brand" href="{{ route('landing') }}">
            <x-icon name="tooth" />
            <span>
                {{ __('TAQWEEM') }}
                <small>{{ __('DENTAL') }}</small>
            </span>
        </a>

        <x-language-switch />

        <p class="role-label">
            {{ __($role ?: 'DENTAL CARE') }}
        </p>

        <nav aria-label="{{ __('Main navigation') }}">
            @foreach($links as [$label, $route, $icon])
            <a href="{{ route($route) }}"
                @if(request()->routeIs($route)) aria-current="page" @endif>

                <x-icon :name="$icon" />

                <span>{{ __($label) }}</span>
            </a>
            @endforeach
        </nav>

        <div class="sidebar-account">
            @auth
            <span class="avatar">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </span>

            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <small>
                    {{ __($role) }} {{ __('account') }}
                </small>
            </div>

            <form method="post" action="{{ route('logout.submit') }}">
                @csrf

                <button class="sidebar-logout">
                    {{ __('Sign out') }}
                </button>
            </form>
            @else
            <a href="{{ route('register') }}">
                {{ __('Create your patient account →') }}
            </a>
            @endauth
        </div>

    </aside>

    <main id="main" class="workspace-main">

        <header class="page-heading">
            <p class="eyebrow">
                {{ __('DENTAL CLINIC /') }}
                {{ __($role ?: 'WELCOME') }}
            </p>

            <h1>{{ __($title) }}</h1>

            @if($subtitle)
            <p>{{ __($subtitle) }}</p>
            @endif
        </header>

        <x-feedback />

        {{ $slot }}

    </main>

    <nav class="mobile-bottom" aria-label="{{ __('Quick navigation') }}">
        @foreach(array_slice($links, 0, 4) as [$label, $route, $icon])
        <a href="{{ route($route) }}"
            @if(request()->routeIs($route)) aria-current="page" @endif>

            <x-icon :name="$icon" />

            <span>{{ __($label) }}</span>
        </a>
        @endforeach
    </nav>

    <!-- TAQWEEM Floating Chatbot -->
    <x-medassist-chatbot />

    <!-- TAQWEEM Chatbot JavaScript -->
    <script src="{{ asset('js/medassist-chatbot.js') }}" defer></script>

    @stack('scripts')

</body>

</html>