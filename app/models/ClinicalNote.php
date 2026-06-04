<?php
class ClinicalNote {
  public static function forPatient(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM clinical_notes WHERE patient_id = ? AND therapist_id = ? ORDER BY created_at DESC",
      [$patientId, $therapistId]
    );
  }

  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT cn.*, u.name as patient_name, u.initials as patient_initials
       FROM clinical_notes cn JOIN users u ON u.id = cn.patient_id
       WHERE cn.therapist_id = ? ORDER BY cn.created_at DESC LIMIT 50",
      [$therapistId]
    );
  }

  public static function create(int $patientId, int $therapistId, string $content, ?string $sessionDate = null): int {
    return Database::insert('clinical_notes', [
      'patient_id' => $patientId,
      'therapist_id' => $therapistId,
      'content' => $content,
      'session_date' => $sessionDate,
    ]);
  }

  public static function delete(int $id, int $therapistId): void {
    Database::query("DELETE FROM clinical_notes WHERE id = ? AND therapist_id = ?", [$id, $therapistId]);
  }
}
