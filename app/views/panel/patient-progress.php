<a href="/therapist/patient/<?= $patient['id'] ?>" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> Back to Patient</a>

<div class="card" style="display:flex;align-items:center;gap:12px;">
  <?php if (!empty($patient['avatar'])): ?>
  <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
  <?php else: ?>
  <div style="width:48px;height:48px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
  <?php endif; ?>
  <div>
    <h2 style="margin:0;"><?= htmlspecialchars($patient['name']) ?></h2>
    <span style="font-size:14px;color:var(--color-text-muted);">Recovery Progress</span>
  </div>
</div>

<div class="card">
  <h2>Treatment Stages</h2>
</div>

<?php foreach ($stages as $stage): ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:start;">
    <div style="flex:1;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <?php
        $statusIcon = match($stage['status']) {
          'completed' => '<i class="fa-solid fa-check-circle" style="color:var(--color-success);font-size:20px;"></i>',
          'in_progress' => '<i class="fa-solid fa-spinner" style="color:var(--color-accent);font-size:20px;"></i>',
          default => '<i class="fa-solid fa-circle" style="color:var(--color-border);font-size:20px;"></i>',
        };
        ?>
        <?= $statusIcon ?>
        <h3 style="margin:0;"><?= htmlspecialchars($stage['stage_name']) ?></h3>
        <span class="badge badge-<?= $stage['status'] === 'completed' ? 'green' : ($stage['status'] === 'in_progress' ? 'orange' : 'gray') ?>"><?= ucfirst(str_replace('_', ' ', $stage['status'])) ?></span>
      </div>
      <?php if ($stage['started_at']): ?>
      <div style="font-size:13px;color:var(--color-text-muted);margin-bottom:4px;">Started: <?= date('M j, Y', strtotime($stage['started_at'])) ?></div>
      <?php endif; ?>
      <?php if ($stage['completed_at']): ?>
      <div style="font-size:13px;color:var(--color-text-muted);margin-bottom:4px;">Completed: <?= date('M j, Y', strtotime($stage['completed_at'])) ?></div>
      <?php endif; ?>
      <?php if ($stage['therapist_notes']): ?>
      <div style="background:var(--color-bg-alt);padding:8px 12px;border-radius:var(--radius-sm);margin-top:8px;font-size:14px;">
        <strong>Notes:</strong> <?= nl2br(htmlspecialchars($stage['therapist_notes'])) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <form action="/therapist/patient/<?= $patient['id'] ?>/progress/update" method="POST" style="margin-top:12px;display:flex;gap:8px;align-items:end;">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="stage_id" value="<?= $stage['id'] ?>">
    <div class="form-group" style="flex:1;margin:0;">
      <select class="form-select" name="status">
        <option value="not_started" <?= $stage['status'] === 'not_started' ? 'selected' : '' ?>>Not Started</option>
        <option value="in_progress" <?= $stage['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="completed" <?= $stage['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
      </select>
    </div>
    <div class="form-group" style="flex:2;margin:0;">
      <input type="text" name="notes" class="form-input" placeholder="Add note..." value="">
    </div>
    <button type="submit" class="btn btn-primary" style="white-space:nowrap;"><i class="fa-solid fa-save"></i> Update</button>
  </form>
</div>
<?php endforeach; ?>
