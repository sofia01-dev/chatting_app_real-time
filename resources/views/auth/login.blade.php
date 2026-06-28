<h2>Login</h2>

<form method="POST" action="/login">
    @csrf

    <input type="email" name="email" placeholder="Email">

    <br><br>

    <input type="password" name="password" placeholder="Password">

    <br><br>

    <button type="submit">
        Login
    </button>
</form>

<a href="/register">
    Belum punya akun?
</a>