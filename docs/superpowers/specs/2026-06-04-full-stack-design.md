# Full-Stack Rebirth Platform — Design Spec

## Overview

Convert the existing PHP MVC prototype (mock data) into a fully functional MariaDB-backed application with role-based dashboards, complete CRUD, and real authentication. Keep the existing architecture (no Composer, no framework) and add a PDO database layer.

## Tech Stack

- PHP 8.4 (existing)
- MariaDB 11.8 via DDEV (existing)
- Nginx-fpm via DDEV (existing)
- No Composer, no third-party dependencies
- FontAwesome 6.5.1 CDN (existing)

## Database Schema

### `users`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| name | VARCHAR(100) | |
| email | VARCHAR(255) UNIQUE | |
| password_hash | VARCHAR(255) | |
| role | ENUM('member','therapist','admin') | DEFAULT 'member' |
| stage | ENUM('Onboarding','Active','Maintenance','Alumni') | DEFAULT 'Onboarding' |
| avatar | VARCHAR(255) | nullable |
| initials | VARCHAR(4) | computed on register |
| created_at | DATETIME | |
| updated_at | DATETIME | |

### `check_ins`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| user_id | INT FK→users | |
| mood | ENUM('great','good','okay','tough','struggling') | |
| craving_level | TINYINT(0-100) | |
| note | TEXT | |
| check_date | DATE | UNIQUE per user per day |
| created_at | DATETIME | |

### `journal_entries`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| user_id | INT FK→users | |
| content | TEXT | |
| mood | ENUM('great','good','okay','tough','struggling') | |
| created_at | DATETIME | |

### `appointments`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| user_id | INT FK→users | patient |
| therapist_id | INT FK→users | nullable |
| title | VARCHAR(200) | |
| date_time | DATETIME | |
| status | ENUM('confirmed','pending','cancelled','completed') | |
| notes | TEXT | |
| created_at | DATETIME | |

### `milestones` (system-defined)
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| name | VARCHAR(100) | 'First Week', '30 Days', etc. |
| description | TEXT | |
| target_days | INT | e.g., 7, 30, 60, 90, 180, 365 |

### `user_milestones`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| user_id | INT FK→users | |
| milestone_id | INT FK→milestones | |
| progress | TINYINT(0-100) | |
| achieved | BOOLEAN | DEFAULT false |
| achieved_at | DATETIME | nullable |

### `groups`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| name | VARCHAR(100) | |
| description | TEXT | |
| tag | VARCHAR(50) | |
| member_count | INT | cached counter |
| created_by | INT FK→users | |
| created_at | DATETIME | |

### `group_members`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| group_id | INT FK→groups | |
| user_id | INT FK→users | |
| joined_at | DATETIME | |

### `messages` (community feed)
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| user_id | INT FK→users | |
| group_id | INT FK→groups | 0 = main feed |
| text | TEXT | |
| created_at | DATETIME | |

### `message_likes`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| message_id | INT FK→messages | |
| user_id | INT FK→users | UNIQUE per message+user |

### `resources`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| type | VARCHAR(50) | 'Article','Video','Guide' |
| icon | VARCHAR(100) | FontAwesome class |
| title | VARCHAR(200) | |
| description | TEXT | |
| tag | VARCHAR(50) | 'Education','Wellness','Support' |
| url | VARCHAR(500) | |
| created_by | INT FK→users | |
| created_at | DATETIME | |

### `therapist_patients`
| Column | Type | Notes |
|--------|------|-------|
| id | INT AUTO_INCREMENT PK | |
| therapist_id | INT FK→users | |
| patient_id | INT FK→users | |
| assigned_at | DATETIME | UNIQUE per therapist+patient |

## Architecture

### File Layout (new/modified files)
```
config.php                  + DB constants (DB_HOST, DB_NAME, DB_USER, DB_PASS)
app/
  core/
    Database.php            NEW — PDO singleton wrapper
    Middleware.php          NEW — auth/role check helpers
    Controller.php          MODIFIED — add middleware calls
    Router.php              MODIFIED — add before-dispatch middleware
  models/
    User.php                REWRITE — PDO queries
    Appointment.php         REWRITE — PDO queries
    JournalEntry.php        REWRITE — PDO queries
    Group.php               REWRITE — PDO queries
    Milestone.php           REWRITE — PDO queries
    Resource.php            REWRITE — PDO queries
  controllers/
    AuthController.php      REWRITE — real password_hash/verify
    PanelController.php     MODIFIED — real data + POST handlers
    AdminController.php     MODIFIED — real data + POST handlers
  views/
    panel/                  (mostly unchanged - may add forms)
    admin/                  (mostly unchanged - may add forms)
  database/
    schema.sql              NEW — full CREATE TABLE
    seed.sql                NEW — sample data for all tables
```

### Database Layer (`Database.php`)
```php
class Database {
  private static ?PDO $instance = null;
  public static function connect(): PDO;
  public static function query(string $sql, array $params = []): PDOStatement;
  public static function fetch(string $sql, array $params = []): ?array;
  public static function fetchAll(string $sql, array $params = []): array;
  public static function insert(string $table, array $data): int;
  public static function update(string $table, int $id, array $data): int;
  public static function delete(string $table, int $id): int;
}
```

### Middleware (`Middleware.php`)
```php
trait Middleware {
  public function requireAuth(): void;        // redirect to /login if no session
  public function requireRole(string $role): void; // 403 if wrong role
  public function requireAdmin(): void;
  public function currentUser(): ?array;      // fetch from DB by session id
  public function user(): array;              // same, but throws if missing
}
```

### Auth Flow
1. **Register**: `password_hash($password, PASSWORD_BCRYPT)` → INSERT user → `$_SESSION['user_id']`
2. **Login**: SELECT by email → `password_verify()` → `$_SESSION['user_id']`
3. **Role redirect**: post-login check: admin → `/admin/dashboard`, else → `/panel/dashboard`
4. **Session check**: Every protected controller action calls `$this->requireAuth()`

### CRUD Route Map

| Method | Path | Controller | Purpose |
|--------|------|------------|---------|
| POST | `/login` | AuthController@authenticate | Login with email/password |
| POST | `/signup` | AuthController@register | Create account |
| POST | `/logout` | AuthController@logout | Clear session |
| POST | `/checkin` | PanelController@saveCheckin | Submit daily check-in |
| POST | `/journal/create` | PanelController@createJournal | New journal entry |
| POST | `/journal/delete` | PanelController@deleteJournal | Delete entry |
| POST | `/appointments/create` | PanelController@createAppointment | Book appointment |
| POST | `/appointments/cancel` | PanelController@cancelAppointment | Cancel appointment |
| POST | `/messages/create` | PanelController@createMessage | Post community message |
| POST | `/messages/like` | PanelController@likeMessage | Like/unlike message |
| POST | `/settings/profile` | PanelController@updateProfile | Update name/email |
| POST | `/settings/join-group` | PanelController@joinGroup | Join a support group |
| POST | `/admin/users/create` | AdminController@createUser | |
| POST | `/admin/users/update` | AdminController@updateUser | |
| POST | `/admin/users/delete` | AdminController@deleteUser | |
| POST | `/admin/appointments/create` | AdminController@createAppointment | |
| POST | `/admin/appointments/update` | AdminController@updateAppointment | |
| POST | `/admin/resources/create` | AdminController@createResource | |
| POST | `/admin/resources/update` | AdminController@updateResource | |
| POST | `/admin/resources/delete` | AdminController@deleteResource | |
| POST | `/admin/moderation/delete` | AdminController@deleteMessage | |
| GET | `/therapist/patients` | PanelController@myPatients | Therapist patient list |
| GET | `/therapist/patient/{id}` | PanelController@patientDetail | View patient data |

### Dashboard Data Per Role

**Member dashboard** (`panel/dashboard`):
- `stats`: streak days, total check-ins, current streak
- `todayCheckin`: whether checked in today
- `upcomingAppointments`: next 2 appointments
- `recentMilestones`: milestone progress

**Therapist dashboard** (`panel/dashboard`):
- `patientCount`, `todayAppointments`, `pendingRequests`
- `recentActivity`: last 5 patient check-ins/journals

**Admin dashboard** (`admin/dashboard`):
- `totalUsers`, `usersByRole`, `usersByStage`, `appointmentsToday`
- `recentRegistrations`: last 5 users
- `upcomingSessions`: next 4 appointments
- `moderationQueue`: reported messages count

### View Changes
- Views stay mostly unchanged — they receive the same shaped data (assoc arrays)
- Admin views get edit/delete buttons wiring to POST routes
- Journal view gets a working modal form
- Check-in view gets a functional form
- Community view gets a working message input
- Admin users/appointments/resources tables get CRUD action columns

### Seeding
- `seed.sql`: 3 predefined milestones, 3 support groups, 6 resources, 2 sample users (1 member, 1 admin), 1 therapist, sample appointments/journal entries/messages
- `config.php` gets DB constants with DDEV defaults (`db` host, `db` user, `db` pass, `rebirth` database)

### Database Setup
- The `rebirth` database is created via `CREATE DATABASE IF NOT EXISTS rebirth` in schema.sql
- DDEV provides MariaDB at host `db`, user `db`, password `db` (defaults)
- Run migration via: `ddev mysql < app/database/schema.sql && ddev mysql rebirth < app/database/seed.sql`

## Error Handling
- `Database.php` methods return `null` or `[]` on query failure (no exceptions — keep it simple)
- Failed auth redirects back to `/login?error=invalid_credentials`
- Failed role check shows 403 page
- 404 for unknown routes (existing)
- POST handlers redirect back with `?success=` or `?error=` query params

## What Stays Unchanged
- All CSS files (app.css, styles.css)
- All JS files (app.js, admin.js, auth.js)
- All view template HTML structures
- Landing page (SiteController)
- Layout files (panel.php, admin.php, auth.php, landing.php)
- Router basic structure, App.php, Controller::render() methods
