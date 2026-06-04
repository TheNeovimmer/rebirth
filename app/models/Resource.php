<?php
class Resource {
  public static function all(): array {
    return Database::fetchAll("SELECT * FROM resources ORDER BY created_at DESC");
  }

  public static function find(int $id): ?array {
    return Database::fetch("SELECT * FROM resources WHERE id = ?", [$id]);
  }

  public static function create(array $data): int {
    return Database::insert('resources', $data);
  }

  public static function update(int $id, array $data): int {
    return Database::update('resources', $id, $data);
  }

  public static function delete(int $id): int {
    return Database::delete('resources', $id);
  }

  public static function totalCount(): int {
    return Database::count("SELECT COUNT(*) as count FROM resources");
  }
}
