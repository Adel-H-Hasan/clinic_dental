<x-workspace title="{{ __('Edit working hours') }}">
<div class="panel">
    @php
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    @endphp

    <div class="view-content">
        <article class="panel">


            <form action="{{ route('doctor.schedules.update', $schedule->id) }}" method="POST" class="form-stack">
                @csrf
                @method('PUT')

                <label for="day_of_week">{{ __('Day') }}</label>
                <select id="day_of_week" name="day_of_week" required>
                    @foreach ($days as $number => $day)
                        <option value="{{ $number }}" @selected(old('day_of_week', $schedule->day_of_week) == $number)>
                            {{ $day }}
                        </option>
                    @endforeach
                </select>

                <label for="start_time">{{ __('Start time') }}</label>
                <input id="start_time" type="time" name="start_time"
                    value="{{ old('start_time', substr($schedule->start_time, 0, 5)) }}" required>

                <label for="end_time">{{ __('End time') }}</label>
                <input id="end_time" type="time" name="end_time"
                    value="{{ old('end_time', substr($schedule->end_time, 0, 5)) }}" required>

                <label for="is_available">{{ __('Availability') }}</label>
                <select id="is_available" name="is_available">
                    <option value="1" @selected(old('is_available', $schedule->is_available) == 1)>{{ __('Available') }}</option>
                    <option value="0" @selected(old('is_available', $schedule->is_available) == 0)>{{ __('Unavailable') }}</option>
                </select>

                <button type="submit" class="btn">{{ __('Save changes') }}</button>
                <a href="{{ route('doctor.schedules.index') }}">{{ __('Back to schedule') }}</a>
            </form>
        </article>
    </div>

</div>
</x-workspace>
