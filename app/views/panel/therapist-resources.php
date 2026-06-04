<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="section-header">
  <h2>My Resources</h2>
  <button class="btn btn-primary" onclick="document.getElementById('uploadModal').style.display='flex'"><i class="fa-solid fa-upload"></i> Upload</button>
</div>

<?php if (empty($resources)): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-regular fa-folder-open"></i>
    <p>No resources uploaded yet.</p>
  </div>
</div>
<?php else: ?>
<div class="resources-grid">
  <?php foreach ($resources as $res): ?>
  <div class="resource-card">
    <div class="resource-card-header">
      <?php $icon = match($res['type']) { 'video'=>'fa-solid fa-video', 'pdf'=>'fa-solid fa-file-pdf', 'article'=>'fa-solid fa-file-lines', 'image'=>'fa-solid fa-image', default=>'fa-solid fa-file' }; ?>
      <i class="<?= $icon ?>"></i>
      <span class="resource-card-badge"><?= htmlspecialchars(ucfirst($res['type'])) ?></span>
    </div>
    <h4><?= htmlspecialchars($res['title']) ?></h4>
    <?php if ($res['description']): ?><p><?= htmlspecialchars($res['description']) ?></p><?php endif; ?>
    <div class="resource-card-footer">
      <span style="font-size:12px;color:var(--color-text-muted);">For: <?= htmlspecialchars($res['patient_name'] ?? 'All patients') ?></span>
      <form action="/therapist/resources/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this resource?')">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $res['id'] ?>">
        <button type="submit" class="btn btn-outline btn-xs" style="color:var(--color-danger);"><i class="fa-solid fa-trash"></i></button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="modal-overlay" id="uploadModal">
  <div class="modal">
    <form action="/therapist/resources/create" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>Upload Resource</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('uploadModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input class="form-input" name="title" required>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select class="form-select" name="type" required>
            <option value="video">Video</option>
            <option value="pdf">PDF</option>
            <option value="article">Article</option>
            <option value="image">Image</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Assign To</label>
          <select class="form-select" name="patient_id">
            <option value="">All my patients</option>
            <?php foreach ($patients as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Description (optional)</label>
          <textarea class="form-input" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">File</label>
          <input class="form-input" type="file" name="file" required accept=".pdf,.mp4,.webm,.jpg,.jpeg,.png,.gif,.webp,.txt,.md">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Upload</button>
      </div>
    </form>
  </div>
</div>
