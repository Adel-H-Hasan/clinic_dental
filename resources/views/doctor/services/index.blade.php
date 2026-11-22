<x-workspace title="{{ __('My services') }}">
<div class="panel">
    <div class="view-content">
        <article class="panel">

            @if ($appointmentTypes->isEmpty())
                <p>{{ __('No appointment types are available to add.') }}</p>
            @else
                <form action="{{ route('doctor.services.store') }}" method="POST" class="form-stack">
                    @csrf

                    <label for="appointment_type_id">{{ __('Appointment type') }}</label>
                    <select id="appointment_type_id" name="appointment_type_id" required>
                        <option value="">{{ __('Choose a type') }}</option>

                        @foreach ($appointmentTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('appointment_type_id') == $type->id)>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="duration_minutes">{{ __('Duration in minutes') }}</label>
                    <input id="duration_minutes" type="number" name="duration_minutes"
                        min="1" value="{{ old('duration_minutes') }}" required>

                    <label for="price">{{ __('Price') }}</label>
                    <input id="price" type="number" name="price"
                        min="0" max="999999.99" step="0.01" value="{{ old('price') }}" required>

                    <button type="submit" class="btn">{{ __('Add service') }}</button>
                </form>
            @endif

            @if ($services->isEmpty())
                <p>{{ __('You have no services yet.') }}</p>
            @else
                <div class="workspace-table" role="region" aria-label="{{ __('Records') }}" tabindex="0"><table>
                    <thead>
                        <tr>
                            <th>{{ __('Service') }}</th>
                            <th>{{ __('Duration') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->appointment_type->name }}</td>
                                <td>{{ $service->duration_minutes }} {{ __('min') }}</td>
                                <td>{{ $service->price }}</td>
                                <td>
                                    <a href="{{ route('doctor.services.edit', $service->id) }}">{{ __('Edit') }}</a>

                                    <form action="{{ route('doctor.services.destroy', $service->id) }}"
                                          method="POST"
                                          onsubmit="return confirm(@js(__('Remove this service from your list?')))">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn outline">{{ __('Delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table></div>
            @endif
        </article>
    </div>

</div>
</x-workspace>
