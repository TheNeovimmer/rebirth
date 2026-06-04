<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <h2>Admin Profile</h2>
  <form action="/panel/settings/profile" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <div class="avatar-upload">
      <label class="form-label">Profile Picture</label>
      <?php if (!empty($user['avatar'])): ?>
      <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Profile" class="avatar-preview">
      <?php else: ?>
      <div class="avatar-preview-initials"><?= htmlspecialchars($user['initials']) ?></div>
      <?php endif; ?>
      <label class="btn btn-outline" style="cursor:pointer;font-size:14px;">
        <i class="fa-solid fa-camera"></i> Upload Photo
        <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="this.closest('label').nextElementSibling.textContent = this.files[0]?.name || ''">
      </label>
      <span class="avatar-upload-name"></span>
    </div>
    <div class="form-group">
      <label class="form-label">Name</label>
      <input class="form-input" name="name" value="<?= htmlspecialchars($user['name']) ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Email</label>
      <input class="form-input" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>
    <div class="form-group">
      <label class="form-label">New Password</label>
      <input class="form-input" name="password" type="password" placeholder="Leave blank to keep current">
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
</div>

<div class="card" style="text-align:center;">
  <a href="/logout" class="btn btn-outline" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
</div>
