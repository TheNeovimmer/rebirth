<?php if (empty($patients)): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-regular fa-user"></i>
    <p>No patients assigned yet.</p>
  </div>
</div>
<?php else: ?>
<div class="card" style="padding-bottom:8px;">
  <h2>My Patients (<?= count($patients) ?>)</h2>
  <?php foreach ($patients as $p): ?>
  <div class="data-row">
    <?php if (!empty($p['avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($p['avatar']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <?php else: ?>
    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-accent);color:var(--color-primary-dark);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;"><?= htmlspecialchars($p['initials']) ?></div>
    <?php endif; ?>
    <div class="data-row-body">
      <strong><?= htmlspecialchars($p['name']) ?></strong>
      <span><?= htmlspecialchars($p['email']) ?></span>
    </div>
    <a href="/therapist/patient/<?= $p['id'] ?>" class="btn btn-outline btn-sm">View</a>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
