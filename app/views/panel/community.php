<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <h2>Support Groups</h2>
  <div class="resources-grid">
    <?php foreach ($groups as $group): ?>
    <div class="resource-card">
      <div class="resource-card-header">
        <i class="fa-regular fa-comments"></i>
        <span class="resource-card-badge"><?= htmlspecialchars($group['tag']) ?></span>
      </div>
      <h4><?= htmlspecialchars($group['name']) ?></h4>
      <p><?= htmlspecialchars($group['description']) ?></p>
      <div class="resource-card-footer">
        <span><?= $group['member_count'] ?> members</span>
        <?php
        $isMember = false;
        foreach ($memberGroups as $mg) { if ($mg['id'] == $group['id']) { $isMember = true; break; } }
        ?>
        <?php if ($isMember): ?>
        <span class="badge badge-green">Joined</span>
        <?php else: ?>
        <form action="/panel/settings/join-group" method="POST" style="display:inline;">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
          <button type="submit" class="btn btn-primary" style="padding:6px 16px;font-size:13px;">Join</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="card">
  <h2>Community Feed</h2>
  <div class="chat-feed">
    <?php if (empty($messages)): ?>
    <div class="empty-state" style="padding:20px;">
      <i class="fa-regular fa-comments"></i>
      <p>No messages yet. Be the first to post!</p>
    </div>
    <?php else: ?>
    <?php foreach ($messages as $msg): ?>
    <div class="chat-message">
      <div class="chat-message-header">
        <?php if (!empty($msg['author_avatar'])): ?>
        <img src="/uploads/avatars/<?= htmlspecialchars($msg['author_avatar']) ?>" alt="" class="chat-message-avatar" style="object-fit:cover;">
        <?php else: ?>
        <div class="chat-message-avatar"><?= htmlspecialchars($msg['initials']) ?></div>
        <?php endif; ?>
        <div style="flex:1;">
          <strong><?= htmlspecialchars($msg['author']) ?></strong>
          <span style="font-size:12px;color:var(--color-text-muted);margin-left:6px;"><?= date('M j, g:i a', strtotime($msg['created_at'])) ?></span>
        </div>
      </div>
      <p><?= htmlspecialchars($msg['text']) ?></p>
      <div class="chat-message-actions">
        <form action="/panel/messages/like" method="POST" style="display:inline;">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
          <button type="submit"><i class="fa-regular fa-heart"></i> <?= $msg['likes'] ?></button>
        </form>
        <button onclick="toggleReplyForm('reply-form-<?= $msg['id'] ?>')"><i class="fa-regular fa-comment"></i> Reply</button>
      </div>

      <?php if (!empty($msg['comments'])): ?>
      <div class="comment-thread">
        <?php foreach ($msg['comments'] as $comment): ?>
        <div class="comment-item">
          <?php if (!empty($comment['author_avatar'])): ?>
          <img src="/uploads/avatars/<?= htmlspecialchars($comment['author_avatar']) ?>" alt="" class="comment-avatar" style="object-fit:cover;">
          <?php else: ?>
          <div class="comment-avatar"><?= htmlspecialchars($comment['initials']) ?></div>
          <?php endif; ?>
          <div class="comment-body">
            <div>
              <strong><?= htmlspecialchars($comment['author']) ?></strong>
              <span class="comment-time"><?= date('M j, g:i a', strtotime($comment['created_at'])) ?></span>
            </div>
            <p><?= htmlspecialchars($comment['text']) ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div id="reply-form-<?= $msg['id'] ?>" class="reply-form">
        <form action="/panel/messages/create" method="POST">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          <input type="hidden" name="parent_id" value="<?= $msg['id'] ?>">
          <input type="text" name="text" class="form-input" placeholder="Write a reply..." required>
          <button type="submit" class="btn btn-primary" style="padding:8px 14px;font-size:13px;"><i class="fa-regular fa-reply"></i></button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <form action="/panel/messages/create" method="POST" class="chat-input">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="text" name="text" class="form-input" placeholder="Share your thoughts..." required>
    <button type="submit" class="btn btn-primary"><i class="fa-regular fa-paper-plane"></i></button>
  </form>
</div>

<script>
function toggleReplyForm(id) {
  var el = document.getElementById(id);
  if (el) el.classList.toggle('open');
}
</script>
