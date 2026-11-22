<x-workspace title="{{ __('specialities') }}">
<div class="panel">

    <a href="{{ route('admin.specialities.create') }}">
     {{ __('Add New Speciality') }}
    </a>

    <div class="workspace-table" role="region" aria-label="{{ __('Records') }}" tabindex="0"><table>
        <thead>
            <tr>
                <th>{{ __('ID') }}</th>
                <th>{{ __('Name') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Actions') }}</th>

            </tr>
        </thead>

        <tbody>
            @forelse($specialities as $speciality)
            <tr>

                   <td>{{ $speciality->id }}</td>
                   <td>{{ $speciality->name }}</td>
                   <td>{{ $speciality->description ?? __('No description') }}</td>
                   <td>
                    <a href="{{ route('admin.specialities.edit',$speciality->id) }}">
                        {{ __('Edit') }}
                    </a>

                     <form action="{{ route('admin.specialities.destroy',$speciality->id)}}" method="POST">
                        @csrf
                        @method('DELETE')

                    <button type="submit"
                    onclick="return confirm(@js(__('Are you sure you want to delete this speciality?')))">{{ __('Delete') }}</button>
                    </form>
                   </td>

            </tr>
            @empty
            <tr>
                <td>{{ __('No specialities found') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table></div>


</div>
</x-workspace>
