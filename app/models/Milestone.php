<?php
class Milestone {
  public static function all(): array {
    return Database::fetchAll("SELECT * FROM milestones ORDER BY target_days ASC");
  }

  public static function forUser(int $userId): array {
    return Database::fetchAll(
      "SELECT m.*, COALESCE(um.progress, 0) as progress,
       COALESCE(um.achieved, FALSE) as achieved,
       um.achieved_at
       FROM milestones m
       LEFT JOIN user_milestones um ON um.milestone_id = m.id AND um.user_id = ?
       ORDER BY m.target_days ASC",
      [$userId]
    );
  }

  public static function updateProgress(int $userId, int $streak): void {
    $all = self::all();
    foreach ($all as $m) {
      $progress = min(100, (int) round(($streak / $m['target_days']) * 100));
      $achieved = $streak >= $m['target_days'];
      $existing = Database::fetch(
        "SELECT id, achieved, achieved_at FROM user_milestones WHERE user_id = ? AND milestone_id = ?",
        [$userId, $m['id']]
      );
      $alreadyAchieved = $existing && $existing['achieved'];
      if ($existing) {
        Database::update('user_milestones', $existing['id'], [
          'progress' => $progress,
          'achieved' => $alreadyAchieved || $achieved ? 1 : 0,
          'achieved_at' => $alreadyAchieved ? $existing['achieved_at'] : ($achieved ? date('Y-m-d H:i:s') : null),
        ]);
      } else {
        Database::insert('user_milestones', [
          'user_id' => $userId,
          'milestone_id' => $m['id'],
          'progress' => $progress,
          'achieved' => $achieved ? 1 : 0,
          'achieved_at' => $achieved ? date('Y-m-d H:i:s') : null,
        ]);
      }
    }
  }

  public static function currentStreak(int $userId): int {
    $rows = Database::fetchAll(
      "SELECT check_date FROM check_ins WHERE user_id = ? ORDER BY check_date DESC",
      [$userId]
    );
    if (empty($rows)) return 0;
    $streak = 0;
    $today = new DateTime();
    foreach ($rows as $row) {
      $expected = (clone $today)->modify("-{$streak} days")->format('Y-m-d');
      if ($row['check_date'] === $expected) {
        $streak++;
      } else {
        break;
      }
    }
    return $streak;
  }

  public static function totalCheckins(int $userId): int {
    return Database::count(
      "SELECT COUNT(*) as count FROM check_ins WHERE user_id = ?",
      [$userId]
    );
  }

  public static function avgMood(int $userId): float {
    $row = Database::fetch(
      "SELECT AVG(CASE mood
         WHEN 'great' THEN 100 WHEN 'good' THEN 80
         WHEN 'okay' THEN 60 WHEN 'tough' THEN 40
         WHEN 'struggling' THEN 20 ELSE 50 END) as avg
       FROM check_ins WHERE user_id = ?",
      [$userId]
    );
    return $row ? round((float) $row['avg']) : 0;
  }

  public static function moodTrend(int $userId, int $days = 7): array {
    return Database::fetchAll(
      "SELECT check_date,
       CASE mood
         WHEN 'great' THEN 100 WHEN 'good' THEN 80
         WHEN 'okay' THEN 60 WHEN 'tough' THEN 40
         WHEN 'struggling' THEN 20 ELSE 50 END as value,
       mood
       FROM check_ins WHERE user_id = ? ORDER BY check_date ASC LIMIT ?",
      [$userId, $days]
    );
  }

  public static function weeklyProgress(int $userId): array {
    $rows = [];
    $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $start = (new DateTime())->modify('monday this week');
    foreach ($days as $i => $day) {
      $date = (clone $start)->modify("+{$i} days")->format('Y-m-d');
      $checkin = Database::fetch(
        "SELECT mood FROM check_ins WHERE user_id = ? AND check_date = ?",
        [$userId, $date]
      );
      $rows[] = [
        'day' => $day,
        'done' => $checkin ? true : false,
        'mood' => $checkin['mood'] ?? null,
      ];
    }
    return $rows;
  }
}
