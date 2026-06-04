# Rebirth — Addiction Recovery Platform

A full-stack PHP MVC platform supporting addiction recovery through daily check-ins, private journaling, community support, and professional therapy management.

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
│   ├── App.php        → Route registration (29 routes)
│   ├── Router.php     → Exact + pattern matching for {id} params
│   ├── Controller.php → render(), renderWithLayout(), redirect()
│   ├── Database.php   → PDO singleton: query/fetch/fetchAll/insert/update/delete
│   └── Middleware.php  → requireAuth(), requireRole(), CSRF token trait
├── controllers/
│   ├── AuthController.php    → Login/signup/logout with bcrypt
│   ├── PanelController.php   → 8 panel views + 11 POST CRUD handlers + 2 therapist
│   ├── AdminController.php   → 6 admin views + 7 POST CRUD handlers + settings
│   └── SiteController.php    → Landing page
├── models/
│   ├── User.php         → auth, CRUD, therapists, patients
│   ├── Appointment.php  → CRUD, upcoming, by patient/therapist
│   ├── JournalEntry.php → CRUD, recent, therapist view
│   ├── Group.php        → groups, messages, likes, moderation
│   ├── Milestone.php    → streak, mood trend, weekly progress
│   └── Resource.php     → CRUD
├── views/
│   ├── layouts/    → admin.php, panel.php, auth.php, landing.php
│   ├── auth/       → login.php, signup.php
│   ├── panel/      → 10 views (8 main + 2 therapist)
│   ├── admin/      → 7 views (6 main + settings)
│   └── site/       → index.php (landing page)
├── database/
│   ├── schema.sql  → 11 tables with FKs
│   └── seed.sql    → Sample data (5 users, groups, resources)
css/ → app.css
js/  → admin.js, app.js, auth.js
```

## Database Schema (11 tables)

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

## Role System

| Page | member | therapist | admin |
|---|---|---|---|
| `/panel/dashboard` | ✓ | ✓ | ✓ |
| `/panel/checkin` | ✓ | ✓ | ✓ |
| `/panel/journal` | ✓ | ✓ | ✓ |
| `/panel/appointments` | ✓ | ✓ | ✓ |
| `/panel/community` | ✓ | ✓ | ✓ |
| `/panel/progress` | ✓ | ✓ | ✓ |
| `/panel/resources` | ✓ | ✓ | ✓ |
| `/panel/settings` | ✓ | ✓ | ✓ |
| `/therapist/patients` | 403 | ✓ | ✓ |
| `/therapist/patient/{id}` | 403 | ✓ | ✓ |
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

## Routes (29 total)

### Auth
- `GET /login`, `POST /login` — CSRF-protected login
- `GET /signup`, `POST /signup` — CSRF-protected registration
- `GET /logout`, `POST /logout`

### Panel (member/therapist/admin)
- `GET /panel/{dashboard,checkin,journal,appointments,community,progress,resources,settings}`
- `POST /checkin` — Save daily mood
- `POST /journal/create`, `POST /journal/delete`
- `POST /appointments/create`, `POST /appointments/cancel`
- `POST /messages/create`, `POST /messages/like`
- `POST /settings/profile`, `POST /settings/join-group`

### Therapist
- `GET /therapist/patients`
- `GET /therapist/patient/{id}`

### Admin
- `GET /admin/{dashboard,users,appointments,resources,moderation,analytics,settings}`
- `POST /admin/users/{create,update,delete}`
- `POST /admin/appointments/{create,update}`
- `POST /admin/resources/{create,update,delete}`
- `POST /admin/moderation/delete`

## Security

- **Authentication:** `password_hash()` bcrypt + `password_verify()`
- **Authorization:** `requireRole('admin')` / `requireRole('therapist')` middleware
- **CSRF:** Per-session token verified on all POST requests via `verifyCsrf()`
- **Session-based:** PHP sessions with `$_SESSION['user_id']` + `$_SESSION['user']` cache
- **XSS:** All output uses `htmlspecialchars()`
- **SQL injection:** All queries use PDO prepared statements
