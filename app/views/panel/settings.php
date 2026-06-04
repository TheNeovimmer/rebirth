<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="alert" style="background:rgba(209,69,59,0.08);color:var(--color-danger);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<form action="/settings/profile" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="_token" value="<?= $_token ?>">
  <div class="card">
    <h2>Profile</h2>
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
      <label class="form-label" for="displayName">Display Name</label>
      <input class="form-input" id="displayName" name="name" type="text" value="<?= htmlspecialchars($user['name']) ?>">
    </div>
    <div class="form-group">
      <label class="form-label" for="settingsEmail">Email</label>
      <input class="form-input" id="settingsEmail" type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.7;">
    </div>
    <div class="form-group">
      <label class="form-label" for="settingsPassword">New Password</label>
      <input class="form-input" id="settingsPassword" name="password" type="password" placeholder="Leave blank to keep current">
    </div>
    <div class="form-group">
      <label class="form-label">Recovery Stage</label>
      <input class="form-input" type="text" value="<?= htmlspecialchars($user['stage']) ?>" disabled style="opacity:0.7;">
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
    <div class="setting-info"><strong>Appointment Alerts</strong><span>Receive notifications before sessions</span></div>
    <label class="switch"><input type="checkbox" checked><span class="switch-slider"></span></label>
  </div>
  <div class="setting-row">
    <div class="setting-info"><strong>Community Messages</strong><span>Get notified when someone replies</span></div>
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

<div class="card" style="text-align:center;padding:20px;">
  <a href="/logout" class="btn btn-outline" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i> Sign Out</a>
</div>
