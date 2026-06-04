<div class="stats-row">
  <div class="stat-card">
    <div class="stat-card-icon green"><i class="fa-solid fa-users"></i></div>
    <div><div class="stat-card-value"><?= $totalUsers ?></div><div class="stat-card-label">Total Users</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon blue"><i class="fa-regular fa-calendar-check"></i></div>
    <div><div class="stat-card-value"><?= $appointmentsToday ?></div><div class="stat-card-label">Appointments Today</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon orange"><i class="fa-regular fa-clock"></i></div>
    <div><div class="stat-card-value"><?= count($upcomingSessions) ?></div><div class="stat-card-label">Upcoming Sessions</div></div>
  </div>
  <div class="stat-card">
    <div class="stat-card-icon red"><i class="fa-regular fa-flag"></i></div>
    <div><div class="stat-card-value"><?= $moderationQueue ?></div><div class="stat-card-label">Moderation Queue</div></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div class="card">
    <div class="card-header"><h3>Recent Registrations</h3></div>
    <div class="card-body">
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
  </div>
  <div class="card">
    <div class="card-header"><h3>Upcoming Sessions</h3></div>
    <div class="card-body">
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
</div>

<div class="card" style="margin-top:24px;">
  <div class="card-header"><h3>Platform Overview</h3></div>
  <div class="card-body">
    <div style="display:flex;gap:24px;margin-bottom:24px;flex-wrap:wrap;">
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;"><?= $totalUsers ?></div>
        <div class="stat-card-label">Total Users</div>
      </div>
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;"><?= $appointmentsToday ?></div>
        <div class="stat-card-label">Today's Appointments</div>
      </div>
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;"><?= $moderationQueue ?></div>
        <div class="stat-card-label">Pending Moderation</div>
      </div>
    </div>
  </div>
</div>
