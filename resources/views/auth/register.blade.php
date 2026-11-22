<x-auth-shell title="Create your account" subtitle="Your dental care, all in one secure place.">
  <form class="auth-form" method="POST" action="{{ route('register') }}">
    @csrf
    <div class="auth-field">
      <label for="name">{{ __('Full name') }}</label>
      <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="255" required>
    </div>
    <div class="auth-field">
      <label for="email">{{ __('Email') }}</label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
    </div>
    <div class="auth-field">
      <label for="phone">{{ __('Phone number') }}</label>
      <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" maxlength="20" placeholder="+962790000000" required>
      <small class="auth-hint">{{ __('Include your country code, for example +962.') }}</small>
    </div>
    <div class="auth-two-col">
      <div class="auth-field">
        <label for="date_of_birth">{{ __('Date of birth (optional)') }}</label>
        <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" autocomplete="bday" max="{{ now()->toDateString() }}">
      </div>
      <div class="auth-field">
        <label for="gender">{{ __('Gender (optional)') }}</label>
        <select id="gender" name="gender">
          <option value="">{{ __('Select gender') }}</option>
          <option value="male" @selected(old('gender') === 'male')>{{ __('Male') }}</option>
          <option value="female" @selected(old('gender') === 'female')>{{ __('Female') }}</option>
        </select>
      </div>
    </div>
    <div class="auth-two-col auth-password-row">
      <div class="auth-field">
        <label for="password">{{ __('Password') }}</label>
        <input type="password" id="password" name="password" autocomplete="new-password" minlength="8" required>
      </div>
      <div class="auth-field">
        <label for="password_confirmation">{{ __('Confirm password') }}</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" minlength="8" required>
      </div>
    </div>
    <p class="auth-hint auth-password-hint">{{ __('At least 8 characters with upper and lowercase letters, a number and a symbol.') }}</p>
    <button class="auth-submit" type="submit">{{ __('Create account') }}</button>
  </form>
</x-auth-shell>
