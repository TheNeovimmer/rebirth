<div class="card">
  <div class="section-header">
    <h2>Resources</h2>
  </div>
  <div class="resources-grid">
    <?php foreach ($resources as $res): ?>
    <div class="resource-card">
      <div class="resource-card-header">
        <i class="<?= htmlspecialchars($res['icon']) ?>"></i>
        <span class="resource-card-badge"><?= htmlspecialchars($res['type']) ?></span>
      </div>
      <h4><?= htmlspecialchars($res['title']) ?></h4>
      <p><?= htmlspecialchars($res['description']) ?></p>
      <div class="resource-card-footer">
        <span class="badge badge-green"><?= htmlspecialchars($res['tag']) ?></span>
        <a href="#" class="btn btn-primary" style="padding:6px 16px;font-size:13px;">Read</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if (isset($therapistResources) && !empty($therapistResources)): ?>
<div class="section-header" style="margin-top:8px;">
  <h2>From Your Therapist</h2>
</div>
<div class="resources-grid">
  <?php foreach ($therapistResources as $res): ?>
  <div class="resource-card">
    <div class="resource-card-header">
      <?php
      $icon = match($res['type']) {
        'video' => 'fa-solid fa-video',
        'pdf' => 'fa-solid fa-file-pdf',
        'article' => 'fa-solid fa-file-lines',
        'image' => 'fa-solid fa-image',
        default => 'fa-solid fa-file',
      };
      ?>
      <i class="<?= $icon ?>"></i>
      <span class="resource-card-badge"><?= htmlspecialchars(ucfirst($res['type'])) ?></span>
    </div>
    <h4><?= htmlspecialchars($res['title']) ?></h4>
    <?php if ($res['description']): ?>
    <p><?= htmlspecialchars($res['description']) ?></p>
    <?php endif; ?>
    <div class="resource-card-footer">
      <a href="/<?= $res['file_path'] ?>" class="btn btn-primary" style="padding:6px 16px;font-size:13px;" target="_blank">Open</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
