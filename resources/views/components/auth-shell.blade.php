@props(['title', 'subtitle' => 'Your dental care, all in one place.'])
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ __($title) }} · TAQWEEM</title>
  <link rel="stylesheet" href="{{ asset('css/workspace.css') }}">
  <link rel="stylesheet" href="{{ asset('css/auth-refined.css') }}">
  <script>window.TaqweemUI = @js(['submitting' => __('Submitting…')]);</script>
  <script src="{{ asset('js/workspace.js') }}" defer></script>
</head>
<body class="auth-page">
  <a class="skip-link" href="#main">{{ __('Skip to form') }}</a>
  <aside class="auth-story">
    <a class="brand" href="{{ route('landing') }}"><x-icon name="tooth"/> {{ __('TAQWEEM') }} <small>{{ __('DENTAL') }}</small></a>
    <div class="auth-story-center">
      <img src="{{ asset('images/auth-tooth.svg') }}" width="320" height="280" alt="" aria-hidden="true">
      <h2>{{ __('A more connected') }} {{ __('dental care experience.') }}</h2>
      <p>{{ __('Designed for patients, dentists and clinic teams.') }}</p>
    </div>
    <a class="auth-story-return" href="{{ route('landing') }}">{{ __('Back to homepage') }}</a>
  </aside>
  <main id="main" class="auth-content">
    <div class="auth-content-inner">
      <div class="auth-toolbar">
        <a class="auth-mobile-brand brand" href="{{ route('landing') }}"><x-icon name="tooth"/> {{ __('TAQWEEM') }}</a>
        <x-language-switch/>
      </div>
      <div class="auth-intro">
        <h1>{{ __($title) }}</h1>
        <p class="muted">{{ __($subtitle) }}</p>
      </div>
      <x-feedback/>
      {{ $slot }}
      <footer class="auth-footer">
        @if(request()->routeIs('login'))
          <span>{{ __('New to TAQWEEM?') }}</span> <a href="{{ route('register') }}">{{ __('Create account') }}</a>
        @elseif(request()->routeIs('register'))
          <span>{{ __('Already have an account?') }}</span> <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
        @else
          <a href="{{ route('login') }}">{{ __('Sign in') }}</a> <span aria-hidden="true">·</span>
          <a href="{{ route('register') }}">{{ __('Create account') }}</a>
        @endif
      </footer>
    </div>
  </main>
</body>
</html>
