<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    @vite(['resources/css/chat.css'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
<div class="chart-container">
    <div class="chart-card"> 
    <h2>Login</h2>

<form method="POST" action="/login">
    @csrf

    <input type="email" name="email" placeholder="Email">
    <div class="password-group">
    <input
        type="password"
        id="password"
        name="password"
        placeholder="Password">

    <i class="bi bi-eye-slash toggle-password"
       onclick="togglePassword('password', this)"></i>
</div>
    <button type="submit">Login</button>
</form>

<a href="/register">
    Belum punya akun?
</a>
</div>

<script>
function togglePassword(id, icon) {
    const input = document.getElementById(id);

    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("bi-eye-slash", "bi-eye");
    } else {
        input.type = "password";
        icon.classList.replace("bi-eye", "bi-eye-slash");
    }
}
</script>

</body>
</html>