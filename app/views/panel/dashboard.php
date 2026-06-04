<?php
$checkinStatus = $todayCheckin ? 'completed' : 'pending';
$nextAppt = !empty($appointments) ? $appointments[0] : null;
$avgMilestoneProgress = $milestones ? round(array_sum(array_column($milestones, 'progress')) / count($milestones)) : 0;
$nextMilestone = null;
foreach ($milestones as $ms) { if (!$ms['achieved']) { $nextMilestone = $ms; break; } }
$hasMilestones = !empty($milestones);
?>

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
  <div class="overview-row">
    <i class="fa-regular fa-circle-check" style="color:<?= $checkinStatus === 'completed' ? 'var(--color-success)' : 'var(--color-text-muted)' ?>;"></i>
    <span><?= $checkinStatus === 'completed' ? "Checked in at " . date('g:i A', strtotime($todayCheckin['created_at'])) : 'Check-in pending for today' ?></span>
    <?php if ($checkinStatus === 'pending'): ?>
    <a href="/panel/checkin" class="btn btn-primary btn-sm" style="margin-left:auto;"><i class="fa-regular fa-face-smile"></i> Check In Now</a>
    <?php endif; ?>
  </div>
  <div class="overview-row">
    <i class="fa-regular fa-calendar"></i>
    <span><?= $nextAppt ? 'Next: ' . htmlspecialchars($nextAppt['title']) . ' — ' . date('M j, g:i A', strtotime($nextAppt['date_time'])) : 'No upcoming appointments' ?></span>
    <?php if ($nextAppt): ?>
    <span class="badge badge-green" style="margin-left:auto;"><?= $nextAppt['status'] === 'confirmed' ? 'Confirmed' : 'Pending' ?></span>
    <?php endif; ?>
  </div>
  <div class="overview-row">
    <i class="fa-regular fa-comment"></i>
    <span>Streak: <?= $streak ?> consecutive day<?= $streak !== 1 ? 's' : '' ?></span>
    <?php if ($streak >= 7): ?>
    <span class="badge badge-green" style="margin-left:auto;"><i class="fa-solid fa-fire"></i> <?= floor($streak/7) ?> week<?= floor($streak/7) > 1 ? 's' : '' ?></span>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h2>Milestone Progress</h2>
  <div class="today-progress">
    <div class="progress-header">
      <span><?= $hasMilestones ? "Overall progress" : 'No milestones set' ?></span>
      <span><?= $avgMilestoneProgress ?>%</span>
    </div>
    <div class="progress-bar"><div class="progress-bar-fill accent" style="width:<?= $avgMilestoneProgress ?>%"></div></div>
  </div>
  <?php if ($nextMilestone): ?>
  <div class="milestone" style="margin-top:12px;border:1px solid var(--color-border);border-radius:var(--radius-sm);padding:12px;">
    <div class="milestone-info">
      <strong>Next milestone:</strong>
      <span><?= htmlspecialchars($nextMilestone['name']) ?> — <?= $nextMilestone['progress'] ?>%</span>
    </div>
    <div class="progress-bar" style="flex:1;margin:0 12px;height:6px;">
      <div class="progress-bar-fill accent" style="width:<?= $nextMilestone['progress'] ?>%"></div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <?php if ($checkinStatus === 'completed'): ?>
    <a href="/panel/checkin" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;border:2px solid var(--color-success);"><i class="fa-regular fa-circle-check" style="color:var(--color-success);"></i> Checked In</a>
    <?php else: ?>
    <a href="/panel/checkin" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;animation:pulse-subtle 2s infinite;"><i class="fa-regular fa-face-smile"></i> Check In</a>
    <?php endif; ?>
    <a href="/panel/journal" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-book"></i> Journal</a>
    <a href="/panel/treatment-plan" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-clipboard-list"></i> My Plan</a>
    <a href="/panel/progress" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-chart-line"></i> Progress</a>
    <a href="/panel/relapses" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-heart-crack"></i> Relapses</a>
    <a href="/panel/community" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-comments"></i> Community</a>
    <a href="/panel/sos" class="btn danger" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-phone"></i> SOS</a>
  </div>
</div>

<div class="card">
  <h2>Upcoming Sessions</h2>
  <?php if (empty($appointments)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No upcoming sessions. Book one from the Appointments page.</span></div>
  <?php else: ?>
  <?php foreach ($appointments as $apt): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['title']) ?></span></div>
    <span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : ($apt['status'] === 'pending' ? 'orange' : 'gray') ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
  <a href="/panel/appointments" class="btn btn-outline" style="margin-top:12px;width:100%;justify-content:center;">Manage Appointments</a>
</div>
