
```markdown
# PHP Quiz App 🧠

A simple timed quiz application built with PHP and MySQL. Users can answer multiple-choice questions, and their scores are saved to a database.

## 🚀 Features

- 20 multiple-choice PHP questions
- Countdown timer (15 minutes)
- Auto-submit on timeout
- Stores quiz results in a MySQL database
- Displays user score after submission
- User session tracking for result association

## 🛠️ Technologies Used

- PHP 8+
- MySQL
- HTML/CSS
- JavaScript (for countdown timer)
- PDO (for secure database interaction)

## 📁 Project Structure

```

/quiz\_app
├── db/
│   └── Database.php          # Database connection class using PDO
├── session.php              # Session handler
├── quiz.php                 # Main quiz logic (form + grading)
├── index.php                # (Optional) Entry point or redirect
└── README.md

````

## ⚙️ Setup Instructions

1. 📥 Clone the repository:
   ```bash
   git clone https://github.com/your-username/php-quiz-app.git
   cd php-quiz-app
````

2. 🧾 Set up your MySQL database:

   Create a database named `quiz_app`, and run this SQL to create the `results` table:

   ```sql
   CREATE TABLE results (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       score INT NOT NULL,
       total INT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

3. ⚙️ Configure Database:

   Edit `db/Database.php` and update the credentials if needed:

   ```php
   private $host = 'localhost';
   private $db = 'quiz_app';
   private $user = 'root';
   private $pass = '';
   ```

4. 🧪 Start your development server:

   Using XAMPP or the built-in PHP server:

   ```bash
   php -S localhost:8000
   ```

   Then visit [http://localhost:8000/quiz.php](http://localhost:8000/quiz.php) in your browser.

## 🔐 Session Handling

Make sure `session_start()` is included in `session.php` and is required at the top of `quiz.php` to track user identity.

## 👤 Authors

* [Your Name or Team Name](https://github.com/your-username)

## 📝 License

This project is open source under the MIT License.

```

Let me know if you want this version tailored for deployment or to include user registration/login features.
```
