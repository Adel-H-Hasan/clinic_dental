<x-auth-shell title="Choose a new password" subtitle="Use a unique password for your account.">
  <form class="auth-form" method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="email" value="{{ $email }}">
    <input type="hidden" name="token" value="{{ $token }}">
    <p class="auth-reset-email">{{ __('Choose a new password for') }} <strong>{{ $email }}</strong></p>
    <div class="auth-field">
      <label for="password">{{ __('New password') }}</label>
      <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
    </div>
    <div class="auth-field">
      <label for="password_confirmation">{{ __('Confirm new password') }}</label>
      <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
    </div>
    <p class="auth-hint">{{ __('At least 8 characters with upper and lowercase letters, a number and a symbol.') }}</p>
    @error('email')<a href="{{ route('password.request') }}">{{ __('Request a new reset link') }}</a>@enderror
    <button class="auth-submit" type="submit">{{ __('Save password') }}</button>
  </form>
</x-auth-shell>
