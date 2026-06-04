<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (!$hasTherapist): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-solid fa-user-md"></i>
    <p>You don't have a therapist assigned yet.</p>
  </div>
</div>
<?php else: ?>
<div class="card sos-card">
  <i class="fa-solid fa-shield-halved"></i>
  <h2>Need Urgent Help?</h2>
  <p>Your therapist <strong><?= htmlspecialchars($therapistName) ?></strong> will be notified immediately.</p>
  <?php
  $hasActive = false;
  foreach ($alerts as $a) { if ($a['status'] === 'active' || $a['status'] === 'acknowledged') { $hasActive = true; break; } }
  ?>
  <?php if ($hasActive): ?>
  <div class="sos-active-banner">
    <p><i class="fa-solid fa-triangle-exclamation"></i> You have an active alert. Your therapist has been notified.</p>
  </div>
  <?php else: ?>
  <form action="/panel/sos/send" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <button type="submit" class="sos-btn" onclick="return confirm('Send SOS alert to your therapist?')">
      <i class="fa-solid fa-triangle-exclamation"></i> I NEED HELP
    </button>
  </form>
  <?php endif; ?>
</div>

<?php if (!empty($alerts)): ?>
<div class="card">
  <h2>SOS History</h2>
  <?php foreach ($alerts as $alert): ?>
  <div class="sos-history-item">
    <div>
      <span class="badge badge-<?= $alert['status'] === 'resolved' ? 'green' : ($alert['status'] === 'acknowledged' ? 'orange' : 'red') ?>">
        <?= ucfirst($alert['status']) ?>
      </span>
      <span style="font-size:13px;color:var(--color-text-muted);margin-left:8px;"><?= date('M j, g:i A', strtotime($alert['created_at'])) ?></span>
    </div>
    <?php if ($alert['status'] === 'resolved' && $alert['notes']): ?>
    <span style="font-size:12px;color:var(--color-text-muted);max-width:200px;text-align:right;"><?= htmlspecialchars($alert['notes']) ?></span>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>
