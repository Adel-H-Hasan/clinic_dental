<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>specialities</title>
</head>
<body>
    <h1>Dental specialities</h1>
    <a href="{{ route('admin.specialities.create') }}">
     Add New Speciality
    </a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
               
            </tr>
        </thead>

        <tbody>
            @forelse($specialities as $speciality)
            <tr>
                
                   <td>{{ $speciality->id }}</td>
                   <td>{{ $speciality->name }}</td>
                   <td>{{ $speciality->description ?? 'No description' }}</td>
                   <td>
                    <a href="{{ route('admin.specialities.edit',$speciality->id) }}">
                        Edit
                    </a>

                     <form action="{{ route('admin.specialities.destroy',$speciality->id)}}" method="POST">
                        @csrf
                        @method('DELETE')

                    <button type="submit" 
                    onclick="return confirm('Are you sure you want to delete this speciality?')">Delete</button>
                    </form>
                   </td>
                   
            </tr>
            @empty 
            <tr>
                <td>No specialities found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    
</body>
</html>