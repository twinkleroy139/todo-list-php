<?php
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Redirect to To-Do List
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}

// Include header (which opens the container)
require_once 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow border-0 rounded-3">
            <div class="card-header bg-dark text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Welcome Back</h4>
            </div>
            <div class="card-body p-4">
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between">
                            <label class="form-label fw-bold">Password</label>
                            <a href="#" class="text-muted small text-decoration-none">Forgot Password?</a>
                        </div>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-dark btn-lg fw-bold">Log In</button>
                    </div>
                </form>

            </div>
            <div class="card-footer text-center bg-light py-3">
                Don't have an account? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign up here</a>
            </div>
        </div>
    </div>
</div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>