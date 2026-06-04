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
    $this->redirect('/panel/checkin?success=Check-in saved');
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

  // ─── MESSAGING (Patient) ───

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

  // ─── SOS (Patient) ───

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

  // ─── THERAPIST RESOURCES ───

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

  // ─── THERAPIST MESSAGING ───

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

  // ─── THERAPIST AVAILABILITY ───

  public function therapistAvailability(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $slots = TherapistAvailability::forTherapist($user['id']);
    $this->renderPanel('panel/therapist-availability', 'Availability', [
      'slots' => $slots,
    ]);
  }

  public function saveTherapistAvailability(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $days = $_POST['days'] ?? [];
    $starts = $_POST['starts'] ?? [];
    $ends = $_POST['ends'] ?? [];
    $slots = [];
    foreach ($days as $i => $day) {
      if (isset($starts[$i]) && isset($ends[$i])) {
        $slots[] = ['day' => $day, 'start' => $starts[$i], 'end' => $ends[$i]];
      }
    }
    TherapistAvailability::save($user['id'], $slots);
    $this->redirect('/therapist/availability?success=Availability saved');
  }

  // ─── SOS MANAGEMENT ───

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

  // ─── RECOVERY PROGRESS ───

  public function patientProgress(int $id): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $isAssigned = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$user['id'], $id]);
    if (!$isAssigned) { http_response_code(403); exit; }
    $patient = User::find($id);
    $stages = RecoveryProgress::forPatientPair($id, $user['id']);
    $this->renderPanel('panel/patient-progress', 'Patient Progress', [
      'patient' => $patient, 'stages' => $stages,
    ]);
  }

  public function updatePatientProgress(int $id): void {
    $this->verifyCsrf();
    $user = $this->user();
    $stageId = (int)($_POST['stage_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $notes = trim($_POST['notes'] ?? '');
    if ($stageId && $status) {
      RecoveryProgress::updateStage($stageId, $status, $notes);
    }
    $this->redirect('/therapist/patient/' . $id . '/progress');
  }

  // ─── SOS COUNT / AVAILABILITY CHECK (JSON endpoints) ───

  public function sosCount(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $count = SOSAlert::activeCount($user['id']);
    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
    exit;
  }

  public function checkAvailability(): void {
    $therapistId = (int)($_GET['therapist_id'] ?? 0);
    $available = $therapistId ? TherapistAvailability::isAvailableNow($therapistId) : false;
    header('Content-Type: application/json');
    echo json_encode(['available' => $available]);
    exit;
  }

  // ─── CLINICAL NOTES ───

  public function createClinicalNote(int $id): void {
    $this->verifyCsrf();
    $user = $this->user();
    $isAssigned = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$user['id'], $id]);
    if (!$isAssigned) { http_response_code(403); exit; }
    $content = trim($_POST['content'] ?? '');
    $sessionDate = !empty($_POST['session_date']) ? $_POST['session_date'] : null;
    if ($content) {
      ClinicalNote::create($id, $user['id'], $content, $sessionDate);
    }
    $this->redirect('/therapist/patient/' . $id . '?success=Note saved');
  }

  // ─── RELAPSE TRACKING ───

  public function relapses(): void {
    $this->requireRole('member');
    $user = $this->user();
    $therapistId = $this->getPatientTherapistId($user['id']);
    $relapses = $therapistId ? Relapse::forPatient($user['id']) : [];
    $triggers = $therapistId ? Relapse::triggerAnalysis($user['id'], $therapistId) : [];
    $this->renderPanel('panel/relapses', 'Relapse Tracking', [
      'relapses' => $relapses,
      'triggers' => $triggers,
      'hasTherapist' => $therapistId !== null,
      'therapistId' => $therapistId,
    ]);
  }

  public function createRelapse(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $therapistId = $this->getPatientTherapistId($user['id']);
    if (!$therapistId) { $this->redirect('/panel/relapses?error=No therapist assigned'); }
    Relapse::create($user['id'], $therapistId, [
      'relapse_date' => $_POST['relapse_date'] ?? date('Y-m-d H:i:s'),
      'trigger' => trim($_POST['trigger'] ?? ''),
      'severity' => $_POST['severity'] ?? 'moderate',
      'description' => trim($_POST['description'] ?? ''),
      'action_taken' => trim($_POST['action_taken'] ?? ''),
    ]);
    Notification::create($therapistId, 'relapse', $user['name'] . ' logged a relapse', 'Severity: ' . ($_POST['severity'] ?? 'moderate'), '/therapist/patient/' . $user['id'] . '/timeline', $user['id']);
    $this->redirect('/panel/relapses?success=Relapse recorded');
  }

  public function deleteRelapse(): void {
    $this->verifyCsrf();
    $user = $this->user();
    $id = (int)($_POST['id'] ?? 0);
    Relapse::delete($id, $user['id']);
    $this->redirect('/panel/relapses?success=Entry deleted');
  }

  // ─── TREATMENT PLANS ───

  public function myTreatmentPlan(): void {
    $this->requireRole('member');
    $user = $this->user();
    $activePlan = TreatmentPlan::activeForPatient($user['id']);
    $allPlans = TreatmentPlan::forPatient($user['id']);
    $this->renderPanel('panel/treatment-plan', 'Treatment Plan', [
      'activePlan' => $activePlan,
      'allPlans' => $allPlans,
    ]);
  }

  public function therapistPlans(int $id): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $isAssigned = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$user['id'], $id]);
    if (!$isAssigned) { http_response_code(403); exit; }
    $patient = User::find($id);
    $plans = TreatmentPlan::forPatientTherapist($id, $user['id']);
    $this->renderPanel('panel/therapist-plans', 'Treatment Plans', [
      'patient' => $patient, 'plans' => $plans,
    ]);
  }

  public function therapistPlanCreate(int $id): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $isAssigned = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$user['id'], $id]);
    if (!$isAssigned) { http_response_code(403); exit; }
    $patient = User::find($id);
    $this->renderPanel('panel/therapist-plan-form', 'Create Treatment Plan', [
      'patient' => $patient, 'plan' => null,
    ]);
  }

  public function therapistPlanCreatePost(int $id): void {
    $this->verifyCsrf();
    $user = $this->user();
    $isAssigned = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$user['id'], $id]);
    if (!$isAssigned) { http_response_code(403); exit; }
    $title = trim($_POST['title'] ?? '');
    if (!$title) { $this->redirect('/therapist/patient/' . $id . '/plans/create?error=Title is required'); }
    TreatmentPlan::create($id, $user['id'], [
      'title' => $title,
      'goals' => trim($_POST['goals'] ?? ''),
      'objectives' => trim($_POST['objectives'] ?? ''),
      'activities' => trim($_POST['activities'] ?? ''),
      'coping_strategies' => trim($_POST['coping_strategies'] ?? ''),
      'recommendations' => trim($_POST['recommendations'] ?? ''),
    ]);
    $patient = User::find($id);
    Notification::create($id, 'treatment_plan', 'New treatment plan: ' . $title, 'Your therapist created a new treatment plan', '/panel/treatment-plan');
    $this->redirect('/therapist/patient/' . $id . '/plans?success=Plan created');
  }

  public function therapistPlanEdit(int $id, int $planId): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $plan = TreatmentPlan::find($planId);
    if (!$plan || $plan['therapist_id'] != $user['id']) { http_response_code(404); exit; }
    $patient = User::find($id);
    $this->renderPanel('panel/therapist-plan-form', 'Edit Treatment Plan', [
      'patient' => $patient, 'plan' => $plan,
    ]);
  }

  public function therapistPlanUpdate(int $id): void {
    $this->verifyCsrf();
    $user = $this->user();
    $planId = (int)($_POST['plan_id'] ?? 0);
    $plan = TreatmentPlan::find($planId);
    if (!$plan || $plan['therapist_id'] != $user['id']) { http_response_code(404); exit; }
    TreatmentPlan::update($planId, [
      'title' => trim($_POST['title'] ?? ''),
      'goals' => trim($_POST['goals'] ?? ''),
      'objectives' => trim($_POST['objectives'] ?? ''),
      'activities' => trim($_POST['activities'] ?? ''),
      'coping_strategies' => trim($_POST['coping_strategies'] ?? ''),
      'recommendations' => trim($_POST['recommendations'] ?? ''),
      'status' => $_POST['status'] ?? 'active',
    ]);
    $this->redirect('/therapist/patient/' . $id . '/plans?success=Plan updated');
  }

  public function therapistPlanDelete(int $id): void {
    $this->verifyCsrf();
    $user = $this->user();
    $planId = (int)($_POST['plan_id'] ?? 0);
    TreatmentPlan::delete($planId, $user['id']);
    $this->redirect('/therapist/patient/' . $id . '/plans?success=Plan deleted');
  }

  // ─── NOTIFICATIONS ───

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

  // ─── PATIENT TIMELINE ───

  public function patientTimeline(int $id): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $isAssigned = Database::exists("SELECT id FROM therapist_patients WHERE therapist_id = ? AND patient_id = ?", [$user['id'], $id]);
    if (!$isAssigned) { http_response_code(403); exit; }
    $patient = User::find($id);
    $events = [];
    $checkins = Database::fetchAll("SELECT 'checkin' as type, id, check_date as event_date, CONCAT('Mood: ', mood, IF(craving_level IS NOT NULL, CONCAT(' | Craving: ', craving_level, '/100'), '')) as summary, note as detail FROM check_ins WHERE user_id = ? ORDER BY check_date DESC LIMIT 20", [$id]);
    foreach ($checkins as $e) { $events[] = $e; }
    $journals = Database::fetchAll("SELECT 'journal' as type, id, created_at as event_date, LEFT(content, 100) as summary, content as detail FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 20", [$id]);
    foreach ($journals as $e) { $events[] = $e; }
    $appts = Database::fetchAll("SELECT 'appointment' as type, id, date_time as event_date, title as summary, CONCAT('Status: ', status) as detail FROM appointments WHERE user_id = ? ORDER BY date_time DESC LIMIT 20", [$id]);
    foreach ($appts as $e) { $events[] = $e; }
    $resources = Database::fetchAll("SELECT 'resource' as type, id, created_at as event_date, CONCAT('Shared: ', title) as summary, description as detail FROM therapist_resources WHERE patient_id = ? OR (patient_id IS NULL AND therapist_id = ?) ORDER BY created_at DESC LIMIT 20", [$id, $user['id']]);
    foreach ($resources as $e) { $events[] = $e; }
    $sos = Database::fetchAll("SELECT 'sos' as type, id, created_at as event_date, CONCAT('SOS Alert: ', status) as summary, notes as detail FROM sos_alerts WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20", [$id]);
    foreach ($sos as $e) { $events[] = $e; }
    $relapses = Database::fetchAll("SELECT 'relapse' as type, id, relapse_date as event_date, CONCAT('Relapse (', severity, ')') as summary, CONCAT(IFNULL(CONCAT('Trigger: ', `trigger`, ' | '), ''), IFNULL(CONCAT('Action: ', action_taken), '')) as detail FROM relapses WHERE patient_id = ? ORDER BY relapse_date DESC LIMIT 20", [$id]);
    foreach ($relapses as $e) { $events[] = $e; }
    $plans = Database::fetchAll("SELECT 'treatment_plan' as type, id, created_at as event_date, CONCAT('Plan: ', title) as summary, CONCAT('Status: ', status) as detail FROM treatment_plans WHERE patient_id = ? ORDER BY created_at DESC LIMIT 20", [$id]);
    foreach ($plans as $e) { $events[] = $e; }
    $notes = Database::fetchAll("SELECT 'clinical_note' as type, id, created_at as event_date, 'Clinical note' as summary, LEFT(content, 100) as detail FROM clinical_notes WHERE patient_id = ? AND therapist_id = ? ORDER BY created_at DESC LIMIT 20", [$id, $user['id']]);
    foreach ($notes as $e) { $events[] = $e; }
    usort($events, fn($a, $b) => strtotime($b['event_date']) - strtotime($a['event_date']));
    $this->renderPanel('panel/patient-timeline', 'Patient Timeline', [
      'patient' => $patient, 'events' => $events,
    ]);
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
      if ($therapistId) {
        Notification::create($therapistId, 'appointment', 'New appointment from ' . $user['name'], $title . ' on ' . date('M j, g:i A', strtotime($dateTime)), '/panel/appointments');
      }
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
    $recoveryStages = RecoveryProgress::forPatientView($user['id']);
    $this->renderPanel('panel/progress', 'Progress', [
      'streak' => $streak, 'totalCheckins' => $totalCheckins, 'avgMood' => $avgMood,
      'milestones' => $milestones, 'moodTrend' => $moodTrend, 'weeklyProgress' => $weeklyProgress,
      'recoveryStages' => $recoveryStages,
    ]);
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
    $milestones = Milestone::forUser($id);
    $clinicalNotes = ClinicalNote::forPatient($id, $user['id']);
    $relapses = Relapse::forTherapistPatient($id, $user['id']);
    $triggers = Relapse::triggerAnalysis($id, $user['id']);
    $this->renderPanel('panel/patient-detail', 'Patient Detail', [
      'patient' => $patient,
      'checkins' => $checkins,
      'journal' => $journal,
      'milestones' => $milestones,
      'clinicalNotes' => $clinicalNotes,
      'relapses' => $relapses,
      'triggers' => $triggers,
    ]);
  }

  // ─── HELPER ───

  private function getPatientTherapistId(int $patientId): ?int {
    $tp = Database::fetch("SELECT therapist_id FROM therapist_patients WHERE patient_id = ? LIMIT 1", [$patientId]);
    return $tp ? (int)$tp['therapist_id'] : null;
  }
}
