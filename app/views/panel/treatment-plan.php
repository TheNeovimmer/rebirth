<div class="card">
  <h2>My Treatment Plan</h2>
</div>

<?php if ($activePlan): ?>
<div class="card" style="border-left:4px solid var(--color-accent);">
  <div style="padding:20px 22px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
      <h3 style="margin:0;"><?= htmlspecialchars($activePlan['title']) ?></h3>
      <span class="badge badge-green">Active</span>
    </div>
    <p style="font-size:13px;color:var(--color-text-muted);margin-bottom:16px;">Created by <?= htmlspecialchars($activePlan['therapist_name']) ?></p>

    <?php if ($activePlan['goals']): ?>
    <div style="margin-bottom:16px;">
      <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--color-primary);">Recovery Goals</h4>
      <p style="font-size:14px;color:var(--color-text-muted);white-space:pre-wrap;"><?= htmlspecialchars($activePlan['goals']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($activePlan['objectives']): ?>
    <div style="margin-bottom:16px;">
      <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--color-primary);">Treatment Objectives</h4>
      <p style="font-size:14px;color:var(--color-text-muted);white-space:pre-wrap;"><?= htmlspecialchars($activePlan['objectives']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($activePlan['activities']): ?>
    <div style="margin-bottom:16px;">
      <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--color-primary);">Assigned Activities</h4>
      <p style="font-size:14px;color:var(--color-text-muted);white-space:pre-wrap;"><?= htmlspecialchars($activePlan['activities']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($activePlan['coping_strategies']): ?>
    <div style="margin-bottom:16px;">
      <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--color-primary);">Coping Strategies</h4>
      <p style="font-size:14px;color:var(--color-text-muted);white-space:pre-wrap;"><?= htmlspecialchars($activePlan['coping_strategies']) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($activePlan['recommendations']): ?>
    <div>
      <h4 style="font-size:14px;font-weight:600;margin-bottom:8px;color:var(--color-primary);">Therapist Recommendations</h4>
      <p style="font-size:14px;color:var(--color-text-muted);white-space:pre-wrap;"><?= htmlspecialchars($activePlan['recommendations']) ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-clipboard-list" style="font-size:48px;color:var(--color-text-muted);margin-bottom:16px;"></i>
  <p style="color:var(--color-text-muted);">No active treatment plan yet. Your therapist will create one for you.</p>
</div>
<?php endif; ?>

<?php if (!empty($allPlans)): ?>
<div class="card">
  <h2>Plan History</h2>
  <div style="padding:0 22px 16px;">
    <?php foreach ($allPlans as $p): ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid var(--color-border);">
      <div>
        <strong style="font-size:14px;"><?= htmlspecialchars($p['title']) ?></strong>
        <span style="font-size:12px;color:var(--color-text-muted);margin-left:8px;"><?= date('M j, Y', strtotime($p['created_at'])) ?></span>
      </div>
      <span class="badge badge-<?= $p['status'] === 'active' ? 'green' : ($p['status'] === 'completed' ? 'gray' : 'orange') ?>"><?= ucfirst($p['status']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
