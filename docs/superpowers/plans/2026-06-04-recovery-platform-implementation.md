# Recovery Platform Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use subagent-driven-development or executing-plans. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Implement 6 interconnected features: therapist resources, patient-therapist messaging, availability-gated admin scheduling, recovery progress tracking, SOS alerts.

**Architecture:** Pure PHP MVC — schema migration (6 new tables) → 6 new models → 20+ routes → controller methods → views. All within existing panel/admin layouts with role-based sidebar gating.

**Tech Stack:** PHP 8.4, MariaDB, PDO, polling-based "realtime" chat, file uploads to `uploads/resources/`

---

### Task 1: Schema Migration + Uploads Directory

**Files:**
- Modify: `app/database/schema.sql` — add 6 new tables
- Create: `app/database/migration.sql` — run to add tables without dropping existing
- Create: `uploads/resources/.gitkeep`

- [ ] **Create migration SQL** — `app/database/migration.sql`:

```sql
USE `rebirth`;

CREATE TABLE IF NOT EXISTS `therapist_availability` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `therapist_id` INT NOT NULL,
  `day_of_week` TINYINT NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `therapist_resources` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `therapist_id` INT NOT NULL,
  `patient_id` INT DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `type` ENUM('video','pdf','article','image') NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `conversations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_pair` (`patient_id`, `therapist_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `conversation_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `sender_id` INT NOT NULL,
  `content` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`conversation_id`) REFERENCES `conversations`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `sos_alerts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `status` ENUM('active','acknowledged','resolved') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `recovery_progress` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL,
  `therapist_id` INT NOT NULL,
  `stage_name` VARCHAR(100) NOT NULL,
  `stage_order` TINYINT NOT NULL DEFAULT 0,
  `status` ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
  `therapist_notes` TEXT DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`therapist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
```

- [ ] **Run migration** and create uploads dir:
```bash
ddev mysql < app/database/migration.sql
mkdir -p uploads/resources && touch uploads/resources/.gitkeep
```

- [ ] **Update `app/database/schema.sql`** — append the 6 CREATE TABLE statements from above (after existing tables, before seed data section).

---

### Task 2: New Models (6 files)

**Files:**
- Create: `app/models/TherapistResource.php`
- Create: `app/models/Conversation.php`
- Create: `app/models/ConversationMessage.php`
- Create: `app/models/SOSAlert.php`
- Create: `app/models/RecoveryProgress.php`
- Create: `app/models/TherapistAvailability.php`

- [ ] **Create `app/models/TherapistResource.php`**:

```php
<?php
class TherapistResource {
  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT tr.*, u.name as patient_name FROM therapist_resources tr
       LEFT JOIN users u ON u.id = tr.patient_id
       WHERE tr.therapist_id = ?
       ORDER BY tr.created_at DESC", [$therapistId]
    );
  }

  public static function forPatient(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM therapist_resources
       WHERE therapist_id = ? AND (patient_id = ? OR patient_id IS NULL)
       ORDER BY created_at DESC", [$therapistId, $patientId]
    );
  }

  public static function create(array $data): int {
    return Database::insert('therapist_resources', $data);
  }

  public static function delete(int $id, int $therapistId): void {
    $res = Database::fetch("SELECT * FROM therapist_resources WHERE id = ? AND therapist_id = ?", [$id, $therapistId]);
    if ($res) {
      $file = BASE_PATH . '/' . $res['file_path'];
      if (file_exists($file)) unlink($file);
      Database::delete('therapist_resources', $id);
    }
  }
}
```

- [ ] **Create `app/models/Conversation.php`**:

```php
<?php
class Conversation {
  public static function ensure(int $patientId, int $therapistId): int {
    $existing = Database::fetch(
      "SELECT id FROM conversations WHERE patient_id = ? AND therapist_id = ?",
      [$patientId, $therapistId]
    );
    if ($existing) return (int) $existing['id'];
    return Database::insert('conversations', [
      'patient_id' => $patientId, 'therapist_id' => $therapistId
    ]);
  }

  public static function forPatient(int $patientId): ?array {
    return Database::fetch(
      "SELECT c.*, u.name as therapist_name, u.initials as therapist_initials, u.avatar as therapist_avatar
       FROM conversations c JOIN users u ON u.id = c.therapist_id
       WHERE c.patient_id = ?", [$patientId]
    );
  }

  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT c.*, u.name as patient_name, u.initials as patient_initials, u.avatar as patient_avatar,
       (SELECT COUNT(*) FROM conversation_messages WHERE conversation_id = c.id AND sender_id != ? AND read_at IS NULL) as unread
       FROM conversations c JOIN users u ON u.id = c.patient_id
       WHERE c.therapist_id = ?
       ORDER BY c.updated_at DESC", [$therapistId, $therapistId]
    );
  }

  public static function find(int $id): ?array {
    return Database::fetch(
      "SELECT c.*, u.name as other_name, u.initials as other_initials, u.avatar as other_avatar, u.role as other_role
       FROM conversations c JOIN users u ON u.id = IF(c.patient_id = ?, c.therapist_id, c.patient_id)
       WHERE c.id = ?",
      [$_SESSION['user_id'], $id]
    );
  }

  public static function unreadCount(int $userId): int {
    return (int) Database::fetch(
      "SELECT COUNT(*) as count FROM conversation_messages cm
       JOIN conversations c ON c.id = cm.conversation_id
       WHERE cm.sender_id != ? AND cm.read_at IS NULL AND (
         c.patient_id = ? OR c.therapist_id = ?
       )",
      [$userId, $userId, $userId]
    )['count'] ?? 0;
  }

  public static function markRead(int $conversationId, int $userId): void {
    Database::query("UPDATE conversation_messages SET read_at = NOW() WHERE conversation_id = ? AND sender_id != ? AND read_at IS NULL", [$conversationId, $userId]);
  }
}
```

- [ ] **Create `app/models/ConversationMessage.php`**:

```php
<?php
class ConversationMessage {
  public static function forConversation(int $conversationId, int $afterId = 0): array {
    $sql = "SELECT cm.*, u.name as sender_name FROM conversation_messages cm
            JOIN users u ON u.id = cm.sender_id
            WHERE cm.conversation_id = ?";
    $params = [$conversationId];
    if ($afterId > 0) { $sql .= " AND cm.id > ?"; $params[] = $afterId; }
    $sql .= " ORDER BY cm.created_at ASC LIMIT 100";
    return Database::fetchAll($sql, $params);
  }

  public static function send(int $conversationId, int $senderId, string $content): int {
    return Database::insert('conversation_messages', [
      'conversation_id' => $conversationId,
      'sender_id' => $senderId,
      'content' => $content,
    ]);
  }
}
```

- [ ] **Create `app/models/SOSAlert.php`**:

```php
<?php
class SOSAlert {
  public static function activeForTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT sa.*, u.name as patient_name, u.initials as patient_initials
       FROM sos_alerts sa JOIN users u ON u.id = sa.patient_id
       WHERE sa.therapist_id = ? AND sa.status IN ('active','acknowledged')
       ORDER BY sa.created_at DESC", [$therapistId]
    );
  }

  public static function activeCount(int $therapistId): int {
    $row = Database::fetch(
      "SELECT COUNT(*) as count FROM sos_alerts WHERE therapist_id = ? AND status IN ('active','acknowledged')",
      [$therapistId]
    );
    return (int) ($row['count'] ?? 0);
  }

  public static function forPatient(int $patientId, int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM sos_alerts WHERE patient_id = ? AND therapist_id = ? ORDER BY created_at DESC LIMIT 10",
      [$patientId, $therapistId]
    );
  }

  public static function create(int $patientId, int $therapistId): int {
    return Database::insert('sos_alerts', [
      'patient_id' => $patientId, 'therapist_id' => $therapistId
    ]);
  }

  public static function acknowledge(int $id, int $therapistId): void {
    Database::query("UPDATE sos_alerts SET status = 'acknowledged' WHERE id = ? AND therapist_id = ? AND status = 'active'", [$id, $therapistId]);
  }

  public static function resolve(int $id, int $therapistId, string $notes = ''): void {
    Database::query("UPDATE sos_alerts SET status = 'resolved', resolved_at = NOW(), notes = CONCAT(COALESCE(notes,''), '\n', ?) WHERE id = ? AND therapist_id = ?", [$notes, $id, $therapistId]);
  }
}
```

- [ ] **Create `app/models/RecoveryProgress.php`**:

```php
<?php
class RecoveryProgress {
  private static array $defaultStages = [
    ['stage_name' => 'Assessment', 'stage_order' => 1],
    ['stage_name' => 'Detox', 'stage_order' => 2],
    ['stage_name' => 'Therapy', 'stage_order' => 3],
    ['stage_name' => 'Relapse Prevention', 'stage_order' => 4],
    ['stage_name' => 'Aftercare', 'stage_order' => 5],
  ];

  public static function ensure(int $patientId, int $therapistId): void {
    $existing = Database::fetch(
      "SELECT COUNT(*) as count FROM recovery_progress WHERE patient_id = ? AND therapist_id = ?",
      [$patientId, $therapistId]
    );
    if ((int)($existing['count'] ?? 0) > 0) return;
    foreach (self::$defaultStages as $stage) {
      Database::insert('recovery_progress', array_merge($stage, [
        'patient_id' => $patientId, 'therapist_id' => $therapistId
      ]));
    }
  }

  public static function forPatientPair(int $patientId, int $therapistId): array {
    self::ensure($patientId, $therapistId);
    return Database::fetchAll(
      "SELECT * FROM recovery_progress WHERE patient_id = ? AND therapist_id = ? ORDER BY stage_order ASC",
      [$patientId, $therapistId]
    );
  }

  public static function forPatientView(int $patientId): array {
    $tp = Database::fetch(
      "SELECT therapist_id FROM therapist_patients WHERE patient_id = ? LIMIT 1", [$patientId]
    );
    if (!$tp) return [];
    return self::forPatientPair($patientId, (int)$tp['therapist_id']);
  }

  public static function updateStage(int $id, string $status, string $notes = ''): void {
    $data = ['status' => $status];
    if ($notes) $data['therapist_notes'] = $notes;
    if ($status === 'in_progress') $data['started_at'] = date('Y-m-d H:i:s');
    if ($status === 'completed') $data['completed_at'] = date('Y-m-d H:i:s');
    Database::update('recovery_progress', $id, $data);
  }
}
```

- [ ] **Create `app/models/TherapistAvailability.php`**:

```php
<?php
class TherapistAvailability {
  public static function forTherapist(int $therapistId): array {
    return Database::fetchAll(
      "SELECT * FROM therapist_availability WHERE therapist_id = ? ORDER BY day_of_week, start_time",
      [$therapistId]
    );
  }

  public static function save(int $therapistId, array $slots): void {
    Database::query("DELETE FROM therapist_availability WHERE therapist_id = ?", [$therapistId]);
    foreach ($slots as $slot) {
      if (!empty($slot['day']) && !empty($slot['start']) && !empty($slot['end'])) {
        Database::insert('therapist_availability', [
          'therapist_id' => $therapistId,
          'day_of_week' => (int)$slot['day'],
          'start_time' => $slot['start'],
          'end_time' => $slot['end'],
        ]);
      }
    }
  }

  public static function isAvailableNow(int $therapistId): bool {
    $day = (int)date('w');
    $time = date('H:i:s');
    $slot = Database::fetch(
      "SELECT id FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ? AND start_time <= ? AND end_time >= ? LIMIT 1",
      [$therapistId, $day, $time, $time]
    );
    return $slot !== null;
  }

  public static function availableSlotsForDate(int $therapistId, string $date): array {
    $dayOfWeek = (int)date('w', strtotime($date));
    $avail = Database::fetchAll(
      "SELECT * FROM therapist_availability WHERE therapist_id = ? AND day_of_week = ? ORDER BY start_time",
      [$therapistId, $dayOfWeek]
    );
    $booked = Database::fetchAll(
      "SELECT date_time FROM appointments WHERE therapist_id = ? AND DATE(date_time) = ? AND status NOT IN ('cancelled')",
      [$therapistId, $date]
    );
    $slots = [];
    foreach ($avail as $a) {
      $start = strtotime($a['start_time']);
      $end = strtotime($a['end_time']);
      while ($start < $end) {
        $timeStr = date('H:i', $start);
        $bookedTime = false;
        foreach ($booked as $b) {
          if (date('H:i', strtotime($b['date_time'])) === $timeStr) { $bookedTime = true; break; }
        }
        $slots[] = ['time' => $timeStr, 'available' => !$bookedTime];
        $start = strtotime('+30 minutes', $start);
      }
    }
    return $slots;
  }
}
```

---

### Task 3: Routes (App.php)

**Files:**
- Modify: `app/core/App.php` — add 20+ new routes

- [ ] **Add routes after existing therapist routes and before admin routes**:

```php
    // Messaging
    $this->router->get('/panel/messages', 'PanelController@messages');
    $this->router->post('/messages/send', 'PanelController@sendMessage');
    $this->router->get('/messages/poll', 'PanelController@pollMessages');

    // SOS
    $this->router->get('/panel/sos', 'PanelController@sos');
    $this->router->post('/panel/sos/send', 'PanelController@sendSos');

    // Therapist features
    $this->router->get('/therapist/resources', 'PanelController@therapistResources');
    $this->router->post('/therapist/resources/create', 'PanelController@createTherapistResource');
    $this->router->post('/therapist/resources/delete', 'PanelController@deleteTherapistResource');
    $this->router->get('/therapist/messages', 'PanelController@therapistMessages');
    $this->router->get('/therapist/messages/{id}', 'PanelController@therapistConversation');
    $this->router->get('/therapist/availability', 'PanelController@therapistAvailability');
    $this->router->post('/therapist/availability/save', 'PanelController@saveTherapistAvailability');
    $this->router->get('/therapist/sos', 'PanelController@therapistSos');
    $this->router->post('/therapist/sos/acknowledge', 'PanelController@acknowledgeSos');
    $this->router->post('/therapist/sos/resolve', 'PanelController@resolveSos');
    $this->router->get('/therapist/patient/{id}/progress', 'PanelController@patientProgress');
    $this->router->post('/therapist/patient/{id}/progress/update', 'PanelController@updatePatientProgress');

    // Admin availability endpoint
    $this->router->get('/admin/appointments/availability', 'AdminController@getAvailability');
```

---

### Task 4: PanelController Methods (11 new methods)

**Files:**
- Modify: `app/controllers/PanelController.php` — add new methods

- [ ] **Add these imports at the top** (inside the class, before existing methods, after class opening brace):

Add these methods between `deleteJournal()` and `appointments()`:

```php
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

  // ─── HELPERS ───

  private function getPatientTherapistId(int $patientId): ?int {
    $tp = Database::fetch("SELECT therapist_id FROM therapist_patients WHERE patient_id = ? LIMIT 1", [$patientId]);
    return $tp ? (int)$tp['therapist_id'] : null;
  }
```

- [ ] **Update `resources()` method** to show therapist-shared resources for members:

Replace the existing `resources()` method:
```php
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
```

- [ ] **Update `progress()` method** to show recovery stages:

Replace the existing `progress()` method:
```php
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
```

---

### Task 5: AdminController Enhancements

**Files:**
- Modify: `app/controllers/AdminController.php` — add availability endpoint

- [ ] **Add method `getAvailability()`** after `deleteMessage()`:

```php
  public function getAvailability(): void {
    $therapistId = (int)($_GET['therapist_id'] ?? 0);
    $date = $_GET['date'] ?? '';
    if (!$therapistId || !$date) {
      header('Content-Type: application/json');
      echo json_encode([]);
      exit;
    }
    $slots = TherapistAvailability::availableSlotsForDate($therapistId, $date);
    header('Content-Type: application/json');
    echo json_encode($slots);
    exit;
  }
```

- [ ] **Update `appointments()`** — fetch therapist availability data for the view:

Replace the existing `appointments()` method:
```php
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
```

---

### Task 6: Patient Views (5 new/enhanced views)

**Files:**
- Create: `app/views/panel/messages.php`
- Create: `app/views/panel/sos.php`
- Modify: `app/views/panel/resources.php` — add therapist resources section
- Modify: `app/views/panel/progress.php` — add recovery stages section

- [ ] **Create `app/views/panel/messages.php`**:

```php
<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (!$therapistId): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-user-md" style="font-size:48px;color:var(--color-text-muted);margin-bottom:16px;"></i>
  <p>You don't have a therapist assigned yet. An admin will assign one soon.</p>
</div>
<?php elseif (!$conversation): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p>Conversation ready. Start sending messages below.</p>
</div>
<?php else: ?>
<div class="card" style="display:flex;flex-direction:column;height:calc(100dvh - 220px);">
  <div class="chat-header" style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--color-border);margin-bottom:12px;">
    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--color-primary-dark);flex-shrink:0;">
      <?= htmlspecialchars($conversation['therapist_initials'] ?? 'TH') ?>
    </div>
    <div>
      <strong><?= htmlspecialchars($conversation['therapist_name'] ?? 'Your Therapist') ?></strong>
      <div style="font-size:12px;color:var(--color-text-muted);" id="availabilityStatus">Checking availability...</div>
    </div>
  </div>
  <div id="messageContainer" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;padding:8px 0;min-height:200px;" data-conversation-id="<?= $conversation['id'] ?>" data-last-id="<?= !empty($messages) ? end($messages)['id'] : 0 ?>">
    <?php foreach ($messages as $msg): ?>
    <div class="chat-msg <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'chat-msg-sent' : 'chat-msg-received' ?>">
      <div class="chat-msg-content"><?= htmlspecialchars($msg['content']) ?></div>
      <div class="chat-msg-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <form action="/messages/send" method="POST" style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--color-border);">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="conversation_id" value="<?= $conversation['id'] ?>">
    <input type="text" name="content" class="form-input" placeholder="Type a message..." required style="flex:1;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>
<?php endif; ?>
```

- [ ] **Create `app/views/panel/sos.php`**:

```php
<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (!$hasTherapist): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-user-md" style="font-size:48px;color:var(--color-text-muted);margin-bottom:16px;"></i>
  <p>You don't have a therapist assigned yet.</p>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-shield-halved" style="font-size:48px;color:var(--color-danger);margin-bottom:16px;"></i>
  <h2 style="margin-bottom:8px;">Need Urgent Help?</h2>
  <p style="color:var(--color-text-muted);margin-bottom:24px;">Your therapist <strong><?= htmlspecialchars($therapistName) ?></strong> will be notified immediately.</p>
  <?php
  $hasActive = false;
  foreach ($alerts as $a) { if ($a['status'] === 'active' || $a['status'] === 'acknowledged') { $hasActive = true; break; } }
  ?>
  <?php if ($hasActive): ?>
  <div style="background:rgba(209,69,59,0.06);border:1px solid rgba(209,69,59,0.2);border-radius:12px;padding:16px;margin-bottom:16px;">
    <p style="font-weight:600;color:var(--color-danger);">You have an active alert. Your therapist has been notified.</p>
  </div>
  <?php else: ?>
  <form action="/panel/sos/send" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <button type="submit" class="btn" style="background:var(--color-danger);color:white;padding:16px 48px;font-size:18px;font-weight:600;border-radius:12px;border:none;cursor:pointer;" onclick="return confirm('Send SOS alert to your therapist?')">
      <i class="fa-solid fa-triangle-exclamation"></i> I NEED HELP
    </button>
  </form>
  <?php endif; ?>
</div>

<?php if (!empty($alerts)): ?>
<div class="card">
  <h3>Your SOS History</h3>
  <div style="margin-top:12px;">
  <?php foreach ($alerts as $alert): ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-bottom:1px solid var(--color-border);">
    <div>
      <span class="badge badge-<?= $alert['status'] === 'resolved' ? 'green' : ($alert['status'] === 'acknowledged' ? 'orange' : 'red') ?>">
        <?= ucfirst($alert['status']) ?>
      </span>
      <span style="font-size:13px;color:var(--color-text-muted);margin-left:8px;"><?= date('M j, g:i A', strtotime($alert['created_at'])) ?></span>
    </div>
    <?php if ($alert['status'] === 'resolved' && $alert['notes']): ?>
    <span style="font-size:12px;color:var(--color-text-muted);max-width:200px;text-align:right;"><?= htmlspecialchars($alert['notes']) ?></span>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>
```

- [ ] **Update `app/views/panel/resources.php`** — add therapist resources section after existing resources:

Add before closing `</div>` (after the resources grid):
```php
<?php if (isset($therapistResources) && !empty($therapistResources)): ?>
<div class="card" style="margin-top:24px;">
  <h2>From Your Therapist</h2>
</div>
<div class="resources-grid">
  <?php foreach ($therapistResources as $res): ?>
  <div class="resource-card">
    <div class="resource-card-header">
      <?php
      $icon = match($res['type']) {
        'video' => 'fa-solid fa-video',
        'pdf' => 'fa-solid fa-file-pdf',
        'article' => 'fa-solid fa-file-lines',
        'image' => 'fa-solid fa-image',
        default => 'fa-solid fa-file',
      };
      ?>
      <i class="<?= $icon ?>"></i>
      <span class="resource-card-badge"><?= htmlspecialchars(ucfirst($res['type'])) ?></span>
    </div>
    <h4><?= htmlspecialchars($res['title']) ?></h4>
    <?php if ($res['description']): ?>
    <p><?= htmlspecialchars($res['description']) ?></p>
    <?php endif; ?>
    <div class="resource-card-footer">
      <a href="/<?= $res['file_path'] ?>" class="btn btn-primary" style="padding:6px 16px;font-size:13px;" target="_blank">Open</a>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
```

- [ ] **Update `app/views/panel/progress.php`** — add recovery stages section:

Add before the final `</div>` (before the closing Weekly Progress card):
```php
<?php if (!empty($recoveryStages)): ?>
<div class="card">
  <h2>Recovery Program</h2>
  <p style="color:var(--color-text-muted);font-size:14px;margin-bottom:16px;">Tracked by your therapist</p>
  <div class="milestones">
    <?php foreach ($recoveryStages as $stage): ?>
    <div class="milestone <?= $stage['status'] === 'completed' ? 'done' : '' ?>">
      <div class="milestone-icon">
        <?php if ($stage['status'] === 'completed'): ?>
        <i class="fa-solid fa-check-circle" style="color:var(--color-success);"></i>
        <?php elseif ($stage['status'] === 'in_progress'): ?>
        <i class="fa-solid fa-spinner" style="color:var(--color-accent);"></i>
        <?php else: ?>
        <i class="fa-solid fa-circle" style="color:var(--color-border);"></i>
        <?php endif; ?>
      </div>
      <div class="milestone-info">
        <strong><?= htmlspecialchars($stage['stage_name']) ?></strong>
        <span><?= ucfirst(str_replace('_', ' ', $stage['status'])) ?></span>
      </div>
      <?php if ($stage['completed_at']): ?>
      <span style="font-size:12px;color:var(--color-text-muted);"><?= date('M j', strtotime($stage['completed_at'])) ?></span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
```

---

### Task 7: Therapist Views (7 new views)

**Files:**
- Create: `app/views/panel/therapist-resources.php`
- Create: `app/views/panel/therapist-messages.php`
- Create: `app/views/panel/therapist-conversation.php`
- Create: `app/views/panel/therapist-availability.php`
- Create: `app/views/panel/therapist-sos.php`
- Create: `app/views/panel/patient-progress.php`

- [ ] **Create `app/views/panel/therapist-resources.php`**:

```php
<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <h2>My Resources</h2>
    <button class="btn btn-primary" onclick="document.getElementById('uploadModal').style.display='flex'"><i class="fa-solid fa-upload"></i> Upload</button>
  </div>
</div>

<?php if (empty($resources)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p style="color:var(--color-text-muted);">No resources uploaded yet.</p>
</div>
<?php else: ?>
<div class="resources-grid">
  <?php foreach ($resources as $res): ?>
  <div class="resource-card">
    <div class="resource-card-header">
      <?php $icon = match($res['type']) { 'video'=>'fa-solid fa-video', 'pdf'=>'fa-solid fa-file-pdf', 'article'=>'fa-solid fa-file-lines', 'image'=>'fa-solid fa-image', default=>'fa-solid fa-file' }; ?>
      <i class="<?= $icon ?>"></i>
      <span class="resource-card-badge"><?= htmlspecialchars(ucfirst($res['type'])) ?></span>
    </div>
    <h4><?= htmlspecialchars($res['title']) ?></h4>
    <?php if ($res['description']): ?><p><?= htmlspecialchars($res['description']) ?></p><?php endif; ?>
    <div class="resource-card-footer">
      <span style="font-size:12px;color:var(--color-text-muted);">For: <?= htmlspecialchars($res['patient_name'] ?? 'All patients') ?></span>
      <form action="/therapist/resources/delete" method="POST" style="display:inline;" onsubmit="return confirm('Delete this resource?')">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $res['id'] ?>">
        <button type="submit" class="btn btn-outline" style="padding:4px 10px;font-size:12px;color:var(--color-danger);"><i class="fa-solid fa-trash"></i></button>
      </form>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Upload Modal -->
<div class="modal-overlay" id="uploadModal" style="display:none;">
  <div class="modal">
    <form action="/therapist/resources/create" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= $_token ?>">
      <div class="modal-header">
        <h3>Upload Resource</h3>
        <button type="button" class="modal-close" onclick="document.getElementById('uploadModal').style.display='none'"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Title</label>
          <input class="form-input" name="title" required>
        </div>
        <div class="form-group">
          <label class="form-label">Type</label>
          <select class="form-select" name="type" required>
            <option value="video">Video</option>
            <option value="pdf">PDF</option>
            <option value="article">Article</option>
            <option value="image">Image</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Assign To</label>
          <select class="form-select" name="patient_id">
            <option value="">All my patients</option>
            <?php foreach ($patients as $p): ?>
            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Description (optional)</label>
          <textarea class="form-input" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">File</label>
          <input class="form-input" type="file" name="file" required accept=".pdf,.mp4,.webm,.jpg,.jpeg,.png,.gif,.webp,.txt,.md">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Upload</button>
      </div>
    </form>
  </div>
</div>
```

- [ ] **Create `app/views/panel/therapist-messages.php`**:

```php
<div class="card">
  <h2>Patient Conversations</h2>
</div>

<?php if (empty($conversations)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <p style="color:var(--color-text-muted);">No conversations yet.</p>
</div>
<?php else: ?>
<?php foreach ($conversations as $conv): ?>
<a href="/therapist/messages/<?= $conv['id'] ?>" class="card" style="display:block;padding:16px;margin-bottom:8px;">
  <div style="display:flex;align-items:center;gap:12px;">
    <?php if (!empty($conv['patient_avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($conv['patient_avatar']) ?>" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <?php else: ?>
    <div style="width:40px;height:40px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($conv['patient_initials']) ?></div>
    <?php endif; ?>
    <div style="flex:1;">
      <strong><?= htmlspecialchars($conv['patient_name']) ?></strong>
      <?php if ($conv['unread'] > 0): ?>
      <span class="badge badge-red" style="margin-left:8px;"><?= $conv['unread'] ?> new</span>
      <?php endif; ?>
      <div style="font-size:12px;color:var(--color-text-muted);">Last message: <?= date('M j, g:i A', strtotime($conv['updated_at'])) ?></div>
    </div>
    <i class="fa-solid fa-chevron-right" style="color:var(--color-text-muted);"></i>
  </div>
</a>
<?php endforeach; ?>
<?php endif; ?>
```

- [ ] **Create `app/views/panel/therapist-conversation.php`**:

```php
<a href="/therapist/messages" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> All Conversations</a>

<div class="card" style="display:flex;flex-direction:column;height:calc(100dvh - 220px);">
  <div style="display:flex;align-items:center;gap:12px;padding-bottom:12px;border-bottom:1px solid var(--color-border);margin-bottom:12px;">
    <?php if (!empty($patient['avatar'])): ?>
    <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">
    <?php else: ?>
    <div style="width:36px;height:36px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
    <?php endif; ?>
    <strong><?= htmlspecialchars($patient['name']) ?></strong>
  </div>
  <div id="messageContainer" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;padding:8px 0;min-height:200px;" data-conversation-id="<?= $conv['id'] ?>" data-last-id="<?= !empty($messages) ? end($messages)['id'] : 0 ?>">
    <?php foreach ($messages as $msg): ?>
    <div class="chat-msg <?= $msg['sender_id'] == ($_SESSION['user_id'] ?? 0) ? 'chat-msg-sent' : 'chat-msg-received' ?>">
      <div class="chat-msg-content"><?= htmlspecialchars($msg['content']) ?></div>
      <div class="chat-msg-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <form action="/messages/send" method="POST" style="display:flex;gap:8px;padding-top:12px;border-top:1px solid var(--color-border);">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="conversation_id" value="<?= $conv['id'] ?>">
    <input type="text" name="content" class="form-input" placeholder="Type a message..." required style="flex:1;">
    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
  </form>
</div>
```

- [ ] **Create `app/views/panel/therapist-availability.php`**:

```php
<?php if (isset($_GET['success'])): ?>
<div class="alert" style="background:rgba(76,175,125,0.08);color:var(--color-success);padding:12px 20px;border-radius:var(--radius-sm);margin-bottom:16px;"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<div class="card">
  <h2>Your Availability</h2>
  <p style="color:var(--color-text-muted);font-size:14px;">Set your weekly work hours. Patients can message you during these times.</p>
</div>

<div class="card">
  <form action="/therapist/availability/save" method="POST">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <div id="availabilitySlots">
      <?php
      $dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      $existingByDay = [];
      foreach ($slots as $s) { $existingByDay[$s['day_of_week']][] = $s; }
      $rowIndex = 0;
      foreach ($dayNames as $dow => $name):
      $daySlots = $existingByDay[$dow] ?? [];
      if (empty($daySlots)) $daySlots[] = ['day_of_week' => $dow, 'start_time' => '', 'end_time' => ''];
      foreach ($daySlots as $slot):
      ?>
      <div class="avail-row" style="display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px;background:var(--color-bg-alt);border-radius:var(--radius-sm);">
        <span style="min-width:90px;font-weight:500;"><?= $name ?></span>
        <input type="hidden" name="days[]" value="<?= $dow ?>">
        <input type="time" name="starts[]" class="form-input" style="flex:1;" value="<?= htmlspecialchars($slot['start_time'] ?? '') ?>">
        <span style="color:var(--color-text-muted);">to</span>
        <input type="time" name="ends[]" class="form-input" style="flex:1;" value="<?= htmlspecialchars($slot['end_time'] ?? '') ?>">
        <button type="button" class="btn btn-outline" style="padding:4px 8px;font-size:12px;color:var(--color-danger);" onclick="this.closest('.avail-row').remove()"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <?php $rowIndex++; endforeach; endforeach; ?>
    </div>
    <button type="button" class="btn btn-outline" onclick="addAvailRow()" style="margin-top:8px;"><i class="fa-solid fa-plus"></i> Add Time Slot</button>
    <button type="submit" class="btn btn-primary" style="margin-top:12px;width:100%;">Save Availability</button>
  </form>
</div>

<script>
const dayNames = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
function addAvailRow() {
  const container = document.getElementById('availabilitySlots');
  const picker = document.createElement('div');
  picker.innerHTML = `<select class="form-select" style="flex:1;" onchange="addRowForDay(this.value);this.parentElement.remove()"><option value="">Select day...</option>${dayNames.map((n,i)=>'<option value="'+i+'">'+n+'</option>').join('')}</select>`;
  picker.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px;background:var(--color-bg-alt);border-radius:var(--radius-sm);';
  container.appendChild(picker);
}
function addRowForDay(dow) {
  const container = document.getElementById('availabilitySlots');
  const div = document.createElement('div');
  div.className = 'avail-row';
  div.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:8px;padding:8px;background:var(--color-bg-alt);border-radius:var(--radius-sm);';
  div.innerHTML = `
    <span style="min-width:90px;font-weight:500;">${dayNames[dow]}</span>
    <input type="hidden" name="days[]" value="${dow}">
    <input type="time" name="starts[]" class="form-input" style="flex:1;">
    <span style="color:var(--color-text-muted);">to</span>
    <input type="time" name="ends[]" class="form-input" style="flex:1;">
    <button type="button" class="btn btn-outline" style="padding:4px 8px;font-size:12px;color:var(--color-danger);" onclick="this.closest('.avail-row').remove()"><i class="fa-solid fa-xmark"></i></button>
  `;
  container.appendChild(div);
}
</script>
```

- [ ] **Create `app/views/panel/therapist-sos.php`**:

```php
<div class="card">
  <h2>SOS Alerts</h2>
  <p style="color:var(--color-text-muted);font-size:14px;">Active and recent emergency alerts from your patients.</p>
</div>

<?php if (empty($alerts)): ?>
<div class="card" style="text-align:center;padding:40px;">
  <i class="fa-solid fa-shield" style="font-size:48px;color:var(--color-success);margin-bottom:16px;"></i>
  <p style="color:var(--color-text-muted);">No active SOS alerts. All patients are safe.</p>
</div>
<?php else: ?>
<?php foreach ($alerts as $alert): ?>
<div class="card" style="border-left:4px solid <?= $alert['status'] === 'active' ? 'var(--color-danger)' : 'var(--color-warning)' ?>;">
  <div style="display:flex;justify-content:space-between;align-items:start;">
    <div>
      <h3 style="margin-bottom:4px;"><?= htmlspecialchars($alert['patient_name']) ?></h3>
      <span class="badge badge-<?= $alert['status'] === 'active' ? 'red' : 'orange' ?>"><?= ucfirst($alert['status']) ?></span>
      <span style="font-size:13px;color:var(--color-text-muted);margin-left:8px;"><?= date('M j, g:i A', strtotime($alert['created_at'])) ?> (<?= time() - strtotime($alert['created_at']) > 60 ? floor((time() - strtotime($alert['created_at']))/60) . ' min ago' : 'Just now' ?>)</span>
    </div>
    <div style="display:flex;gap:8px;">
      <?php if ($alert['status'] === 'active'): ?>
      <form action="/therapist/sos/acknowledge" method="POST" style="display:inline;">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $alert['id'] ?>">
        <button type="submit" class="btn btn-outline" style="border-color:var(--color-warning);color:var(--color-warning);">Acknowledge</button>
      </form>
      <?php endif; ?>
      <form action="/therapist/sos/resolve" method="POST" style="display:inline;" onsubmit="return confirm('Mark this alert as resolved?')">
        <input type="hidden" name="_token" value="<?= $_token ?>">
        <input type="hidden" name="id" value="<?= $alert['id'] ?>">
        <button type="submit" class="btn btn-outline" style="border-color:var(--color-success);color:var(--color-success);">Resolve</button>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
```

- [ ] **Create `app/views/panel/patient-progress.php`**:

```php
<a href="/therapist/patient/<?= $patient['id'] ?>" class="btn btn-outline" style="margin-bottom:12px;"><i class="fa-solid fa-arrow-left"></i> Back to Patient</a>

<div class="card" style="display:flex;align-items:center;gap:12px;">
  <?php if (!empty($patient['avatar'])): ?>
  <img src="/uploads/avatars/<?= htmlspecialchars($patient['avatar']) ?>" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
  <?php else: ?>
  <div style="width:48px;height:48px;border-radius:50%;background:var(--color-accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;color:var(--color-primary-dark);flex-shrink:0;"><?= htmlspecialchars($patient['initials']) ?></div>
  <?php endif; ?>
  <div>
    <h2 style="margin:0;"><?= htmlspecialchars($patient['name']) ?></h2>
    <span style="font-size:14px;color:var(--color-text-muted);">Recovery Progress</span>
  </div>
</div>

<div class="card">
  <h2>Treatment Stages</h2>
</div>

<?php foreach ($stages as $stage): ?>
<div class="card">
  <div style="display:flex;justify-content:space-between;align-items:start;">
    <div style="flex:1;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <?php
        $statusIcon = match($stage['status']) {
          'completed' => '<i class="fa-solid fa-check-circle" style="color:var(--color-success);font-size:20px;"></i>',
          'in_progress' => '<i class="fa-solid fa-spinner" style="color:var(--color-accent);font-size:20px;"></i>',
          default => '<i class="fa-solid fa-circle" style="color:var(--color-border);font-size:20px;"></i>',
        };
        ?>
        <?= $statusIcon ?>
        <h3 style="margin:0;"><?= htmlspecialchars($stage['stage_name']) ?></h3>
        <span class="badge badge-<?= $stage['status'] === 'completed' ? 'green' : ($stage['status'] === 'in_progress' ? 'orange' : 'gray') ?>"><?= ucfirst(str_replace('_', ' ', $stage['status'])) ?></span>
      </div>
      <?php if ($stage['started_at']): ?>
      <div style="font-size:13px;color:var(--color-text-muted);margin-bottom:4px;">Started: <?= date('M j, Y', strtotime($stage['started_at'])) ?></div>
      <?php endif; ?>
      <?php if ($stage['completed_at']): ?>
      <div style="font-size:13px;color:var(--color-text-muted);margin-bottom:4px;">Completed: <?= date('M j, Y', strtotime($stage['completed_at'])) ?></div>
      <?php endif; ?>
      <?php if ($stage['therapist_notes']): ?>
      <div style="background:var(--color-bg-alt);padding:8px 12px;border-radius:var(--radius-sm);margin-top:8px;font-size:14px;">
        <strong>Notes:</strong> <?= nl2br(htmlspecialchars($stage['therapist_notes'])) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <form action="/therapist/patient/<?= $patient['id'] ?>/progress/update" method="POST" style="margin-top:12px;display:flex;gap:8px;align-items:end;">
    <input type="hidden" name="_token" value="<?= $_token ?>">
    <input type="hidden" name="stage_id" value="<?= $stage['id'] ?>">
    <div class="form-group" style="flex:1;margin:0;">
      <select class="form-select" name="status">
        <option value="not_started" <?= $stage['status'] === 'not_started' ? 'selected' : '' ?>>Not Started</option>
        <option value="in_progress" <?= $stage['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
        <option value="completed" <?= $stage['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
      </select>
    </div>
    <div class="form-group" style="flex:2;margin:0;">
      <input type="text" name="notes" class="form-input" placeholder="Add note..." value="">
    </div>
    <button type="submit" class="btn btn-primary" style="white-space:nowrap;"><i class="fa-solid fa-save"></i> Update</button>
  </form>
</div>
<?php endforeach; ?>
```

---

### Task 8: Admin Appointment View Enhancement

**Files:**
- Modify: `app/views/admin/appointments.php` — add availability calendar JS

- [ ] **Update the create modal** — add availability fetching:

Replace the create modal's date_time input in `app/views/admin/appointments.php`:
```php
        <div class="form-group">
          <label class="form-label">Date & Time</label>
          <input class="form-input" name="date_time" id="apptDateTime" type="datetime-local" required>
          <div id="availabilitySlots" style="margin-top:8px;display:none;">
            <label class="form-label">Available Slots</label>
            <div id="slotsList" style="display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;"></div>
          </div>
        </div>
```

- [ ] **Add JS at the end of the file** (before closing `</html>` — add inside the existing `<script>` block):

```javascript
  // Availability-aware scheduling
  const therapistSelect = document.querySelector('[name="therapist_id"]');
  const dateTimeInput = document.getElementById('apptDateTime');
  const slotsContainer = document.getElementById('availabilitySlots');
  const slotsList = document.getElementById('slotsList');

  if (therapistSelect && dateTimeInput) {
    async function fetchSlots() {
      const therapistId = therapistSelect.value;
      const date = dateTimeInput.value ? dateTimeInput.value.split('T')[0] : '';
      if (!therapistId || !date) { slotsContainer.style.display = 'none'; return; }
      try {
        const resp = await fetch(`/admin/appointments/availability?therapist_id=${therapistId}&date=${date}`);
        const slots = await resp.json();
        slotsContainer.style.display = 'block';
        slotsList.innerHTML = '';
        let hasAvailable = false;
        slots.forEach(s => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = 'btn ' + (s.available ? 'btn-outline' : 'btn-outline');
          btn.textContent = s.time;
          btn.style.cssText = 'padding:4px 12px;font-size:13px;' + (s.available ? '' : 'opacity:0.4;cursor:not-allowed;');
          btn.disabled = !s.available;
          if (s.available) {
            btn.onclick = () => {
              dateTimeInput.value = date + 'T' + s.time;
              document.querySelectorAll('#slotsList .btn').forEach(b => b.style.borderColor = '');
              btn.style.borderColor = 'var(--color-primary)';
            };
            hasAvailable = true;
          }
          slotsList.appendChild(btn);
        });
        if (!hasAvailable) {
          slotsList.innerHTML = '<span style="color:var(--color-danger);font-size:13px;">No available slots on this date</span>';
        }
      } catch(e) { slotsContainer.style.display = 'none'; }
    }
    therapistSelect.addEventListener('change', fetchSlots);
    dateTimeInput.addEventListener('change', fetchSlots);
  }
```

---

### Task 9: Layout Sidebar Updates

**Files:**
- Modify: `app/views/layouts/panel.php` — add Messages, SOS, Resources, Availability, SOS alerts to sidebars

- [ ] **Update therapist sidebar** — add Messages, Resources, Availability, SOS Alert badge:

Replace the therapist sidebar nav block (lines 22-32):
```php
        <?php if ($user['role'] === 'therapist'): ?>

        <div class="app-sidebar-section">Overview</div>
        <a href="/panel/dashboard" class="app-sidebar-link <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a>
        <a href="/therapist/patients" class="app-sidebar-link <?= $page === 'My Patients' ? 'active' : '' ?>"><i class="fa-solid fa-heart-pulse"></i>My Patients</a>
        <div class="app-sidebar-section">Communication</div>
        <a href="/therapist/messages" class="app-sidebar-link <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i>Messages</a>
        <a href="/therapist/sos" class="app-sidebar-link <?= $page === 'SOS Alerts' ? 'active' : '' ?>">
          <i class="fa-solid fa-triangle-exclamation" style="color:var(--color-danger);"></i>SOS Alerts
          <span id="sosBadge" style="display:none;background:var(--color-danger);color:white;font-size:11px;padding:1px 6px;border-radius:10px;margin-left:auto;"></span>
        </a>
        <a href="/therapist/resources" class="app-sidebar-link <?= $page === 'Resources' ? 'active' : '' ?>"><i class="fa-solid fa-folder-open"></i>Resources</a>
        <a href="/panel/appointments" class="app-sidebar-link <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i>Schedule</a>
        <a href="/panel/community" class="app-sidebar-link <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i>Community</a>
        <div class="app-sidebar-section">Settings</div>
        <a href="/therapist/availability" class="app-sidebar-link <?= $page === 'Availability' ? 'active' : '' ?>"><i class="fa-solid fa-clock"></i>Availability</a>
        <a href="/panel/settings" class="app-sidebar-link <?= $page === 'Settings' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="/logout" class="app-sidebar-link" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a>

```

- [ ] **Update member sidebar** — add Messages and SOS links:

Replace the member sidebar nav block (lines 34-46):
```php
        <?php else: ?>

        <div class="app-sidebar-section">Main</div>
        <a href="/panel/dashboard" class="app-sidebar-link <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i>Dashboard</a>
        <a href="/panel/checkin" class="app-sidebar-link <?= $page === 'Daily Check-in' ? 'active' : '' ?>"><i class="fa-regular fa-face-smile"></i>Daily Check-in</a>
        <a href="/panel/journal" class="app-sidebar-link <?= $page === 'Journal' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i>Journal</a>
        <a href="/panel/appointments" class="app-sidebar-link <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i>Appointments</a>
        <a href="/panel/community" class="app-sidebar-link <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i>Community</a>
        <a href="/panel/messages" class="app-sidebar-link <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i>Messages</a>
        <a href="/panel/sos" class="app-sidebar-link <?= $page === 'SOS' ? 'active' : '' ?>"><i class="fa-solid fa-triangle-exclamation" style="color:var(--color-danger);"></i>SOS</a>
        <a href="/panel/resources" class="app-sidebar-link <?= $page === 'Resources' ? 'active' : '' ?>"><i class="fa-regular fa-file-lines"></i>Resources</a>
        <div class="app-sidebar-section">Account</div>
        <a href="/panel/progress" class="app-sidebar-link <?= $page === 'Progress' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i>Progress</a>
        <a href="/panel/settings" class="app-sidebar-link <?= $page === 'Settings' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Settings</a>
        <a href="/logout" class="app-sidebar-link" style="color:var(--color-danger);"><i class="fa-solid fa-right-from-bracket"></i>Sign Out</a>

        <?php endif; ?>
```

- [ ] **Update therapist mobile tabs** — add Messages:

Replace the therapist mobile tabs block:
```php
    <?php if ($user['role'] === 'therapist'): ?>
    <nav class="app-tabs">
      <a href="/panel/dashboard" class="app-tab <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i><span>Home</span></a>
      <a href="/therapist/patients" class="app-tab <?= $page === 'My Patients' ? 'active' : '' ?>"><i class="fa-solid fa-heart-pulse"></i><span>Patients</span></a>
      <a href="/therapist/messages" class="app-tab <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i><span>Messages</span></a>
      <a href="/panel/community" class="app-tab <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i><span>Community</span></a>
      <a href="/panel/appointments" class="app-tab <?= $page === 'Appointments' ? 'active' : '' ?>"><i class="fa-regular fa-calendar"></i><span>Schedule</span></a>
    </nav>
    <?php else: ?>
    <nav class="app-tabs">
      <a href="/panel/dashboard" class="app-tab <?= $page === 'Dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i><span>Home</span></a>
      <a href="/panel/checkin" class="app-tab <?= $page === 'Daily Check-in' ? 'active' : '' ?>"><i class="fa-regular fa-face-smile"></i><span>Check-in</span></a>
      <a href="/panel/messages" class="app-tab <?= $page === 'Messages' ? 'active' : '' ?>"><i class="fa-regular fa-comment-dots"></i><span>Messages</span></a>
      <a href="/panel/community" class="app-tab <?= $page === 'Community' ? 'active' : '' ?>"><i class="fa-regular fa-comments"></i><span>Community</span></a>
      <a href="/panel/progress" class="app-tab <?= $page === 'Progress' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i><span>Progress</span></a>
    </nav>
    <?php endif; ?>
```

---

### Task 10: CSS + JS Additions

**Files:**
- Modify: `css/app.css` — add chat message styles
- Create: `js/app.js` — polling + SOS badge

- [ ] **Add chat message styles** to `css/app.css`:

```css
/* ─── CHAT MESSAGES ─── */
.chat-msg { display:flex; flex-direction:column; max-width:75%; margin-bottom:4px; }
.chat-msg-sent { align-self:flex-end; align-items:flex-end; }
.chat-msg-received { align-self:flex-start; align-items:flex-start; }
.chat-msg-content { padding:10px 16px; border-radius:16px; font-size:14px; line-height:1.5; word-break:break-word; }
.chat-msg-sent .chat-msg-content { background:var(--color-primary); color:white; border-bottom-right-radius:4px; }
.chat-msg-received .chat-msg-content { background:var(--color-bg-alt); color:var(--color-text); border-bottom-left-radius:4px; }
.chat-msg-time { font-size:11px; color:var(--color-text-muted); margin-top:2px; padding:0 4px; }

/* ─── SOS BADGE ─── */
#sosBadge { display:none !important; }
#sosBadge.show { display:inline !important; }
```

- [ ] **Create `js/app.js`** with messaging poll and SOS badge:

```javascript
// Chat polling
document.querySelectorAll('#messageContainer').forEach(container => {
  const convId = container.dataset.conversationId;
  if (!convId) return;
  setInterval(async () => {
    const lastId = container.dataset.lastId || '0';
    try {
      const resp = await fetch(`/messages/poll?conversation_id=${convId}&after=${lastId}`);
      const msgs = await resp.json();
      if (msgs.length > 0) {
        const userId = document.body.dataset.userId;
        msgs.forEach(msg => {
          const div = document.createElement('div');
          div.className = 'chat-msg ' + (parseInt(msg.sender_id) === parseInt(userId) ? 'chat-msg-sent' : 'chat-msg-received');
          div.innerHTML = `<div class="chat-msg-content">${escapeHtml(msg.content)}</div><div class="chat-msg-time">${new Date(msg.created_at).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'})}</div>`;
          container.appendChild(div);
          container.dataset.lastId = msg.id;
        });
        container.scrollTop = container.scrollHeight;
      }
    } catch(e) { /* silent */ }
  }, 3000);
  container.scrollTop = container.scrollHeight;
});

// Availability status (patient side)
const availabilityEl = document.getElementById('availabilityStatus');
if (availabilityEl && window.__therapistId) {
  fetch(`/therapist/availability/check?therapist_id=${window.__therapistId}`)
    .then(r => r.json())
    .then(data => {
      availabilityEl.textContent = data.available ? '🟢 Online now' : '⚫ Currently offline';
      availabilityEl.style.color = data.available ? 'var(--color-success)' : 'var(--color-text-muted)';
    })
    .catch(() => { availabilityEl.textContent = '⚫ Offline'; });
}

// SOS badge polling
const sosBadge = document.getElementById('sosBadge');
if (sosBadge) {
  async function updateSosBadge() {
    try {
      const resp = await fetch('/therapist/sos/count');
      const data = await resp.json();
      if (data.count > 0) {
        sosBadge.textContent = data.count;
        sosBadge.classList.add('show');
      } else {
        sosBadge.classList.remove('show');
      }
    } catch(e) { /* silent */ }
  }
  updateSosBadge();
  setInterval(updateSosBadge, 10000);
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str;
  return div.innerHTML;
}
```

- [ ] **Add SOS count route** to `App.php`:
```php
    $this->router->get('/therapist/sos/count', 'PanelController@sosCount');
```

- [ ] **Add `sosCount()` method** to `PanelController`:
```php
  public function sosCount(): void {
    $this->requireRole('therapist');
    $user = $this->user();
    $count = SOSAlert::activeCount($user['id']);
    header('Content-Type: application/json');
    echo json_encode(['count' => $count]);
    exit;
  }
```

- [ ] **Add `data-user-id` to `<body>`** in panel layout:
```php
<body data-user-id="<?= $_SESSION['user_id'] ?? 0 ?>">
```

- [ ] **Add `window.__therapistId`** in the messages view for the availability check:
Add before the closing `<?php endif; ?>` in `app/views/panel/messages.php`:
```php
<script>window.__therapistId = <?= json_encode($therapistId) ?>;</script>
```

- [ ] **Add availability check route** to `App.php`:
```php
    $this->router->get('/therapist/availability/check', 'PanelController@checkAvailability');
```

- [ ] **Add `checkAvailability()` method** to `PanelController`:
```php
  public function checkAvailability(): void {
    $therapistId = (int)($_GET['therapist_id'] ?? 0);
    $available = $therapistId ? TherapistAvailability::isAvailableNow($therapistId) : false;
    header('Content-Type: application/json');
    echo json_encode(['available' => $available]);
    exit;
  }
```
