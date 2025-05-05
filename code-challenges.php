<?php
require 'session.php';

// Simple in-memory challenge data (replace with DB in production)
$challenges = [
    1 => [
        'title' => 'Registration Form',
        'description' => 'Create a registration form that validates user input',
        'requirements' => [
            'Fields for name, email, and password',
            'Validate all fields are filled',
            'Validate email format',
            'Display welcome message on success'
        ],
        'difficulty' => 3,
        'starter_code' => '<?php
// Registration form code here

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validation and processing code
    $name = $_POST["name"] ?? "";
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    
    // Add your validation logic here
    
    if (/* validation passes */ false) {
        echo "Welcome, " . htmlspecialchars($name) . "!";
    } else {
        echo "Please fix the errors in the form";
    }
}
?>'
    ],
    2 => [
        'title' => 'PHP Calculator',
        'description' => 'Build a PHP-based calculator',
        'requirements' => [
            'Takes two numbers and an operation (+, -, *, /)',
            'Validate inputs are numbers',
            'Handle division by zero',
            'Display the calculation result'
        ],
        'difficulty' => 2,
        'starter_code' => '<?php
// Calculator code here

$num1 = $_POST["num1"] ?? 0;
$num2 = $_POST["num2"] ?? 0;
$operation = $_POST["operation"] ?? "+";
$result = 0;

// Add your calculation logic here

echo "Result: " . $result;
?>'
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_code'])) {
    $code = $_POST['code'] ?? '';
    $challenge_id = (int)($_POST['challenge_id'] ?? 0);
    
    // Validate the code
    $validation = validateCode($code);
    
    if ($validation['valid']) {
        // Process and grade the submission
        $result = gradeSubmission($code, $challenge_id);
        $_SESSION['last_result'] = $result;
        $_SESSION['last_code'] = $code;
        header("Location: ".$_SERVER['PHP_SELF']."?challenge=$challenge_id");
        exit();
    } else {
        $_SESSION['errors'] = $validation['errors'];
        $_SESSION['last_code'] = $code;
        header("Location: ".$_SERVER['PHP_SELF']."?challenge=$challenge_id");
        exit();
    }
}

// Function to validate PHP code
function validateCode($code) {
    $errors = [];
    
    // Check for empty code
    if (empty(trim($code))) {
        $errors[] = "Code cannot be empty";
        return ['valid' => false, 'errors' => $errors];
    }
    
    // Check for disallowed functions
    $disallowed = ['exec', 'shell_exec', 'system', 'eval', 'passthru'];
    foreach ($disallowed as $function) {
        if (strpos($code, $function) !== false) {
            $errors[] = "Use of $function() is not allowed";
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

// Function to grade submission
function gradeSubmission($code, $challenge_id) {
    // Simple grading - in production you would have proper test cases
    try {
        ob_start();
        // Wrap code in a temporary function to prevent global scope pollution
        $result = eval('return function() { ?>'.$code.' };');
        if ($result instanceof Closure) {
            $result();
        }
        $output = ob_get_clean();
        
        // Check for expected patterns in output
        if ($challenge_id == 1 && strpos($output, 'Welcome') !== false) {
            return ['passed' => true, 'score' => 100, 'feedback' => 'Form validation works!'];
        } elseif ($challenge_id == 2 && strpos($output, 'Result:') !== false) {
            return ['passed' => true, 'score' => 100, 'feedback' => 'Calculator works!'];
        } else {
            return ['passed' => false, 'score' => 50, 'feedback' => 'Some requirements missing'];
        }
    } catch (Throwable $e) {
        return ['passed' => false, 'score' => 0, 'feedback' => 'Error: ' . $e->getMessage()];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Code Challenges</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
    <style>
        /* [Previous CSS remains exactly the same] */
    </style>
</head>
<body>
    <h1>PHP Code Challenges</h1>
    
    <?php foreach ($challenges as $id => $challenge): ?>
    <div class="challenge">
        <div class="challenge-header">
            <span>Challenge <?= $id ?>: <?= htmlspecialchars($challenge['title']) ?></span>
            <span class="difficulty">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <span class="star"><?= $i < $challenge['difficulty'] ? '★' : '☆' ?></span>
                <?php endfor; ?>
            </span>
        </div>
        <div class="challenge-content">
            <div class="requirements">
                <h3>Description:</h3>
                <p><?= htmlspecialchars($challenge['description']) ?></p>
                
                <h3>Requirements:</h3>
                <ul>
                    <?php foreach ($challenge['requirements'] as $req): ?>
                        <li><?= htmlspecialchars($req) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <form method="post" action="">
                <input type="hidden" name="challenge_id" value="<?= $id ?>">
                <div class="editor-container">
                    <textarea id="code<?= $id ?>" name="code"><?= 
                        isset($_SESSION['last_code']) && (!isset($_POST['challenge_id']) || $_POST['challenge_id'] == $id)
                            ? htmlspecialchars($_SESSION['last_code']) 
                            : htmlspecialchars($challenge['starter_code'])
                    ?></textarea>
                </div>
                
                <div class="button-group">
                    <button type="submit" name="submit_code">Submit Code</button>
                    <button type="button" class="format-btn" data-editor="code<?= $id ?>">Format Code</button>
                    <button type="button" class="run-btn" data-editor="code<?= $id ?>">Run Code</button>
                </div>
                
                <div class="note">Note: Your code will be validated before execution.</div>
                
                <?php if (isset($_SESSION['last_result']) && (!isset($_POST['challenge_id']) || $_POST['challenge_id'] == $id)): ?>
                    <div class="result <?= $_SESSION['last_result']['passed'] ? 'success' : 'error' ?>">
                        <h3>Result:</h3>
                        <p>Score: <?= $_SESSION['last_result']['score'] ?>%</p>
                        <p><?= htmlspecialchars($_SESSION['last_result']['feedback']) ?></p>
                    </div>
                    <?php unset($_SESSION['last_result']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['errors']) && (!isset($_POST['challenge_id']) || $_POST['challenge_id'] == $id)): ?>
                    <div class="result error">
                        <h3>Validation Errors:</h3>
                        <ul>
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/php/php.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
    <script>
        // Initialize CodeMirror for all textareas
        const editors = {};
        
        document.querySelectorAll('textarea[id^="code"]').forEach(textarea => {
            const editor = CodeMirror.fromTextArea(textarea, {
                lineNumbers: true,
                mode: "application/x-httpd-php",
                theme: "dracula",
                indentUnit: 4,
                lineWrapping: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                extraKeys: {
                    "Tab": function(cm) {
                        if (cm.somethingSelected()) {
                            cm.indentSelection("add");
                        } else {
                            const spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
                            cm.replaceSelection(spaces, "end", "+input");
                        }
                    },
                    "Ctrl-Enter": function() { 
                        this.form.submit();
                    }
                }
            });
            
            // Store editor reference
            editors[textarea.id] = editor;
            
            // Set initial height and focus
            editor.setSize(null, '400px');
            editor.refresh();
        });

        // Format code function
        function formatCode(editor) {
            const code = editor.getValue();
            let indent = 0;
            let inPhp = false;
            const lines = code.split('\n');
            let formatted = [];
            
            lines.forEach(line => {
                // Skip empty lines
                if (line.trim() === '') {
                    formatted.push('');
                    return;
                }
                
                // Check for PHP open/close tags
                if (line.includes('<\\?php') || line.includes('<\\?=')) {
                    inPhp = true;
                } else if (line.includes('?>')) {
                    inPhp = false;
                }
                
                // Handle indentation
                if (inPhp) {
                    const trimmed = line.trim();
                    
                    // Decrease indent after closing braces
                    if (trimmed.endsWith('}') || trimmed.endsWith('};')) {
                        indent = Math.max(0, indent - 1);
                    }
                    
                    // Add current indentation
                    formatted.push('    '.repeat(indent) + trimmed);
                    
                    // Increase indent after opening braces
                    if (trimmed.endsWith('{') || trimmed.startsWith('case') || trimmed.startsWith('default:')) {
                        indent++;
                    }
                } else {
                    formatted.push(line);
                }
            });
            
            editor.setValue(formatted.join('\n'));
        }

        // Format button click handlers
        document.querySelectorAll('.format-btn').forEach(button => {
            button.addEventListener('click', function() {
                const editorId = this.getAttribute('data-editor');
                formatCode(editors[editorId]);
            });
        });

        // Run button functionality
        document.querySelectorAll('.run-btn').forEach(button => {
            button.addEventListener('click', function() {
                const editorId = this.getAttribute('data-editor');
                const form = this.closest('form');
                const challengeId = form.querySelector('input[name="challenge_id"]').value;
                const code = editors[editorId].getValue();
                
                // Create a preview iframe instead of new window
                const preview = document.createElement('div');
                preview.style.position = 'fixed';
                preview.style.top = '0';
                preview.style.left = '0';
                preview.style.width = '100%';
                preview.style.height = '100%';
                preview.style.backgroundColor = 'white';
                preview.style.zIndex = '1000';
                preview.style.overflow = 'auto';
                preview.style.padding = '20px';
                
                const closeBtn = document.createElement('button');
                closeBtn.textContent = 'Close Preview';
                closeBtn.style.position = 'fixed';
                closeBtn.style.top = '10px';
                closeBtn.style.right = '10px';
                closeBtn.style.zIndex = '1001';
                closeBtn.addEventListener('click', () => document.body.removeChild(preview));
                
                const iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = 'calc(100% - 40px)';
                iframe.style.border = 'none';
                iframe.style.marginTop = '40px';
                
                preview.appendChild(closeBtn);
                preview.appendChild(iframe);
                document.body.appendChild(preview);
                
                // Create form to submit code to iframe
                const formHtml = `
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Code Preview</title>
                        <style>
                            body { font-family: Arial; padding: 20px; }
                            pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
                        </style>
                    </head>
                    <body>
                        <h2>Code Output Preview</h2>
                        <pre><?php echo htmlspecialchars(${'code'}); ?></pre>
                        <h3>Output:</h3>
                        <div style="border:1px solid #ddd; padding:15px; border-radius:5px;">
                            <?php 
                                try {
                                    ob_start();
                                    eval('?>' . ${'code'});
                                    $output = ob_get_clean();
                                    echo nl2br(htmlspecialchars($output));
                                } catch (Throwable $e) {
                                    echo "Error: " . htmlspecialchars($e->getMessage());
                                }
                            ?>
                        </div>
                    </body>
                    </html>
                `;
                
                iframe.contentDocument.open();
                iframe.contentDocument.write(formHtml.replace('${code}', code));
                iframe.contentDocument.close();
            });
        });

        // Toggle challenge content
        const headers = document.querySelectorAll('.challenge-header');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const content = header.nextElementSibling;
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
                
                // Refresh editor when made visible
                if (content.style.display === 'block') {
                    const editorId = content.querySelector('textarea').id;
                    setTimeout(() => {
                        editors[editorId].refresh();
                        editors[editorId].focus();
                    }, 100);
                }
            });
        });

        // Auto-open the challenge if specified in URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const challengeId = urlParams.get('challenge');
            
            if (challengeId) {
                const challenge = document.querySelector(`input[name="challenge_id"][value="${challengeId}"]`);
                if (challenge) {
                    const content = challenge.closest('.challenge-content');
                    content.style.display = 'block';
                    
                    // Refresh the editor
                    const editorId = content.querySelector('textarea').id;
                    setTimeout(() => {
                        editors[editorId].refresh();
                        editors[editorId].focus();
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>