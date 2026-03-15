<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Ensure user is logged in
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = get_current_user_id();
$note = null;
$error = '';
$success = false;

// 1. Fetch the existing note securely
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM notepad WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $note = $result->fetch_assoc();
    $stmt->close();

    if (!$note) {
        $error = "Note not found or you don't have permission to edit it.";
    }
}

// 2. Handle form submission to update the note
if ($_SERVER["REQUEST_METHOD"] === "POST" && $note) {
    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = "Note content cannot be empty.";
    } else {
        $stmt = $conn->prepare("UPDATE notepad SET content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $content, $id, $user_id);

        if ($stmt->execute()) {
            $success = true;
            $note['content'] = $content; // Update local variable for display
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
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm border-0 rounded-3 mt-4">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0"><i class="fas fa-pen me-2"></i> Edit Note</h4>
            </div>

            <div class="card-body p-4">
                <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x me-3"></i>
                        <div>
                            <strong>Success!</strong> Your note has been updated.
                            <br><a href="notepad.php" class="alert-link mt-1 d-inline-block">← Back to Notepad</a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
                    <a href="notepad.php" class="btn btn-outline-secondary mt-2">← Back to Notepad</a>
                <?php endif; ?>

                <?php if ($note && !$success): ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-muted">Last updated: <?= date('M d, Y g:i A', strtotime($note['updated_at'])) ?></label>
                            <textarea name="content" class="form-control" rows="10" required autofocus><?= htmlspecialchars($note['content']) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="notepad.php" class="btn btn-light border"><i class="fas fa-arrow-left me-1"></i> Cancel</a>
                            <button type="submit" class="btn btn-primary fw-bold">
                                <i class="fas fa-save me-1"></i> Save Note
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