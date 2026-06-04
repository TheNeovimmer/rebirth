<?php
class Group {
  public static function all(): array {
    return Database::fetchAll("SELECT * FROM `groups` ORDER BY member_count DESC");
  }

  public static function find(int $id): ?array {
    return Database::fetch("SELECT * FROM `groups` WHERE id = ?", [$id]);
  }

  public static function messages(int $groupId = 0): array {
    $where = $groupId ? "m.group_id = ? AND m.parent_id IS NULL" : "m.group_id = 0 AND m.parent_id IS NULL";
    $params = $groupId ? [$groupId] : [];
    $posts = Database::fetchAll(
      "SELECT m.*, u.name as author, u.initials, u.avatar as author_avatar,
       (SELECT COUNT(*) FROM message_likes WHERE message_id = m.id) as likes
       FROM messages m
       JOIN users u ON u.id = m.user_id
       WHERE $where
       ORDER BY m.created_at DESC LIMIT 50",
      $params
    );
    foreach ($posts as &$post) {
      $post['comments'] = Database::fetchAll(
        "SELECT m.*, u.name as author, u.initials, u.avatar as author_avatar
         FROM messages m
         JOIN users u ON u.id = m.user_id
         WHERE m.parent_id = ?
         ORDER BY m.created_at ASC",
        [$post['id']]
      );
    }
    return $posts;
  }

  public static function createMessage(int $userId, string $text, int $groupId = 0, ?int $parentId = null): int {
    $data = [
      'user_id' => $userId,
      'group_id' => $groupId,
      'text' => $text,
    ];
    if ($parentId) $data['parent_id'] = $parentId;
    return Database::insert('messages', $data);
  }

  public static function deleteMessage(int $id): int {
    return Database::delete('messages', $id);
  }

  public static function toggleLike(int $messageId, int $userId): bool {
    $existing = Database::fetch(
      "SELECT id FROM message_likes WHERE message_id = ? AND user_id = ?",
      [$messageId, $userId]
    );
    if ($existing) {
      Database::delete('message_likes', $existing['id']);
      return false; // unliked
    }
    Database::insert('message_likes', [
      'message_id' => $messageId,
      'user_id' => $userId,
    ]);
    return true; // liked
  }

  public static function isMember(int $groupId, int $userId): bool {
    return Database::exists(
      "SELECT id FROM group_members WHERE group_id = ? AND user_id = ?",
      [$groupId, $userId]
    );
  }

  public static function join(int $groupId, int $userId): bool {
    if (self::isMember($groupId, $userId)) return false;
    Database::insert('group_members', ['group_id' => $groupId, 'user_id' => $userId]);
    Database::query("UPDATE `groups` SET member_count = member_count + 1 WHERE id = ?", [$groupId]);
    return true;
  }

  public static function leave(int $groupId, int $userId): void {
    Database::query(
      "DELETE FROM group_members WHERE group_id = ? AND user_id = ?",
      [$groupId, $userId]
    );
    Database::query("UPDATE `groups` SET member_count = GREATEST(member_count - 1, 0) WHERE id = ?", [$groupId]);
  }

  public static function moderationQueue(): int {
    return Database::count(
      "SELECT COUNT(*) as count FROM messages m
       LEFT JOIN message_likes ml ON ml.message_id = m.id
       WHERE ml.id IS NULL AND LENGTH(m.text) > 500"
    );
  }
}
