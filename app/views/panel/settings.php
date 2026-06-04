<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="alert alert-error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form action="/panel/settings/profile" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="_token" value="<?= $_token ?>">
  <div class="card">
    <h2>Profile</h2>
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
      <label class="form-label" for="displayName">Display Name</label>
      <input class="form-input" id="displayName" name="name" type="text" value="<?= htmlspecialchars($user['name']) ?>">
    </div>
    <div class="form-group">
      <label class="form-label" for="settingsEmail">Email</label>
      <input class="form-input" id="settingsEmail" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
    </div>
    <div class="form-group">
      <label class="form-label" for="settingsPassword">New Password</label>
      <input class="form-input" id="settingsPassword" name="password" type="password" placeholder="Leave blank to keep current">
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </div>
</form>

<div class="card">
  <h2>Notifications</h2>
  <div class="setting-row">
    <div class="setting-info"><strong>Daily Reminders</strong><span>Get reminded to complete your check-in</span></div>
    <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
  </div>
  <div class="setting-row">
    <div class="setting-info"><strong>Community Replies</strong><span>Get notified when someone replies to your post</span></div>
    <label class="switch"><input type="checkbox"><span class="switch-slider"></span></label>
  </div>
</div>

<div class="card">
  <h2>Privacy</h2>
  <div class="setting-row">
    <div class="setting-info"><strong>Anonymous Profile</strong><span>Show as anonymous in community</span></div>
    <label class="switch"><input type="checkbox"><span class="switch-slider"></span></label>
  </div>
  <div class="setting-row">
    <div class="setting-info"><strong>Share Progress</strong><span>Allow therapist to view your data</span></div>
    <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
  </div>
</div>

<div class="card" style="text-align:center;">
  <a href="/logout" class="btn btn-outline" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
</div>
