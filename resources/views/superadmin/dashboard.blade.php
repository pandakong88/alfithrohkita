<!DOCTYPE html>
<html>
<head>
    <title>Super Admin Dashboard</title>
</head>
<body>

    <h2>Super Admin Dashboard</h2>

    <p>Selamat datang, {{ auth()->user()->name }}</p>
    <p>Role: {{ auth()->user()->role }}</p>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit">Logout</button>
    </form>

</body>
</html>
