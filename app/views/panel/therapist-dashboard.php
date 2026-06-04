<div class="therapist-stats">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
    <div>
      <div class="stat-value"><?= $patientCount ?></div>
      <div class="stat-label">Patients</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-regular fa-comment-dots"></i></div>
    <div>
      <div class="stat-value"><?= $unreadCount ?></div>
      <div class="stat-label">Unread Messages</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red"><i class="fa-solid fa-circle-exclamation"></i></div>
    <div>
      <div class="stat-value" style="color:var(--color-danger);"><?= $activeSos ?></div>
      <div class="stat-label">Active SOS</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-calendar-check"></i></div>
    <div>
      <div class="stat-value"><?= $recentActivity ?></div>
      <div class="stat-label">Check-ins Today</div>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <div class="section-header">
      <h2>My Patients</h2>
      <a href="/therapist/patients" class="btn btn-ghost btn-xs">View all</a>
    </div>
    <?php if (!empty($patients)): ?>
      <?php foreach (array_slice($patients, 0, 5) as $patient): ?>
      <div class="patient-list-item">
        <div class="patient-list-item-info">
          <strong><?= htmlspecialchars($patient['name'] ?? '') ?></strong>
          <span><?= htmlspecialchars($patient['email'] ?? '') ?></span>
        </div>
        <a href="/therapist/patient/<?= $patient['id'] ?>" class="btn btn-outline btn-sm">View</a>
      </div>
      <?php endforeach; ?>
      <a href="/therapist/patients" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;margin-top:8px;">View All Patients</a>
    <?php else: ?>
    <div class="empty-state">
      <i class="fa-regular fa-user"></i>
      <p>No patients assigned yet.</p>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="section-header">
      <h2>Recent Check-ins</h2>
    </div>
    <?php if (!empty($recentCheckins)): ?>
      <?php foreach ($recentCheckins as $checkin): ?>
      <div class="data-row">
        <div class="data-row-body">
          <strong><?= htmlspecialchars($checkin['patient_name'] ?? '') ?></strong>
          <span><?= date('M j, Y', strtotime($checkin['date'] ?? 'now')) ?></span>
        </div>
        <span class="badge badge-<?= $checkin['mood'] === 'struggling' || $checkin['mood'] === 'difficult' ? 'red' : ($checkin['mood'] === 'neutral' ? 'orange' : 'green') ?>">
          <?= ucfirst(htmlspecialchars($checkin['mood'] ?? '')) ?>
        </span>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
    <div class="empty-state">
      <i class="fa-regular fa-calendar"></i>
      <p>No recent check-ins.</p>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card" style="padding-bottom:16px;">
  <h2>Quick Actions</h2>
  <div class="quick-actions">
    <a href="/therapist/patients" class="btn"><i class="fa-regular fa-user"></i> My Patients</a>
    <a href="/therapist/messages" class="btn"><i class="fa-regular fa-comment-dots"></i> Messages</a>
    <a href="/therapist/sos" class="btn danger"><i class="fa-solid fa-circle-exclamation"></i> SOS</a>
    <a href="/therapist/resources" class="btn"><i class="fa-regular fa-bookmark"></i> Resources</a>
  </div>
</div>
