<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->isLocale('ar') ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('My weekly schedule') }} | MedAssist Dental</title>
    <link rel="stylesheet" href="{{ asset('css/desktop.css') }}">
</head>
<body>
    @php
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    @endphp

    <main class="workspace-main">
        <article class="panel">
            <h1>{{ __('My weekly schedule') }}</h1>

            <form action="{{ route('doctor.schedules.store') }}" method="POST" class="form-stack">
                @csrf

                <label for="day_of_week">Day</label>
                <select id="day_of_week" name="day_of_week" required>
                    @foreach ($days as $number => $day)
                        <option value="{{ $number }}" @selected(old('day_of_week') == $number)>
                            {{ __($day) }}
                        </option>
                    @endforeach
                </select>

                <label for="start_time">Start time</label>
                <input id="start_time" type="time" name="start_time" value="{{ old('start_time') }}" required>

                <label for="end_time">End time</label>
                <input id="end_time" type="time" name="end_time" value="{{ old('end_time') }}" required>

                @foreach ($errors->all() as $error)
                    <p role="alert">{{ $error }}</p>
                @endforeach

                <button type="submit" class="btn">Add working hours</button>
            </form>

            <div class="schedule-grid">
                @foreach ($days as $dayNumber => $dayName)
                    <section>
                        <h2>{{ __($dayName) }}</h2>

                        @forelse ($schedulesByDay->get($dayNumber, []) as $schedule)
                            <p>
                                <bdi>
                                    {{ substr($schedule->start_time, 0, 5) }}
                                    –
                                    {{ substr($schedule->end_time, 0, 5) }}
                                </bdi>
                            </p>

                            @if ($schedule->is_available)
                                <span class="badge">{{ __('Available') }}</span>
                            @else
                                <span class="badge pending">{{ __('Unavailable') }}</span>
                            @endif

                            <a href="{{ route('doctor.schedules.edit', $schedule->id) }}">Edit</a>

                            <form action="{{ route('doctor.schedules.destroy', $schedule->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this working period?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn outline">Delete</button>
                            </form>
                        @empty
                            <p class="muted">{{ __('No working hours') }}</p>
                        @endforelse
                    </section>
                @endforeach
            </div>
        </article>
    </main>
</body>
</html>
