<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="section-header">
    <h2>Community Messages</h2>
  </div>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Author</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if (empty($messages)): ?>
        <tr><td colspan="4" style="text-align:center;color:var(--color-text-muted);padding:24px;">No messages to moderate</td></tr>
        <?php else: ?>
        <?php foreach ($messages as $msg): ?>
        <tr>
          <td><?= htmlspecialchars($msg['author_name']) ?></td>
          <td style="max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($msg['text']) ?></td>
          <td><?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?></td>
          <td>
            <form action="/admin/moderation/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?')">
              <input type="hidden" name="_token" value="<?= $_token ?>">
              <input type="hidden" name="id" value="<?= $msg['id'] ?>">
              <button type="submit" class="btn btn-outline btn-xs" style="color:var(--color-danger);"><i class="fa-solid fa-trash-can"></i> Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
