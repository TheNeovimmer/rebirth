<?php
class AuthController extends Controller {
  public function login(): void {
    if ($_SESSION['user_id'] ?? false) {
      $this->redirectBasedOnRole();
    }
    $error = $_GET['error'] ?? '';
    $this->renderWithLayout('auth', 'auth/login', [
      'pageTitle' => 'Sign In - Rebirth',
      'action' => '/login',
      'error' => $error,
      '_token' => $this->csrf(),
    ]);
  }

  public function authenticate(): void {
    $this->verifyCsrf();
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = User::login($email, $password);
    if (!$user) {
      $this->redirect('/login?error=Invalid email or password');
    }
    $this->redirectBasedOnRole();
  }

  public function signup(): void {
    if ($_SESSION['user_id'] ?? false) {
      $this->redirectBasedOnRole();
    }
    $error = $_GET['error'] ?? '';
    $this->renderWithLayout('auth', 'auth/signup', [
      'pageTitle' => 'Create Account - Rebirth',
      'action' => '/signup',
      'error' => $error,
      '_token' => $this->csrf(),
    ]);
  }

  public function register(): void {
    $this->verifyCsrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$name || !$email || !$password) {
      $this->redirect('/signup?error=All fields are required');
    }
    if (strlen($password) < 6) {
      $this->redirect('/signup?error=Password must be at least 6 characters');
    }

    $user = User::register($name, $email, $password);
    if (!$user) {
      $this->redirect('/signup?error=Email already registered');
    }
    $this->redirectBasedOnRole();
  }

  public function logout(): void {
    User::logout();
    $this->redirect('/login');
  }

  private function redirectBasedOnRole(): void {
    $user = User::current();
    if ($user && $user['role'] === 'admin') {
      $this->redirect('/admin/dashboard');
    }
    $this->redirect('/panel/dashboard');
  }
}
