<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
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
        <form action="/settings/join-group" method="POST" style="display:inline;">
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
    <div style="padding:20px;text-align:center;color:var(--color-text-muted);">No messages yet. Be the first to post!</div>
    <?php else: ?>
    <?php foreach ($messages as $msg): ?>
    <div class="chat-message">
      <div class="chat-message-header">
        <?php if (!empty($msg['author_avatar'])): ?>
        <img src="/uploads/avatars/<?= htmlspecialchars($msg['author_avatar']) ?>" alt="" class="chat-message-avatar" style="object-fit:cover;">
        <?php else: ?>
        <div class="chat-message-avatar"><?= htmlspecialchars($msg['initials']) ?></div>
        <?php endif; ?>
        <div>
          <strong><?= htmlspecialchars($msg['author']) ?></strong>
          <span style="font-size:12px;color:var(--color-text-muted);margin-left:6px;"><?= date('M j, g:i a', strtotime($msg['created_at'])) ?></span>
        </div>
      </div>
      <p><?= htmlspecialchars($msg['text']) ?></p>
      <div class="chat-message-actions">
        <form action="/messages/like" method="POST" style="display:inline;">
          <input type="hidden" name="_token" value="<?= $_token ?>">
          <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
          <button type="submit"><i class="fa-regular fa-heart"></i> <?= $msg['likes'] ?></button>
        </form>
        <button><i class="fa-regular fa-comment"></i> Reply</button>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>
  <form action="/messages/create" method="POST" class="chat-input">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="text" name="text" class="form-input" placeholder="Share your thoughts..." style="flex:1;" required>
    <button type="submit" class="btn btn-primary" style="padding:10px 16px;"><i class="fa-regular fa-paper-plane"></i></button>
  </form>
</div>
