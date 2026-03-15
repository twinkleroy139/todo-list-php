<?php
// 1. Include Config and Auth
require_once 'config/db.php';
require_once 'includes/auth_functions.php';

// Check status
$is_user_logged_in = is_logged_in();
$user_id = get_current_user_id();

// If logged in, fetch their permanent notes from the database
$db_notes = [];
if ($is_user_logged_in) {
    // Note: Your notepad table needs 'user_id', 'content', and 'updated_at' columns
    $stmt = $conn->prepare("SELECT * FROM notepad WHERE user_id = ? ORDER BY updated_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $db_notes[] = $row;
        }
        $stmt->close();
    }
}

// 2. Include Header (Outputs navbar and opens <div class="container">)
require_once 'includes/header.php'; 
?>

<?php if (!$is_user_logged_in): ?>
    <div class="alert alert-warning shadow-sm d-flex align-items-center" role="alert">
        <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
        <div>
            <strong>Guest Mode:</strong> Your notes are saved temporarily in this browser. 
            <a href="login.php" class="alert-link">Log in or Sign up</a> to save them permanently!
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success shadow-sm d-flex align-items-center" role="alert">
        <i class="fas fa-cloud-upload-alt fa-2x me-3"></i>
        <div>
            <strong>Cloud Sync Active:</strong> Your notes are securely saved to your account.
        </div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-pen-nib me-2"></i> Write a Note</h5>
            </div>
            <div class="card-body d-flex flex-column">
                <form id="noteForm" action="<?= $is_user_logged_in ? 'add_note.php' : '#' ?>" method="POST" class="d-flex flex-column h-100">
                    <div class="mb-3 flex-grow-1">
                        <label class="form-label fw-bold">Note Content</label>
                        <textarea name="content" id="noteContent" class="form-control h-100" rows="8" required placeholder="Type your thoughts here..."></textarea>
                    </div>
                    <div class="d-grid mt-auto">
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-save me-1"></i> Save Note
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i> My Saved Notes</h5>
                <span class="badge bg-secondary" id="noteCount"><?= count($db_notes) ?> Total</span>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3" id="notesListContainer">
                    
                    <?php if ($is_user_logged_in): ?>
                        <?php if (count($db_notes) > 0): ?>
                            <?php foreach ($db_notes as $note): ?>
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-body">
                                            <p class="card-text text-break"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                                        </div>
                                        <div class="card-footer bg-white text-muted d-flex justify-content-between align-items-center py-2">
                                            <small><i class="far fa-clock me-1"></i> <?= date('M d, Y g:i A', strtotime($note['updated_at'])) ?></small>
                                            <div>
                                                <a href="edit_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-primary py-0 px-2"><i class="fas fa-edit"></i></a>
                                                <a href="delete_note.php?id=<?= $note['id'] ?>" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="return confirm('Delete this note?');"><i class="fas fa-trash"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center p-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                                <h5>No permanent notes found.</h5>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="col-12 text-center p-5 text-muted" id="guestEmptyState">
                            Loading temporary notes...
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

</div> <?php if (!$is_user_logged_in): ?>
<script>
    const noteForm = document.getElementById('noteForm');
    const notesListContainer = document.getElementById('notesListContainer');
    const noteCount = document.getElementById('noteCount');
    let guestNotes = JSON.parse(localStorage.getItem('guest_notes')) || [];

    function renderGuestNotes() {
        notesListContainer.innerHTML = '';
        noteCount.innerText = guestNotes.length + ' Total';

        if (guestNotes.length === 0) {
            notesListContainer.innerHTML = `
                <div class="col-12 text-center p-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3 d-block"></i>
                    <h5>No temporary notes yet.</h5>
                </div>`;
            return;
        }

        // Loop backwards to show newest notes at the top
        guestNotes.slice().reverse().forEach((note, index) => {
            const originalIndex = guestNotes.length - 1 - index; // Keep track of actual array index for deletion
            
            // Format content to handle line breaks safely
            const safeContent = note.content.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\n/g, "<br>");
            
            const noteDiv = document.createElement('div');
            noteDiv.className = 'col-12';
            noteDiv.innerHTML = `
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <p class="card-text">${safeContent}</p>
                    </div>
                    <div class="card-footer bg-white text-muted d-flex justify-content-between align-items-center py-2">
                        <small><i class="far fa-clock me-1"></i> ${note.date}</small>
                        <button onclick="deleteGuestNote(${originalIndex})" class="btn btn-sm btn-outline-danger py-0 px-2"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
            notesListContainer.appendChild(noteDiv);
        });
    }

    // Handle Form Submission
    noteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const now = new Date();
        const dateString = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        
        const newNote = {
            content: document.getElementById('noteContent').value,
            date: dateString
        };

        guestNotes.push(newNote);
        localStorage.setItem('guest_notes', JSON.stringify(guestNotes));
        
        noteForm.reset();
        renderGuestNotes();
    });

    // Delete Note
    window.deleteGuestNote = function(index) {
        if(confirm('Delete this temporary note?')) {
            guestNotes.splice(index, 1);
            localStorage.setItem('guest_notes', JSON.stringify(guestNotes));
            renderGuestNotes();
        }
    };

    // Initial load
    renderGuestNotes();
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>`;

<?php $conn->close(); ?>