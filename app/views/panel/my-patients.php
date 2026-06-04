<div class="card">
  <h2>My Patients</h2>
  <?php if (empty($patients)): ?>
  <div style="padding:20px;text-align:center;color:var(--color-text-muted);">No patients assigned yet.</div>
  <?php else: ?>
  <?php foreach ($patients as $p): ?>
  <div class="appointment-item">
    <div style="display:flex;align-items:center;gap:12px;">
      <?php if (!empty($p['avatar'])): ?>
      <img src="/uploads/avatars/<?= htmlspecialchars($p['avatar']) ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">
      <?php else: ?>
      <div style="width:40px;height:40px;border-radius:50%;background:var(--color-accent);color:var(--color-primary-dark);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;flex-shrink:0;"><?= htmlspecialchars($p['initials']) ?></div>
      <?php endif; ?>
      <div>
        <strong><?= htmlspecialchars($p['name']) ?></strong>
        <span><?= htmlspecialchars($p['email']) ?> — <?= htmlspecialchars($p['stage']) ?></span>
      </div>
    </div>
    <a href="/therapist/patient/<?= $p['id'] ?>" class="btn btn-outline" style="padding:6px 14px;font-size:13px;">View</a>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>
