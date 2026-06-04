<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (!$hasTherapist): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-user-md" style="font-size:48px;color:var(--color-text-muted);margin-bottom:16px;"></i>
  <p>You don't have a therapist assigned yet.</p>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-shield-halved" style="font-size:48px;color:var(--color-danger);margin-bottom:16px;"></i>
  <h2 style="margin-bottom:8px;">Need Urgent Help?</h2>
  <p style="color:var(--color-text-muted);margin-bottom:24px;">Your therapist <strong><?= htmlspecialchars($therapistName) ?></strong> will be notified immediately.</p>
  <?php
  $hasActive = false;
  foreach ($alerts as $a) { if ($a['status'] === 'active' || $a['status'] === 'acknowledged') { $hasActive = true; break; } }
  ?>
  <?php if ($hasActive): ?>
  <div style="background:rgba(209,69,59,0.06);border:1px solid rgba(209,69,59,0.2);border-radius:12px;padding:16px;margin-bottom:16px;">
    <p style="font-weight:600;color:var(--color-danger);">You have an active alert. Your therapist has been notified.</p>
  </div>
  <?php else: ?>
  <form action="/panel/sos/send" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <button type="submit" class="btn" style="background:var(--color-danger);color:white;padding:16px 48px;font-size:18px;font-weight:600;border-radius:12px;border:none;cursor:pointer;" onclick="return confirm('Send SOS alert to your therapist?')">
      <i class="fa-solid fa-triangle-exclamation"></i> I NEED HELP
    </button>
  </form>
  <?php endif; ?>
</div>

<?php if (!empty($alerts)): ?>
<div class="card">
  <h3>Your SOS History</h3>
  <div style="margin-top:12px;">
  <?php foreach ($alerts as $alert): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-bottom:1px solid var(--color-border);">
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
</div>
<?php endif; ?>
<?php endif; ?>
