<?php
require 'session.php';
require 'db/Database.php';
$database = new Database();
$pdo = $database->pdo;

class Quiz
{
    private array $questions = [];

    public function __construct()
    {
        $this->questions = [
            ["What does PHP stand for?", ["Personal Home Page", "PHP: Hypertext Preprocessor", "Private Home Page", "Preprocessor Home Page"], 1],
            ["Which symbol is used to declare a variable in PHP?", ["%", "$", "#", "&"], 1],
            ["Which function is used to output text in PHP?", ["echo()", "print()", "say()", "write()"], 0],
            ["PHP files have a default extension of?", [".php", ".html", ".js", ".css"], 0],
            ["How do you start a PHP block?", ["<?php", "<php>", "<?", "php:"], 0],
            ["Which global variable holds form data sent via POST?", ["\$_POST", "\$_GET", "\$_FORM", "\$_DATA"], 0],
            ["Which function returns the length of a string?", ["str_length()", "strlen()", "count()", "strcount()"], 1],
            ["How do you write comments in PHP?", ["// comment", "# comment", "/* comment */", "All of the above"], 3],
            ["Which method is used to connect to MySQL in OOP?", ["mysql_connect()", "mysqli_connect()", "PDO", "db_connect()"], 2],
            ["Which error control operator suppresses errors in PHP?", ["@", "#", "!", "$"], 0],
            ["Which function includes a file only once?", ["include()", "require()", "require_once()", "include_once()"], 2],
            ["How do you create a constant in PHP?", ["define()", "const", "let", "constant()"], 0],
            ["Which loop executes at least once?", ["for", "while", "do...while", "foreach"], 2],
            ["What is the correct way to create a class?", ["class MyClass {}", "MyClass class {}", "new class MyClass {}", "class = MyClass"], 0],
            ["Which keyword is used to inherit a class?", ["inherits", "extends", "instanceof", "implement"], 1],
            ["What does isset() do?", ["Checks if a variable is set", "Deletes a variable", "Returns variable value", "Initializes a variable"], 0],
            ["What will count(\$arr) return?", ["Sum of values", "Array length", "True/False", "Error"], 1],
            ["Which function is used to redirect in PHP?", ["header()", "redirect()", "sendLocation()", "goto()"], 0],
            ["How do you check if a file exists?", ["is_file()", "exists()", "file_exists()", "check_file()"], 2],
            ["What does json_encode() do?", ["Parses JSON", "Converts array to JSON", "Sends HTTP header", "Creates HTML"], 1],
        ];
    }

    public function displayForm(): void
    {
        echo '<div id="timer" style="font-size: 20px; font-weight: bold;">Time left: 15:00</div>';

        echo '<form id="quizForm" method="post">';
        foreach ($this->questions as $index => $q) {
            $question = htmlspecialchars($q[0]);
            echo "<p><strong>Q" . ($index + 1) . ". {$question}</strong></p>";
            foreach ($q[1] as $k => $option) {
                $safeOption = htmlspecialchars($option);
                echo "<label><input type='radio' name='q$index' value='$k'> $safeOption</label><br>";
            }
        }
        echo '<br><button type="submit">Submit Quiz</button></form>';

        // JavaScript countdown
        echo <<<JS
<script>
    let timeLeft = 15 * 60; // 15 minutes in seconds
    const timerDisplay = document.getElementById('timer');
    const form = document.getElementById('quizForm');

    function updateTimer() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;

        // Color logic
        if (timeLeft > 10 * 60) {
            timerDisplay.style.color = 'green';
        } else if (timeLeft > 5 * 60) {
            timerDisplay.style.color = 'orange';
        } else {
            timerDisplay.style.color = 'red';
        }

        timerDisplay.textContent = "Time left: " + 
            (minutes < 10 ? "0" + minutes : minutes) + ":" + 
            (seconds < 10 ? "0" + seconds : seconds);

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            form.submit();
        } else {
            timeLeft--;
        }
    }

    updateTimer(); // Initialize immediately
    const timerInterval = setInterval(updateTimer, 1000);
</script>
JS;
    }

    // public function gradeQuiz(array $postData): void
    // {
    //     $score = 0;
    //     foreach ($this->questions as $index => $q) {
    //         $key = 'q' . $index;
    //         if (isset($postData[$key]) && $postData[$key] == $q[2]) {
    //             $score++;
    //         }
    //     }
    //     echo "<h2>You scored $score / " . count($this->questions) . "</h2>";
    // }

    public function gradeQuiz(array $postData): void
    {
        global $pdo; // Use the global PDO instance

        $score = 0;
        foreach ($this->questions as $index => $q) {
            $key = 'q' . $index;
            if (isset($postData[$key]) && $postData[$key] == $q[2]) {
                $score++;
            }
        }

        $total = count($this->questions);
        $userId = $_SESSION['user_id'] ?? 0; // Optional: assumes user is logged in

        // Insert result into DB
        $stmt = $pdo->prepare("INSERT INTO results (user_id, score, total) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $score, $total]);

        echo "<h2>You scored $score / $total</h2>";
    }
}

$quiz = new Quiz();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz->gradeQuiz($_POST);
} else {
    $quiz->displayForm();
}
