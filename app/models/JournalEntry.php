<?php
class JournalEntry {
  public static function forUser(int $userId): array {
    return Database::fetchAll(
      "SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC",
      [$userId]
    );
  }

  public static function recentForUser(int $userId, int $limit = 10): array {
    return Database::fetchAll(
      "SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
      [$userId, $limit]
    );
  }

  public static function create(int $userId, string $content, string $mood): int {
    return Database::insert('journal_entries', [
      'user_id' => $userId,
      'content' => $content,
      'mood' => $mood,
    ]);
  }

  public static function delete(int $id, int $userId): int {
    $stmt = Database::query(
      "DELETE FROM journal_entries WHERE id = ? AND user_id = ?",
      [$id, $userId]
    );
    return $stmt->rowCount();
  }

  public static function recentForTherapistPatient(int $patientId, int $limit = 5): array {
    return Database::fetchAll(
      "SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
      [$patientId, $limit]
    );
  }
}
