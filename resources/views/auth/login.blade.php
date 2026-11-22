<x-auth-shell title="Welcome back" subtitle="Sign in to continue your dental care journey.">
  <form class="auth-form" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="auth-field">
      <label for="email">{{ __('Email') }}</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required autofocus>
    </div>
    <div class="auth-field">
      <div class="auth-label-row">
        <label for="password">{{ __('Password') }}</label>
        <a href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
      </div>
      <input type="password" id="password" name="password" autocomplete="current-password" required>
    </div>
    <label class="auth-remember"><input type="checkbox" name="remember" value="1"> {{ __('Remember me') }}</label>
    <button class="auth-submit" type="submit">{{ __('Sign in') }}</button>
  </form>
</x-auth-shell>
