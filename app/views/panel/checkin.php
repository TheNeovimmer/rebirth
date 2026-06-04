<div class="stats-row">
  <div class="stat-card">
    <div class="stat-icon green"><i class="fa-regular fa-face-smile"></i></div>
    <div>
      <div class="stat-value"><?= $streak ?></div>
      <div class="stat-label">Day Streak</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fa-regular fa-clock"></i></div>
    <div>
      <div class="stat-value"><?= $totalCheckins ?></div>
      <div class="stat-label">Total Check-ins</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fa-regular fa-calendar-check"></i></div>
    <div>
      <div class="stat-value"><?= $avgMood ?>%</div>
      <div class="stat-label">Avg Mood</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fa-solid fa-trophy"></i></div>
    <div>
      <div class="stat-value"><?= $bestStreak ?></div>
      <div class="stat-label">Best Streak</div>
    </div>
  </div>
</div>

<?php
$checkedIn = $todayCheckin !== null;
$currentMood = $checkedIn ? $todayCheckin['mood'] : '';
$currentCraving = $checkedIn && $todayCheckin['craving_level'] !== null ? $todayCheckin['craving_level'] : 0;
$currentNote = $checkedIn ? $todayCheckin['note'] : '';
$moodEmojis = ['great'=>'😊','good'=>'🙂','neutral'=>'😐','difficult'=>'😔','struggling'=>'😢'];
$moodLabels = ['None', 'Mild', 'Moderate', 'Strong', 'Severe'];
$moodIdx = $checkedIn ? min(round($currentCraving / 25), 4) : 0;
?>

<form action="/panel/checkin" method="POST">
  <input type="hidden" name="_token" value="<?= $_token ?>">
  <input type="hidden" name="mood" id="selectedMood" value="<?= $currentMood ?>">
  <input type="hidden" name="craving_level" id="cravingInput" value="<?= $currentCraving ?>">

  <div class="card">
    <div class="section-header">
      <h2>How are you feeling right now?</h2>
      <?php if ($checkedIn): ?>
      <span style="font-size:12px;color:var(--color-text-muted);">Updated <?= date('g:i A', strtotime($todayCheckin['created_at'])) ?></span>
      <?php endif; ?>
    </div>
    <div class="mood-grid">
      <button type="button" class="mood-btn<?= $currentMood === 'great' ? ' active' : '' ?>" data-value="great"><span>😊</span>Great</button>
      <button type="button" class="mood-btn<?= $currentMood === 'good' ? ' active' : '' ?>" data-value="good"><span>🙂</span>Good</button>
      <button type="button" class="mood-btn<?= $currentMood === 'neutral' ? ' active' : '' ?>" data-value="neutral"><span>😐</span>Neutral</button>
      <button type="button" class="mood-btn<?= $currentMood === 'difficult' ? ' active' : '' ?>" data-value="difficult"><span>😔</span>Difficult</button>
      <button type="button" class="mood-btn<?= $currentMood === 'struggling' ? ' active' : '' ?>" data-value="struggling"><span>😢</span>Struggling</button>
    </div>
  </div>

  <div class="card">
    <h2>Cravings</h2>
    <div class="slider-group">
      <label>How strong are your cravings right now?</label>
      <div class="range-labels"><span>None</span><span>Mild</span><span>Moderate</span><span>Strong</span><span>Severe</span></div>
      <input type="range" min="0" max="100" class="craving-slider" id="cravingSlider" value="<?= $currentCraving ?>">
      <div class="craving-value" id="cravingDisplay"><?= $moodLabels[$moodIdx] ?></div>
    </div>
  </div>

  <div class="card">
    <h2>Journal Note</h2>
    <textarea class="form-input" name="note" rows="3" placeholder="How was your day? Any wins or challenges?"><?= htmlspecialchars($currentNote) ?></textarea>
  </div>

  <button type="submit" class="btn btn-primary btn-block" id="saveCheckinBtn"<?= !$checkedIn ? ' disabled' : '' ?>>
    <?= $checkedIn ? '<i class="fa-regular fa-pen-to-square"></i> Update Check-in' : 'Select your mood first' ?>
  </button>
</form>

<?php if (!empty($recentCheckins)): ?>
<div class="card" style="margin-top:20px;">
  <h2>Past 7 Days</h2>
  <div>
    <?php foreach ($recentCheckins as $c): ?>
    <div class="checkin-history-item<?= $c['check_date'] === date('Y-m-d') ? ' today' : '' ?>">
      <span class="checkin-mood-emoji"><?= $moodEmojis[$c['mood']] ?? '😐' ?></span>
      <div class="checkin-info">
        <strong><?= ucfirst($c['mood']) ?></strong>
        <span><?= date('D', strtotime($c['check_date'])) ?></span>
      </div>
      <?php if ($c['craving_level'] !== null): ?>
      <span class="badge badge-<?= $c['craving_level'] <= 25 ? 'green' : ($c['craving_level'] <= 50 ? 'orange' : 'red') ?>"><?= $c['craving_level'] ?></span>
      <?php endif; ?>
      <?php if ($c['check_date'] === date('Y-m-d')): ?>
      <span class="badge badge-green">Today</span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
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
          saveBtn.innerHTML = '<?= $checkedIn ? '<i class="fa-regular fa-pen-to-square"></i> Update Check-in' : '<i class="fa-regular fa-circle-check"></i> Save Check-in' ?>';
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
