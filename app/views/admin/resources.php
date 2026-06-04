<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3>Resources Library</h3>
    <button class="btn btn-primary" id="createResourceBtn"><i class="fa-solid fa-plus"></i> Add Resource</button>
  </div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Title</th><th>Type</th><th>Category</th><th>Created</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($resources as $res): ?>
          <tr>
            <td><?= htmlspecialchars($res['title']) ?></td>
            <td><?= htmlspecialchars($res['type']) ?></td>
            <td><span class="badge badge-green"><?= htmlspecialchars($res['tag']) ?></span></td>
            <td><?= date('M j, Y', strtotime($res['created_at'])) ?></td>
            <td>
              <button class="btn btn-outline" style="padding:4px 10px;font-size:12px;" onclick="editResource(<?= $res['id'] ?>, '<?= htmlspecialchars(addslashes($res['title'])) ?>', '<?= htmlspecialchars(addslashes($res['description'])) ?>', '<?= $res['type'] ?>', '<?= $res['tag'] ?>')"><i class="fa-regular fa-pen-to-square"></i></button>
              <form action="/admin/resources/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete resource?')">
                <input type="hidden" name="_token" value="<?= $_token ?>">
                <input type="hidden" name="id" value="<?= $res['id'] ?>">
                <button type="submit" class="btn btn-outline" style="padding:4px 10px;font-size:12px;color:var(--color-danger);"><i class="fa-solid fa-trash-can"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Resource Modal -->
<div class="modal-overlay" id="createResourceModal" style="display:none;">
  <div class="modal">
    <form action="/admin/resources/create" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>Add Resource</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('createResourceModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input class="form-input" name="title" required>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select class="form-select" name="type">
            <option value="Article">Article</option>
            <option value="Video">Video</option>
            <option value="Guide">Guide</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Icon</label>
          <select class="form-select" name="icon">
            <option value="fa-solid fa-file-lines">Article</option>
            <option value="fa-solid fa-circle-play">Video</option>
            <option value="fa-solid fa-book">Guide</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select class="form-select" name="tag">
            <option value="Education">Education</option>
            <option value="Wellness">Wellness</option>
            <option value="Support">Support</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-input" name="description" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Create Resource</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Resource Modal -->
<div class="modal-overlay" id="editResourceModal" style="display:none;">
  <div class="modal">
    <form action="/admin/resources/update" method="POST">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <input type="hidden" name="id" id="editResourceId">
      <div class="modal-header">
        <h3>Edit Resource</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('editResourceModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input class="form-input" name="title" id="editResourceTitle" required>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select class="form-select" name="type" id="editResourceType">
            <option value="Article">Article</option>
            <option value="Video">Video</option>
            <option value="Guide">Guide</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select class="form-select" name="tag" id="editResourceTag">
            <option value="Education">Education</option>
            <option value="Wellness">Wellness</option>
            <option value="Support">Support</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea class="form-input" name="description" id="editResourceDesc" rows="3" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('createResourceBtn').addEventListener('click', function() {
    document.getElementById('createResourceModal').style.display = 'flex';
  });
  function editResource(id, title, desc, type, tag) {
    document.getElementById('editResourceId').value = id;
    document.getElementById('editResourceTitle').value = title;
    document.getElementById('editResourceDesc').value = desc;
    document.getElementById('editResourceType').value = type;
    document.getElementById('editResourceTag').value = tag;
    document.getElementById('editResourceModal').style.display = 'flex';
  }
</script>
