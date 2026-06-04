<?php
class PanelController extends Controller {
  private function renderPanel(string $view, string $title, array $extra = []): void {
    $this->requireAuth();
    $user = $this->user();
    $memberGroups = Database::fetchAll(
      "SELECT g.* FROM `groups` g JOIN group_members gm ON gm.group_id = g.id WHERE gm.user_id = ?",
      [$user['id']]
    );
    $this->renderWithLayout('panel', $view, array_merge([
      'pageTitle' => $title . ' - Rebirth',
      'page' => $title,
      'user' => $user,
      'memberGroups' => $memberGroups,
      '_token' => $this->csrf(),
    ], $extra));
  }

  public function dashboard(): void {
    $user = $this->user();

    if ($user['role'] === 'therapist') {
      $patients = User::patientsForTherapist($user['id']);
      $patientCount = count($patients);
      $upcomingAppointments = Appointment::upcomingForTherapist($user['id']);
      $todayAppointments = count(array_filter($upcomingAppointments, fn($a) => date('Y-m-d', strtotime($a['date_time'])) === date('Y-m-d')));
      $patientIds = array_column($patients, 'id');
      $recentCheckins = [];
      $recentActivity = 0;
      if (!empty($patientIds)) {
        $placeholders = implode(',', array_fill(0, count($patientIds), '?'));
        $recentCheckins = Database::fetchAll(
          "SELECT c.*, u.name as patient_name FROM check_ins c
           JOIN users u ON u.id = c.user_id
           WHERE c.user_id IN ($placeholders)
           ORDER BY c.check_date DESC LIMIT 10",
          $patientIds
        );
        $recentActivity = Database::count(
          "SELECT COUNT(*) as count FROM check_ins WHERE user_id IN ($placeholders) AND check_date >= DATE_SUB(NOW(), INTERVAL 3 DAY)",
          $patientIds
        );
      }
      $this->renderPanel('panel/therapist-dashboard', 'Dashboard', [
        'patients' => $patients,
        'patientCount' => $patientCount,
        'upcomingAppointments' => $upcomingAppointments,
        'todayAppointments' => $todayAppointments,
        'recentCheckins' => $recentCheckins,
        'recentActivity' => $recentActivity,
      ]);
      return;
    }

    $streak = Milestone::currentStreak($user['id']);
    $totalCheckins = Milestone::totalCheckins($user['id']);
    $avgMood = Milestone::avgMood($user['id']);
    $todayCheckin = Database::fetch(
      "SELECT * FROM check_ins WHERE user_id = ? AND check_date = CURDATE()",
      [$user['id']]
    );
    $appointments = Appointment::upcoming($user['id']);
    $milestones = Milestone::forUser($user['id']);

    $this->renderPanel('panel/dashboard', 'Dashboard', [
      'streak' => $streak,
      'totalCheckins' => $totalCheckins,
      'avgMood' => $avgMood,
      'todayCheckin' => $todayCheckin,
      'appointments' => $appointments,
      'milestones' => $milestones,
    ]);
  }

  public function checkin(): void {
    $this->requireRole('member', 'admin');
    $user = $this->user();
    $todayCheckin = Database::fetch(
      "SELECT * FROM check_ins WHERE user_id = ? AND check_date = CURDATE()",
      [$user['id']]
    );
    $recentCheckins = Database::fetchAll(
      "SELECT * FROM check_ins WHERE user_id = ? ORDER BY check_date DESC LIMIT 7",
      [$user['id']]
    );
    $this->renderPanel('panel/checkin', 'Daily Check-in', [
      'todayCheckin' => $todayCheckin,
      'recentCheckins' => $recentCheckins,
    ]);
  }

  public function saveCheckin(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $existing = Database::fetch(
      "SELECT id FROM check_ins WHERE user_id = ? AND check_date = CURDATE()",
      [$user['id']]
    );
    if ($existing) {
      $this->redirect('/panel/checkin?error=Already checked in today');
    }
    $mood = $_POST['mood'] ?? '';
    if (!$mood) {
      $this->redirect('/panel/checkin?error=Please select your mood');
    }
    $craving = $_POST['craving_level'] ?? null;
    $note = $_POST['note'] ?? '';
    Database::insert('check_ins', [
      'user_id' => $user['id'],
      'mood' => $mood,
      'craving_level' => $craving !== null ? (int) $craving : null,
      'note' => $note,
      'check_date' => date('Y-m-d'),
    ]);
    $streak = Milestone::currentStreak($user['id']);
    Milestone::updateProgress($user['id'], $streak);
    $this->redirect('/panel/dashboard?success=Check-in saved');
  }

  public function journal(): void {
    $this->requireRole('member', 'admin');
    $user = $this->user();
    $entries = JournalEntry::forUser($user['id']);
    $this->renderPanel('panel/journal', 'Journal', ['entries' => $entries]);
  }

  public function createJournal(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $content = trim($_POST['content'] ?? '');
    $mood = $_POST['mood'] ?? 'okay';
    if ($content) {
      JournalEntry::create($user['id'], $content, $mood);
    }
    $this->redirect('/panel/journal?success=Entry created');
  }

  public function deleteJournal(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int) ($_POST['id'] ?? 0);
    JournalEntry::delete($id, $user['id']);
    $this->redirect('/panel/journal?success=Entry deleted');
  }

  public function appointments(): void {
    $user = $this->user();
    if ($user['role'] === 'therapist') {
      $myAppointments = Appointment::upcomingForTherapist($user['id']);
      $pastAppointments = [];
      $therapists = [];
    } else {
      $myAppointments = Appointment::forPatient($user['id']);
      $therapists = User::therapists();
      $pastAppointments = [];
    }
    $this->renderPanel('panel/appointments', 'Appointments', [
      'appointments' => $myAppointments,
      'therapists' => $therapists ?? [],
      'pastAppointments' => $pastAppointments,
    ]);
  }

  public function createAppointment(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $title = trim($_POST['title'] ?? '');
    $dateTime = $_POST['date_time'] ?? '';
    $therapistId = !empty($_POST['therapist_id']) ? (int) $_POST['therapist_id'] : null;
    if ($title && $dateTime) {
      Appointment::create([
        'user_id' => $user['id'],
        'therapist_id' => $therapistId,
        'title' => $title,
        'date_time' => $dateTime,
        'status' => 'pending',
      ]);
    }
    $this->redirect('/panel/appointments?success=Appointment booked');
  }

  public function cancelAppointment(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int) ($_POST['id'] ?? 0);
    $appt = Appointment::find($id);
    if ($appt && $appt['user_id'] == $user['id']) {
      Appointment::cancel($id);
    }
    $this->redirect('/panel/appointments?success=Appointment cancelled');
  }

  public function community(): void {
    $user = $this->user();
    $groups = Group::all();
    $messages = Group::messages();
    $this->renderPanel('panel/community', 'Community', [
      'groups' => $groups,
      'messages' => $messages,
    ]);
  }

  public function createMessage(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $text = trim($_POST['text'] ?? '');
    $groupId = (int) ($_POST['group_id'] ?? 0);
    if ($text) {
      Group::createMessage($user['id'], $text, $groupId);
    }
    $this->redirect('/panel/community?success=Message posted');
  }

  public function likeMessage(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $messageId = (int) ($_POST['message_id'] ?? 0);
    Group::toggleLike($messageId, $user['id']);
    $this->redirect('/panel/community');
  }

  public function joinGroup(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $groupId = (int) ($_POST['group_id'] ?? 0);
    Group::join($groupId, $user['id']);
    $this->redirect('/panel/community?success=Joined group');
  }

  public function progress(): void {
    $this->requireRole('member', 'admin');
    $user = $this->user();
    $streak = Milestone::currentStreak($user['id']);
    $totalCheckins = Milestone::totalCheckins($user['id']);
    $avgMood = Milestone::avgMood($user['id']);
    $milestones = Milestone::forUser($user['id']);
    $moodTrend = Milestone::moodTrend($user['id'], 7);
    $weeklyProgress = Milestone::weeklyProgress($user['id']);
    $this->renderPanel('panel/progress', 'Progress', [
      'streak' => $streak,
      'totalCheckins' => $totalCheckins,
      'avgMood' => $avgMood,
      'milestones' => $milestones,
      'moodTrend' => $moodTrend,
      'weeklyProgress' => $weeklyProgress,
    ]);
  }

  public function resources(): void {
    $resources = Resource::all();
    $this->renderPanel('panel/resources', 'Resources', ['resources' => $resources]);
  }

  public function settings(): void {
    $user = $this->user();
    $this->renderPanel('panel/settings', 'Settings', ['user' => $user]);
  }

  public function updateProfile(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $data = [];
    if ($name) $data['name'] = $name;
    if ($password) $data['password'] = $password;

    if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
      $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
      finfo_close($finfo);
      if (!in_array($mime, $allowed, true)) {
        $this->redirect('/panel/settings?error=Invalid file type. Allowed: jpg, png, gif, webp');
      }
      $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => 'jpg',
      };
      $filename = 'avatar_' . $user['id'] . '_' . time() . '.' . $ext;
      $dest = BASE_PATH . '/uploads/avatars/' . $filename;
      if (move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
        if (!empty($user['avatar']) && file_exists(BASE_PATH . '/uploads/avatars/' . $user['avatar'])) {
          unlink(BASE_PATH . '/uploads/avatars/' . $user['avatar']);
        }
        $data['avatar'] = $filename;
      }
    }

    if (!empty($data)) {
      User::updateProfile($user['id'], $data);
      $_SESSION['user'] = User::find($user['id']);
    }
    $this->redirect('/panel/settings?success=Profile updated');
  }

  public function myPatients(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $patients = User::patientsForTherapist($user['id']);
    $this->renderPanel('panel/my-patients', 'My Patients', ['patients' => $patients]);
  }

  public function patientDetail(int $id): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $isAssigned = Database::exists(
      "SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?",
      [$user['id'], $id]
    );
    if (!$isAssigned) {
      http_response_code(403);
      echo '<h1>403 — Not your patient</h1>';
      exit;
    }
    $patient = User::find($id);
    $checkins = Database::fetchAll(
      "SELECT * FROM check_ins WHERE user_id = ? ORDER BY check_date DESC LIMIT 10",
      [$id]
    );
    $journal = JournalEntry::recentForTherapistPatient($id, 10);
    $milestones = Milestone::forUser($id);
    $this->renderPanel('panel/patient-detail', 'Patient Detail', [
      'patient' => $patient,
      'checkins' => $checkins,
      'journal' => $journal,
      'milestones' => $milestones,
    ]);
  }
}
