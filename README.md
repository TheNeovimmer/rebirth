# Rebirth — Addiction Recovery Platform

A PHP MVC platform supporting addiction recovery through daily check-ins, private journaling, community support groups, 1-on-1 patient-therapist messaging, resource sharing, and SOS emergency alerts.

Built with pure PHP 8.4 + MariaDB, running on DDEV with nginx-fpm. Zero Composer or third-party PHP dependencies.

## Tech Stack

- **Backend:** PHP 8.4 (pure, no framework), PDO + MariaDB
- **Frontend:** Vanilla PHP templates, FontAwesome 6.5.1 (free), Inter font
- **Environment:** DDEV (nginx-fpm, MariaDB 11.8)
- **Dependencies:** None (no Composer, no npm)

## Architecture

```
index.php              → Entry point (starts session, boots App)
config.php             → DB constants, autoloader
app/
├── core/
│   ├── App.php        → Route registration (48 routes)
│   ├── Router.php     → Exact + pattern matching for {id} params
│   ├── Controller.php → render(), renderWithLayout(), redirect()
│   ├── Database.php   → PDO singleton: query/fetch/fetchAll/insert/update/delete/count
│   └── Middleware.php → requireAuth(), requireRole(), CSRF trait
├── controllers/
│   ├── AuthController.php    → Login/signup/logout with bcrypt
│   ├── PanelController.php   → Member + therapist panel (dashboard, checkin, journal,
│   │                            community, messages, SOS, resources, settings)
│   ├── AdminController.php   → Admin CRUD (users, moderation, settings)
│   └── SiteController.php    → Landing page
├── models/
│   ├── User.php              → Auth, CRUD, role-based queries, therapist-patient
│   ├── JournalEntry.php      → CRUD, recent entries, therapist view
│   ├── Group.php             → Groups, messages, likes, moderation
│   ├── Resource.php          → Educational content library (admin-managed)
│   ├── TherapistResource.php → Therapist shared files (video/pdf/article/image)
│   ├── Conversation.php      → 1-on-1 chat rooms, unread counts
│   ├── ConversationMessage.php → Chat messages with polling support
│   ├── SOSAlert.php          → Emergency alerts (active/acknowledged/resolved)
│   └── Notification.php      → In-app notifications (SOS, messages, resources)
├── views/
│   ├── layouts/     → admin.php, panel.php, auth.php, landing.php
│   ├── auth/        → login.php, signup.php
│   ├── panel/       → 15 views (dashboard, therapist-dashboard, checkin, journal,
│   │                    community, messages, therapist-messages, therapist-conversation,
│   │                    my-patients, patient-detail, resources, therapist-resources,
│   │                    sos, therapist-sos, settings)
│   ├── admin/       → 5 views (dashboard, users, moderation, resources, settings)
│   └── site/        → index.php (landing page)
├── database/
│   └── database.sql → Full schema + seed data (14 tables)
css/ → app.css (Inter-based minimal design system)
js/  → app.js, auth.js, admin.js
```

## Database Schema (14 tables)

| Table | Purpose |
|---|---|
| `users` | Authentication, roles (member/therapist/admin) |
| `check_ins` | Daily mood/craving logging |
| `journal_entries` | Private journal entries with mood tagging |
| `groups` | Support groups |
| `group_members` | Group membership |
| `messages` | Community feed posts with threaded replies via `parent_id` |
| `message_likes` | Message likes |
| `resources` | Educational content library |
| `therapist_patients` | Therapist-patient assignments |
| `therapist_resources` | Therapist-shared files (video/pdf/article/image) |
| `conversations` | 1-on-1 chat rooms between patient and therapist |
| `conversation_messages` | Chat messages with read receipts |
| `sos_alerts` | Emergency alerts (active/acknowledged/resolved) |
| `notifications` | In-app notifications |

## Role System

| Feature | member | therapist | admin |
|---|---|---|---|
| `/panel/dashboard` | ✓ | ✓ | ✓ |
| `/panel/checkin` | ✓ | 403 | ✓ |
| `/panel/journal` | ✓ | 403 | ✓ |
| `/panel/community` | ✓ | ✓ | ✓ |
| `/panel/resources` | ✓ | ✓ | ✓ |
| `/panel/settings` | ✓ | ✓ | ✓ |
| `/panel/messages` (1-on-1 chat) | ✓ (their therapist) | — | — |
| `/panel/sos` (emergency alert) | ✓ | — | — |
| `/therapist/patients` | 403 | ✓ | 403 |
| `/therapist/patient/{id}` | 403 | ✓ | 403 |
| `/therapist/resources` (manage) | 403 | ✓ | 403 |
| `/therapist/messages` (patient convos) | 403 | ✓ | 403 |
| `/therapist/messages/{id}` | 403 | ✓ | 403 |
| `/therapist/sos` (manage alerts) | 403 | ✓ | 403 |
| `/admin/dashboard` | 403 | 403 | ✓ |
| `/admin/users` | 403 | 403 | ✓ |
| `/admin/moderation` | 403 | 403 | ✓ |
| `/admin/settings` | 403 | 403 | ✓ |

## Quick Start

```bash
# Start DDEV
ddev start

# Grant DB permissions (first time only)
ddev mysql -e "GRANT ALL PRIVILEGES ON rebirth.* TO 'db'@'%'; FLUSH PRIVILEGES;"

# Initialize database with schema + seed data
ddev exec mysql -h db -u db -pdb rebirth < app/database/database.sql

# Or visit in browser
open https://nada.ddev.site
```

### Test Accounts (password: `password123`)

| Email | Role |
|---|---|
| `jamie@example.com` | Member |
| `mwebb@example.com` | Member |
| `emily.t@example.com` | Member |
| `sarah.mitchell@rebirth.app` | Therapist |
| `admin@rebirth.app` | Admin |

Therapist-patient assignments: sarah.mitchell@rebirth.app (id 3) is assigned to jamie (id 1) and mwebb (id 4).


## Laragon (Windows)
### Prerequisites
- [Laragon](https://laragon.org/download/) installed (PHP 8.4 + MariaDB)
- Laragon running (Start All)
### Setup
1. **Clone the project** into `C:\laragon\www\rebirth`
2. **Update database config** in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'rebirth');
   define('DB_USER', 'root');
   define('DB_PASS', '');
3. Create the database — Laragon Menu → Database → Open phpMyAdmin (http://localhost/phpmyadmin), then:
- Click New in the left sidebar
- Database name: rebirth, Charset: utf8mb4_general_ci, click Create
4. Import schema + seed data — in phpMyAdmin:
- Select the rebirth database from the left sidebar
- Click the Import tab
- Click Choose File and select app/database/database.sql
- Scroll down and click Import
5. Visit http://rebirth.test in your browser
Laragon auto-creates a Virtual Host for every folder in www/, so rebirth → http://rebirth.test. The existing .htaccess with mod_rewrite works out of the box.

## Routes (48 total)

### Auth
- `GET /login`, `POST /login` — CSRF-protected login
- `GET /signup`, `POST /signup` — CSRF-protected registration
- `GET /logout`, `POST /logout`

### Panel (member/therapist)
- `GET /panel/{dashboard,checkin,journal,community,resources,settings,messages,sos}`
- `POST /panel/checkin` — Save daily mood/craving/note
- `POST /panel/journal/create`, `POST /panel/journal/update`, `POST /panel/journal/delete`
- `POST /panel/messages/create`, `POST /panel/messages/like`
- `POST /panel/messages/send` — Send 1-on-1 chat message
- `GET /panel/messages/poll?conversation_id=X&after=Y` — Poll for new messages
- `POST /panel/settings/profile`, `POST /panel/settings/join-group`
- `POST /panel/sos/send` — Send SOS emergency alert

### Therapist
- `GET /therapist/patients` — List assigned patients
- `GET /therapist/patient/{id}` — Patient detail with check-ins and journal
- `GET /therapist/resources` — Manage shared resources
- `POST /therapist/resources/create`, `POST /therapist/resources/delete`
- `GET /therapist/messages` — List patient conversations
- `GET /therapist/messages/{id}` — View conversation
- `GET /therapist/sos` — View SOS alerts
- `POST /therapist/sos/acknowledge`, `POST /therapist/sos/resolve`
- `GET /therapist/sos/count` — JSON: active alert count (for badge)

### Notifications
- `GET /notifications/count` — JSON: unread notification count
- `GET /notifications/list` — List notifications
- `POST /notifications/read`, `POST /notifications/read-all` — Mark as read

### Admin
- `GET /admin/{dashboard,users,moderation,settings}`
- `POST /admin/users/{create,update,delete}`
- `POST /admin/users/{assign,unassign}` — Manage therapist-patient assignments
- `POST /admin/moderation/delete` — Remove community messages

## Features

### Daily Check-in
Members log their mood (great/good/neutral/difficult/struggling), craving level (0-100 slider), and notes once per day. Day streak tracking rewards consistency. Therapists can view their patients' check-in history.

### Private Journal
Personal journal entries with mood tagging. Therapists can view their patients' entries for clinical insight. Entries can be created, edited, and deleted.

### Community Support
Topic-based support groups with a community feed. Members can post messages, like posts, reply in threaded discussions, and join groups.

### 1-on-1 Patient-Therapist Messaging
Private chat between each patient and their assigned therapist. Messages are polled for near-realtime delivery. Therapists see unread message counts per conversation.

### Therapist Resource Sharing
Therapists upload videos, PDFs, articles, or images for individual patients or all their patients at once. Files stored under `uploads/resources/{therapist_id}/`. Patients see shared content in a dedicated "From Your Therapist" section.

### SOS Emergency Alert System
Patients can trigger an SOS alert that immediately notifies their therapist. The therapist sidebar shows a live badge with active alert count (polled). Therapists can acknowledge or resolve alerts with notes.

### Notifications
In-app notifications for SOS alerts, new messages, resource sharing, and therapist-patient assignments. Unread counts shown in sidebar.

### Role-Based Access
Three roles with distinct navigation and permissions:
- **Member:** Dashboard, check-in, journal, community, messages, SOS, resources, settings
- **Therapist:** Dashboard, patients, messages, SOS alerts, resources, community, settings
- **Admin:** Full admin panel (dashboard, users, moderation, settings)

## Security

- **Authentication:** `password_hash()` bcrypt + `password_verify()`
- **Authorization:** `requireRole('admin')` / `requireRole('therapist')` middleware on routes
- **CSRF:** Per-session token verified on all POST requests via `verifyCsrf()`
- **Session-based:** PHP sessions with `$_SESSION['user_id']` + `$_SESSION['user']` cache
- **XSS:** All output uses `htmlspecialchars()`
- **SQL injection:** All queries use PDO prepared statements

## Testing

```bash
# Run the system test suite (96 tests)
bash tests/system_test.sh
```

The test suite covers: public pages, auth flows (login/logout/signup), role-based access control, member/therapist/admin pages, POST submissions with CSRF, database structure verification, error handling, and duplicate/validation edge cases.
