<?php
require 'classes/User.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = new User();
    $registered = $user->register($_POST['name'], $_POST['email'], $_POST['password']);
    echo $registered ? header('Location: login.php') : "Email already exists.";
}
?>
<form method="post">
    <input type="text" name="name" required placeholder="Name"><br><br>
    <input type="email" name="email" required placeholder="Email">  <br><br>
    <input type="password" name="password" required placeholder="Password"> <br><br>
    <button type="submit">Register</button> <br><br>
    <a href="login.php">Already have an account? Login here.</a>


</form>