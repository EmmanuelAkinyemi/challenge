<?php
require 'session.php';
echo "<h1>Welcome, {$_SESSION['name']}!</h1>";
echo "<a href='quiz.php'>Take Quiz</a> | <a href='code-challenges.php'>Code Challenges</a> | <a href='logout.php'>Logout</a>";
