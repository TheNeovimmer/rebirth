<a href="/therapist/patient/<?= $patient['id'] ?>" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> Back to <?= htmlspecialchars($patient['name']) ?></a>

<div class="card">
  <div style="display:flex;align-items:center;gap:12px;padding:20px 22px;">
    <?php if (!empty($patient['avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
    <?php else: ?>
    <div style="width:48px;height:48px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
    <?php endif; ?>
    <div>
      <h2 style="margin:0;"><?= htmlspecialchars($patient['name']) ?></h2>
      <span style="font-size:14px;color:var(--color-text-muted);">Patient Timeline</span>
    </div>
  </div>
</div>

<div class="card">
  <h2>All Activity</h2>
</div>

<?php if (empty($events)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p style="color:var(--color-text-muted);">No activity recorded yet.</p>
</div>
<?php else: ?>
<?php foreach ($events as $ev): ?>
<div class="card" style="margin-bottom:8px;">
  <div style="display:flex;align-items:flex-start;gap:12px;padding:14px 18px;">
    <div style="flex-shrink:0;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;
      <?= match($ev['type']) {
        'checkin' => 'background:rgba(76,175,125,0.1);color:var(--color-success)',
        'journal' => 'background:rgba(0,122,255,0.1);color:#007aff',
        'appointment' => 'background:rgba(180,191,115,0.15);color:var(--color-primary-dark)',
        'resource' => 'background:rgba(120,80,200,0.1);color:#7850c8',
        'sos' => 'background:rgba(209,69,59,0.1);color:var(--color-danger)',
        'relapse' => 'background:rgba(232,168,56,0.12);color:var(--color-warning)',
        'treatment_plan' => 'background:rgba(76,175,125,0.1);color:var(--color-success)',
        'clinical_note' => 'background:rgba(180,191,115,0.1);color:var(--color-accent)',
        default => 'background:var(--color-bg);color:var(--color-text-muted)',
      } ?>">
      <?= match($ev['type']) {
        'checkin' => '<i class="fa-regular fa-face-smile"></i>',
        'journal' => '<i class="fa-solid fa-book"></i>',
        'appointment' => '<i class="fa-regular fa-calendar"></i>',
        'resource' => '<i class="fa-solid fa-file"></i>',
        'sos' => '<i class="fa-solid fa-triangle-exclamation"></i>',
        'relapse' => '<i class="fa-solid fa-heart-crack"></i>',
        'treatment_plan' => '<i class="fa-solid fa-clipboard-list"></i>',
        'clinical_note' => '<i class="fa-solid fa-note-sticky"></i>',
        default => '<i class="fa-solid fa-circle"></i>',
      } ?>
    </div>
    <div style="flex:1;min-width:0;">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
        <strong style="font-size:14px;text-transform:capitalize;"><?= str_replace('_', ' ', $ev['type']) ?></strong>
        <span style="font-size:12px;color:var(--color-text-muted);flex-shrink:0;"><?= date('M j, Y g:i A', strtotime($ev['event_date'])) ?></span>
      </div>
      <div style="font-size:14px;color:var(--color-text);margin-top:2px;"><?= htmlspecialchars($ev['summary']) ?></div>
      <?php if ($ev['detail']): ?>
      <div style="font-size:13px;color:var(--color-text-muted);margin-top:2px;"><?= htmlspecialchars($ev['detail']) ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
