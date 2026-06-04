<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="section-header">
  <h2>Your Journal</h2>
  <button class="btn btn-primary" id="newEntryBtn"><i class="fa-solid fa-plus"></i> New Entry</button>
</div>

<div class="journal-list">
  <?php if (empty($entries)): ?>
  <div class="card">
    <div class="empty-state">
      <i class="fa-regular fa-book"></i>
      <p>No journal entries yet. Click "New Entry" to write your first one.</p>
    </div>
  </div>
  <?php else: ?>
  <?php foreach ($entries as $entry): ?>
  <div class="journal-entry">
    <div class="journal-entry-header">
      <span class="journal-date"><?= date('M j, g:i A', strtotime($entry['created_at'])) ?></span>
      <span class="journal-mood mood-<?= $entry['mood'] ?>"><?= ucfirst($entry['mood']) ?></span>
    </div>
    <p><?= htmlspecialchars($entry['content']) ?></p>
    <div class="journal-entry-actions">
      <button class="btn btn-outline edit-entry-btn" style="padding:4px 12px;font-size:12px;"
        data-id="<?= $entry['id'] ?>"
        data-content="<?= htmlspecialchars(str_replace(["\r\n", "\r", "\n"], ' ', $entry['content']), ENT_QUOTES) ?>"
        data-mood="<?= htmlspecialchars($entry['mood']) ?>">
        <i class="fa-regular fa-pen-to-square"></i> Edit
      </button>
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

<div class="modal-overlay" id="newEntryModal">
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
            <button type="button" class="mood-btn" data-mood="neutral"><span>😐</span>Neutral</button>
            <button type="button" class="mood-btn" data-mood="difficult"><span>😔</span>Difficult</button>
            <button type="button" class="mood-btn" data-mood="struggling"><span>😢</span>Struggling</button>
          </div>
          <input type="hidden" name="mood" id="journalMood" value="neutral">
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

<div class="modal-overlay" id="editEntryModal">
  <div class="modal">
    <form action="/panel/journal/update" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="id" id="editEntryId" value="">
      <div class="modal-header">
        <h3>Edit Journal Entry</h3>
        <button type="button" class="modal-close" id="closeEditModal"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">How are you feeling?</label>
          <div class="mood-grid">
            <button type="button" class="mood-btn" data-mood="great"><span>😊</span>Great</button>
            <button type="button" class="mood-btn" data-mood="good"><span>🙂</span>Good</button>
            <button type="button" class="mood-btn" data-mood="neutral"><span>😐</span>Neutral</button>
            <button type="button" class="mood-btn" data-mood="difficult"><span>😔</span>Difficult</button>
            <button type="button" class="mood-btn" data-mood="struggling"><span>😢</span>Struggling</button>
          </div>
          <input type="hidden" name="mood" id="editJournalMood" value="neutral">
        </div>
        <div class="form-group">
          <label class="form-label" for="editEntryText">What's on your mind?</label>
          <textarea class="form-input" id="editEntryText" name="content" rows="5" placeholder="Write your thoughts..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Entry</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var newBtn = document.getElementById('newEntryBtn');
  var newModal = document.getElementById('newEntryModal');
  var closeBtn = document.getElementById('closeModal');
  if (newBtn) newBtn.addEventListener('click', function () { newModal.style.display = 'flex'; });
  if (closeBtn) closeBtn.addEventListener('click', function () { newModal.style.display = 'none'; });
  newModal.addEventListener('click', function (e) { if (e.target === newModal) newModal.style.display = 'none'; });
  document.querySelectorAll('#newEntryModal .mood-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      document.querySelectorAll('#newEntryModal .mood-btn').forEach(function(b) { b.classList.remove('active'); });
      this.classList.add('active');
      document.getElementById('journalMood').value = this.dataset.mood;
    });
  });

  var editModal = document.getElementById('editEntryModal');
  var closeEditBtn = document.getElementById('closeEditModal');
  if (closeEditBtn) closeEditBtn.addEventListener('click', function () { editModal.style.display = 'none'; });
  editModal.addEventListener('click', function (e) { if (e.target === editModal) editModal.style.display = 'none'; });

  document.querySelectorAll('.edit-entry-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = this.dataset.id;
      var content = this.dataset.content;
      var mood = this.dataset.mood;
      document.getElementById('editEntryId').value = id;
      document.getElementById('editEntryText').value = content;
      document.getElementById('editJournalMood').value = mood;
      document.querySelectorAll('#editEntryModal .mood-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.mood === mood);
      });
      editModal.style.display = 'flex';
    });
  });
});
</script>
