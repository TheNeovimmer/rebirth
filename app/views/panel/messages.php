<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (!$therapistId): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-user-md" style="font-size:48px;color:var(--color-text-muted);margin-bottom:16px;"></i>
  <p>You don't have a therapist assigned yet. An admin will assign one soon.</p>
</div>
<?php elseif (!$conversation): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p>Conversation ready. Start sending messages below.</p>
</div>
<?php else: ?>
<div class="card" style="display:flex;flex-direction:column;height:calc(100dvh - 220px);">
  <div class="chat-header" style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--color-border);margin-bottom:12px;">
    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--color-primary-dark);flex-shrink:0;">
      <?= htmlspecialchars($conversation['therapist_initials'] ?? 'TH') ?>
    </div>
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
  <form action="/messages/send" method="POST" style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--color-border);">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
    <input type="text" name="content" class="form-input" placeholder="Type a message..." required style="flex:1;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>
<script>window.__therapistId = <?= json_encode($therapistId) ?>;</script>
<?php endif; ?>
