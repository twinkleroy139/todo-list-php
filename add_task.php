<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Protect this route - kick out anyone who isn't logged in
require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Securely get the user ID from the active session, NOT from the form
    $user_id     = get_current_user_id();
    
    // Sanitize inputs
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    if (!empty($title)) {
        // Prepare and execute the insert statement securely
        $stmt = $conn->prepare("INSERT INTO tasks (user_id, title, description, due_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $title, $description, $due_date);
        
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to the To-Do list immediately
header("Location: index.php");
exit();
?> 

<?php $conn->close(); ?>

