<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Ensure user is logged in
require_login();

$user_id = get_current_user_id();
$user = null;
$msg = '';
$msgType = '';

// 1. Handle Form Submissions (Change Password or Delete Account)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    
    // --- CHANGE PASSWORD LOGIC ---
    if ($_POST['action'] === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass     = $_POST['new_password'] ?? '';
        $confirm_pass = $_POST['confirm_password'] ?? '';

        // Verify current password first
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $hash = $stmt->get_result()->fetch_assoc()['password'];
        $stmt->close();

        if (password_verify($current_pass, $hash)) {
            if ($new_pass === $confirm_pass && strlen($new_pass) >= 6) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $new_hash, $user_id);
                
                if ($stmt->execute()) {
                    $msg = "Password updated successfully!";
                    $msgType = "success";
                } else {
                    $msg = "Database error. Please try again.";
                    $msgType = "danger";
                }
                $stmt->close();
            } else {
                $msg = "New passwords do not match or are less than 6 characters.";
                $msgType = "danger";
            }
        } else {
            $msg = "Incorrect current password.";
            $msgType = "danger";
        }
    }

    // --- DELETE ACCOUNT LOGIC ---
    if ($_POST['action'] === 'delete_account') {
        // Because of our FOREIGN KEY ... ON DELETE CASCADE database setup, 
        // deleting the user automatically deletes all their tasks and notes!
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // Destroy session and redirect to register page
        session_destroy();
        header("Location: register.php");
        exit();
    }
}

// 2. Fetch User Info & Statistics for the Dashboard
// Get User Info
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get Task Count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$task_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get Note Count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM notepad WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$note_count = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Include header (opens the container)
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <?php if ($msg): ?>
            <div class="alert alert-<?= $msgType ?> alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 mb-4 overflow-hidden">
            <div class="bg-primary text-white p-4 d-flex align-items-center">
                <div class="bg-white text-primary rounded-circle d-flex justify-content-center align-items-center me-3 shadow" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 class="mb-0 fw-bold"><?= htmlspecialchars($user['username']) ?></h3>
                    <p class="mb-0 opacity-75"><i class="fas fa-envelope me-1"></i> <?= htmlspecialchars($user['email']) ?></p>
                </div>
            </div>
            <div class="card-body bg-light">
                <p class="text-muted mb-0 small">
                    <i class="fas fa-calendar-alt me-1"></i> Member since <?= date('F j, Y', strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <h6 class="text-muted fw-bold text-uppercase mb-1">Total Tasks</h6>
                        <h2 class="mb-0 text-dark"><?= $task_count ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100 border-start border-4 border-success">
                    <div class="card-body">
                        <h6 class="text-muted fw-bold text-uppercase mb-1">Total Notes</h6>
                        <h2 class="mb-0 text-dark"><?= $note_count ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 text-dark"><i class="fas fa-lock me-2 text-muted"></i> Security & Password</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="profile.php">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <div class="form-text">Minimum 6 characters.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary mt-2">Update Password</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm border-0 border-danger mb-5">
            <div class="card-header bg-danger text-white py-3">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Danger Zone</h5>
            </div>
            <div class="card-body p-4 bg-light">
                <p class="text-danger fw-bold mb-1">Delete Account</p>
                <p class="text-muted small mb-3">Once you delete your account, there is no going back. All of your saved tasks and notes will be permanently erased from the database.</p>
                
                <form method="POST" action="profile.php" onsubmit="return confirm('WARNING: Are you absolutely sure you want to delete your account? This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete_account">
                    <button type="submit" class="btn btn-outline-danger border-2 fw-bold">
                        <i class="fas fa-trash-alt me-1"></i> Permanently Delete My Account
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>