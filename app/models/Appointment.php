<?php
class Appointment {
  public static function upcoming(int $userId = 0): array {
    if ($userId) {
      return Database::fetchAll(
        "SELECT a.*, u.name as patient_name, u2.name as therapist_name
         FROM appointments a
         LEFT JOIN users u ON u.id = a.user_id
         LEFT JOIN users u2 ON u2.id = a.therapist_id
         WHERE a.user_id = ? AND a.status IN ('confirmed','pending')
         ORDER BY a.date_time ASC LIMIT 5",
        [$userId]
      );
    }
    return [];
  }

  public static function all(): array {
    return Database::fetchAll(
      "SELECT a.*, u.name as patient_name, u2.name as therapist_name
       FROM appointments a
       LEFT JOIN users u ON u.id = a.user_id
       LEFT JOIN users u2 ON u2.id = a.therapist_id
       ORDER BY a.date_time DESC"
    );
  }

  public static function find(int $id): ?array {
    return Database::fetch(
      "SELECT a.*, u.name as patient_name, u2.name as therapist_name
       FROM appointments a
       LEFT JOIN users u ON u.id = a.user_id
       LEFT JOIN users u2 ON u2.id = a.therapist_id
       WHERE a.id = ?",
      [$id]
    );
  }

  public static function create(array $data): int {
    return Database::insert('appointments', $data);
  }

  public static function update(int $id, array $data): int {
    return Database::update('appointments', $id, $data);
  }

  public static function cancel(int $id): int {
    return Database::update('appointments', $id, ['status' => 'cancelled']);
  }

  public static function upcomingForAdmin(int $limit = 4): array {
    return Database::fetchAll(
      "SELECT a.*, u.name as patient_name, u2.name as therapist_name
       FROM appointments a
       LEFT JOIN users u ON u.id = a.user_id
       LEFT JOIN users u2 ON u2.id = a.therapist_id
       WHERE a.date_time >= NOW() AND a.status IN ('confirmed','pending')
       ORDER BY a.date_time ASC LIMIT ?",
      [$limit]
    );
  }

  public static function todayCount(): int {
    return Database::count(
      "SELECT COUNT(*) as count FROM appointments WHERE DATE(date_time) = CURDATE() AND status NOT IN ('cancelled')"
    );
  }

  public static function upcomingForTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT a.*, u.name as patient_name
       FROM appointments a
       JOIN users u ON u.id = a.user_id
       WHERE a.therapist_id = ? AND a.date_time >= NOW() AND a.status IN ('confirmed','pending')
       ORDER BY a.date_time ASC LIMIT 10",
      [$therapistId]
    );
  }

  public static function forPatient(int $userId): array {
    return Database::fetchAll(
      "SELECT a.*, u.name as therapist_name
       FROM appointments a
       LEFT JOIN users u ON u.id = a.therapist_id
       WHERE a.user_id = ?
       ORDER BY a.date_time DESC",
      [$userId]
    );
  }

  public static function delete(int $id): int {
    return Database::delete('appointments', $id);
  }
}
