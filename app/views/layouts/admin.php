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
<body>
  <div class="app-layout">

    <aside class="app-sidebar">
      <div class="app-sidebar-header">
        <img src="/logo.png" alt="Rebirth Admin">
      </div>
      <nav class="app-sidebar-nav">
        <div class="app-sidebar-section">Admin</div>
        <a href="/admin/dashboard" class="app-sidebar-link <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i>Dashboard</a>
        <a href="/admin/users" class="app-sidebar-link <?= $page === 'Users' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i>Users</a>
        <a href="/admin/appointments" class="app-sidebar-link <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i>Appointments</a>
        <a href="/admin/resources" class="app-sidebar-link <?= $page === 'Resources' ? 'active' : '' ?>"><i class="fa-regular fa-file-lines"></i>Resources</a>
        <a href="/admin/moderation" class="app-sidebar-link <?= $page === 'Moderation' ? 'active' : '' ?>"><i class="fa-solid fa-shield"></i>Moderation</a>
        <a href="/admin/analytics" class="app-sidebar-link <?= $page === 'Analytics' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i>Analytics</a>
        <div class="app-sidebar-section">Account</div>
        <a href="/admin/settings" class="app-sidebar-link <?= $page === 'Settings' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="/logout" class="app-sidebar-link" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a>
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
          <span class="app-topbar-title">Admin <?= htmlspecialchars($page) ?></span>
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
  </div>
  <script src="/js/admin.js"></script>
</body>
</html>
