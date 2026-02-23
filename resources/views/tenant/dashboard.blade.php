<!DOCTYPE html>
<html>
<head>
    <title>Tenant Dashboard</title>
</head>
<body>

    <h2>Dashboard Pondok</h2>

    <p>Selamat datang, {{ auth()->user()->name }}</p>
    <p>Role: {{ auth()->user()->role }}</p>

    @if(auth()->user()->pondok)
        <p>Pondok: {{ auth()->user()->pondok->name }}</p>
    @endif

    <form method="POST" action="/logout">
        @csrf
        <button type="submit">Logout</button>
    </form>

</body>
</html>
