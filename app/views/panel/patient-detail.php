<a href="/therapist/patients" class="btn btn-outline" style="margin-bottom:14px;"><i class="fa-solid fa-arrow-left"></i> Back to Patients</a>

<div class="card patient-header">
  <?php if (!empty($patient['avatar'])): ?>
  <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" class="patient-avatar" style="object-fit:cover;">
  <?php else: ?>
  <div class="patient-avatar"><?= htmlspecialchars($patient['initials']) ?></div>
  <?php endif; ?>
  <div class="patient-info">
    <h2><?= htmlspecialchars($patient['name']) ?></h2>
    <span>Email: <?= htmlspecialchars($patient['email']) ?></span>
    <span>Member since <?= date('M j, Y', strtotime($patient['created_at'])) ?></span>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <h2>Recent Check-ins</h2>
    <?php if (empty($checkins)): ?>
    <div class="empty-state" style="padding:16px;">
      <i class="fa-regular fa-calendar"></i>
      <p>No check-ins yet.</p>
    </div>
    <?php else: ?>
    <?php foreach ($checkins as $c): ?>
    <div class="data-row">
      <div class="data-row-body">
        <strong><?= date('M j', strtotime($c['check_date'])) ?></strong>
        <span>Mood: <?= ucfirst($c['mood']) ?><?= $c['craving_level'] !== null ? ' &middot; Craving: ' . $c['craving_level'] . '/100' : '' ?></span>
      </div>
      <span class="badge badge-<?= $c['mood'] === 'struggling' || $c['mood'] === 'difficult' ? 'red' : ($c['mood'] === 'neutral' ? 'orange' : 'green') ?>">
        <?= ucfirst($c['mood']) ?>
      </span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="card">
    <h2>Recent Journal</h2>
    <?php if (empty($journal)): ?>
    <div class="empty-state" style="padding:16px;">
      <i class="fa-regular fa-book"></i>
      <p>No journal entries yet.</p>
    </div>
    <?php else: ?>
    <?php foreach ($journal as $entry): ?>
    <div class="data-row">
      <div class="data-row-body">
        <strong><?= date('M j, g:i A', strtotime($entry['created_at'])) ?></strong>
        <span><?= htmlspecialchars(substr($entry['content'], 0, 80)) ?><?= strlen($entry['content']) > 80 ? '...' : '' ?></span>
      </div>
      <span class="badge badge-<?= $entry['mood'] === 'struggling' || $entry['mood'] === 'difficult' ? 'red' : ($entry['mood'] === 'neutral' ? 'orange' : 'green') ?>">
        <?= ucfirst($entry['mood']) ?>
      </span>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
