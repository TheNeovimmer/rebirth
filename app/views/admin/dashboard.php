<?php
$memberCount = $memberCount ?? 0;
$therapistCount = $therapistCount ?? 0;
$adminCount = $adminCount ?? 0;
$totalUsers = $totalUsers ?? 0;
$totalCheckins = $totalCheckins ?? 0;
$weekActivity = $weekActivity ?? 0;
$moderationQueue = $moderationQueue ?? 0;
$recentRegistrations = $recentRegistrations ?? [];
?>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
    <div>
      <div class="stat-value"><?= $totalUsers ?></div>
      <div class="stat-label">Total Users</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-regular fa-calendar-check"></i></div>
    <div>
      <div class="stat-value"><?= $totalCheckins ?></div>
      <div class="stat-label">Check-ins</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-chart-line"></i></div>
    <div>
      <div class="stat-value"><?= $weekActivity ?></div>
      <div class="stat-label">7-Day Activity</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-regular fa-flag"></i></div>
    <div>
      <div class="stat-value"><?= $moderationQueue ?></div>
      <div class="stat-label">Moderation Queue</div>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="section-header">
      <h2>User Breakdown</h2>
    </div>
    <div class="user-breakdown-item">
      <span><i class="fa-solid fa-user" style="color:var(--color-primary);"></i> Members</span>
      <span class="badge badge-green"><?= $memberCount ?></span>
    </div>
    <div class="user-breakdown-item">
      <span><i class="fa-solid fa-user-md" style="color:var(--color-accent);"></i> Therapists</span>
      <span class="badge badge-blue"><?= $therapistCount ?></span>
    </div>
    <div class="user-breakdown-item">
      <span><i class="fa-solid fa-shield-halved" style="color:var(--color-danger);"></i> Admins</span>
      <span class="badge badge-red"><?= $adminCount ?></span>
    </div>
  </div>

  <div class="card">
    <div class="section-header">
      <h2>Recent Registrations</h2>
    </div>
    <?php if (empty($recentRegistrations)): ?>
    <div class="empty-state" style="padding:20px;">
      <i class="fa-regular fa-user-plus"></i>
      <p>No recent registrations.</p>
    </div>
    <?php else: ?>
    <?php foreach (array_slice($recentRegistrations, 0, 5) as $u): ?>
    <div class="data-row">
      <div class="data-row-body">
        <strong><?= htmlspecialchars($u['name'] ?? '') ?></strong>
        <span><?= htmlspecialchars($u['email'] ?? '') ?> &middot; <?= date('M j', strtotime($u['created_at'] ?? '')) ?></span>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="padding-bottom:16px;">
  <h2>Quick Links</h2>
  <div class="quick-actions">
    <a href="/admin/users" class="btn"><i class="fa-solid fa-users"></i> Manage Users</a>
    <a href="/admin/moderation" class="btn"><i class="fa-regular fa-flag"></i> Moderation</a>
    <a href="/admin/settings" class="btn"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>
