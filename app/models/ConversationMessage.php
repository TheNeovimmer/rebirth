<?php
class ConversationMessage {
  public static function forConversation(int $conversationId, int $afterId = 0): array {
    $sql = "SELECT cm.*, u.name as sender_name FROM conversation_messages cm
            JOIN users u ON u.id = cm.sender_id
            WHERE cm.conversation_id = ?";
    $params = [$conversationId];
    if ($afterId > 0) { $sql .= " AND cm.id > ?"; $params[] = $afterId; }
    $sql .= " ORDER BY cm.created_at ASC LIMIT 100";
    return Database::fetchAll($sql, $params);
  }

  public static function send(int $conversationId, int $senderId, string $content): int {
    return Database::insert('conversation_messages', [
      'conversation_id' => $conversationId,
      'sender_id' => $senderId,
      'content' => $content,
    ]);
  }
}
