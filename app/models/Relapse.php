<?php
class Relapse {
  public static function forPatient(int $patientId): array {
    return Database::fetchAll(
      "SELECT * FROM relapses WHERE patient_id = ? ORDER BY relapse_date DESC",
      [$patientId]
    );
  }

  public static function forTherapistPatient(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM relapses WHERE patient_id = ? AND therapist_id = ? ORDER BY relapse_date DESC",
      [$patientId, $therapistId]
    );
  }

  public static function triggerAnalysis(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT `trigger`, COUNT(*) as count FROM relapses
       WHERE patient_id = ? AND therapist_id = ? AND `trigger` IS NOT NULL AND `trigger` != ''
       GROUP BY `trigger` ORDER BY count DESC LIMIT 10",
      [$patientId, $therapistId]
    );
  }

  public static function create(int $patientId, int $therapistId, array $data): int {
    return Database::insert('relapses', [
      'patient_id' => $patientId,
      'therapist_id' => $therapistId,
      'relapse_date' => $data['relapse_date'] ?? date('Y-m-d H:i:s'),
      'trigger' => $data['trigger'] ?? '',
      'severity' => $data['severity'] ?? 'moderate',
      'description' => $data['description'] ?? '',
      'action_taken' => $data['action_taken'] ?? '',
    ]);
  }

  public static function delete(int $id, int $patientId): void {
    Database::query("DELETE FROM relapses WHERE id = ? AND patient_id = ?", [$id, $patientId]);
  }
}
