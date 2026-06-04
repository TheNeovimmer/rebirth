<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3>All Appointments</h3>
    <button class="btn btn-primary" id="createApptBtn"><i class="fa-solid fa-plus"></i> New</button>
  </div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Date/Time</th><th>Patient</th><th>Therapist</th><th>Title</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($appointments as $apt): ?>
          <tr>
            <td><?= date('M j, g:i A', strtotime($apt['date_time'])) ?></td>
            <td><?= htmlspecialchars($apt['patient_name']) ?></td>
            <td><?= htmlspecialchars($apt['therapist_name'] ?? 'Unassigned') ?></td>
            <td><?= htmlspecialchars($apt['title']) ?></td>
            <td><span class="badge badge-<?= $apt['status'] === 'confirmed' ? 'green' : ($apt['status'] === 'pending' ? 'orange' : ($apt['status'] === 'completed' ? 'gray' : 'gray')) ?>"><?= htmlspecialchars(ucfirst($apt['status'])) ?></span></td>
            <td>
              <button class="btn btn-outline" style="padding:4px 10px;font-size:12px;" onclick="editAppt(<?= $apt['id'] ?>, '<?= htmlspecialchars(addslashes($apt['title'])) ?>', '<?= $apt['date_time'] ?>', '<?= $apt['status'] ?>', <?= $apt['therapist_id'] ?? 'null' ?>)"><i class="fa-regular fa-pen-to-square"></i></button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Appointment Modal -->
<div class="modal-overlay" id="createApptModal" style="display:none;">
  <div class="modal">
    <form action="/admin/appointments/create" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>New Appointment</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('createApptModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Patient</label>
          <select class="form-select" name="user_id" required>
            <option value="">Select patient</option>
            <?php foreach ($patients as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Therapist</label>
          <select class="form-select" name="therapist_id">
            <option value="">Select therapist</option>
            <?php foreach ($therapists as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Title</label>
          <input class="form-input" name="title" required>
        </div>
        <div class="form-group">
          <label class="form-label">Date & Time</label>
          <input class="form-input" name="date_time" id="apptDateTime" type="datetime-local" required>
          <div id="availabilitySlots" style="margin-top:8px;display:none;">
            <label class="form-label">Available Slots</label>
            <div id="slotsList" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;"></div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create Appointment</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal-overlay" id="editApptModal" style="display:none;">
  <div class="modal">
    <form action="/admin/appointments/update" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="id" id="editApptId">
      <div class="modal-header">
        <h3>Edit Appointment</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('editApptModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input class="form-input" name="title" id="editApptTitle" required>
        </div>
        <div class="form-group">
          <label class="form-label">Therapist</label>
          <select class="form-select" name="therapist_id" id="editApptTherapist">
            <option value="">Select therapist</option>
            <?php foreach ($therapists as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Date & Time</label>
          <input class="form-input" name="date_time" id="editApptDate" type="datetime-local" required>
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-select" name="status" id="editApptStatus">
            <option value="confirmed">Confirmed</option>
            <option value="pending">Pending</option>
            <option value="cancelled">Cancelled</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('createApptBtn').addEventListener('click', function() {
    document.getElementById('createApptModal').style.display = 'flex';
  });
  function editAppt(id, title, dateTime, status, therapistId) {
    document.getElementById('editApptId').value = id;
    document.getElementById('editApptTitle').value = title;
    document.getElementById('editApptDate').value = dateTime.substring(0, 16);
    document.getElementById('editApptStatus').value = status;
    if (therapistId) document.getElementById('editApptTherapist').value = therapistId;
    document.getElementById('editApptModal').style.display = 'flex';
  }

  // Availability-aware scheduling
  const therapistSelect = document.querySelector('[name="therapist_id"]');
  const dateTimeInput = document.getElementById('apptDateTime');
  const slotsContainer = document.getElementById('availabilitySlots');
  const slotsList = document.getElementById('slotsList');

  if (therapistSelect && dateTimeInput) {
    async function fetchSlots() {
      const therapistId = therapistSelect.value;
      const date = dateTimeInput.value ? dateTimeInput.value.split('T')[0] : '';
      if (!therapistId || !date) { slotsContainer.style.display = 'none'; return; }
      try {
        const resp = await fetch(`/admin/appointments/availability?therapist_id=${therapistId}&date=${date}`);
        const slots = await resp.json();
        slotsContainer.style.display = 'block';
        slotsList.innerHTML = '';
        let hasAvailable = false;
        slots.forEach(s => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn ' + (s.available ? 'btn-outline' : 'btn-outline');
          btn.textContent = s.time;
          btn.style.cssText = 'padding:4px 12px;font-size:13px;' + (s.available ? '' : 'opacity:0.4;cursor:not-allowed;');
          btn.disabled = !s.available;
          if (s.available) {
            btn.onclick = () => {
              dateTimeInput.value = date + 'T' + s.time;
              document.querySelectorAll('#slotsList .btn').forEach(b => b.style.borderColor = '');
              btn.style.borderColor = 'var(--color-primary)';
            };
            hasAvailable = true;
          }
          slotsList.appendChild(btn);
        });
        if (!hasAvailable) {
          slotsList.innerHTML = '<span style="color:var(--color-danger);font-size:13px;">No available slots on this date</span>';
        }
      } catch(e) { slotsContainer.style.display = 'none'; }
    }
    therapistSelect.addEventListener('change', fetchSlots);
    dateTimeInput.addEventListener('change', fetchSlots);
  }
</script>
