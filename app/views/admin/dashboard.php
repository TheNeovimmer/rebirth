<?php
$memberCount = $usersByRole['member'] ?? 0;
$therapistCount = $usersByRole['therapist'] ?? 0;
$adminCount = $usersByRole['admin'] ?? 0;
$totalActive = $usersByStage['Active'] ?? 0;
$totalOnboarding = $usersByStage['Onboarding'] ?? 0;
$totalAftercare = $usersByStage['Aftercare'] ?? 0;
$totalRelapse = $usersByStage['Relapse'] ?? 0;
?>

<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
    <div class="stat-value"><?= $totalUsers ?></div>
    <div class="stat-label">Total Users</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-regular fa-calendar-check"></i></div>
    <div class="stat-value"><?= $appointmentsToday ?></div>
    <div class="stat-label">Appointments Today</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-clock"></i></div>
    <div class="stat-value"><?= count($upcomingSessions) ?></div>
    <div class="stat-label">Upcoming Sessions</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-regular fa-flag"></i></div>
    <div class="stat-value"><?= $moderationQueue ?></div>
    <div class="stat-label">Moderation Queue</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div class="card">
    <h2>User Breakdown</h2>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span><i class="fa-solid fa-user" style="color:var(--color-primary);width:20px;"></i> Members</span>
        <span class="badge badge-green"><?= $memberCount ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span><i class="fa-solid fa-user-md" style="color:var(--color-accent);width:20px;"></i> Therapists</span>
        <span class="badge badge-blue"><?= $therapistCount ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;">
        <span><i class="fa-solid fa-shield-halved" style="color:var(--color-danger);width:20px;"></i> Admins</span>
        <span class="badge badge-red"><?= $adminCount ?></span>
      </div>
    </div>
  </div>
  <div class="card">
    <h2>Recovery Stages</h2>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span>Onboarding</span>
        <span class="badge badge-orange"><?= $totalOnboarding ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span>Active</span>
        <span class="badge badge-green"><?= $totalActive ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span>Aftercare</span>
        <span class="badge badge-blue"><?= $totalAftercare ?></span>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;">
        <span>Relapse</span>
        <span class="badge badge-red"><?= $totalRelapse ?></span>
      </div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;">
  <div class="card">
    <h2>Recent Registrations</h2>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Name</th><th>Email</th><th>Stage</th><th>Date</th></tr></thead>
        <tbody>
          <?php foreach ($recentRegistrations as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><span class="badge badge-<?= $u['stage'] === 'Active' ? 'green' : ($u['stage'] === 'Onboarding' ? 'orange' : 'gray') ?>"><?= htmlspecialchars($u['stage']) ?></span></td>
            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <h2>Upcoming Sessions</h2>
    <?php if (empty($upcomingSessions)): ?>
    <div style="padding:16px;text-align:center;color:var(--color-text-muted);">No upcoming sessions</div>
    <?php else: ?>
    <?php foreach ($upcomingSessions as $apt): ?>
    <div class="appointment-item">
      <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['patient_name']) ?> with <?= htmlspecialchars($apt['therapist_name'] ?? 'Unassigned') ?></span></div>
      <span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : 'orange' ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="margin-top:16px;">
  <h2>Quick Links</h2>
  <div class="quick-actions">
    <a href="/admin/users" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-users"></i> Manage Users</a>
    <a href="/admin/appointments" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-calendar"></i> Appointments</a>
    <a href="/admin/resources" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-file-lines"></i> Resources</a>
    <a href="/admin/analytics" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-chart-line"></i> Analytics</a>
    <a href="/admin/moderation" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-flag"></i> Moderation</a>
    <a href="/admin/settings" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-gear"></i> Settings</a>
  </div>
</div>
