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
      $unreadCount = Database::count(
        "SELECT COUNT(*) as count FROM conversation_messages cm
         JOIN conversations c ON c.id = cm.conversation_id
         WHERE c.therapist_id = ? AND cm.sender_id != ? AND cm.read_at IS NULL",
        [$user['id'], $user['id']]
      );
      $activeSos = SOSAlert::activeCount($user['id']);
      $this->renderPanel('panel/therapist-dashboard', 'Dashboard', [
        'patients' => $patients,
        'patientCount' => $patientCount,
        'recentCheckins' => $recentCheckins,
        'recentActivity' => $recentActivity,
        'unreadCount' => $unreadCount,
        'activeSos' => $activeSos,
      ]);
      return;
    }

    $todayCheckin = Database::fetch(
      "SELECT * FROM check_ins WHERE user_id = ? AND check_date = CURDATE()",
      [$user['id']]
    );
    $recentJournals = JournalEntry::forUser($user['id'], 3);
    $unreadCount = Database::count(
      "SELECT COUNT(*) as count FROM conversation_messages cm
       JOIN conversations c ON c.id = cm.conversation_id
       WHERE c.patient_id = ? AND cm.sender_id != ? AND cm.read_at IS NULL",
      [$user['id'], $user['id']]
    );
    $recentResources = Database::fetchAll("SELECT * FROM resources ORDER BY created_at DESC LIMIT 3");
    $totalCheckins = Database::count("SELECT COUNT(*) FROM check_ins WHERE user_id = ?", [$user['id']]);

    $this->renderPanel('panel/dashboard', 'Dashboard', [
      'todayCheckin' => $todayCheckin,
      'recentJournals' => $recentJournals,
      'unreadCount' => $unreadCount,
      'recentResources' => $recentResources,
      'totalCheckins' => $totalCheckins,
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
    $totalCheckins = Database::count("SELECT COUNT(*) FROM check_ins WHERE user_id = ?", [$user['id']]);
    $moodScores = Database::fetchAll(
      "SELECT CASE mood
        WHEN 'great' THEN 100 WHEN 'good' THEN 80 WHEN 'neutral' THEN 60
        WHEN 'difficult' THEN 30 WHEN 'struggling' THEN 10 ELSE 50
      END as score FROM check_ins WHERE user_id = ?",
      [$user['id']]
    );
    $avgMood = $totalCheckins > 0 ? round(array_sum(array_column($moodScores, 'score')) / $totalCheckins) : 0;
    $this->renderPanel('panel/checkin', 'Daily Check-in', [
      'todayCheckin' => $todayCheckin,
      'recentCheckins' => $recentCheckins,
      'totalCheckins' => $totalCheckins,
      'avgMood' => $avgMood,
    ]);
  }

  public function saveCheckin(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $mood = $_POST['mood'] ?? '';
    if (!$mood) {
      $this->redirect('/panel/checkin?error=Please select your mood');
    }
    $craving = $_POST['craving_level'] ?? null;
    $note = $_POST['note'] ?? '';
    $existing = Database::fetch(
      "SELECT id FROM check_ins WHERE user_id = ? AND check_date = CURDATE()",
      [$user['id']]
    );
    if ($existing) {
      Database::update('check_ins', $existing['id'], [
        'mood' => $mood,
        'craving_level' => $craving !== null ? (int) $craving : null,
        'note' => $note,
      ]);
      $this->redirect('/panel/checkin?success=Mood updated');
    } else {
      Database::insert('check_ins', [
        'user_id' => $user['id'],
        'mood' => $mood,
        'craving_level' => $craving !== null ? (int) $craving : null,
        'note' => $note,
        'check_date' => date('Y-m-d'),
      ]);
      $this->redirect('/panel/checkin?success=Check-in saved');
    }
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
    $mood = $_POST['mood'] ?? 'neutral';
    if ($content) {
      JournalEntry::create($user['id'], $content, $mood);
    }
    $this->redirect('/panel/journal?success=Entry created');
  }

  public function updateJournal(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int) ($_POST['id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $mood = $_POST['mood'] ?? 'neutral';
    if ($id && $content) {
      JournalEntry::update($id, $user['id'], $content, $mood);
    }
    $this->redirect('/panel/journal?success=Entry updated');
  }

  public function deleteJournal(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int) ($_POST['id'] ?? 0);
    JournalEntry::delete($id, $user['id']);
    $this->redirect('/panel/journal?success=Entry deleted');
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
    $parentId = !empty($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;
    if ($text) {
      Group::createMessage($user['id'], $text, $groupId, $parentId);
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

  public function messages(): void {
    $user = $this->user();
    if ($user['role'] === 'therapist') { http_response_code(403); exit; }
    $therapistId = $this->getPatientTherapistId($user['id']);
    $conversation = null;
    $messages = [];
    if ($therapistId) {
      $convId = Conversation::ensure($user['id'], $therapistId);
      $conversation = Conversation::forPatient($user['id']);
      $messages = ConversationMessage::forConversation($convId);
    }
    $this->renderPanel('panel/messages', 'Messages', [
      'conversation' => $conversation,
      'messages' => $messages,
      'therapistId' => $therapistId,
    ]);
  }

  public function sendMessage(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $convId = (int)($_POST['conversation_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    if ($convId && $content) {
      $conv = Database::fetch("SELECT * FROM conversations WHERE id = ? AND (patient_id = ? OR therapist_id = ?)", [$convId, $user['id'], $user['id']]);
      if ($conv) {
        ConversationMessage::send($convId, $user['id'], $content);
        $recipientId = $conv['patient_id'] == $user['id'] ? $conv['therapist_id'] : $conv['patient_id'];
        $recipientLink = '/panel/messages';
        $convCheck = Database::fetch("SELECT therapist_id FROM conversations WHERE id = ?", [$convId]);
        if ($convCheck && $convCheck['therapist_id'] == $recipientId) {
          $recipientLink = '/therapist/messages';
        }
        Notification::create($recipientId, 'message', 'New message from ' . $user['name'], mb_substr($content, 0, 80), $recipientLink, $convId);
      }
    }
    $this->redirect($_SERVER['HTTP_REFERER'] ?? '/panel/messages');
  }

  public function pollMessages(): void {
    $user = $this->user();
    $convId = (int)($_GET['conversation_id'] ?? 0);
    $afterId = (int)($_GET['after'] ?? 0);
    if (!$convId) { echo json_encode([]); exit; }
    $conv = Database::fetch("SELECT * FROM conversations WHERE id = ? AND (patient_id = ? OR therapist_id = ?)", [$convId, $user['id'], $user['id']]);
    if (!$conv) { echo json_encode([]); exit; }
    Conversation::markRead($convId, $user['id']);
    $messages = ConversationMessage::forConversation($convId, $afterId);
    header('Content-Type: application/json');
    echo json_encode($messages);
    exit;
  }

  public function sos(): void {
    $user = $this->user();
    if ($user['role'] !== 'member') { http_response_code(403); exit; }
    $therapistId = $this->getPatientTherapistId($user['id']);
    $alerts = $therapistId ? SOSAlert::forPatient($user['id'], $therapistId) : [];
    $therapistName = $therapistId ? (Database::fetch("SELECT name FROM users WHERE id = ?", [$therapistId])['name'] ?? 'your therapist') : null;
    $this->renderPanel('panel/sos', 'SOS', [
      'alerts' => $alerts,
      'therapistName' => $therapistName,
      'hasTherapist' => $therapistId !== null,
    ]);
  }

  public function sendSos(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $therapistId = $this->getPatientTherapistId($user['id']);
    if ($therapistId) {
      SOSAlert::create($user['id'], $therapistId);
      Notification::create($therapistId, 'sos', 'SOS Alert from ' . $user['name'], 'Urgent help requested', '/therapist/sos', $user['id']);
    }
    $this->redirect('/panel/sos?success=SOS alert sent to your therapist');
  }

  public function therapistResources(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $resources = TherapistResource::forTherapist($user['id']);
    $patients = User::patientsForTherapist($user['id']);
    $this->renderPanel('panel/therapist-resources', 'Resources', [
      'resources' => $resources,
      'patients' => $patients,
    ]);
  }

  public function createTherapistResource(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? '';
    $patientId = !empty($_POST['patient_id']) ? (int)$_POST['patient_id'] : null;
    $description = trim($_POST['description'] ?? '');
    if ($title && $type && !empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
      $allowed = [
        'video' => ['video/mp4', 'video/webm', 'video/ogg'],
        'pdf' => ['application/pdf'],
        'article' => ['text/plain', 'text/markdown'],
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
      ];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
      finfo_close($finfo);
      if (isset($allowed[$type]) && in_array($mime, $allowed[$type], true)) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = 'res_' . $user['id'] . '_' . time() . '.' . $ext;
        $dir = BASE_PATH . '/uploads/resources/' . $user['id'];
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $filename)) {
          TherapistResource::create([
            'therapist_id' => $user['id'],
            'patient_id' => $patientId,
            'title' => $title,
            'type' => $type,
            'file_path' => 'uploads/resources/' . $user['id'] . '/' . $filename,
            'description' => $description,
          ]);
          if ($patientId) {
            Notification::create($patientId, 'resource', 'New resource: ' . $title, $description ?? 'Resource shared by your therapist', '/panel/resources');
          } else {
            $patients = User::patientsForTherapist($user['id']);
            foreach ($patients as $p) {
              Notification::create($p['id'], 'resource', 'New resource: ' . $title, $description ?? 'Resource shared by your therapist', '/panel/resources');
            }
          }
        }
      }
    }
    $this->redirect('/therapist/resources');
  }

  public function deleteTherapistResource(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int)($_POST['id'] ?? 0);
    TherapistResource::delete($id, $user['id']);
    $this->redirect('/therapist/resources?success=Resource deleted');
  }

  public function therapistMessages(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $conversations = Conversation::forTherapist($user['id']);
    $this->renderPanel('panel/therapist-messages', 'Messages', [
      'conversations' => $conversations,
    ]);
  }

  public function therapistConversation(int $id): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $conv = Database::fetch("SELECT * FROM conversations WHERE id = ? AND therapist_id = ?", [$id, $user['id']]);
    if (!$conv) { http_response_code(404); exit; }
    Conversation::markRead($id, $user['id']);
    $patient = Database::fetch("SELECT id, name, initials, avatar FROM users WHERE id = ?", [$conv['patient_id']]);
    $messages = ConversationMessage::forConversation($id);
    $this->renderPanel('panel/therapist-conversation', 'Messages', [
      'conv' => $conv,
      'patient' => $patient,
      'messages' => $messages,
    ]);
  }

  public function therapistSos(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $alerts = SOSAlert::activeForTherapist($user['id']);
    $this->renderPanel('panel/therapist-sos', 'SOS Alerts', ['alerts' => $alerts]);
  }

  public function acknowledgeSos(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int)($_POST['id'] ?? 0);
    SOSAlert::acknowledge($id, $user['id']);
    $this->redirect('/therapist/sos');
  }

  public function resolveSos(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int)($_POST['id'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    SOSAlert::resolve($id, $user['id'], $notes);
    $this->redirect('/therapist/sos');
  }

  public function sosCount(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $count = SOSAlert::activeCount($user['id']);
    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
    exit;
  }

  public function notificationCount(): void {
    $user = $this->user();
    header('Content-Type: application/json');
    echo json_encode(['count' => Notification::unreadCount($user['id'])]);
    exit;
  }

  public function notificationList(): void {
    $user = $this->user();
    $notifications = Notification::recent($user['id'], 10);
    header('Content-Type: application/json');
    echo json_encode($notifications);
    exit;
  }

  public function notificationRead(): void {
    $user = $this->user();
    $id = (int)($_POST['id'] ?? 0);
    if ($id) Notification::markRead($id, $user['id']);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
  }

  public function notificationReadAll(): void {
    $user = $this->user();
    Notification::markAllRead($user['id']);
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
  }

  public function resources(): void {
    $resources = Resource::all();
    $extra = ['resources' => $resources];
    $user = $this->user();
    if ($user['role'] === 'member') {
      $therapistId = $this->getPatientTherapistId($user['id']);
      $extra['therapistResources'] = $therapistId ? TherapistResource::forPatient($user['id'], $therapistId) : [];
    }
    $this->renderPanel('panel/resources', 'Resources', $extra);
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
    $this->renderPanel('panel/patient-detail', 'Patient Detail', [
      'patient' => $patient,
      'checkins' => $checkins,
      'journal' => $journal,
    ]);
  }

  private function getPatientTherapistId(int $patientId): ?int {
    $tp = Database::fetch("SELECT therapist_id FROM therapist_patients WHERE patient_id = ? LIMIT 1", [$patientId]);
    return $tp ? (int)$tp['therapist_id'] : null;
  }
}
