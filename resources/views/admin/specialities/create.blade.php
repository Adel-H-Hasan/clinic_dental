<x-workspace title="{{ __('Add Specialities') }}">
<div class="panel">

    <form action="{{ route('admin.specialities.store')}}" method="POST">
        @csrf

    <label for="name">{{ __('Speciality Name') }}</label>
    <input type="text" name="name" id="name" placeholder="{{ __('Enter a speciality name') }}"
                value="{{ old('name') }}" required><br><br>

    <label for="description">{{ __('Speciality Description') }}</label>
    <textarea name="description" id="description" placeholder="{{ __('Enter a description') }}"
    >{{ old('description') }}</textarea><br><br>

    <button type="submit">{{ __('Submit') }}</button>
    </form>

    <a href="{{ route('admin.specialities.index') }}">{{ __('Back') }}</a>



</div>
</x-workspace>
