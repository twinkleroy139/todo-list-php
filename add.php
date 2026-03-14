<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/db.php");

$success = false;
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = $_POST['due_date'] ?: null;

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tasks (title, description, due_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $due_date);

        if ($stmt->execute()) {
            $success = true;
            // header("Location: index.php"); // uncomment later if you want instant redirect
        } else {
            $error = "Database error: " . $stmt->error;
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
    <title>Add New Task</title>
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
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Create New Task</h4>
        </div>

        <div class="card-body">
            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        <strong>Success!</strong> Task added.
                        <a href="index.php" class="alert-link ms-2">View all tasks →</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>