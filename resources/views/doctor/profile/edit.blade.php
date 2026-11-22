<x-workspace title="{{ __('Edit Doctor Profile') }}">
<div class="panel">
    <div class="view-content">


        <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div>
                <label for="bio">{{ __('Bio') }}</label>
                <textarea id="bio" name="bio" rows="4" required>{{ old('bio', $profile->bio) }}</textarea>
            </div>

            <div>
                <label for="years_experience">{{ __('Years of experience') }}</label>
                <input id="years_experience" name="years_experience" type="number" min="0" max="255"
                    required value="{{ old('years_experience', $profile->years_experience) }}">
            </div>

            <div>
                <label for="image">{{ __('Profile photo (JPG, PNG, or WebP, up to 2 MB)') }}</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            </div>
            <button type="submit">{{ __('Save changes') }}</button>
            <a href="{{ route('doctor.profile.show') }}">{{ __('Cancel') }}</a>
        </form>
    </div>

</div>
</x-workspace>
