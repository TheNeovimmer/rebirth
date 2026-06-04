<?php
class SOSAlert {
  public static function activeForTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT sa.*, u.name as patient_name, u.initials as patient_initials
       FROM sos_alerts sa JOIN users u ON u.id = sa.patient_id
       WHERE sa.therapist_id = ? AND sa.status IN ('active','acknowledged')
       ORDER BY sa.created_at DESC", [$therapistId]
    );
  }

  public static function activeCount(int $therapistId): int {
    $row = Database::fetch(
      "SELECT COUNT(*) as count FROM sos_alerts WHERE therapist_id = ? AND status IN ('active','acknowledged')",
      [$therapistId]
    );
    return (int) ($row['count'] ?? 0);
  }

  public static function forPatient(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM sos_alerts WHERE patient_id = ? AND therapist_id = ? ORDER BY created_at DESC LIMIT 10",
      [$patientId, $therapistId]
    );
  }

  public static function create(int $patientId, int $therapistId): int {
    return Database::insert('sos_alerts', [
      'patient_id' => $patientId, 'therapist_id' => $therapistId
    ]);
  }

  public static function acknowledge(int $id, int $therapistId): void {
    Database::query("UPDATE sos_alerts SET status = 'acknowledged' WHERE id = ? AND therapist_id = ? AND status = 'active'", [$id, $therapistId]);
  }

  public static function resolve(int $id, int $therapistId, string $notes = ''): void {
    Database::query("UPDATE sos_alerts SET status = 'resolved', resolved_at = NOW(), notes = CONCAT(COALESCE(notes,''), '\n', ?) WHERE id = ? AND therapist_id = ?", [$notes, $id, $therapistId]);
  }
}
