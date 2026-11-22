<x-auth-shell title="Verify your email" subtitle="Check your inbox to finish account setup.">
  <div class="auth-verification" role="status">
    <p>{{ __('Open the verification message sent after registration and follow its secure link. Check your spam folder if it is not in your inbox.') }}</p>
    <form class="auth-form" method="POST" action="{{ route('verification.send') }}">
      @csrf
      <div class="auth-field">
        <label for="email">{{ __('Email address') }}</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
      </div>
      <button class="auth-submit" type="submit">{{ __('Resend verification link') }}</button>
      <p class="auth-hint">{{ __('You can request up to three links per email every ten minutes.') }}</p>
    </form>
    <a href="{{ route('login') }}" class="auth-back-link">{{ __('Back to sign in') }}</a>
  </div>
</x-auth-shell>
