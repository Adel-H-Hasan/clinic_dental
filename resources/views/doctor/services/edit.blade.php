<x-workspace title="{{ __('Edit service') }}">
<div class="panel">
    <div class="view-content">
        <article class="panel">


            <form action="{{ route('doctor.services.update', $service->id) }}"
                  method="POST" class="form-stack">
                @csrf
                @method('PUT')

                <label for="duration_minutes">{{ __('Duration in minutes') }}</label>
                <input id="duration_minutes" name="duration_minutes" type="number"
                       min="1" value="{{ old('duration_minutes', $service->duration_minutes) }}" required>

                <label for="price">{{ __('Price') }}</label>
                <input id="price" name="price" type="number"
                       min="0" max="999999.99" step="0.01"
                       value="{{ old('price', $service->price) }}" required>

                <button type="submit" class="btn">{{ __('Save changes') }}</button>
                <a href="{{ route('doctor.services.index') }}">{{ __('Back to services') }}</a>
            </form>
        </article>
    </div>

</div>
</x-workspace>
