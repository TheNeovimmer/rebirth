<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>Your Entries</h2>
    <button class="btn btn-primary" id="newEntryBtn"><i class="fa-solid fa-plus"></i> New Entry</button>
  </div>
</div>

<div class="journal-list">
  <?php if (empty($entries)): ?>
  <div class="journal-entry">
    <p style="color:var(--color-text-muted);text-align:center;padding:20px;">No journal entries yet. Click "New Entry" to write your first one.</p>
  </div>
  <?php else: ?>
  <?php foreach ($entries as $entry): ?>
  <div class="journal-entry">
    <div class="journal-entry-header">
      <span class="journal-date"><?= date('M j, g:i A', strtotime($entry['created_at'])) ?></span>
      <span class="journal-mood mood-<?= $entry['mood'] ?>"><?= ucfirst($entry['mood']) ?></span>
    </div>
    <p><?= htmlspecialchars($entry['content']) ?></p>
    <div style="margin-top:12px;">
      <form action="/panel/journal/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this entry?')">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $entry['id'] ?>">
        <button type="submit" class="btn btn-outline" style="padding:4px 12px;font-size:12px;color:var(--color-danger);"><i class="fa-regular fa-trash-can"></i> Delete</button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>
</div>

<div class="modal-overlay" id="newEntryModal" style="display:none;">
  <div class="modal">
    <form action="/panel/journal/create" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>New Journal Entry</h3>
        <button type="button" class="modal-close" id="closeModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">How are you feeling?</label>
          <div class="mood-grid">
            <button type="button" class="mood-btn" data-mood="great"><span>😊</span>Great</button>
            <button type="button" class="mood-btn" data-mood="good"><span>🙂</span>Good</button>
            <button type="button" class="mood-btn" data-mood="okay"><span>😐</span>Okay</button>
            <button type="button" class="mood-btn" data-mood="tough"><span>😔</span>Tough</button>
            <button type="button" class="mood-btn" data-mood="struggling"><span>😢</span>Struggling</button>
          </div>
          <input type="hidden" name="mood" id="journalMood" value="okay">
        </div>
        <div class="form-group">
          <label class="form-label" for="entryText">What's on your mind?</label>
          <textarea class="form-input" id="entryText" name="content" rows="5" placeholder="Write your thoughts..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Entry</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var newBtn = document.getElementById('newEntryBtn');
    var modal = document.getElementById('newEntryModal');
    var closeBtn = document.getElementById('closeModal');
    if (newBtn) newBtn.addEventListener('click', function () { modal.style.display = 'flex'; });
    if (closeBtn) closeBtn.addEventListener('click', function () { modal.style.display = 'none'; });
    modal.addEventListener('click', function (e) { if (e.target === modal) modal.style.display = 'none'; });
    document.querySelectorAll('#newEntryModal .mood-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.querySelectorAll('#newEntryModal .mood-btn').forEach(function(b) { b.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('journalMood').value = this.dataset.mood;
      });
    });
  });
</script>
