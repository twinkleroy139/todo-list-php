<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Protect this route
require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the current user's ID
    $user_id = get_current_user_id();
    
    // Grab the note content
    $content = trim($_POST['content'] ?? '');

    if (!empty($content)) {
        // Insert the note linked to this specific user
        $stmt = $conn->prepare("INSERT INTO notepad (user_id, content) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $content);
        
        $stmt->execute();
        $stmt->close();
    }
}

// Redirect back to the Notepad immediately
header("Location: notepad.php");
exit();
?>

<?php $conn->close(); ?>