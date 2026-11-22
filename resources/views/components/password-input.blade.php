<div class="password-control">
  <input type="password" {{ $attributes }}>
  <button type="button" class="password-eye" hidden aria-controls="{{ $attributes->get('id') }}"
          aria-label="{{ __('Show password') }}" aria-pressed="false">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
      <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
      <circle cx="12" cy="12" r="3"/>
      <path class="eye-slash" d="m3 3 18 18"/>
    </svg>
  </button>
</div>
