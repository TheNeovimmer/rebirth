<?php
class TreatmentPlan {
  public static function forPatient(int $patientId): array {
    return Database::fetchAll(
      "SELECT tp.*, u.name as therapist_name
       FROM treatment_plans tp JOIN users u ON u.id = tp.therapist_id
       WHERE tp.patient_id = ? ORDER BY tp.created_at DESC",
      [$patientId]
    );
  }

  public static function activeForPatient(int $patientId): ?array {
    return Database::fetch(
      "SELECT tp.*, u.name as therapist_name
       FROM treatment_plans tp JOIN users u ON u.id = tp.therapist_id
       WHERE tp.patient_id = ? AND tp.status = 'active' LIMIT 1",
      [$patientId]
    );
  }

  public static function forPatientTherapist(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM treatment_plans WHERE patient_id = ? AND therapist_id = ? ORDER BY created_at DESC",
      [$patientId, $therapistId]
    );
  }

  public static function find(int $id): ?array {
    return Database::fetch("SELECT * FROM treatment_plans WHERE id = ?", [$id]);
  }

  public static function create(int $patientId, int $therapistId, array $data): int {
    return Database::insert('treatment_plans', [
      'patient_id' => $patientId,
      'therapist_id' => $therapistId,
      'title' => $data['title'],
      'goals' => $data['goals'] ?? '',
      'objectives' => $data['objectives'] ?? '',
      'activities' => $data['activities'] ?? '',
      'coping_strategies' => $data['coping_strategies'] ?? '',
      'recommendations' => $data['recommendations'] ?? '',
      'status' => $data['status'] ?? 'active',
    ]);
  }

  public static function update(int $id, array $data): void {
    $allowed = ['title', 'goals', 'objectives', 'activities', 'coping_strategies', 'recommendations', 'status'];
    $updates = [];
    $params = [];
    foreach ($allowed as $field) {
      if (isset($data[$field])) {
        $updates[] = "`$field` = ?";
        $params[] = $data[$field];
      }
    }
    if (!empty($updates)) {
      $params[] = $id;
      Database::query("UPDATE treatment_plans SET " . implode(', ', $updates) . " WHERE id = ?", $params);
    }
  }

  public static function delete(int $id, int $therapistId): void {
    Database::query("DELETE FROM treatment_plans WHERE id = ? AND therapist_id = ?", [$id, $therapistId]);
  }
}
