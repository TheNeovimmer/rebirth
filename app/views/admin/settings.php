<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3>Admin Profile</h3>
  </div>
  <div class="card-body">
    <form action="/settings/profile" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="form-group" style="text-align:center;">
        <label class="form-label">Profile Picture</label>
        <div style="display:flex;flex-direction:column;align-items:center;gap:12px;margin-top:8px;">
          <?php if (!empty($user['avatar'])): ?>
          <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Profile" style="width:96px;height:96px;border-radius:50%;object-fit:cover;border:3px solid var(--color-border);">
          <?php else: ?>
          <div style="width:96px;height:96px;border-radius:50%;background:var(--color-accent);color:var(--color-primary-dark);display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;border:3px solid var(--color-border);"><?= htmlspecialchars($user['initials']) ?></div>
          <?php endif; ?>
          <label class="btn btn-outline" style="cursor:pointer;font-size:14px;">
            <i class="fa-solid fa-camera"></i> Upload Photo
            <input type="file" name="avatar" accept="image/*" style="display:none;" onchange="this.closest('label').nextElementSibling.textContent = this.files[0]?.name || ''">
          </label>
          <span style="font-size:12px;color:var(--color-text-muted);"></span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Name</label>
        <input class="form-input" name="name" value="<?= htmlspecialchars($user['name']) ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.7;">
      </div>
      <div class="form-group">
        <label class="form-label">New Password</label>
        <input class="form-input" name="password" type="password" placeholder="Leave blank to keep current">
      </div>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
  </div>
</div>

<div class="card" style="text-align:center;padding:20px;">
  <a href="/logout" class="btn btn-outline" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
</div>
