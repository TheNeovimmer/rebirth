<?php
class Notification {
  public static function create(int $userId, string $type, string $title, ?string $body = null, ?string $link = null, ?int $relatedId = null): int {
    return Database::insert('notifications', [
      'user_id' => $userId,
      'type' => $type,
      'title' => $title,
      'body' => $body,
      'link' => $link,
      'related_id' => $relatedId,
    ]);
  }

  public static function unreadCount(int $userId): int {
    return (int) Database::fetch(
      "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND read_at IS NULL",
      [$userId]
    )['count'];
  }

  public static function recent(int $userId, int $limit = 10): array {
    return Database::fetchAll(
      "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
      [$userId, $limit]
    );
  }

  public static function markRead(int $id, int $userId): void {
    Database::query("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?", [$id, $userId]);
  }

  public static function markAllRead(int $userId): void {
    Database::query("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL", [$userId]);
  }
}
