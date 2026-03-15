<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Ensure user is logged in
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = get_current_user_id();
$task = null;
$error = '';
$success = false;

// 1. Fetch the existing task securely
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();

    if (!$task) {
        $error = "Task not found or you don't have permission to edit it.";
    }
}

// 2. Handle form submission to update the task
if ($_SERVER["REQUEST_METHOD"] === "POST" && $task) {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date    = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    if (empty($title)) {
        $error = "Title is required.";
    } else {
        $stmt = $conn->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sssii", $title, $description, $due_date, $id, $user_id);

        if ($stmt->execute()) {
            $success = true;
            // Update the local $task variable so the form shows the new data immediately
            $task['title'] = $title;
            $task['description'] = $description;
            $task['due_date'] = $due_date;
        } else {
            $error = "Update failed: " . $conn->error;
        }
        $stmt->close();
    }
}

// Include header (opens the container)
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm border-0 rounded-3 mt-4">
            <div class="card-header bg-warning text-dark py-3">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Edit Task</h4>
            </div>

            <div class="card-body p-4">
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <strong>Success!</strong> Your task has been updated.
                            <br><a href="index.php" class="alert-link mt-1 d-inline-block">← Back to To-Do List</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
                    <a href="index.php" class="btn btn-outline-secondary mt-2">← Back to To-Do List</a>
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

                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Cancel</a>
                            <button type="submit" class="btn btn-warning fw-bold">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>