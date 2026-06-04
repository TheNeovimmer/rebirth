<?php
class RecoveryProgress {
  private static array $defaultStages = [
    ['stage_name' => 'Assessment', 'stage_order' => 1],
    ['stage_name' => 'Detox', 'stage_order' => 2],
    ['stage_name' => 'Therapy', 'stage_order' => 3],
    ['stage_name' => 'Relapse Prevention', 'stage_order' => 4],
    ['stage_name' => 'Aftercare', 'stage_order' => 5],
  ];

  public static function ensure(int $patientId, int $therapistId): void {
    $existing = Database::fetch(
      "SELECT COUNT(*) as count FROM recovery_progress WHERE patient_id = ? AND therapist_id = ?",
      [$patientId, $therapistId]
    );
    if ((int)($existing['count'] ?? 0) > 0) return;
    foreach (self::$defaultStages as $stage) {
      Database::insert('recovery_progress', array_merge($stage, [
        'patient_id' => $patientId, 'therapist_id' => $therapistId
      ]));
    }
  }

  public static function forPatientPair(int $patientId, int $therapistId): array {
    self::ensure($patientId, $therapistId);
    return Database::fetchAll(
      "SELECT * FROM recovery_progress WHERE patient_id = ? AND therapist_id = ? ORDER BY stage_order ASC",
      [$patientId, $therapistId]
    );
  }

  public static function forPatientView(int $patientId): array {
    $tp = Database::fetch(
      "SELECT therapist_id FROM therapist_patients WHERE patient_id = ? LIMIT 1", [$patientId]
    );
    if (!$tp) return [];
    return self::forPatientPair($patientId, (int)$tp['therapist_id']);
  }

  public static function updateStage(int $id, string $status, string $notes = ''): void {
    $data = ['status' => $status];
    if ($notes) $data['therapist_notes'] = $notes;
    if ($status === 'in_progress') $data['started_at'] = date('Y-m-d H:i:s');
    if ($status === 'completed') $data['completed_at'] = date('Y-m-d H:i:s');
    Database::update('recovery_progress', $id, $data);
  }
}
