<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <link rel="icon" href="/logo.png">
  <link rel="stylesheet" href="/css/app.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body data-user-id="<?= $_SESSION['user_id'] ?? 0 ?>">
  <div class="app-layout">

    <aside class="app-sidebar">
      <div class="app-sidebar-header">
        <img src="/logo.png" alt="Rebirth">
      </div>
      <nav class="app-sidebar-nav">
        <?php if ($user['role'] === 'therapist'): ?>

        <div class="app-sidebar-section">Overview</div>
        <a href="/panel/dashboard" class="app-sidebar-link <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a>
        <a href="/therapist/patients" class="app-sidebar-link <?= $page === 'My Patients' ? 'active' : '' ?>"><i class="fa-solid fa-heart-pulse"></i>My Patients</a>
        <div class="app-sidebar-section">Communication</div>
        <a href="/therapist/messages" class="app-sidebar-link <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i>Messages</a>
        <a href="/therapist/sos" class="app-sidebar-link <?= $page === 'SOS Alerts' ? 'active' : '' ?>">
          <i class="fa-solid fa-triangle-exclamation" style="color:var(--color-danger);"></i>SOS Alerts
          <span id="sosBadge" style="display:none;background:var(--color-danger);color:white;font-size:11px;padding:1px 6px;border-radius:10px;margin-left:auto;"></span>
        </a>
        <a href="/therapist/resources" class="app-sidebar-link <?= $page === 'Resources' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i>Resources</a>
        <a href="/panel/appointments" class="app-sidebar-link <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i>Schedule</a>
        <a href="/panel/community" class="app-sidebar-link <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i>Community</a>
        <div class="app-sidebar-section">Settings</div>
        <a href="/therapist/availability" class="app-sidebar-link <?= $page === 'Availability' ? 'active' : '' ?>"><i class="fa-solid fa-clock"></i>Availability</a>
        <a href="/panel/settings" class="app-sidebar-link <?= $page === 'Settings' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="/logout" class="app-sidebar-link" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a>

        <?php else: ?>

        <div class="app-sidebar-section">Main</div>
        <a href="/panel/dashboard" class="app-sidebar-link <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a>
        <a href="/panel/checkin" class="app-sidebar-link <?= $page === 'Daily Check-in' ? 'active' : '' ?>"><i class="fa-regular fa-face-smile"></i>Daily Check-in</a>
        <a href="/panel/journal" class="app-sidebar-link <?= $page === 'Journal' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i>Journal</a>
        <a href="/panel/appointments" class="app-sidebar-link <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i>Appointments</a>
        <a href="/panel/community" class="app-sidebar-link <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i>Community</a>
        <a href="/panel/messages" class="app-sidebar-link <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i>Messages</a>
        <a href="/panel/sos" class="app-sidebar-link <?= $page === 'SOS' ? 'active' : '' ?>"><i class="fa-solid fa-triangle-exclamation" style="color:var(--color-danger);"></i>SOS</a>
        <a href="/panel/resources" class="app-sidebar-link <?= $page === 'Resources' ? 'active' : '' ?>"><i class="fa-regular fa-file-lines"></i>Resources</a>
        <div class="app-sidebar-section">Account</div>
        <a href="/panel/progress" class="app-sidebar-link <?= $page === 'Progress' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i>Progress</a>
        <a href="/panel/settings" class="app-sidebar-link <?= $page === 'Settings' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="/logout" class="app-sidebar-link" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a>

        <?php endif; ?>
      </nav>
      <div class="app-sidebar-footer">
        <div class="app-sidebar-user">
          <?php if (!empty($user['avatar'])): ?>
          <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="" class="app-sidebar-user-avatar" style="object-fit:cover;">
          <?php else: ?>
          <div class="app-sidebar-user-avatar"><?= htmlspecialchars($user['initials']) ?></div>
          <?php endif; ?>
          <div>
            <div class="app-sidebar-user-name"><?= htmlspecialchars($user['name']) ?></div>
            <div class="app-sidebar-user-role"><?= htmlspecialchars(ucfirst($user['role'])) ?></div>
          </div>
        </div>
      </div>
    </aside>

    <div class="app-main" style="flex:1;display:flex;flex-direction:column;min-width:0;">
      <header class="app-topbar">
        <div class="app-topbar-left">
          <span class="app-topbar-title"><?= htmlspecialchars($page) ?></span>
        </div>
        <div class="app-topbar-right">
          <button class="app-topbar-btn"><i class="fa-regular fa-bell"></i><span class="dot"></span></button>
          <?php if (!empty($user['avatar'])): ?>
          <img src="/uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="" class="app-topbar-avatar" style="object-fit:cover;">
          <?php else: ?>
          <div class="app-topbar-avatar"><?= htmlspecialchars($user['initials']) ?></div>
          <?php endif; ?>
        </div>
      </header>

      <div class="app-content">
        <?= $content ?>
      </div>
    </div>

    <?php if ($user['role'] === 'therapist'): ?>
    <nav class="app-tabs">
      <a href="/panel/dashboard" class="app-tab <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i><span>Home</span></a>
      <a href="/therapist/patients" class="app-tab <?= $page === 'My Patients' ? 'active' : '' ?>"><i class="fa-solid fa-heart-pulse"></i><span>Patients</span></a>
      <a href="/therapist/messages" class="app-tab <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i><span>Messages</span></a>
      <a href="/panel/community" class="app-tab <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i><span>Community</span></a>
      <a href="/panel/appointments" class="app-tab <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i><span>Schedule</span></a>
    </nav>
    <?php else: ?>
    <nav class="app-tabs">
      <a href="/panel/dashboard" class="app-tab <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i><span>Home</span></a>
      <a href="/panel/checkin" class="app-tab <?= $page === 'Daily Check-in' ? 'active' : '' ?>"><i class="fa-regular fa-face-smile"></i><span>Check-in</span></a>
      <a href="/panel/messages" class="app-tab <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i><span>Messages</span></a>
      <a href="/panel/community" class="app-tab <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i><span>Community</span></a>
      <a href="/panel/progress" class="app-tab <?= $page === 'Progress' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i><span>Progress</span></a>
    </nav>
    <?php endif; ?>

  </div>
  <script src="/js/app.js"></script>
</body>
</html>
