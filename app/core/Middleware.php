<?php
trait Middleware {
  protected function requireAuth(): void {
    if (!isset($_SESSION['user_id'])) {
      $this->redirect('/login');
    }
    $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
    if (!$user) {
      unset($_SESSION['user_id']);
      $this->redirect('/login');
    }
    $_SESSION['user'] = $user;
  }

  protected function requireRole(string ...$roles): void {
    $this->requireAuth();
    $user = $_SESSION['user'];
    if (!in_array($user['role'], $roles, true)) {
      http_response_code(403);
      echo '<h1>403 — Forbidden</h1><p>You do not have access to this page.</p>';
      exit;
    }
  }

  protected function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
  }

  protected function user(): array {
    $u = $this->currentUser();
    if (!$u) {
      $this->redirect('/login');
    }
    return $u;
  }

  protected function csrf(): string {
    if (empty($_SESSION['_token'])) {
      $_SESSION['_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_token'];
  }

  protected function verifyCsrf(): void {
    $token = $_POST['_token'] ?? '';
    if (empty($_SESSION['_token']) || !hash_equals($_SESSION['_token'], $token)) {
      http_response_code(419);
      echo '<h1>419 — Session Expired</h1>';
      exit;
    }
  }

  protected function redirectWith(string $path, string $type, string $message): void {
    $this->redirect($path . (str_contains($path, '?') ? '&' : '?') . $type . '=' . urlencode($message));
  }

  protected function error(string $message): string {
    return '<div class="alert alert-error">' . htmlspecialchars($message) . '</div>';
  }

  protected function success(string $message): string {
    return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
  }
}
