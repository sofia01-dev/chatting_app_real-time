<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    @vite(['resources/css/chat.css'])
</head>

<body>
<div class="chart-container">
<div class="chart-card"> 
    <h2>Register</h2>
<form method="POST" action="/register">
    @csrf

    <input type="text" name="name" placeholder="Nama"><br><br>

    <input type="email" name="email" placeholder="Email"><br><br>

    <input type="password" name="password" placeholder="Password"><br><br>

    <input type="password" name="password_confirmation" placeholder="Konfirmasi Password"><br><br>

    <button type="submit">Register</button>
</form>

<a href="/login">Sudah punya akun? Login di sini.</a>
</div>

</body>
</html>