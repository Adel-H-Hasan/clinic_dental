<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit service | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/desktop.css') }}">
</head>
<body>
    <main class="workspace-main">
        <article class="panel">
            <h1>Edit {{ $service->appointment_type->name }}</h1>

            <form action="{{ route('doctor.services.update', $service->id) }}"
                  method="POST" class="form-stack">
                @csrf
                @method('PUT')

                <label for="duration_minutes">Duration in minutes</label>
                <input id="duration_minutes" name="duration_minutes" type="number"
                       min="1" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>

                <label for="price">Price</label>
                <input id="price" name="price" type="number"
                       min="0" max="999999.99" step="0.01"
                       value="{{ old('price', $service->price) }}" required>

                @foreach ($errors->all() as $error)
                    <p role="alert">{{ $error }}</p>
                @endforeach

                <button type="submit" class="btn">Save changes</button>
                <a href="{{ route('doctor.services.index') }}">Back to services</a>
            </form>
        </article>
    </main>
</body>
</html>
