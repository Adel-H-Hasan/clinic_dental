<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My services | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/desktop.css') }}">
</head>
<body>
    <main class="workspace-main">
        <article class="panel">
            <h1>My services</h1>

            @foreach ($errors->all() as $error)
                <p role="alert">{{ $error }}</p>
            @endforeach

            @if ($appointmentTypes->isEmpty())
                <p>No appointment types are available to add.</p>
            @else
                <form action="{{ route('doctor.services.store') }}" method="POST" class="form-stack">
                    @csrf

                    <label for="appointment_type_id">Appointment type</label>
                    <select id="appointment_type_id" name="appointment_type_id" required>
                        <option value="">Choose a type</option>

                        @foreach ($appointmentTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('appointment_type_id') == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="duration_minutes">Duration in minutes</label>
                    <input id="duration_minutes" type="number" name="duration_minutes"
                        min="1" value="{{ old('duration_minutes') }}" required>

                    <label for="price">Price</label>
                    <input id="price" type="number" name="price"
                        min="0" max="999999.99" step="0.01" value="{{ old('price') }}" required>

                    <button type="submit" class="btn">Add service</button>
                </form>
            @endif

            @if ($services->isEmpty())
                <p>You have no services yet.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->appointment_type->name }}</td>
                                <td>{{ $service->duration_minutes }} min</td>
                                <td>{{ $service->price }}</td>
                                <td>
                                    <a href="{{ route('doctor.services.edit', $service->id) }}">Edit</a>

                                    <form action="{{ route('doctor.services.destroy', $service->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Remove this service from your list?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn outline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </article>
    </main>
</body>
</html>
