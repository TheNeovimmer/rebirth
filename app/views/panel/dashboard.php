<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-regular fa-calendar-check"></i></div>
    <div class="stat-value"><?= $streak ?></div>
    <div class="stat-label">Day Streak</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-regular fa-clock"></i></div>
    <div class="stat-value"><?= count($appointments) ?></div>
    <div class="stat-label">Upcoming Sessions</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-face-smile"></i></div>
    <div class="stat-value"><?= $avgMood ?>%</div>
    <div class="stat-label">Mood Score</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fa-solid fa-trophy"></i></div>
    <div class="stat-value"><?= $totalCheckins ?></div>
    <div class="stat-label">Total Check-ins</div>
  </div>
</div>

<div class="card">
  <h2>Today's Overview</h2>
  <div class="overview-row"><i class="fa-regular fa-circle-check"></i><span><?= $todayCheckin ? 'Check-in completed' : 'Check-in pending' ?></span></div>
  <div class="overview-row"><i class="fa-regular fa-calendar"></i><span><?= !empty($appointments) ? 'Next: ' . htmlspecialchars($appointments[0]['title']) . ' — ' . date('M j, g:i A', strtotime($appointments[0]['date_time'])) : 'No upcoming appointments' ?></span></div>
  <div class="overview-row"><i class="fa-regular fa-comment"></i><span>Streak: <?= $streak ?> consecutive days</span></div>
  <div class="today-progress">
    <div class="progress-header"><span>Milestone Progress</span><span><?= $milestones ? round(array_sum(array_column($milestones, 'progress')) / count($milestones)) : 0 ?>%</span></div>
    <div class="progress-bar"><div class="progress-bar-fill accent" style="width:<?= $milestones ? round(array_sum(array_column($milestones, 'progress')) / count($milestones)) : 0 ?>%"></div></div>
  </div>
</div>

<div class="card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/panel/checkin" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-face-smile"></i> Check In</a>
    <a href="/panel/journal" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-book"></i> Journal</a>
    <a href="/panel/treatment-plan" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-clipboard-list"></i> My Plan</a>
    <a href="/panel/relapses" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-heart-crack"></i> Relapses</a>
    <a href="/panel/community" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-comments"></i> Community</a>
    <a href="/panel/sos" class="btn danger" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-phone"></i> SOS</a>
  </div>
</div>

<div class="card">
  <h2>Upcoming</h2>
  <?php if (empty($appointments)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No upcoming sessions</span></div>
  <?php else: ?>
  <?php foreach ($appointments as $apt): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['title']) ?></span></div>
    <span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : ($apt['status'] === 'pending' ? 'orange' : 'gray') ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
