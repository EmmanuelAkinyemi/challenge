<?php
require 'session.php';
require 'db/Database.php';
$database = new Database();
$pdo = $database->pdo;

$userId = $_SESSION['user_id']; // Assuming the user is logged in and has a user_id in the session

// Fetch previous quiz results
$query = "SELECT score, total, submitted_at FROM results WHERE user_id = :userId ORDER BY submitted_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(['userId' => $userId]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Dashboard HTML and Styling
echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 0 auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        h1 {
            font-size: 2rem;
            color: #333;
            margin-bottom: 20px;
        }

        a {
            text-decoration: none;
            color: #007bff;
            margin-right: 20px;
            font-size: 18px;
        }

        a:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table, th, td {
            border: 1px solid #ddd;
            text-align: left;
        }

        th, td {
            padding: 12px;
            font-size: 16px;
        }

        th {
            background-color: #f1f1f1;
        }

        .score-cell {
            font-weight: bold;
        }

        .no-results {
            text-align: center;
            font-size: 18px;
            color: #666;
        }

        .actions {
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Welcome, {$_SESSION['name']}!</h1>
        
        <!-- Dashboard Links -->
        <div class="actions">
            <a href="quiz.php">Take Quiz</a> | 
            <a href="code-challenges.php">Code Challenges</a> | 
            <a href="logout.php">Logout</a>
        </div>

        <!-- Results Table -->
        <h2>Previous Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Quiz Date</th>
                    <th>Score</th>
                    <th>Total Questions</th>
                </tr>
            </thead>
            <tbody>
HTML;

// Display the results if any
if ($results) {
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td>" . date('F j, Y \a\t g:i A', strtotime($result['submitted_at'])) . "</td>";
        echo "<td class='score-cell'>" . $result['score'] . "</td>";
        echo "<td>" . $result['total'] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3' class='no-results'>No previous results found.</td></tr>";
}

echo <<<HTML
            </tbody>
        </table>
    </div>

</body>
</html>
HTML;
