<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Ensure the user is logged in
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = get_current_user_id();

if ($id > 0) {
    // We strictly check user_id so users can only delete their own tasks
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    
    $stmt->execute();
    $stmt->close();
}

// Send them right back to the To-Do list
header("Location: index.php");
exit();
?>

<?php $conn->close(); ?>