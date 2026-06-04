<div class="card">
  <h2>SOS Alerts</h2>
  <p style="color:var(--color-text-muted);font-size:14px;">Active and recent emergency alerts from your patients.</p>
</div>

<?php if (empty($alerts)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-shield" style="font-size:48px;color:var(--color-success);margin-bottom:16px;"></i>
  <p style="color:var(--color-text-muted);">No active SOS alerts. All patients are safe.</p>
</div>
<?php else: ?>
<?php foreach ($alerts as $alert): ?>
<div class="card" style="border-left:4px solid <?= $alert['status'] === 'active' ? 'var(--color-danger)' : 'var(--color-warning)' ?>;">
  <div style="display:flex;justify-content:space-between;align-items:start;">
    <div>
      <h3 style="margin-bottom:4px;"><?= htmlspecialchars($alert['patient_name']) ?></h3>
      <span class="badge badge-<?= $alert['status'] === 'active' ? 'red' : 'orange' ?>"><?= ucfirst($alert['status']) ?></span>
      <span style="font-size:13px;color:var(--color-text-muted);margin-left:8px;"><?= date('M j, g:i A', strtotime($alert['created_at'])) ?> (<?= time() - strtotime($alert['created_at']) > 60 ? floor((time() - strtotime($alert['created_at']))/60) . ' min ago' : 'Just now' ?>)</span>
    </div>
    <div style="display:flex;gap:8px;">
      <?php if ($alert['status'] === 'active'): ?>
      <form action="/therapist/sos/acknowledge" method="POST" style="display:inline;">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $alert['id'] ?>">
        <button type="submit" class="btn btn-outline" style="border-color:var(--color-warning);color:var(--color-warning);">Acknowledge</button>
      </form>
      <?php endif; ?>
      <form action="/therapist/sos/resolve" method="POST" style="display:inline;" onsubmit="return confirm('Mark this alert as resolved?')">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $alert['id'] ?>">
        <button type="submit" class="btn btn-outline" style="border-color:var(--color-success);color:var(--color-success);">Resolve</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
