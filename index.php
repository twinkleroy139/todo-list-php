<?php
// 1. Include Config and Auth (Header will be included below)
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Check status
$is_user_logged_in = is_logged_in();
$user_id = get_current_user_id();

// If logged in, fetch their permanent tasks from the database
$db_tasks = [];
if ($is_user_logged_in) {
    // Note: Your tasks table will need a 'user_id' column for this to work!
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY due_date ASC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $db_tasks[] = $row;
        }
        $stmt->close();
    }
}

// 2. Include Header (This outputs the <html>, <head>, navbar, and opens the main <div class="container">)
require_once 'includes/header.php'; 
?>

<?php if (!$is_user_logged_in): ?>
    <div class="alert alert-warning shadow-sm d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <strong>Guest Mode:</strong> Your tasks are currently being saved temporarily in this browser. 
            <a href="login.php" class="alert-link">Log in or Sign up</a> to save them permanently across all devices!
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success shadow-sm d-flex align-items-center" role="alert">
        <i class="fas fa-cloud-upload-alt fa-2x me-3"></i>
        <div>
            <strong>Cloud Sync Active:</strong> Your tasks are securely saved to your account.
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Add New Task</h5>
            </div>
            <div class="card-body">
                <form id="taskForm" action="<?= $is_user_logged_in ? 'add_task.php' : '#' ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="taskTitle" class="form-control" required placeholder="What needs to be done?">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="taskDesc" class="form-control" rows="3" placeholder="Optional details..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Due Date</label>
                        <input type="date" name="due_date" id="taskDate" class="form-control">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary fw-bold">
                            <i class="fas fa-save me-1"></i> Save Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> My Tasks</h5>
                <span class="badge bg-secondary" id="taskCount"><?= count($db_tasks) ?> Total</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Task</th>
                                <th>Due</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="taskListBody">
                            <?php if ($is_user_logged_in): ?>
                                <?php if (count($db_tasks) > 0): ?>
                                    <?php foreach ($db_tasks as $task): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($task['title']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($task['description']) ?></small>
                                            </td>
                                            <td><?= $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : '<span class="text-muted">—</span>' ?></td>
                                            <td>
                                                <?php if ($task['status'] === 'done'): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Done</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <?php if ($task['status'] === 'pending'): ?>
                                                    <a href="mark_done.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></a>
                                                <?php endif; ?>
                                                <a href="edit_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                                <a href="delete_task.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this task?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center p-4 text-muted">No permanent tasks found. Create one!</td></tr>
                                <?php endif; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center p-4 text-muted" id="guestEmptyState">Loading temporary tasks...</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div> <?php if (!$is_user_logged_in): ?>
<script>
    // Guest Mode Logic
    const taskForm = document.getElementById('taskForm');
    const taskListBody = document.getElementById('taskListBody');
    const taskCount = document.getElementById('taskCount');
    let guestTasks = JSON.parse(localStorage.getItem('guest_todo')) || [];

    function renderGuestTasks() {
        taskListBody.innerHTML = '';
        taskCount.innerText = guestTasks.length + ' Total';

        if (guestTasks.length === 0) {
            taskListBody.innerHTML = '<tr><td colspan="4" class="text-center p-4 text-muted">No temporary tasks yet. Add one on the left!</td></tr>';
            return;
        }

        guestTasks.forEach((task, index) => {
            const isDone = task.status === 'done';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <strong style="${isDone ? 'text-decoration: line-through; color: #6c757d;' : ''}">${task.title}</strong><br>
                    <small class="text-muted">${task.description || ''}</small>
                </td>
                <td>${task.date || '<span class="text-muted">—</span>'}</td>
                <td>
                    ${isDone 
                        ? '<span class="badge bg-success"><i class="fas fa-check"></i> Done</span>' 
                        : '<span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Pending</span>'}
                </td>
                <td class="text-end">
                    ${!isDone ? `<button onclick="toggleGuestTask(${index})" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>` : ''}
                    <button onclick="deleteGuestTask(${index})" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                </td>
            `;
            taskListBody.appendChild(tr);
        });
    }

    // Handle Form Submission
    taskForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Stop page reload since we aren't sending to DB
        
        const newTask = {
            title: document.getElementById('taskTitle').value,
            description: document.getElementById('taskDesc').value,
            date: document.getElementById('taskDate').value,
            status: 'pending'
        };

        guestTasks.push(newTask);
        localStorage.setItem('guest_todo', JSON.stringify(guestTasks));
        
        taskForm.reset();
        renderGuestTasks();
    });

    // Mark as Done
    window.toggleGuestTask = function(index) {
        guestTasks[index].status = 'done';
        localStorage.setItem('guest_todo', JSON.stringify(guestTasks));
        renderGuestTasks();
    };

    // Delete Task
    window.deleteGuestTask = function(index) {
        if(confirm('Delete this temporary task?')) {
            guestTasks.splice(index, 1);
            localStorage.setItem('guest_todo', JSON.stringify(guestTasks));
            renderGuestTasks();
        }
    };

    // Initial load
    renderGuestTasks();
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>`;

<?php $conn->close(); ?>