<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <h2>Your Availability</h2>
  <p style="color:var(--color-text-muted);font-size:14px;">Set your weekly work hours. Patients can message you during these times.</p>
</div>

<div class="card">
  <form action="/therapist/availability/save" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <div id="availabilitySlots">
      <?php
      $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      $existingByDay = [];
      foreach ($slots as $s) { $existingByDay[$s['day_of_week']][] = $s; }
      $rowIndex = 0;
      foreach ($dayNames as $dow => $name):
      $daySlots = $existingByDay[$dow] ?? [];
      if (empty($daySlots)) $daySlots[] = ['day_of_week' => $dow, 'start_time' => '', 'end_time' => ''];
      foreach ($daySlots as $slot):
      ?>
      <div class="avail-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px;background:var(--color-bg-alt);border-radius:var(--radius-sm);">
        <span style="min-width:90px;font-weight:500;"><?= $name ?></span>
        <input type="hidden" name="days[]" value="<?= $dow ?>">
        <input type="time" name="starts[]" class="form-input" style="flex:1;" value="<?= htmlspecialchars($slot['start_time'] ?? '') ?>">
        <span style="color:var(--color-text-muted);">to</span>
        <input type="time" name="ends[]" class="form-input" style="flex:1;" value="<?= htmlspecialchars($slot['end_time'] ?? '') ?>">
        <button type="button" class="btn btn-outline" style="padding:4px 8px;font-size:12px;color:var(--color-danger);" onclick="this.closest('.avail-row').remove()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <?php $rowIndex++; endforeach; endforeach; ?>
    </div>
    <button type="button" class="btn btn-outline" onclick="addAvailRow()" style="margin-top:8px;"><i class="fa-solid fa-plus"></i> Add Time Slot</button>
    <button type="submit" class="btn btn-primary" style="margin-top:12px;width:100%;">Save Availability</button>
  </form>
</div>

<script>
const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
function addAvailRow() {
  const container = document.getElementById('availabilitySlots');
  const picker = document.createElement('div');
  picker.innerHTML = `<select class="form-select" style="flex:1;" onchange="addRowForDay(this.value);this.parentElement.remove()"><option value="">Select day...</option>${dayNames.map((n,i)=>'<option value="'+i+'">'+n+'</option>').join('')}</select>`;
  picker.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px;background:var(--color-bg-alt);border-radius:var(--radius-sm);';
  container.appendChild(picker);
}
function addRowForDay(dow) {
  const container = document.getElementById('availabilitySlots');
  const div = document.createElement('div');
  div.className = 'avail-row';
  div.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px;background:var(--color-bg-alt);border-radius:var(--radius-sm);';
  div.innerHTML = `
    <span style="min-width:90px;font-weight:500;">${dayNames[dow]}</span>
    <input type="hidden" name="days[]" value="${dow}">
    <input type="time" name="starts[]" class="form-input" style="flex:1;">
    <span style="color:var(--color-text-muted);">to</span>
    <input type="time" name="ends[]" class="form-input" style="flex:1;">
    <button type="button" class="btn btn-outline" style="padding:4px 8px;font-size:12px;color:var(--color-danger);" onclick="this.closest('.avail-row').remove()"><i class="fa-solid fa-xmark"></i></button>
  `;
  container.appendChild(div);
}
</script>
