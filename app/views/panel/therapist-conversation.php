<a href="/therapist/messages" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> All Conversations</a>

<div class="card" style="display:flex;flex-direction:column;height:calc(100dvh - 220px);">
  <div style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--color-border);margin-bottom:12px;">
    <?php if (!empty($patient['avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <?php else: ?>
    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
    <?php endif; ?>
    <strong><?= htmlspecialchars($patient['name']) ?></strong>
  </div>
  <div id="messageContainer" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;padding:8px 0;min-height:200px;" data-conversation-id="<?= $conv['id'] ?>" data-last-id="<?= !empty($messages) ? end($messages)['id'] : 0 ?>">
    <?php foreach ($messages as $msg): ?>
    <div class="chat-msg <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'chat-msg-sent' : 'chat-msg-received' ?>">
      <div class="chat-msg-content"><?= htmlspecialchars($msg['content']) ?></div>
      <div class="chat-msg-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <form action="/panel/messages/send" method="POST" style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--color-border);">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="conversation_id" value="<?= $conv['id'] ?>">
    <input type="text" name="content" class="form-input" placeholder="Type a message..." required style="flex:1;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>
