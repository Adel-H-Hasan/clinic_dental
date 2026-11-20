<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit working hours | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/desktop.css') }}">
</head>
<body>
    @php
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    @endphp

    <main class="workspace-main">
        <article class="panel">
            <h1>Edit working hours</h1>

            <form action="{{ route('doctor.schedules.update', $schedule->id) }}" method="POST" class="form-stack">
                @csrf
                @method('PUT')

                <label for="day_of_week">Day</label>
                <select id="day_of_week" name="day_of_week" required>
                    @foreach ($days as $number => $day)
                        <option value="{{ $number }}" @selected(old('day_of_week', $schedule->day_of_week) == $number)>
                            {{ $day }}
                        </option>
                    @endforeach
                </select>

                <label for="start_time">Start time</label>
                <input id="start_time" type="time" name="start_time"
                    value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>

                <label for="end_time">End time</label>
                <input id="end_time" type="time" name="end_time"
                    value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>

                <label for="is_available">Availability</label>
                <select id="is_available" name="is_available">
                    <option value="1" @selected(old('is_available', $schedule->is_available) == 1)>Available</option>
                    <option value="0" @selected(old('is_available', $schedule->is_available) == 0)>Unavailable</option>
                </select>

                @foreach ($errors->all() as $error)
                    <p role="alert">{{ $error }}</p>
                @endforeach

                <button type="submit" class="btn">Save changes</button>
                <a href="{{ route('doctor.schedules.index') }}">Back to schedule</a>
            </form>
        </article>
    </main>
</body>
</html>
