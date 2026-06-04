<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <h3>Community Messages</h3>
  </div>
  <div class="card-body">
    <div class="table-wrap">
      <table>
        <thead><tr><th>Author</th><th>Message</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if (empty($messages)): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--color-text-muted);">No messages to moderate</td></tr>
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
                <button type="submit" class="btn btn-outline" style="padding:4px 10px;font-size:12px;color:var(--color-danger);"><i class="fa-solid fa-trash-can"></i> Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
