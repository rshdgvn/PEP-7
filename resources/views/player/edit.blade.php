<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Player Profile</title>
</head>
<body>
    <div class="form-container">
        <h1>Edit Profile</h1>
        <form action="{{ route('players.update', $player->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <label for="profile_picture">Profile Picture:</label>
            <img src="{{ $player->profile_picture }}" width="100" height="100" >
            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" value="{{ $player->profile_picture }}">

            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="{{ $player->username }}" required>

            <label for="year_level">Year Level:</label>
            <input type="number" name="year_level" id="year_level" value="{{ $player->year_level }}" readonly>

            <label for="section">Section:</label>
            <input type="text" name="section" id="section" value="{{ $player->section }}" required>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
