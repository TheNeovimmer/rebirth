<a href="/therapist/patients" class="btn btn-outline" style="margin-bottom:16px;"><i class="fa-solid fa-arrow-left"></i> Back to Patients</a>

<div class="card" style="display:flex;align-items:center;gap:16px;">
  <?php if (!empty($patient['avatar'])): ?>
  <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">
  <?php else: ?>
  <div style="width:64px;height:64px;border-radius:50%;background:var(--color-accent);color:var(--color-primary-dark);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
  <?php endif; ?>
  <div>
    <h2 style="margin:0 0 4px;"><?= htmlspecialchars($patient['name']) ?></h2>
    <div class="overview-row" style="margin:0;"><span>Email: <?= htmlspecialchars($patient['email']) ?></span></div>
    <div class="overview-row" style="margin:0;"><span>Stage: <?= htmlspecialchars($patient['stage']) ?></span></div>
    <div class="overview-row" style="margin:0;"><span>Member since: <?= date('M j, Y', strtotime($patient['created_at'])) ?></span></div>
  </div>
</div>

<div class="card">
  <h2>Recent Check-ins</h2>
  <?php if (empty($checkins)): ?>
  <div style="padding:12px;color:var(--color-text-muted);">No check-ins yet.</div>
  <?php else: ?>
  <?php foreach ($checkins as $c): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j', strtotime($c['check_date'])) ?></strong><span>Mood: <?= ucfirst($c['mood']) ?><?= $c['craving_level'] !== null ? ' | Craving: ' . $c['craving_level'] . '/100' : '' ?></span></div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Recent Journal Entries</h2>
  <?php if (empty($journal)): ?>
  <div style="padding:12px;color:var(--color-text-muted);">No journal entries yet.</div>
  <?php else: ?>
  <?php foreach ($journal as $entry): ?>
  <div class="journal-entry">
    <div class="journal-entry-header">
      <span class="journal-date"><?= date('M j, g:i A', strtotime($entry['created_at'])) ?></span>
      <span class="journal-mood mood-<?= $entry['mood'] ?>"><?= ucfirst($entry['mood']) ?></span>
    </div>
    <p><?= htmlspecialchars($entry['content']) ?></p>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Milestones</h2>
  <?php if (empty($milestones)): ?>
  <div style="padding:12px;color:var(--color-text-muted);">No milestones data.</div>
  <?php else: ?>
  <div class="milestones">
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
  </div>
  <?php endif; ?>
</div>
