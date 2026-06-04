# Rebirth — Addiction Recovery Platform

A full-stack PHP MVC platform supporting addiction recovery through daily check-ins, private journaling, community support, professional therapy management, 1-on-1 patient-therapist messaging, SOS emergency alerts, step-based recovery progress tracking, resource sharing, and availability-based appointment scheduling.

Built with pure PHP 8.4 + MariaDB, running on DDEV with nginx-fpm. Zero Composer or third-party PHP dependencies.

## Tech Stack

- **Backend:** PHP 8.4 (pure, no framework), PDO + MariaDB
- **Frontend:** Vanilla PHP templates, FontAwesome 6.5.1 (free), Poppins font
- **Environment:** DDEV (nginx-fpm, MariaDB 11.8)
- **Dependencies:** None (no Composer, no npm)

## Architecture

```
index.php              → Entry point (starts session, boots App)
config.php             → DB constants, autoloader
app/
├── core/
│   ├── App.php     → Route registration (51 routes)
│   ├── Router.php     → Exact + pattern matching for {id} params
│   ├── Controller.php → render(), renderWithLayout(), redirect()
│   ├── Database.php   → PDO singleton: query/fetch/fetchAll/insert/update/delete
│   └── Middleware.php  → requireAuth(), requireRole(), CSRF token trait
├── controllers/
│   ├── AuthController.php    → Login/signup/logout with bcrypt
│   ├── PanelController.php   → 20 panel views + 16 POST handlers + 7 therapist
│   ├── AdminController.php   → 6 admin views + 7 POST CRUD handlers + settings
│   └── SiteController.php    → Landing page
├── models/
│   ├── User.php              → auth, CRUD, therapists, patients
│   ├── Appointment.php       → CRUD, upcoming, by patient/therapist
│   ├── JournalEntry.php      → CRUD, recent, therapist view
│   ├── Group.php             → groups, messages, likes, moderation
│   ├── Milestone.php         → streak, mood trend, weekly progress
│   ├── Resource.php          → CRUD
│   ├── TherapistResource.php → per-patient file sharing (video/pdf/article/image)
│   ├── Conversation.php      → 1-on-1 chat rooms, unread count, read receipts
│   ├── ConversationMessage.php → chat messages with polling support
│   ├── SOSAlert.php          → emergency alerts (active/acknowledged/resolved)
│   ├── RecoveryProgress.php  → 5-stage treatment tracking per patient
│   └── TherapistAvailability.php → weekly work hours, availability checking
├── views/
│   ├── layouts/    → admin.php, panel.php, auth.php, landing.php
│   ├── auth/       → login.php, signup.php
│   ├── panel/      → 15 views (dashboard, checkin, journal, appointments, community, progress, resources, settings, sos, messages + therapist-resources, therapist-messages, therapist-conversation, therapist-availability, therapist-sos, patient-progress)
│   ├── admin/      → 7 views (6 main + settings)
│   └── site/       → index.php (landing page)
├── database/
│   ├── schema.sql  → 17 tables with FKs
│   ├── migration.sql → Idempotent migration for new tables
│   └── seed.sql    → Sample data (5 users, groups, resources)
css/ → app.css
js/  → admin.js, app.js, auth.js
```

## Database Schema (17 tables)

| Table | Purpose |
|---|---|
| `users` | Authentication, roles (member/therapist/admin), recovery stage |
| `check_ins` | Daily mood/craving logging |
| `journal_entries` | Private journal entries |
| `appointments` | Therapy session scheduling |
| `milestones` | Achievement definitions (streak-based) |
| `user_milestones` | Per-user milestone progress |
| `groups` | Support groups |
| `group_members` | Group membership |
| `messages` | Community feed messages |
| `message_likes` | Message likes |
| `resources` | Educational content library |
| `therapist_patients` | Therapist-patient assignments |
| `therapist_availability` | Weekly work hours per therapist |
| `therapist_resources` | Therapist-shared files (video/pdf/article/image) |
| `conversations` | 1-on-1 chat rooms between patient and therapist |
| `conversation_messages` | Chat messages with read receipts |
| `sos_alerts` | Emergency alerts (active/acknowledged/resolved) |
| `recovery_progress` | 5-stage treatment tracking per patient |

## Role System

| Feature | member | therapist | admin |
|---|---|---|---|
| `/panel/dashboard` | ✓ | ✓ | ✓ |
| `/panel/checkin` | ✓ | 403 | ✓ |
| `/panel/journal` | ✓ | 403 | ✓ |
| `/panel/appointments` | ✓ | ✓ | ✓ |
| `/panel/community` | ✓ | ✓ | ✓ |
| `/panel/progress` | ✓ | 403 | ✓ |
| `/panel/resources` | ✓ | ✓ | ✓ |
| `/panel/settings` | ✓ | ✓ | ✓ |
| `/panel/messages` (1-on-1 chat) | ✓ (their therapist) | — | — |
| `/panel/sos` (emergency alerts) | ✓ | — | — |
| `/therapist/patients` | 403 | ✓ | 403 |
| `/therapist/patient/{id}` | 403 | ✓ | 403 |
| `/therapist/resources` (manage) | 403 | ✓ | 403 |
| `/therapist/messages` (patient convos) | 403 | ✓ | 403 |
| `/therapist/messages/{id}` | 403 | ✓ | 403 |
| `/therapist/availability` (set hours) | 403 | ✓ | 403 |
| `/therapist/sos` (manage alerts) | 403 | ✓ | 403 |
| `/therapist/patient/{id}/progress` | 403 | ✓ | 403 |
| `/admin/dashboard` | 403 | 403 | ✓ |
| `/admin/users` | 403 | 403 | ✓ |
| `/admin/appointments` | 403 | 403 | ✓ |
| `/admin/resources` | 403 | 403 | ✓ |
| `/admin/moderation` | 403 | 403 | ✓ |
| `/admin/analytics` | 403 | 403 | ✓ |
| `/admin/settings` | 403 | 403 | ✓ |

## Quick Start

```bash
# Start DDEV
ddev start

# If first time, grant DB permissions
ddev mysql -e "GRANT ALL PRIVILEGES ON rebirth.* TO 'db'@'%'; FLUSH PRIVILEGES;"

# Run migration + seed
ddev exec php -r "require '/var/www/html/config.php'; Database::migrate();"

# Or using the web migrator
# Visit: https://nada.ddev.site/migrate
```

### Test Accounts (password: `password123`)

| Email | Role |
|---|---|
| `jamie@example.com` | Member (Active) |
| `mwebb@example.com` | Member (Active) |
| `emily.t@example.com` | Member (Onboarding) |
| `sarah.mitchell@rebirth.app` | Therapist |
| `admin@rebirth.app` | Admin |

## Routes (51 total)

### Auth
- `GET /login`, `POST /login` — CSRF-protected login
- `GET /signup`, `POST /signup` — CSRF-protected registration
- `GET /logout`, `POST /logout`

### Panel (member/therapist)
- `GET /panel/{dashboard,checkin,journal,appointments,community,progress,resources,settings,messages,sos}`
- `POST /checkin` — Save daily mood
- `POST /journal/create`, `POST /journal/delete`
- `POST /appointments/create`, `POST /appointments/cancel`
- `POST /messages/create`, `POST /messages/like`
- `POST /settings/profile`, `POST /settings/join-group`
- `POST /messages/send` — Send 1-on-1 chat message
- `GET /messages/poll?conversation_id=X&after=Y` — Poll for new messages
- `POST /panel/sos/send` — Send SOS emergency alert

### Therapist
- `GET /therapist/patients`
- `GET /therapist/patient/{id}`
- `GET /therapist/patient/{id}/progress` — View patient recovery stages
- `POST /therapist/patient/{id}/progress/update` — Update stage status
- `GET /therapist/resources` — Manage uploaded resources
- `POST /therapist/resources/create` — Upload file resource
- `POST /therapist/resources/delete` — Delete resource
- `GET /therapist/messages` — List patient conversations
- `GET /therapist/messages/{id}` — View conversation
- `GET /therapist/availability` — Set weekly work hours
- `POST /therapist/availability/save` — Save availability
- `GET /therapist/sos` — View SOS alerts
- `POST /therapist/sos/acknowledge` — Acknowledge alert
- `POST /therapist/sos/resolve` — Resolve alert
- `GET /therapist/sos/count` — JSON: active alert count (for badge)
- `GET /therapist/availability/check?therapist_id=X` — JSON: is therapist online now

### Admin
- `GET /admin/{dashboard,users,appointments,resources,moderation,analytics,settings}`
- `GET /admin/appointments/availability?therapist_id=X&date=Y` — JSON: available time slots
- `POST /admin/users/{create,update,delete}`
- `POST /admin/appointments/{create,update}`
- `POST /admin/resources/{create,update,delete}`
- `POST /admin/moderation/delete`

## Features

### Daily Check-in
Members log their mood (great → struggling), craving level (0-100), and notes once per day. Mute streak tracking and milestones reward consistency.

### Private Journal
Personal journal entries with mood tagging. Therapists can view their patients' entries for clinical insight.

### Community Support
Topic-based support groups with a real-time-updating community feed. Members can post messages, like posts, and join groups.

### 1-on-1 Patient-Therapist Messaging
Private chat between each patient and their assigned therapist. Messages are polled every 3 seconds for near-realtime delivery. Patients see therapist availability status (online/offline based on work hours). Therapists see unread message counts per conversation.

### Therapist Resource Sharing
Therapists upload videos, PDFs, articles, or images for individual patients or all their patients at once. Files stored under `uploads/resources/`. Patients see shared content in a dedicated "From Your Therapist" section on their Resources page.

### Appointment Scheduling (Admin)
Admin creates appointments between any patient and therapist. The scheduler checks each therapist's availability calendar and shows only available 30-minute slots. Booked times are hidden, preventing double-booking.

### Therapist Availability Calendar
Therapists set recurring weekly work hours (e.g., Mon 9-12, Tue 14-17). This controls messaging availability and appointment slot generation.

### Recovery Progress Tracking
Therapists track patients through 5 treatment stages: Assessment → Detox → Therapy → Relapse Prevention → Aftercare. Each stage has status (not_started/in_progress/completed), dates, and therapist notes. Patients see their progress on their dashboard.

### SOS Emergency Alert System
Patients can trigger an SOS alert that immediately notifies their therapist. The therapist sidebar shows a live badge with active alert count (polled every 10 seconds). Therapists can acknowledge or resolve alerts with notes. History is logged for both parties.

### Role-Based Access
Three roles with distinct navigation and permissions:
- **Member:** Dashboard, check-in, journal, appointments, community, messages, SOS, resources, progress, settings
- **Therapist:** Dashboard, patients, messages, SOS alerts, resources, schedule, community, availability, settings
- **Admin:** Full admin panel (dashboard, users, appointments, resources, moderation, analytics, settings) + panel settings

## Security

- **Authentication:** `password_hash()` bcrypt + `password_verify()`
- **Authorization:** `requireRole('admin')` / `requireRole('therapist')` middleware
- **CSRF:** Per-session token verified on all POST requests via `verifyCsrf()`
- **Session-based:** PHP sessions with `$_SESSION['user_id']` + `$_SESSION['user']` cache
- **XSS:** All output uses `htmlspecialchars()`
- **SQL injection:** All queries use PDO prepared statements
