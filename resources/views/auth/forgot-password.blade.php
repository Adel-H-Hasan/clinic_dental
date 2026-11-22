<x-auth-shell title="Forgot your password?" subtitle="We will send you a secure recovery link.">
  <form class="auth-form" method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="auth-field">
      <label for="email">{{ __('Email address') }}</label>
      <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
    </div>
    <button class="auth-submit" type="submit">{{ __('Send reset link') }}</button>
  </form>
</x-auth-shell>
