<?php
require 'classes/User.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $loggedIn = $user->login($_POST['email'], $_POST['password']);
    echo $loggedIn ? header('Location: dashboard.php') : "Invalid credentials.";
}
?>
<form method="post">
    <input type="email" name="email" required placeholder="Email"><br><br>
    <input type="password" name="password" required placeholder="Password"><br><br>
    <button type="submit">Login</button>    <br><br>
    <a href="register.php">Don't have an account? Register here.</a><br><br>
    
</form>