<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="section-header">
    <h2>All Users</h2>
    <button class="btn btn-primary" id="createUserBtn"><i class="fa-solid fa-plus"></i> Add User</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Date Joined</th><th>Therapist</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'green' : ($u['role'] === 'therapist' ? 'orange' : 'gray') ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php if ($u['role'] === 'member'): ?>
              <?php
                $assignedTherapistId = $assignmentMap[$u['id']] ?? null;
                $assignedName = '';
                if ($assignedTherapistId) {
                  foreach ($therapists as $t) {
                    if ((int)$t['id'] === (int)$assignedTherapistId) { $assignedName = $t['name']; break; }
                  }
                }
              ?>
              <?php if ($assignedName): ?>
                <span style="font-size:13px;"><?= htmlspecialchars($assignedName) ?></span>
              <?php else: ?>
                <span style="font-size:12px;color:var(--color-text-muted);">&mdash;</span>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:4px;">
              <button class="btn btn-outline btn-xs" onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= $u['role'] ?>')"><i class="fa-regular fa-pen-to-square"></i></button>
              <?php if ($u['role'] === 'member'): ?>
                <?php $tid = $assignmentMap[$u['id']] ?? null; ?>
                <?php if ($tid): ?>
                <form action="/admin/users/unassign" method="POST" style="display:inline;">
                  <input type="hidden" name="_token" value="<?= $_token ?>">
                  <input type="hidden" name="patient_id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="therapist_id" value="<?= $tid ?>">
                  <button type="submit" class="btn btn-outline btn-xs" style="color:var(--color-danger);" onclick="return confirm('Unassign <?= htmlspecialchars(addslashes($u['name'])) ?> from their therapist?')"><i class="fa-solid fa-link-slash"></i></button>
                </form>
                <?php else: ?>
                <button class="btn btn-outline btn-xs" style="color:var(--color-accent);" onclick="openAssignModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>')"><i class="fa-solid fa-link"></i></button>
                <?php endif; ?>
              <?php endif; ?>
              <?php if ($u['id'] !== $currentAdmin['id']): ?>
              <form action="/admin/users/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>?')">
                <input type="hidden" name="_token" value="<?= $_token ?>">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button type="submit" class="btn btn-outline btn-xs" style="color:var(--color-danger);"><i class="fa-solid fa-trash-can"></i></button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Create User Modal -->
<div class="modal-overlay" id="createUserModal">
  <div class="modal">
    <form action="/admin/users/create" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>Add User</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('createUserModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input class="form-input" name="name" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-input" name="email" type="email" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-input" name="password" type="password" required>
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <select class="form-select" name="role">
            <option value="member">Member</option>
            <option value="therapist">Therapist</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create User</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
  <div class="modal">
    <form action="/admin/users/update" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="id" id="editUserId">
      <div class="modal-header">
        <h3>Edit User</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('editUserModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Name</label>
          <input class="form-input" name="name" id="editUserName" required>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input class="form-input" name="password" type="password" placeholder="Leave blank to keep current">
        </div>
        <div class="form-group">
          <label class="form-label">Role</label>
          <select class="form-select" name="role" id="editUserRole">
            <option value="member">Member</option>
            <option value="therapist">Therapist</option>
            <option value="admin">Admin</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Assign Therapist Modal -->
<div class="modal-overlay" id="assignModal">
  <div class="modal">
    <form action="/admin/users/assign" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="patient_id" id="assignPatientId">
      <div class="modal-header">
        <h3>Assign Therapist</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('assignModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <p style="margin-bottom:12px;color:var(--color-text-muted);font-size:14px;" id="assignPatientName"></p>
        <div class="form-group">
          <label class="form-label">Select Therapist</label>
          <select class="form-select" name="therapist_id" required>
            <option value="">&mdash; Choose &mdash;</option>
            <?php foreach ($therapists as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Assign</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('createUserBtn').addEventListener('click', function() {
    document.getElementById('createUserModal').style.display = 'flex';
  });
  function editUser(id, name, role) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUserName').value = name;
    document.getElementById('editUserRole').value = role;
    document.getElementById('editUserModal').style.display = 'flex';
  }
  function openAssignModal(patientId, patientName) {
    document.getElementById('assignPatientId').value = patientId;
    document.getElementById('assignPatientName').textContent = 'Assigning therapist for: ' + patientName;
    document.getElementById('assignModal').style.display = 'flex';
  }
</script>
