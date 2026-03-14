<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/db.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$task = null;
$error = '';
$success = false;

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();

    if (!$task) {
        $error = "Task not found.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $task) {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = $_POST['due_date'] ?: null;

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ?");
        $stmt->bind_param("sssi", $title, $description, $due_date, $id);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Task</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body { background: #f1f3f5; padding: 3rem 1rem; }
        .card { max-width: 600px; margin: auto; border-radius: 12px; box-shadow: 0 6px 24px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Task</h4>
        </div>

        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> Task updated.
                    <a href="index.php" class="alert-link ms-2">Back to list →</a>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($task && !$success): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($task['title']) ?>" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($task['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= htmlspecialchars($task['due_date'] ?? '') ?>">
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i> Update Task
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>