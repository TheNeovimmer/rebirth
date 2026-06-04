<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (!$therapistId): ?>
<div class="card">
  <div class="empty-state">
    <i class="fa-solid fa-user-md"></i>
    <p>You don't have a therapist assigned yet. An admin will assign one soon.</p>
  </div>
</div>
<?php elseif (!$conversation): ?>
<div class="card" style="text-align:center;padding:32px;">
  <p style="color:var(--color-text-muted);">Conversation ready. Start sending messages below.</p>
</div>
<?php else: ?>
<div class="card" style="display:flex;flex-direction:column;height:calc(100dvh - 220px);padding-bottom:16px;">
  <div class="chat-header">
    <?php if (!empty($conversation['therapist_avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($conversation['therapist_avatar']) ?>" alt="" class="chat-header-avatar" style="object-fit:cover;">
    <?php else: ?>
    <div class="chat-header-avatar"><?= htmlspecialchars($conversation['therapist_initials'] ?? 'TH') ?></div>
    <?php endif; ?>
    <div>
      <strong><?= htmlspecialchars($conversation['therapist_name'] ?? 'Your Therapist') ?></strong>
      <div style="font-size:12px;color:var(--color-text-muted);" id="availabilityStatus">Checking availability...</div>
    </div>
  </div>
  <div id="messageContainer" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;padding:8px 0;min-height:200px;" data-conversation-id="<?= $conversation['id'] ?>" data-last-id="<?= !empty($messages) ? end($messages)['id'] : 0 ?>">
    <?php foreach ($messages as $msg): ?>
    <div class="chat-msg <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'chat-msg-sent' : 'chat-msg-received' ?>">
      <div class="chat-msg-content"><?= htmlspecialchars($msg['content']) ?></div>
      <div class="chat-msg-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <form action="/panel/messages/send" method="POST" style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--color-border);">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
    <input type="text" name="content" class="form-input" placeholder="Type a message..." required style="flex:1;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>
<script>window.__therapistId = <?= json_encode($therapistId) ?>;</script>
<?php endif; ?>
