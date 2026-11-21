<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor Profile</title>
</head>

<body>
    <main>
        <h1>Edit my profile</h1>

        <form method="POST" action="{{ route('doctor.profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div>
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4" required>{{ old('bio', $profile->bio) }}</textarea>
                @error('bio')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="years_experience">Years of experience</label>
                <input id="years_experience" name="years_experience" type="number" min="0" max="255"
                    required value="{{ old('years_experience', $profile->years_experience) }}">
                @error('years_experience')
                    <p>{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image">Profile photo (JPG, PNG, or WebP, up to 2 MB)</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">

                @error('image')
                    <p>{{ $message }}</p>
                @enderror
            </div>
            <button type="submit">Save changes</button>
            <a href="{{ route('doctor.profile.show') }}">Cancel</a>
        </form>
    </main>
</body>

</html>
