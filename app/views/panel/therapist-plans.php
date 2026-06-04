<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<a href="/therapist/patient/<?= $patient['id'] ?>" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> Back to <?= htmlspecialchars($patient['name']) ?></a>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Treatment Plans — <?= htmlspecialchars($patient['name']) ?></h2>
    <a href="/therapist/patient/<?= $patient['id'] ?>/plans/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> New Plan</a>
  </div>
</div>

<?php if (empty($plans)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p style="color:var(--color-text-muted);">No treatment plans created yet.</p>
</div>
<?php else: ?>
<?php foreach ($plans as $p): ?>
<div class="card" style="border-left:4px solid <?= $p['status'] === 'active' ? 'var(--color-accent)' : ($p['status'] === 'completed' ? 'var(--color-success)' : 'var(--color-border)') ?>;">
  <div style="padding:16px 22px;">
    <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:12px;">
      <div>
        <h3 style="margin:0 0 4px;"><?= htmlspecialchars($p['title']) ?></h3>
        <span class="badge badge-<?= $p['status'] === 'active' ? 'green' : ($p['status'] === 'completed' ? 'gray' : 'orange') ?>"><?= ucfirst($p['status']) ?></span>
        <span style="font-size:12px;color:var(--color-text-muted);margin-left:8px;">Created <?= date('M j, Y', strtotime($p['created_at'])) ?></span>
      </div>
      <div style="display:flex;gap:8px;">
        <a href="/therapist/patient/<?= $patient['id'] ?>/plans/edit/<?= $p['id'] ?>" class="btn btn-outline" style="padding:6px 12px;font-size:13px;"><i class="fa-regular fa-pen-to-square"></i></a>
        <form action="/therapist/patient/<?= $patient['id'] ?>/plans/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this plan?')">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
          <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:13px;color:var(--color-danger);"><i class="fa-solid fa-trash"></i></button>
        </form>
      </div>
    </div>

    <?php if ($p['goals']): ?>
    <div style="margin-bottom:8px;">
      <strong style="font-size:13px;color:var(--color-primary);">Goals:</strong>
      <p style="font-size:13px;color:var(--color-text-muted);margin-top:2px;white-space:pre-wrap;"><?= htmlspecialchars($p['goals']) ?></p>
    </div>
    <?php endif; ?>
    <?php if ($p['objectives']): ?>
    <div style="margin-bottom:8px;">
      <strong style="font-size:13px;color:var(--color-primary);">Objectives:</strong>
      <p style="font-size:13px;color:var(--color-text-muted);margin-top:2px;white-space:pre-wrap;"><?= htmlspecialchars($p['objectives']) ?></p>
    </div>
    <?php endif; ?>
    <?php if ($p['activities']): ?>
    <div style="margin-bottom:8px;">
      <strong style="font-size:13px;color:var(--color-primary);">Activities:</strong>
      <p style="font-size:13px;color:var(--color-text-muted);margin-top:2px;white-space:pre-wrap;"><?= htmlspecialchars($p['activities']) ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
