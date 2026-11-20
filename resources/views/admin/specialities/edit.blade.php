<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit a speciality</title>
</head>
<body>
    <h1>Edit Speciality</h1>
    <form action="{{ route('admin.specialities.update',$speciality->id) }}" method="POST">
        @csrf
        @method('PUT')

    
<div>
    <label for="name">Speciality Name:</label>
    <input type="text" name="name" id="name" value="{{ $speciality->name }}" required>
    @error('name')
    <p style="color:red">{{ $message }}</p>
    @enderror
</div>

<br>


<div>
<label for="description">Description:</label>
<textarea name="description" id="description">{{ $speciality->description }}</textarea>
@error('description')
<p style="color:red">{{ $message }}</p>
@enderror
</div>

<br>
<button type="submit">Update Speciality</button>
<a href="{{ route('admin.specialities.index') }}">Back</a>

    </form>
</body>
</html>