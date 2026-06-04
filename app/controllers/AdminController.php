<?php
class AdminController extends Controller {
  private function renderAdmin(string $view, string $title, array $extra = []): void {
    $this->requireRole('admin');
    $user = $this->user();
    $this->renderWithLayout('admin', $view, array_merge([
      'pageTitle' => 'Admin ' . $title . ' - Rebirth',
      'page' => $title,
      'user' => $user,
      '_token' => $this->csrf(),
    ], $extra));
  }

  public function dashboard(): void {
    $totalUsers = User::totalCount();
    $usersByRole = User::countByRole();
    $memberCount = 0;
    $therapistCount = 0;
    $adminCount = 0;
    foreach ($usersByRole as $r) {
      if ($r['role'] === 'member') $memberCount = $r['count'];
      if ($r['role'] === 'therapist') $therapistCount = $r['count'];
      if ($r['role'] === 'admin') $adminCount = $r['count'];
    }
    $recentRegistrations = User::recent(5);
    $moderationQueue = Group::moderationQueue();
    $totalCheckins = Database::count("SELECT COUNT(*) as count FROM check_ins");
    $weekActivity = Database::count("SELECT COUNT(*) as count FROM check_ins WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

    $this->renderAdmin('admin/dashboard', 'Dashboard', [
      'totalUsers' => $totalUsers,
      'memberCount' => $memberCount,
      'therapistCount' => $therapistCount,
      'adminCount' => $adminCount,
      'totalCheckins' => $totalCheckins,
      'weekActivity' => $weekActivity,
      'recentRegistrations' => $recentRegistrations,
      'moderationQueue' => $moderationQueue,
    ]);
  }

  public function users(): void {
    $users = User::all();
    $therapists = User::therapists();
    $assignments = Database::fetchAll("SELECT * FROM therapist_patients");
    $assignmentMap = [];
    foreach ($assignments as $a) {
      $assignmentMap[$a['patient_id']] = $a['therapist_id'];
    }
    $this->renderAdmin('admin/users', 'Users', [
      'users' => $users,
      'therapists' => $therapists,
      'assignmentMap' => $assignmentMap,
      'currentAdmin' => $this->user(),
    ]);
  }

  public function createUser(): void {
    $this->verifyCsrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'member';
    if ($name && $email && $password) {
      $data = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
      ];
      User::create($data);
    }
    $this->redirect('/admin/users?success=User created');
  }

  public function updateUser(): void {
    $this->verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $data = [];
    if (!empty($_POST['name'])) $data['name'] = $_POST['name'];
    if (!empty($_POST['role'])) $data['role'] = $_POST['role'];
    if (!empty($_POST['password'])) $data['password'] = $_POST['password'];
    if (!empty($data)) {
      User::updateProfile($id, $data);
    }
    $this->redirect('/admin/users?success=User updated');
  }

  public function deleteUser(): void {
    $this->verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $current = $this->user();
    if ($id !== (int) $current['id']) {
      User::delete($id);
    }
    $this->redirect('/admin/users?success=User deleted');
  }

  public function assignPatient(): void {
    $this->verifyCsrf();
    $patientId = (int) ($_POST['patient_id'] ?? 0);
    $therapistId = (int) ($_POST['therapist_id'] ?? 0);
    if ($patientId && $therapistId) {
      $exists = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$therapistId, $patientId]);
      if (!$exists) {
        Database::insert('therapist_patients', [
          'therapist_id' => $therapistId,
          'patient_id' => $patientId,
          'assigned_at' => date('Y-m-d H:i:s'),
        ]);
        $patient = Database::fetch("SELECT name FROM users WHERE id = ?", [$patientId]);
        Notification::create($therapistId, 'appointment', 'New patient assigned', ($patient['name'] ?? 'A patient') . ' has been assigned to you', '/therapist/patients');
      }
    }
    $this->redirect('/admin/users?success=Patient assigned to therapist');
  }

  public function unassignPatient(): void {
    $this->verifyCsrf();
    $patientId = (int) ($_POST['patient_id'] ?? 0);
    $therapistId = (int) ($_POST['therapist_id'] ?? 0);
    if ($patientId && $therapistId) {
      Database::query("DELETE FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$therapistId, $patientId]);
    }
    $this->redirect('/admin/users?success=Patient unassigned from therapist');
  }

  public function moderation(): void {
    $messages = Database::fetchAll(
      "SELECT m.*, u.name as author_name FROM messages m
       JOIN users u ON u.id = m.user_id
       ORDER BY m.created_at DESC LIMIT 50"
    );
    $this->renderAdmin('admin/moderation', 'Moderation', ['messages' => $messages]);
  }

  public function deleteMessage(): void {
    $this->verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    Group::deleteMessage($id);
    $this->redirect('/admin/moderation?success=Message deleted');
  }

  public function settings(): void {
    $user = $this->user();
    $this->renderAdmin('admin/settings', 'Settings', ['user' => $user]);
  }
}
