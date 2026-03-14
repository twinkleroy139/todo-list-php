<?php
include("config/db.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE tasks SET status = 'done' WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Update failed: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Invalid task ID.";
}

$conn->close();
?>