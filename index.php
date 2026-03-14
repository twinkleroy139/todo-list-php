<?php
// Show errors during dev
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config/db.php");

// Fetch tasks
$sql = "SELECT * FROM tasks ORDER BY due_date ASC";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-Do List</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        body { background: #f1f3f5; padding: 2rem 1rem; }
        .card { border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .card-header { background: #0d6efd; color: white; }
        .status-pending { color: #e63946; font-weight: 600; }
        .status-done    { color: #2a9d8f; font-weight: 600; text-decoration: line-through; }
        .table th { background: #0d6efd; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-list-check me-2"></i> My Tasks</h4>
            <a href="add.php" class="btn btn-light btn-sm">
                <i class="fas fa-plus me-1"></i> New Task
            </a>
        </div>

        <div class="card-body p-0">
            <?php if ($result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 text-center align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($row['description'] ?: '<span class="text-muted">—</span>') ?></td>
                                    <td class="<?= $row['status'] === 'pending' ? 'status-pending' : 'status-done' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </td>
                                    <td><?= $row['due_date'] ?: '<span class="text-muted">No date</span>' ?></td>
                                    <td>
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger me-1"
                                           onclick="return confirm('Delete this task?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <a href="mark_done.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-check"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3 d-block"></i>
                    <h5>No tasks yet</h5>
                    <p>Add your first task to get started!</p>
                    <a href="add.php" class="btn btn-primary mt-2">Create Task</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>