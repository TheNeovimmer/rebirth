<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3>All Users</h3>
    <button class="btn btn-primary" id="createUserBtn"><i class="fa-solid fa-plus"></i> Add User</button>
  </div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Stage</th><th>Date Joined</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'green' : ($u['role'] === 'therapist' ? 'orange' : 'gray') ?>"><?= htmlspecialchars(ucfirst($u['role'])) ?></span></td>
            <td><span class="badge badge-<?= $u['stage'] === 'Active' ? 'green' : ($u['stage'] === 'Onboarding' ? 'orange' : 'gray') ?>"><?= htmlspecialchars($u['stage']) ?></span></td>
            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <button class="btn btn-outline" style="padding:4px 10px;font-size:12px;" onclick="editUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= $u['role'] ?>', '<?= $u['stage'] ?>')"><i class="fa-regular fa-pen-to-square"></i></button>
              <?php if ($u['id'] !== $currentAdmin['id']): ?>
              <form action="/admin/users/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>?')">
                <input type="hidden" name="_token" value="<?= $_token ?>">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button type="submit" class="btn btn-outline" style="padding:4px 10px;font-size:12px;color:var(--color-danger);"><i class="fa-solid fa-trash-can"></i></button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create User Modal -->
<div class="modal-overlay" id="createUserModal" style="display:none;">
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
        <div class="form-group">
          <label class="form-label">Stage</label>
          <select class="form-select" name="stage">
            <option value="Onboarding">Onboarding</option>
            <option value="Active">Active</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Alumni">Alumni</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create User</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal" style="display:none;">
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
        <div class="form-group">
          <label class="form-label">Stage</label>
          <select class="form-select" name="stage" id="editUserStage">
            <option value="Onboarding">Onboarding</option>
            <option value="Active">Active</option>
            <option value="Maintenance">Maintenance</option>
            <option value="Alumni">Alumni</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('createUserBtn').addEventListener('click', function() {
    document.getElementById('createUserModal').style.display = 'flex';
  });
  function editUser(id, name, role, stage) {
    document.getElementById('editUserId').value = id;
    document.getElementById('editUserName').value = name;
    document.getElementById('editUserRole').value = role;
    document.getElementById('editUserStage').value = stage;
    document.getElementById('editUserModal').style.display = 'flex';
  }
</script>
