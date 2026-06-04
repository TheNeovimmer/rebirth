<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if ($user['role'] === 'therapist'): ?>

<div class="card">
  <h2>My Schedule</h2>
  <?php if (empty($appointments)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No upcoming sessions</span></div>
  <?php else: ?>
  <?php foreach ($appointments as $apt): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['patient_name']) ?> — <?= htmlspecialchars($apt['title']) ?></span></div>
    <span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : 'orange' ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Need Help Now?</h2>
  <div class="quick-actions">
    <a href="/therapist/patients" class="btn" style="flex:1;display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-heart-pulse"></i> My Patients</a>
    <a href="/panel/community" class="btn" style="flex:1;display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-comments"></i> Community</a>
  </div>
</div>

<?php else: ?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Upcoming Sessions</h2>
    <button class="btn btn-primary" id="bookBtn"><i class="fa-solid fa-plus"></i> Book</button>
  </div>
  <?php
  $upcoming = array_filter($appointments, fn($a) => in_array($a['status'], ['confirmed','pending']));
  if (empty($upcoming)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No upcoming sessions</span></div>
  <?php else: ?>
  <?php foreach ($upcoming as $apt): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['title']) ?><?= $apt['therapist_name'] ? ' with ' . htmlspecialchars($apt['therapist_name']) : '' ?></span></div>
    <span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : 'orange' ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Past Sessions</h2>
  <?php
  $past = array_filter($appointments, fn($a) => $a['status'] === 'completed');
  if (empty($past)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No past sessions</span></div>
  <?php else: ?>
  <?php foreach ($past as $apt): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['title']) ?></span></div>
    <span class="badge badge-gray">Completed</span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Need Help Now?</h2>
  <div class="quick-actions">
    <a href="/panel/sos" class="btn danger" style="flex:1;display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-phone"></i> Emergency</a>
    <a href="/panel/appointments" class="btn" style="flex:1;display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-clock"></i> Reschedule</a>
  </div>
  <div style="margin-top:12px;text-align:center;">
    <span style="font-size:14px;color:var(--color-text-muted);">24/7 Crisis Hotline: <strong>1-800-REBIRTH</strong></span>
  </div>
</div>

<div class="modal-overlay" id="bookModal" style="display:none;">
  <div class="modal">
    <form action="/panel/appointments/create" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>Book Appointment</h3>
        <button type="button" class="modal-close" id="closeBookModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="appt_title">Session Title</label>
          <input class="form-input" id="appt_title" name="title" placeholder="e.g., Weekly Session" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="appt_therapist">Therapist</label>
          <select class="form-select" id="appt_therapist" name="therapist_id">
            <option value="">Select therapist (optional)</option>
            <?php foreach ($therapists as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="appt_date">Date & Time</label>
          <input class="form-input" id="appt_date" name="date_time" type="datetime-local" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Book Session</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var bookBtn = document.getElementById('bookBtn');
    var modal = document.getElementById('bookModal');
    var closeBtn = document.getElementById('closeBookModal');
    if (bookBtn) bookBtn.addEventListener('click', function () { modal.style.display = 'flex'; });
    if (closeBtn) closeBtn.addEventListener('click', function () { modal.style.display = 'none'; });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.style.display = 'none'; });
  });
</script>

<?php endif; ?>
