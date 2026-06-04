<?php
class TherapistAvailability {
  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM therapist_availability WHERE therapist_id = ? ORDER BY day_of_week, start_time",
      [$therapistId]
    );
  }

  public static function save(int $therapistId, array $slots): void {
    Database::query("DELETE FROM therapist_availability WHERE therapist_id = ?", [$therapistId]);
    foreach ($slots as $slot) {
      if (!empty($slot['day']) && !empty($slot['start']) && !empty($slot['end'])) {
        Database::insert('therapist_availability', [
          'therapist_id' => $therapistId,
          'day_of_week' => (int)$slot['day'],
          'start_time' => $slot['start'],
          'end_time' => $slot['end'],
        ]);
      }
    }
  }

  public static function isAvailableNow(int $therapistId): bool {
    $day = (int)date('w');
    $time = date('H:i:s');
    $slot = Database::fetch(
      "SELECT id FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ? AND start_time <= ? AND end_time >= ? LIMIT 1",
      [$therapistId, $day, $time, $time]
    );
    return $slot !== null;
  }

  public static function availableSlotsForDate(int $therapistId, string $date): array {
    $dayOfWeek = (int)date('w', strtotime($date));
    $avail = Database::fetchAll(
      "SELECT * FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ? ORDER BY start_time",
      [$therapistId, $dayOfWeek]
    );
    $booked = Database::fetchAll(
      "SELECT date_time FROM appointments WHERE therapist_id = ? AND DATE(date_time) = ? AND status NOT IN ('cancelled')",
      [$therapistId, $date]
    );
    $slots = [];
    foreach ($avail as $a) {
      $start = strtotime($a['start_time']);
      $end = strtotime($a['end_time']);
      while ($start < $end) {
        $timeStr = date('H:i', $start);
        $bookedTime = false;
        foreach ($booked as $b) {
          if (date('H:i', strtotime($b['date_time'])) === $timeStr) { $bookedTime = true; break; }
        }
        $slots[] = ['time' => $timeStr, 'available' => !$bookedTime];
        $start = strtotime('+30 minutes', $start);
      }
    }
    return $slots;
  }
}
