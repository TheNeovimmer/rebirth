<?php
class Conversation {
  public static function ensure(int $patientId, int $therapistId): int {
    $existing = Database::fetch(
      "SELECT id FROM conversations WHERE patient_id = ? AND therapist_id = ?",
      [$patientId, $therapistId]
    );
    if ($existing) return (int) $existing['id'];
    return Database::insert('conversations', [
      'patient_id' => $patientId, 'therapist_id' => $therapistId
    ]);
  }

  public static function forPatient(int $patientId): ?array {
    return Database::fetch(
      "SELECT c.*, u.name as therapist_name, u.initials as therapist_initials, u.avatar as therapist_avatar
       FROM conversations c JOIN users u ON u.id = c.therapist_id
       WHERE c.patient_id = ?", [$patientId]
    );
  }

  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT c.*, u.name as patient_name, u.initials as patient_initials, u.avatar as patient_avatar,
       (SELECT COUNT(*) FROM conversation_messages WHERE conversation_id = c.id AND sender_id != ? AND read_at IS NULL) as unread
       FROM conversations c JOIN users u ON u.id = c.patient_id
       WHERE c.therapist_id = ?
       ORDER BY c.updated_at DESC", [$therapistId, $therapistId]
    );
  }

  public static function find(int $id): ?array {
    return Database::fetch(
      "SELECT c.*, u.name as other_name, u.initials as other_initials, u.avatar as other_avatar, u.role as other_role
       FROM conversations c JOIN users u ON u.id = IF(c.patient_id = ?, c.therapist_id, c.patient_id)
       WHERE c.id = ?",
      [$_SESSION['user_id'], $id]
    );
  }

  public static function unreadCount(int $userId): int {
    return (int) Database::fetch(
      "SELECT COUNT(*) as count FROM conversation_messages cm
       JOIN conversations c ON c.id = cm.conversation_id
       WHERE cm.sender_id != ? AND cm.read_at IS NULL AND (
         c.patient_id = ? OR c.therapist_id = ?
       )",
      [$userId, $userId, $userId]
    )['count'] ?? 0;
  }

  public static function markRead(int $conversationId, int $userId): void {
    Database::query("UPDATE conversation_messages SET read_at = NOW() WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL", [$conversationId, $userId]);
  }
}
