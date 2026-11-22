<x-workspace title="{{ __('Edit a speciality') }}">
<div class="panel">

    <form action="{{ route('admin.specialities.update',$speciality->id) }}" method="POST">
        @csrf
        @method('PUT')


<div>
    <label for="name">{{ __('Speciality Name:') }}</label>
    <input type="text" name="name" id="name" value="{{ $speciality->name }}" required>
</div>

<br>


<div>
<label for="description">{{ __('Description:') }}</label>
<textarea name="description" id="description">{{ $speciality->description }}</textarea>
</div>

<br>
<button type="submit">{{ __('Update Speciality') }}</button>
<a href="{{ route('admin.specialities.index') }}">{{ __('Back') }}</a>

    </form>

</div>
</x-workspace>
