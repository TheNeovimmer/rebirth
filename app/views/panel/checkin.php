<?php if (isset($_GET['error'])): ?>
<div class="alert" style="background:rgba(209,69,59,0.08);color:var(--color-danger);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if ($todayCheckin): ?>
<div class="card">
  <h2>Today's Check-in</h2>
  <div class="checkin-list">
    <div style="padding:16px 22px;display:flex;align-items:center;gap:12px;">
      <span style="font-size:32px;"><?php
        $moods = ['great'=>'😊','good'=>'🙂','okay'=>'😐','tough'=>'😔','struggling'=>'😢'];
        echo $moods[$todayCheckin['mood']] ?? '😐';
      ?></span>
      <div>
        <strong style="font-size:16px;"><?= ucfirst($todayCheckin['mood']) ?></strong>
        <div style="font-size:13px;color:var(--color-text-muted);">Checked in today at <?= date('g:i A', strtotime($todayCheckin['created_at'])) ?></div>
      </div>
    </div>
    <?php if ($todayCheckin['craving_level'] !== null): ?>
    <div style="padding:0 22px 16px;">
      <span style="font-size:13px;color:var(--color-text-muted);">Craving level: <?= $todayCheckin['craving_level'] ?>/100</span>
    </div>
    <?php endif; ?>
    <?php if ($todayCheckin['note']): ?>
    <div style="padding:0 22px 16px;">
      <p style="font-size:14px;color:var(--color-text);font-style:italic;">"<?= htmlspecialchars($todayCheckin['note']) ?>"</p>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<form action="/checkin" method="POST">
  <input type="hidden" name="_token" value="<?= $_token ?>">
  <input type="hidden" name="mood" id="selectedMood" value="">
  <input type="hidden" name="craving_level" id="cravingInput" value="0">

  <div class="card">
    <h2>How are you feeling?</h2>
    <div class="mood-grid">
      <button type="button" class="mood-btn" data-value="great"><span>😊</span>Great</button>
      <button type="button" class="mood-btn" data-value="good"><span>🙂</span>Good</button>
      <button type="button" class="mood-btn" data-value="okay"><span>😐</span>Okay</button>
      <button type="button" class="mood-btn" data-value="tough"><span>😔</span>Tough</button>
      <button type="button" class="mood-btn" data-value="struggling"><span>😢</span>Struggling</button>
    </div>
  </div>

  <div class="card">
    <h2>Cravings</h2>
    <div class="slider-group">
      <label>How strong are your cravings right now?</label>
      <div class="range-labels"><span>None</span><span>Mild</span><span>Moderate</span><span>Strong</span><span>Severe</span></div>
      <input type="range" min="0" max="100" class="craving-slider" id="cravingSlider">
      <div class="craving-value" style="text-align:center;font-weight:600;margin-top:8px;color:var(--color-accent);" id="cravingDisplay">None</div>
    </div>
  </div>

  <div class="card">
    <h2>Journal Note</h2>
    <textarea class="form-input" name="note" rows="3" placeholder="How was your day? Any wins or challenges?"></textarea>
  </div>

  <button type="submit" class="btn btn-primary btn-block" style="margin-top:4px;" id="saveCheckinBtn" disabled>Select your mood first</button>
</form>
<?php endif; ?>

<script>
  (function() {
    var moodBtns = document.querySelectorAll('.mood-btn');
    var selectedInput = document.getElementById('selectedMood');
    var saveBtn = document.getElementById('saveCheckinBtn');

    moodBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        moodBtns.forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        selectedInput.value = this.dataset.value;
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Save Check-in';
        }
      });
    });

    var slider = document.getElementById('cravingSlider');
    var display = document.getElementById('cravingDisplay');
    var input = document.getElementById('cravingInput');
    if (slider) {
      var labels = ['None', 'Mild', 'Moderate', 'Strong', 'Severe'];
      slider.addEventListener('input', function() {
        var idx = Math.round(this.value / 25);
        display.textContent = labels[Math.min(idx, 4)];
        input.value = this.value;
      });
    }
  })();
</script>
