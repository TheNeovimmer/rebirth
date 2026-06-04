<div class="card">
  <h2>Patient Conversations</h2>
</div>

<?php if (empty($conversations)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p style="color:var(--color-text-muted);">No conversations yet.</p>
</div>
<?php else: ?>
<?php foreach ($conversations as $conv): ?>
<a href="/therapist/messages/<?= $conv['id'] ?>" class="card" style="display:block;padding:16px;margin-bottom:8px;">
  <div style="display:flex;align-items:center;gap:12px;">
    <?php if (!empty($conv['patient_avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($conv['patient_avatar']) ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <?php else: ?>
    <div style="width:40px;height:40px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($conv['patient_initials']) ?></div>
    <?php endif; ?>
    <div style="flex:1;">
      <strong><?= htmlspecialchars($conv['patient_name']) ?></strong>
      <?php if ($conv['unread'] > 0): ?>
      <span class="badge badge-red" style="margin-left:8px;"><?= $conv['unread'] ?> new</span>
      <?php endif; ?>
      <div style="font-size:12px;color:var(--color-text-muted);">Last message: <?= date('M j, g:i A', strtotime($conv['updated_at'])) ?></div>
    </div>
    <i class="fa-solid fa-chevron-right" style="color:var(--color-text-muted);"></i>
  </div>
</a>
<?php endforeach; ?>
<?php endif; ?>
