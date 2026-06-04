<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-regular fa-calendar-check"></i></div>
    <div class="stat-value"><?= $streak ?></div>
    <div class="stat-label">Day Streak</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-face-smile"></i></div>
    <div class="stat-value"><?= $avgMood ?>%</div>
    <div class="stat-label">Avg. Mood</div>
  </div>
</div>

<div class="card">
  <h2>Mood Trend</h2>
  <div class="chart-box">
    <?php if (empty($moodTrend)): ?>
    <span style="color:var(--color-text-muted);">No data yet. Start checking in!</span>
    <?php else: ?>
    <div class="chart-box-inline">
      <?php foreach ($moodTrend as $day): ?>
      <div class="chart-bar" style="height:<?= $day['value'] ?>%;background:<?= $day['value'] >= 80 ? 'var(--color-success)' : ($day['value'] >= 60 ? 'var(--color-accent)' : ($day['value'] >= 40 ? 'var(--color-warning)' : 'var(--color-danger)')) ?>;"></div>
      <?php endforeach; ?>
    </div>
    <div style="display:flex;justify-content:space-between;margin-top:8px;font-size:12px;color:var(--color-text-muted);width:100%;padding:0 16px;">
      <?php foreach ($moodTrend as $day): ?>
      <span><?= date('D', strtotime($day['check_date'])) ?></span>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h2>Milestones</h2>
  <div class="milestones">
    <?php if (empty($milestones)): ?>
    <div style="padding:20px;text-align:center;color:var(--color-text-muted);">No milestones defined</div>
    <?php else: ?>
    <?php foreach ($milestones as $ms): ?>
    <div class="milestone <?= $ms['achieved'] ? 'done' : '' ?>">
      <div class="milestone-icon"><?= $ms['achieved'] ? '<i class="fa-solid fa-check"></i>' : ($ms['progress'] > 0 ? '<i class="fa-solid fa-spinner"></i>' : '<i class="fa-solid fa-lock"></i>') ?></div>
      <div class="milestone-info">
        <strong><?= htmlspecialchars($ms['name']) ?></strong>
        <span><?= htmlspecialchars($ms['description']) ?></span>
      </div>
      <span><?= $ms['progress'] ?>%</span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <h2>Weekly Progress</h2>
  <div class="milestones">
    <?php foreach ($weeklyProgress as $day): ?>
    <div class="milestone <?= $day['done'] ? 'done' : '' ?>">
      <div class="milestone-info"><?= $day['day'] ?></div>
      <div class="progress-bar" style="flex:1;margin:0 12px;">
        <div class="progress-bar-fill <?= $day['done'] ? 'accent' : '' ?>" style="width:<?= $day['done'] ? '100' : '0' ?>%"></div>
      </div>
      <span><?= $day['done'] ? '✓' : '—' ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if (!empty($recoveryStages)): ?>
<div class="card">
  <h2>Recovery Program</h2>
  <p style="color:var(--color-text-muted);font-size:14px;margin-bottom:16px;">Tracked by your therapist</p>
  <div class="milestones">
    <?php foreach ($recoveryStages as $stage): ?>
    <div class="milestone <?= $stage['status'] === 'completed' ? 'done' : '' ?>">
      <div class="milestone-icon">
        <?php if ($stage['status'] === 'completed'): ?>
        <i class="fa-solid fa-check-circle" style="color:var(--color-success);"></i>
        <?php elseif ($stage['status'] === 'in_progress'): ?>
        <i class="fa-solid fa-spinner" style="color:var(--color-accent);"></i>
        <?php else: ?>
        <i class="fa-solid fa-circle" style="color:var(--color-border);"></i>
        <?php endif; ?>
      </div>
      <div class="milestone-info">
        <strong><?= htmlspecialchars($stage['stage_name']) ?></strong>
        <span><?= ucfirst(str_replace('_', ' ', $stage['status'])) ?></span>
      </div>
      <?php if ($stage['completed_at']): ?>
      <span style="font-size:12px;color:var(--color-text-muted);"><?= date('M j', strtotime($stage['completed_at'])) ?></span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
