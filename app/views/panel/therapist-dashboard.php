<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-heart-pulse"></i></div>
    <div class="stat-value"><?= $patientCount ?></div>
    <div class="stat-label">My Patients</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-regular fa-calendar"></i></div>
    <div class="stat-value"><?= count($upcomingAppointments) ?></div>
    <div class="stat-label">Upcoming Sessions</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-clock"></i></div>
    <div class="stat-value"><?= $todayAppointments ?></div>
    <div class="stat-label">Today's Sessions</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fa-regular fa-comments"></i></div>
    <div class="stat-value"><?= $recentActivity ?></div>
    <div class="stat-label">Recent Activity</div>
  </div>
</div>

<div class="card">
  <h2>My Patients</h2>
  <?php if (empty($patients)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No patients assigned yet</span></div>
  <?php else: ?>
  <?php foreach ($patients as $p): ?>
  <div class="appointment-item">
    <div>
      <strong><?= htmlspecialchars($p['name']) ?></strong>
      <span><?= htmlspecialchars($p['stage']) ?> — <?= htmlspecialchars($p['email']) ?></span>
    </div>
    <a href="/therapist/patient/<?= $p['id'] ?>" class="btn btn-outline" style="padding:6px 14px;font-size:13px;">View</a>
  </div>
  <?php endforeach; ?>
  <a href="/therapist/patients" class="btn btn-outline" style="margin-top:12px;width:100%;justify-content:center;">View All Patients</a>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Upcoming Sessions</h2>
  <?php if (empty($upcomingAppointments)): ?>
  <div class="overview-row"><span style="color:var(--color-text-muted);">No upcoming sessions</span></div>
  <?php else: ?>
  <?php foreach ($upcomingAppointments as $apt): ?>
  <div class="appointment-item">
    <div><strong><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></strong><span><?= htmlspecialchars($apt['patient_name']) ?> — <?= htmlspecialchars($apt['title']) ?></span></div>
    <span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : 'orange' ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="card">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/therapist/patients" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-solid fa-heart-pulse"></i> My Patients</a>
    <a href="/panel/appointments" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-calendar"></i> Schedule</a>
    <a href="/panel/community" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-comments"></i> Community</a>
    <a href="/panel/resources" class="btn" style="display:flex;align-items:center;gap:8px;justify-content:center;"><i class="fa-regular fa-file-lines"></i> Resources</a>
  </div>
</div>

<?php if (!empty($recentCheckins)): ?>
<div class="card">
  <h2>Recent Check-ins</h2>
  <?php foreach ($recentCheckins as $c): ?>
  <div class="overview-row">
    <span><strong><?= htmlspecialchars($c['patient_name']) ?></strong> — <?= ucfirst($c['mood']) ?> (<?= date('M j', strtotime($c['check_date'])) ?>)</span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
