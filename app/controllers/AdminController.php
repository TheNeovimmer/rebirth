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
    $usersByStage = User::countByStage();
    $appointmentsToday = Appointment::todayCount();
    $recentRegistrations = User::recent(5);
    $upcomingSessions = Appointment::upcomingForAdmin(4);
    $moderationQueue = Group::moderationQueue();

    $this->renderAdmin('admin/dashboard', 'Dashboard', [
      'totalUsers' => $totalUsers,
      'usersByRole' => $usersByRole,
      'usersByStage' => $usersByStage,
      'appointmentsToday' => $appointmentsToday,
      'recentRegistrations' => $recentRegistrations,
      'upcomingSessions' => $upcomingSessions,
      'moderationQueue' => $moderationQueue,
    ]);
  }

  public function users(): void {
    $users = User::all();
    $this->renderAdmin('admin/users', 'Users', ['users' => $users, 'currentAdmin' => $this->user()]);
  }

  public function createUser(): void {
    $this->verifyCsrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'member';
    $stage = $_POST['stage'] ?? 'Onboarding';
    if ($name && $email && $password) {
      $data = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'role' => $role,
        'stage' => $stage,
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
    if (!empty($_POST['stage'])) $data['stage'] = $_POST['stage'];
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

  public function appointments(): void {
    $appointments = Appointment::all();
    $therapists = User::therapists();
    $patients = User::all();
    $this->renderAdmin('admin/appointments', 'Appointments', [
      'appointments' => $appointments,
      'therapists' => $therapists,
      'patients' => $patients,
    ]);
  }

  public function createAppointment(): void {
    $this->verifyCsrf();
    $userId = (int) ($_POST['user_id'] ?? 0);
    $therapistId = !empty($_POST['therapist_id']) ? (int) $_POST['therapist_id'] : null;
    $title = trim($_POST['title'] ?? '');
    $dateTime = $_POST['date_time'] ?? '';
    if ($userId && $title && $dateTime) {
      Appointment::create([
        'user_id' => $userId,
        'therapist_id' => $therapistId,
        'title' => $title,
        'date_time' => $dateTime,
        'status' => 'confirmed',
      ]);
    }
    $this->redirect('/admin/appointments?success=Appointment created');
  }

  public function updateAppointment(): void {
    $this->verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $data = [];
    if (!empty($_POST['title'])) $data['title'] = $_POST['title'];
    if (!empty($_POST['date_time'])) $data['date_time'] = $_POST['date_time'];
    if (!empty($_POST['status'])) $data['status'] = $_POST['status'];
    if (!empty($_POST['therapist_id'])) $data['therapist_id'] = (int) $_POST['therapist_id'];
    if (!empty($data)) {
      Appointment::update($id, $data);
    }
    $this->redirect('/admin/appointments?success=Appointment updated');
  }

  public function resources(): void {
    $resources = Resource::all();
    $this->renderAdmin('admin/resources', 'Resources', ['resources' => $resources]);
  }

  public function createResource(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $type = $_POST['type'] ?? '';
    $icon = $_POST['icon'] ?? 'fa-regular fa-file-lines';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tag = $_POST['tag'] ?? 'Education';
    if ($type && $title && $description) {
      Resource::create([
        'type' => $type,
        'icon' => $icon,
        'title' => $title,
        'description' => $description,
        'tag' => $tag,
        'created_by' => $user['id'],
      ]);
    }
    $this->redirect('/admin/resources?success=Resource created');
  }

  public function updateResource(): void {
    $this->verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    $data = [];
    foreach (['type', 'icon', 'title', 'description', 'tag'] as $field) {
      if (!empty($_POST[$field])) $data[$field] = $_POST[$field];
    }
    if (!empty($data)) {
      Resource::update($id, $data);
    }
    $this->redirect('/admin/resources?success=Resource updated');
  }

  public function deleteResource(): void {
    $this->verifyCsrf();
    $id = (int) ($_POST['id'] ?? 0);
    Resource::delete($id);
    $this->redirect('/admin/resources?success=Resource deleted');
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

  public function analytics(): void {
    $totalUsers = User::totalCount();
    $usersByRole = User::countByRole();
    $usersByStage = User::countByStage();
    $appointmentsToday = Appointment::todayCount();
    $totalResources = Resource::totalCount();
    $totalCheckins = Database::count("SELECT COUNT(*) as count FROM check_ins");
    $weekCheckins = Database::fetchAll(
      "SELECT DATE(created_at) as date, COUNT(*) as count
       FROM check_ins WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
       GROUP BY DATE(created_at) ORDER BY date"
    );
    $this->renderAdmin('admin/analytics', 'Analytics', [
      'totalUsers' => $totalUsers,
      'usersByRole' => $usersByRole,
      'usersByStage' => $usersByStage,
      'appointmentsToday' => $appointmentsToday,
      'totalResources' => $totalResources,
      'totalCheckins' => $totalCheckins,
      'weekCheckins' => $weekCheckins,
    ]);
  }
}
