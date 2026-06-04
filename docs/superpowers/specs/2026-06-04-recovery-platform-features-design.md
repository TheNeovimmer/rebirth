# Recovery Platform — Therapist Resources, Messaging, SOS, Progress & Scheduling

## Overview

Six interconnected features for the Rebirth PHP MVC platform: therapist resource sharing, patient-therapist 1-on-1 messaging with availability gating, admin appointment scheduling with calendar-aware availability, recovery progress tracking, SOS alert system, and community access. All roles (member/therapist/admin) have clearly defined access boundaries.

## Database Schema

Six new tables:

### `therapist_availability`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| therapist_id | INT FK → users.id | CASCADE delete |
| day_of_week | TINYINT | 0=Sun, 1=Mon … 6=Sat |
| start_time | TIME | |
| end_time | TIME | |

### `therapist_resources`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| therapist_id | INT FK → users.id | CASCADE delete |
| patient_id | INT FK → users.id | CASCADE delete; NULL = all patients |
| title | VARCHAR(255) | |
| type | ENUM('video','pdf','article','image') | |
| file_path | VARCHAR(255) | |
| description | TEXT | nullable |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### `conversations`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| patient_id | INT FK → users.id | CASCADE |
| therapist_id | INT FK → users.id | CASCADE |
| created_at | DATETIME | |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |
| UNIQUE(patient_id, therapist_id) | | One conversation per pair |

### `conversation_messages`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| conversation_id | INT FK → conversations.id | CASCADE |
| sender_id | INT FK → users.id | CASCADE |
| content | TEXT | |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| read_at | DATETIME | nullable |

### `sos_alerts`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| patient_id | INT FK → users.id | CASCADE |
| therapist_id | INT FK → users.id | CASCADE |
| status | ENUM('active','acknowledged','resolved') | DEFAULT 'active' |
| created_at | DATETIME | |
| resolved_at | DATETIME | nullable |
| notes | TEXT | nullable (therapist fill-in) |

### `recovery_progress`
| Column | Type | Notes |
|---|---|---|
| id | INT AUTO_INCREMENT PK | |
| patient_id | INT FK → users.id | CASCADE |
| therapist_id | INT FK → users.id | CASCADE |
| stage_name | VARCHAR(100) | e.g. 'Assessment' |
| stage_order | TINYINT | Display ordering |
| status | ENUM('not_started','in_progress','completed') | DEFAULT 'not_started' |
| therapist_notes | TEXT | nullable |
| started_at | DATETIME | nullable |
| completed_at | DATETIME | nullable |

Default progress stages for each patient: Assessment, Detox, Therapy, Relapse Prevention, Aftercare.

## Feature Details

### 1. Therapist Resource Sharing

**Routes:**
- `GET /therapist/resources` — Therapist upload/management page
- `POST /therapist/resources/create` — Upload handler
- `POST /therapist/resources/delete` — Delete handler
- `GET /panel/resources` — Patient sees resources from their therapist (enhanced from existing)

**Upload:** File stored under `uploads/resources/{therapist_id}/{filename}`. Allowed MIME types:
- video: video/mp4, video/webm, video/ogg
- pdf: application/pdf
- article: text/plain, text/markdown (stored as file)
- image: image/jpeg, image/png, image/gif, image/webp

**Patient view:** Therapists resources tab separate from admin resources. Shows title, type icon, description, download/open link.

**Access:** Therapists only see their own uploads. Patients only see their therapist's uploads (where patient_id = them OR patient_id IS NULL).

### 2. Therapist Availability

**Routes:**
- `GET /therapist/availability` — Manage availability hours
- `POST /therapist/availability/save` — Save weekly schedule

**UI:** Table of 7 days (Mon-Sun), each with start/end timepickers. Empty = not working that day. Option for multiple time blocks per day.

### 3. Patient-Therapist Messaging

**Routes:**
- `GET /panel/messages` — Patient view of conversation with their therapist
- `GET /therapist/messages` — Therapist view, list of all patient conversations
- `GET /therapist/messages/{id}` — Individual conversation view
- `POST /messages/send` — Send a message (from either role)

**Conversation auto-creation:** When a patient first accesses /panel/messages, a conversation is auto-created between them and their assigned therapist.

**Availability gating:** On message send from patient, the system checks if the therapist is currently available:
- Check `therapist_availability` for today's day_of_week
- Check if current time falls between start_time and end_time
- If available: message saves normally, JS polling picks it up on therapist side
- If unavailable: message saves, but patient sees "Your therapist is offline. They'll respond during their next available hours: Mon 9-12, Tue 14-17"

**Polling:** `setInterval(fetchNewMessages, 3000)` on both sides after last known message ID. Returns only newer messages.

**Sidebar badges:** Therapist sidebar shows unread count badge on "Messages". Patient sidebar shows unread badge on "Messages".

### 4. Admin Appointment Scheduling

**Routes:**
- `GET /admin/appointments` — Existing, enhanced
- `POST /admin/appointments/create` — Enhanced with availability check
- `GET /admin/appointments/availability?therapist_id=X&date=Y` — JSON endpoint returning available slots

**Availability endpoint logic:**
1. Fetch therapist's availability for the day_of_week of the given date
2. Fetch existing appointments for that therapist on that date
3. Generate 30-min slot list from start_time to end_time
4. Remove slots overlapping existing appointments
5. Return JSON array of `{time: "09:00", available: true}`

**Admin form:** After selecting therapist + date, JS fetches `/admin/appointments/availability` and populates a time slot selector. Only available times shown.

### 5. Recovery Progress Tracking

**Routes:**
- `GET /therapist/patient/{id}/progress` — Therapist manages patient stages
- `POST /therapist/patient/{id}/progress/update` — Update stage status + notes
- `GET /panel/progress` — Patient views own progress (enhanced from existing)

**Auto-create:** When patient is assigned to therapist (therapist_patients row created), 5 default recovery_progress rows are created for that pair.

**Therapist UI:** Card per stage. Each card shows stage name, status badge, therapist notes field, started/completed dates, action button (mark in_progress / mark completed).

**Patient UI:** Timeline-style view showing each stage with status icon and dates. Shows therapist notes.

### 6. SOS Alert System

**Routes:**
- `GET /panel/sos` — Patient SOS page with emergency button
- `POST /panel/sos/send` — Create new SOS alert
- `GET /therapist/sos` — List of SOS alerts for therapist
- `POST /therapist/sos/acknowledge` — Mark as acknowledged
- `POST /therapist/sos/resolve` — Mark as resolved with notes

**Patient flow:** Big red "I NEED HELP" button. On click: confirm dialog → POST → redirects back with "Alert sent to [therapist name]". Once sent, shows "Your therapist has been notified" with pending/resolved status.

**Therapist flow:** Sidebar badge counts active SOS alerts. Dedicated page lists alerts sorted by most recent active first. Each shows: patient name, time elapsed, acknowledge/resolve buttons. Acknowledging sends a visual acknowledgment but keeps the alert active until resolved.

### 7. Community (exists, unchanged)

Existing community groups, feed, and messages remain. All roles (member, therapist, admin) can access.

## Role Access Matrix

| Feature | Member | Therapist | Admin |
|---|---|---|---|
| Community feed/chat | ✅ | ✅ | ✅ |
| View therapist-shared resources | ✅ | — | — |
| Manage therapist resources (upload/delete) | ❌ | ✅ | ❌ |
| 1-on-1 messages with assigned therapist | ✅ | ✅ (all patients) | ❌ |
| Set availability hours | ❌ | ✅ | ❌ |
| View own recovery progress | ✅ | — | — |
| Manage patient recovery progress | ❌ | ✅ | ❌ |
| Send SOS | ✅ | — | — |
| Manage/resolve SOS alerts | ❌ | ✅ | ❌ |
| Book appointments (any patient + therapist) | ❌ | ❌ | ✅ |
| View therapist availability calendar | ❌ | — | ✅ |

## New Routes Summary

```
GET  /panel/messages                          → Patient conversation view
GET  /panel/sos                               → Patient SOS page
POST /panel/sos/send                          → Create SOS alert
GET  /panel/resources                         → Patient sees therapist resources (enhanced)

GET  /therapist/resources                     → Therapist resource manager
POST /therapist/resources/create              → Upload resource
POST /therapist/resources/delete              → Delete resource
GET  /therapist/messages                      → Therapist conversation list
GET  /therapist/messages/{id}                 → Single conversation
GET  /therapist/availability                  → Manage availability
POST /therapist/availability/save             → Save availability
GET  /therapist/sos                           → SOS alert list
POST /therapist/sos/acknowledge               → Acknowledge SOS
POST /therapist/sos/resolve                   → Resolve SOS
GET  /therapist/patient/{id}/progress         → Patient progress mgmt
POST /therapist/patient/{id}/progress/update  → Update progress stage

POST /messages/send                           → Send chat message
GET  /messages/poll?after=X&conversation_id=Y → Poll for new messages

GET  /admin/appointments/availability         → JSON available slots
```

## Concurrent Implementation Strategy

All features share no overlapping state dependencies, allowing parallel implementation:
1. Schema migration (all 6 tables at once)
2. Models (TherapistResource, Conversation, ConversationMessage, SOSAlert, RecoveryProgress, TherapistAvailability)
3. Therapist features (resources, availability, SOS management, progress)
4. Messaging (conversation + polling)
5. Patient features (SOS button, resources view, progress view, messages)
6. Admin enhancement (availability-aware appointment creation)
7. Sidebar/badge integration across all roles
