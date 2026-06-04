<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="alert" style="background:rgba(209,69,59,0.08);color:var(--color-danger);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (!$hasTherapist): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-user-md" style="font-size:48px;color:var(--color-text-muted);margin-bottom:16px;"></i>
  <p>You need a therapist assigned before you can track relapses.</p>
</div>
<?php else: ?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Relapse History</h2>
    <button class="btn btn-primary" onclick="document.getElementById('relapseModal').style.display='flex'"><i class="fa-solid fa-plus"></i> Log Relapse</button>
  </div>
</div>

<?php if (empty($relapses)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-shield" style="font-size:48px;color:var(--color-success);margin-bottom:16px;"></i>
  <p style="color:var(--color-text-muted);">No relapses recorded. Keep up the great work!</p>
</div>
<?php else: ?>
<?php foreach ($relapses as $r): ?>
<div class="card" style="border-left:4px solid <?= $r['severity'] === 'severe' ? 'var(--color-danger)' : ($r['severity'] === 'moderate' ? 'var(--color-warning)' : 'var(--color-accent)') ?>;">
  <div style="display:flex;justify-content:space-between;align-items:start;padding:16px;">
    <div style="flex:1;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
        <span class="badge badge-<?= $r['severity'] === 'severe' ? 'red' : ($r['severity'] === 'moderate' ? 'orange' : 'green') ?>"><?= ucfirst($r['severity']) ?></span>
        <span style="font-size:13px;color:var(--color-text-muted);"><?= date('M j, Y g:i A', strtotime($r['relapse_date'])) ?></span>
      </div>
      <?php if ($r['trigger']): ?>
      <div style="font-size:14px;margin-bottom:4px;"><strong>Trigger:</strong> <?= htmlspecialchars($r['trigger']) ?></div>
      <?php endif; ?>
      <?php if ($r['description']): ?>
      <p style="font-size:14px;color:var(--color-text-muted);margin-bottom:4px;"><?= htmlspecialchars($r['description']) ?></p>
      <?php endif; ?>
      <?php if ($r['action_taken']): ?>
      <div style="background:var(--color-bg-alt);padding:8px 12px;border-radius:var(--radius-sm);font-size:13px;margin-top:6px;">
        <strong>Action taken:</strong> <?= htmlspecialchars($r['action_taken']) ?>
      </div>
      <?php endif; ?>
    </div>
    <form action="/panel/relapses/delete" method="POST" style="flex-shrink:0;" onsubmit="return confirm('Delete this entry?')">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="id" value="<?= $r['id'] ?>">
      <button type="submit" class="btn btn-outline" style="padding:4px 8px;font-size:12px;color:var(--color-danger);"><i class="fa-solid fa-trash"></i></button>
    </form>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($triggers)): ?>
<div class="card">
  <h2>Common Triggers</h2>
  <div style="padding:0 22px 16px;">
    <?php foreach ($triggers as $t): ?>
    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--color-border);font-size:14px;">
      <span><?= htmlspecialchars($t['trigger']) ?></span>
      <span class="badge badge-orange"><?= $t['count'] ?>x</span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="modal-overlay" id="relapseModal" style="display:none;">
  <div class="modal">
    <form action="/panel/relapses/create" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>Log Relapse</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('relapseModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Date & Time</label>
          <input class="form-input" name="relapse_date" type="datetime-local" value="<?= date('Y-m-d\TH:i') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Severity</label>
          <select class="form-select" name="severity" required>
            <option value="mild">Mild</option>
            <option value="moderate" selected>Moderate</option>
            <option value="severe">Severe</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Trigger</label>
          <input class="form-input" name="trigger" placeholder="What triggered this?" required>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-input" name="description" rows="3" placeholder="What happened?"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Action Taken</label>
          <textarea class="form-input" name="action_taken" rows="2" placeholder="What did you do after?"></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Entry</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
