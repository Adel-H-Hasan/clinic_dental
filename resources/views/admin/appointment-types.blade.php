<x-workspace title="{{ __('Appointment types') }}">
<section><h2>{{ __('Add appointment type') }}</h2>
<form method="post" action="{{ route('admin.appointment-types.store') }}">@csrf
<label for="name">{{ __('Name') }}</label><input id="name" name="name" maxlength="255" required value="{{ old('name') }}">
<label for="description">{{ __('Description') }}</label><textarea id="description" name="description">{{ old('description') }}</textarea><button>{{ __('Add appointment type') }}</button>
</form></section>
@forelse($types as $type)
<section><h2>{{ $type->name }}</h2><p>{{ $type->description }}</p>
<details><summary>{{ __('Edit') }} {{ $type->name }}</summary><form method="post" action="{{ route('admin.appointment-types.update', $type->id) }}">@csrf @method('PUT')
<label for="name-{{ $type->id }}">{{ __('Name') }}</label><input id="name-{{ $type->id }}" name="name" value="{{ $type->name }}" maxlength="255" required>
<label for="description-{{ $type->id }}">{{ __('Description') }}</label><textarea id="description-{{ $type->id }}" name="description">{{ $type->description }}</textarea><button>{{ __('Save changes') }}</button></form></details>
<details><summary>{{ __('Delete') }} {{ $type->name }}</summary><p>{{ __('Types assigned to doctors cannot be deleted.') }}</p><form method="post" action="{{ route('admin.appointment-types.destroy', $type->id) }}">@csrf @method('DELETE')<button>{{ __('Confirm deletion') }}</button></form></details>
</section>
@empty<p>{{ __('No appointment types have been created.') }}</p>@endforelse
<div class="pagination">{{ $types->links() }}</div>
</x-workspace>
