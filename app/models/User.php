<?php
class User {
  public static function login(string $email, string $password): ?array {
    $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);
    if (!$user || !password_verify($password, $user['password_hash'])) {
      return null;
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = $user;
    return $user;
  }

  public static function register(string $name, string $email, string $password, string $stage): ?array {
    $existing = Database::fetch("SELECT id FROM users WHERE email = ?", [$email]);
    if ($existing) return null;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $initials = implode('', array_map(fn($n) => strtoupper($n[0]), explode(' ', $name)));
    $id = Database::insert('users', [
      'name' => $name,
      'email' => $email,
      'password_hash' => $hash,
      'role' => 'member',
      'stage' => $stage ?: 'Onboarding',
      'initials' => $initials,
    ]);
    $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user'] = $user;
    return $user;
  }

  public static function current(): ?array {
    return $_SESSION['user'] ?? null;
  }

  public static function logout(): void {
    unset($_SESSION['user_id'], $_SESSION['user']);
  }

  public static function all(): array {
    return Database::fetchAll("SELECT * FROM users ORDER BY created_at DESC");
  }

  public static function find(int $id): ?array {
    return Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
  }

  public static function create(array $data): int {
    $data['initials'] = implode('', array_map(fn($n) => strtoupper($n[0]), explode(' ', $data['name'])));
    if (!empty($data['password'])) {
      $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
      unset($data['password']);
    }
    $data['created_at'] = date('Y-m-d H:i:s');
    return Database::insert('users', $data);
  }

  public static function updateProfile(int $id, array $data): int {
    if (!empty($data['password'])) {
      $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
      unset($data['password']);
    }
    unset($data['id'], $data['email'], $data['role'], $data['created_at']);
    return Database::update('users', $id, $data);
  }

  public static function delete(int $id): int {
    return Database::delete('users', $id);
  }

  public static function countByRole(): array {
    return Database::fetchAll("SELECT role, COUNT(*) as count FROM users GROUP BY role");
  }

  public static function countByStage(): array {
    return Database::fetchAll("SELECT stage, COUNT(*) as count FROM users GROUP BY stage");
  }

  public static function recent(int $limit = 5): array {
    return Database::fetchAll("SELECT * FROM users ORDER BY created_at DESC LIMIT ?", [$limit]);
  }

  public static function totalCount(): int {
    return Database::count("SELECT COUNT(*) as count FROM users");
  }

  public static function therapists(): array {
    return Database::fetchAll("SELECT * FROM users WHERE role = 'therapist' ORDER BY name");
  }

  public static function patientsForTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT u.*, tp.assigned_at FROM users u
       JOIN therapist_patients tp ON tp.patient_id = u.id
       WHERE tp.therapist_id = ?
       ORDER BY u.name",
      [$therapistId]
    );
  }
}
