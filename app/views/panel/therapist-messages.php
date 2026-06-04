<?php if (empty($conversations)): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-regular fa-comment-dots"></i>
    <p>No conversations yet.</p>
  </div>
</div>
<?php else: ?>
<div class="card" style="padding-bottom:8px;">
  <h2>Patient Conversations</h2>
  <?php foreach ($conversations as $conv): ?>
  <a href="/therapist/messages/<?= $conv['id'] ?>" class="data-row" style="text-decoration:none;color:inherit;cursor:pointer;">
    <?php if (!empty($conv['patient_avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($conv['patient_avatar']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <?php else: ?>
    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($conv['patient_initials']) ?></div>
    <?php endif; ?>
    <div class="data-row-body">
      <strong><?= htmlspecialchars($conv['patient_name']) ?>
        <?php if ($conv['unread'] > 0): ?>
        <span class="badge badge-red" style="margin-left:6px;"><?= $conv['unread'] ?> new</span>
        <?php endif; ?>
      </strong>
      <span>Last message: <?= date('M j, g:i A', strtotime($conv['updated_at'])) ?></span>
    </div>
    <i class="fa-solid fa-chevron-right" style="color:var(--color-text-muted);flex-shrink:0;"></i>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
