<div class="card">
  <div class="card-header"><h3>Platform Analytics</h3></div>
  <div class="card-body">
    <div style="display:flex;gap:24px;margin-bottom:24px;flex-wrap:wrap;">
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;color:var(--color-primary);"><?= $totalUsers ?></div>
        <div class="stat-card-label">Total Users</div>
      </div>
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;color:var(--color-accent);"><?= $totalCheckins ?></div>
        <div class="stat-card-label">Total Check-ins</div>
      </div>
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;color:var(--color-success);"><?= $appointmentsToday ?></div>
        <div class="stat-card-label">Appointments Today</div>
      </div>
      <div style="flex:1;min-width:140px;">
        <div class="stat-card-value" style="font-size:24px;color:var(--color-danger);"><?= $totalResources ?></div>
        <div class="stat-card-label">Total Resources</div>
      </div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
  <div class="card">
    <div class="card-header"><h3>Users by Role</h3></div>
    <div class="card-body">
      <?php foreach ($usersByRole as $r): ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span style="font-weight:500;"><?= htmlspecialchars(ucfirst($r['role'])) ?></span>
        <span style="font-weight:600;"><?= $r['count'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><h3>Users by Stage</h3></div>
    <div class="card-body">
      <?php foreach ($usersByStage as $s): ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--color-border);">
        <span style="font-weight:500;"><?= htmlspecialchars($s['stage']) ?></span>
        <span style="font-weight:600;"><?= $s['count'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px;">
  <div class="card-header"><h3>Check-ins This Week</h3></div>
  <div class="card-body">
    <div class="chart-box">
      <?php if (empty($weekCheckins)): ?>
      <span style="color:var(--color-text-muted);">No data this week</span>
      <?php else: ?>
      <div class="chart-box-inline">
        <?php $max = max(array_column($weekCheckins, 'count')) ?: 1; ?>
        <?php foreach ($weekCheckins as $day): ?>
        <div class="chart-bar" style="height:<?= round(($day['count'] / $max) * 90) ?>%;background:var(--color-primary);"></div>
        <?php endforeach; ?>
      </div>
      <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:12px;color:var(--color-text-muted);width:100%;padding:0 16px;">
        <?php foreach ($weekCheckins as $day): ?>
        <span><?= date('D', strtotime($day['date'])) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
