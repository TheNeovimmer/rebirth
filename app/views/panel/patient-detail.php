<a href="/therapist/patients" class="btn btn-outline" style="margin-bottom:16px;"><i class="fa-solid fa-arrow-left"></i> Back to Patients</a>

<div class="card" style="display:flex;align-items:center;gap:16px;">
  <?php if (!empty($patient['avatar'])): ?>
  <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;">
  <?php else: ?>
  <div style="width:64px;height:64px;border-radius:50%;background:var(--color-accent);color:var(--color-primary-dark);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
  <?php endif; ?>
  <div style="flex:1;">
    <h2 style="margin:0 0 4px;"><?= htmlspecialchars($patient['name']) ?></h2>
    <div class="overview-row" style="margin:0;"><span>Email: <?= htmlspecialchars($patient['email']) ?></span></div>
    <div class="overview-row" style="margin:0;"><span>Stage: <?= htmlspecialchars($patient['stage']) ?></span></div>
    <div class="overview-row" style="margin:0;"><span>Member since: <?= date('M j, Y', strtotime($patient['created_at'])) ?></span></div>
  </div>
  <div style="display:flex;flex-direction:column;gap:6px;flex-shrink:0;">
    <a href="/therapist/patient/<?= $patient['id'] ?>/plans" class="btn btn-outline btn-sm"><i class="fa-solid fa-clipboard-list"></i> Plans</a>
    <a href="/therapist/patient/<?= $patient['id'] ?>/progress" class="btn btn-outline btn-sm"><i class="fa-solid fa-chart-line"></i> Progress</a>
    <a href="/therapist/patient/<?= $patient['id'] ?>/timeline" class="btn btn-outline btn-sm"><i class="fa-solid fa-timeline"></i> Timeline</a>
  </div>
</div>

<div class="card">
  <h2>Recent Check-ins</h2>
  <?php if (empty($checkins)): ?>
  <div style="padding:12px;color:var(--color-text-muted);">No check-ins yet.</div>
  <?php else: ?>
  <?php foreach ($checkins as $c): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j', strtotime($c['check_date'])) ?></strong><span>Mood: <?= ucfirst($c['mood']) ?><?= $c['craving_level'] !== null ? ' | Craving: ' . $c['craving_level'] . '/100' : '' ?></span></div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Recent Journal Entries</h2>
  <?php if (empty($journal)): ?>
  <div style="padding:12px;color:var(--color-text-muted);">No journal entries yet.</div>
  <?php else: ?>
  <?php foreach ($journal as $entry): ?>
  <div class="journal-entry">
    <div class="journal-entry-header">
      <span class="journal-date"><?= date('M j, g:i A', strtotime($entry['created_at'])) ?></span>
      <span class="journal-mood mood-<?= $entry['mood'] ?>"><?= ucfirst($entry['mood']) ?></span>
    </div>
    <p><?= htmlspecialchars($entry['content']) ?></p>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Milestones</h2>
  <?php if (empty($milestones)): ?>
  <div style="padding:12px;color:var(--color-text-muted);">No milestones data.</div>
  <?php else: ?>
  <div class="milestones">
    <?php foreach ($milestones as $ms): ?>
    <div class="milestone <?= $ms['achieved'] ? 'done' : '' ?>">
      <div class="milestone-icon"><?= $ms['achieved'] ? '<i class="fa-solid fa-check"></i>' : ($ms['progress'] > 0 ? '<i class="fa-solid fa-spinner"></i>' : '<i class="fa-solid fa-lock"></i>') ?></div>
      <div class="milestone-info">
        <strong><?= htmlspecialchars($ms['name']) ?></strong>
        <span><?= htmlspecialchars($ms['description']) ?></span>
      </div>
      <span><?= $ms['progress'] ?>%</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Clinical Notes</h2>
  <div style="padding:0 22px 16px;">
    <?php if (empty($clinicalNotes)): ?>
    <p style="color:var(--color-text-muted);font-size:14px;">No clinical notes yet.</p>
    <?php else: ?>
    <?php foreach ($clinicalNotes as $note): ?>
    <div style="padding:12px 0;border-bottom:1px solid var(--color-border);">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <span style="font-size:12px;font-weight:600;color:var(--color-primary);"><?= $note['session_date'] ? 'Session: ' . date('M j, Y', strtotime($note['session_date'])) : 'Note' ?></span>
        <span style="font-size:11px;color:var(--color-text-muted);"><?= date('M j, Y g:i A', strtotime($note['created_at'])) ?></span>
      </div>
      <p style="font-size:14px;color:var(--color-text);white-space:pre-wrap;"><?= htmlspecialchars($note['content']) ?></p>
      <form action="/therapist/patient/<?= $patient['id'] ?>/notes/create" method="POST" style="display:inline;">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="content" value="">
        <input type="hidden" name="id" value="">
      </form>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <form action="/therapist/patient/<?= $patient['id'] ?>/notes/create" method="POST" style="padding:0 22px 16px;border-top:1px solid var(--color-border);padding-top:16px;">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <div class="form-group" style="margin-bottom:8px;">
      <label class="form-label">Session Date (optional)</label>
      <input class="form-input" name="session_date" type="date">
    </div>
    <div class="form-group" style="margin-bottom:8px;">
      <label class="form-label">Private Note</label>
      <textarea class="form-input" name="content" rows="3" placeholder="Write a private clinical note..." required></textarea>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-save"></i> Save Note</button>
  </form>
</div>

<div class="card">
  <h2>Relapse History</h2>
  <div style="padding:0 22px 16px;">
    <?php if (empty($relapses)): ?>
    <p style="color:var(--color-text-muted);font-size:14px;">No relapses recorded.</p>
    <?php else: ?>
    <?php foreach ($relapses as $r): ?>
    <div style="padding:10px 0;border-bottom:1px solid var(--color-border);border-left:3px solid <?= $r['severity'] === 'severe' ? 'var(--color-danger)' : ($r['severity'] === 'moderate' ? 'var(--color-warning)' : 'var(--color-accent)') ?>;padding-left:10px;">
      <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <span class="badge badge-<?= $r['severity'] === 'severe' ? 'red' : ($r['severity'] === 'moderate' ? 'orange' : 'green') ?>"><?= ucfirst($r['severity']) ?></span>
        <span style="font-size:12px;color:var(--color-text-muted);"><?= date('M j, Y', strtotime($r['relapse_date'])) ?></span>
      </div>
      <?php if ($r['trigger']): ?>
      <div style="font-size:14px;"><strong>Trigger:</strong> <?= htmlspecialchars($r['trigger']) ?></div>
      <?php endif; ?>
      <?php if ($r['action_taken']): ?>
      <div style="font-size:13px;color:var(--color-text-muted);">Action: <?= htmlspecialchars($r['action_taken']) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <?php if (!empty($triggers)): ?>
  <div style="border-top:1px solid var(--color-border);padding:12px 22px;">
    <strong style="font-size:13px;color:var(--color-primary);">Common Triggers</strong>
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
      <?php foreach ($triggers as $t): ?>
      <span class="badge badge-orange"><?= htmlspecialchars($t['trigger']) ?> (<?= $t['count'] ?>)</span>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

