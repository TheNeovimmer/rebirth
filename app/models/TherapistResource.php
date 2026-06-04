<?php
class TherapistResource {
  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT tr.*, u.name as patient_name FROM therapist_resources tr
       LEFT JOIN users u ON u.id = tr.patient_id
       WHERE tr.therapist_id = ?
       ORDER BY tr.created_at DESC", [$therapistId]
    );
  }

  public static function forPatient(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM therapist_resources
       WHERE therapist_id = ? AND (patient_id = ? OR patient_id IS NULL)
       ORDER BY created_at DESC", [$therapistId, $patientId]
    );
  }

  public static function create(array $data): int {
    return Database::insert('therapist_resources', $data);
  }

  public static function delete(int $id, int $therapistId): void {
    $res = Database::fetch("SELECT * FROM therapist_resources WHERE id = ? AND therapist_id = ?", [$id, $therapistId]);
    if ($res) {
      $file = BASE_PATH . '/' . $res['file_path'];
      if (file_exists($file)) unlink($file);
      Database::delete('therapist_resources', $id);
    }
  }
}
