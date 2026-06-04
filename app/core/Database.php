<?php
class Database {
  private static ?PDO $instance = null;

  public static function connect(): PDO {
    if (self::$instance === null) {
      $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=utf8mb4';
      self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
      ]);
      self::$instance->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
      self::$instance->exec('USE `' . DB_NAME . '`');
    }
    return self::$instance;
  }

  public static function query(string $sql, array $params = []): PDOStatement {
    $stmt = self::connect()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
  }

  public static function fetch(string $sql, array $params = []): ?array {
    $row = self::query($sql, $params)->fetch();
    return $row ?: null;
  }

  public static function fetchAll(string $sql, array $params = []): array {
    return self::query($sql, $params)->fetchAll();
  }

  public static function insert(string $table, array $data): int {
    $columns = implode('`, `', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    self::query("INSERT INTO `$table` (`$columns`) VALUES ($placeholders)", array_values($data));
    return (int) self::connect()->lastInsertId();
  }

  public static function update(string $table, int $id, array $data): int {
    $sets = implode(' = ?, ', array_keys($data)) . ' = ?';
    $values = array_values($data);
    $values[] = $id;
    $stmt = self::query("UPDATE `$table` SET $sets WHERE id = ?", $values);
    return $stmt->rowCount();
  }

  public static function delete(string $table, int $id): int {
    $stmt = self::query("DELETE FROM `$table` WHERE id = ?", [$id]);
    return $stmt->rowCount();
  }

  public static function exists(string $sql, array $params = []): bool {
    return (bool) self::fetch($sql, $params);
  }

  public static function count(string $sql, array $params = []): int {
    $row = self::fetch($sql, $params);
    return $row ? (int) current($row) : 0;
  }

  public static function migrate(): void {
    $schema = file_get_contents(APP_PATH . '/database/schema.sql');
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $stmt) {
      if (!empty($stmt)) {
        self::query($stmt);
      }
    }
  }
}
