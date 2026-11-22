<x-workspace title="{{ __('Doctors List') }}">
<div class="panel">
    <div class="view-content">


        @if (session('status'))
            <p role="status">{{ __(session('status')) }}</p>
        @endif

        <p>
            <a href="{{ route('admin.doctors.create') }}">{{ __('Add doctor') }}</a>
        </p>

        @if ($doctors->isEmpty())
            <p>{{ __('No active doctor profiles to display.') }}</p>
        @else
            <div class="workspace-table" role="region" aria-label="{{ __('Records') }}" tabindex="0"><table>
                <thead>
                    <tr>
                        <th scope="col">{{ __('Full name') }}</th>
                        <th scope="col">{{ __('Email') }}</th>
                        <th scope="col">{{ __('Phone') }}</th>
                        <th scope="col">{{ __('Speciality') }}</th>
                        <th scope="col">{{ __('License number') }}</th>
                        <th scope="col">{{ __('Consultation fee') }}</th>
                        <th scope="col">{{ __('Profile status') }}</th>
                        <th scope="col">{{ __('actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($doctors as $doctor)
                        <tr>
                            <td>
                                <a href="{{ route ('admin.doctors.show', $doctor->id) }}">
                                    {{ $doctor->user?->name ?? __('Account unavailable') }}
                                </a>
                            </td>
                            <td>{{ $doctor->user?->email ?? '—' }}</td>
                            <td>{{ $doctor->user?->phone ?? '—' }}</td>
                            <td>{{ $doctor->speciality->name }}</td>
                            <td>{{ $doctor->license_number }}</td>
                            <td>{{ number_format($doctor->consultation_fee, 2) }}</td>
                            <td>{{ __($doctor->is_active ? 'Active' : 'Inactive') }}</td>
                            <td><a href="{{ route('admin.doctors.edit', $doctor->id) }}">{{ __('Edit') }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        @endif
    </div>

</div>
</x-workspace>
