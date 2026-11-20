<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Specialities</title>
</head>
<body>
    <h1>Add a new Speciality</h1>
    <form action="{{ route('admin.specialities.store')}}" method="POST">
        @csrf
     
    <label for="name">Speciality Name</label>
    <input type="text" name="name" id="name" placeholder="Enter a speciality name" 
                value="{{ old('name') }}" required><br><br>
    @error('name')
    <p style="color:red">{{ $message }}</p>
    @enderror

    <label for="description">Speciality Description</label>
    <textarea name="description" id="description" placeholder="Enter a description"
    value="{{ old('description') }}"></textarea><br><br>

     @error('description')
    <p style="color:red">{{ $message }}</p>
    @enderror

    <button type="submit">Submit</button>
    </form>

    <a href="{{ route('admin.specialities.index') }}">Back</a>
    
    
</body>
</html>