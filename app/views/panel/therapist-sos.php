<?php if (empty($alerts)): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-regular fa-circle-check" style="color:var(--color-success);"></i>
    <p>No SOS alerts right now.</p>
  </div>
</div>
<?php else: ?>
<div class="card" style="padding-bottom:8px;">
  <h2>SOS Alerts</h2>
  <?php foreach ($alerts as $alert): ?>
  <div class="data-row">
    <div class="data-row-icon red"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div class="data-row-body">
      <strong><?= htmlspecialchars($alert['patient_name']) ?></strong>
      <span><?= date('M j, g:i A', strtotime($alert['created_at'])) ?> &middot; Status: <?= ucfirst($alert['status']) ?></span>
    </div>
    <?php if ($alert['status'] === 'active'): ?>
    <form action="/therapist/sos/acknowledge" method="POST" style="display:inline;">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="id" value="<?= $alert['id'] ?>">
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa-regular fa-check"></i> Acknowledge</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
