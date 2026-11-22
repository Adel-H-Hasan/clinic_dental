@php
    $hint = request()->cookie('taqweem_ui_locale');
    $errorLocale = in_array($hint, ['en','ar'], true) ? $hint : app()->getLocale();
@endphp
<!doctype html><html lang="{{ $errorLocale }}" dir="{{ $errorLocale === 'ar' ? 'rtl' : 'ltr' }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $code }} | {{ __('TAQWEEM', [], $errorLocale) }}</title><link rel="stylesheet" href="{{ asset('css/workspace.css') }}"></head><body><main class="auth-content"><x-language-switch :current-locale="$errorLocale"/><p class="eyebrow">{{ __('TAQWEEM', [], $errorLocale) }} / {{ $code }}</p><h1>{{ __($heading, [], $errorLocale) }}</h1><p>{{ __($description, [], $errorLocale) }}</p><a class="button" href="{{ url('/') }}">{{ __('Return to homepage', [], $errorLocale) }}</a><p><a href="{{ route('login') }}">{{ __('Sign in', [], $errorLocale) }}</a></p></main><script src="{{ asset('js/workspace.js') }}" defer></script></body></html>
