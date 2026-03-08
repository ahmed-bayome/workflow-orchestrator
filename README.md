# 🚀 Dynamic Workflow Orchestrator

A full-stack dynamic workflow approval system built with **Laravel 11** and **Vue 3**. Supports configurable approval pipelines (sequential, parallel, mixed), dynamic roles, real-time updates, and robust background processing.
---
![Image](https://github.com/user-attachments/assets/fb28baf6-8a2f-455e-9c0f-188cad5583aa)
## ✨ Key Features

- **Dynamic Workflow Engine** — Create workflows (e.g. Purchase Requests, Leave Requests) with flexible multi-step pipelines
- **Parallel & Sequential Approvals** — `any` (first to approve wins) or `all` (unanimous) approval modes per step
- **Real-Time Updates** — Live UI updates powered by **Laravel Reverb** WebSockets
- **Role-Based Access Control** — Dynamic roles via **Spatie Laravel Permission**
- **Dynamic Form Schema** — Define request forms with text, number, select, date, and textarea fields
- **Background Workers** — Jobs for orchestration, step processing, notifications, and failure handling
- **Admin Tools** — Reports dashboard, failed job inspection and retry, user and role management
<img width="1536" height="511" alt="unnamed" src="https://github.com/user-attachments/assets/3132143a-8bf0-4891-a79c-1ab0ea4d73a9" />

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11, PHP 8.2+, JWT Auth (tymon/jwt-auth) |
| Frontend | Vue 3, TypeScript, Vite, Tailwind CSS, Pinia |
| Real-time | Laravel Reverb (WebSockets) |
| Database | SQLite (default) or MySQL |
| Queue | Database driver (Redis-ready) |
| RBAC | Spatie Laravel Permission |

---

## 🔑 Default Credentials

Seeded automatically on fresh install (password: `password`):

| Role | Email |
|---|---|
| **Admin** | `admin@test.com` |
| **Manager** | `manager@test.com` |
| **Employee** | `employee@test.com` |
| **Finance** | `finance@test.com` |

---

## ⚡ Quick Start (All-in-One)

### Windows
```powershell
.\orchestrator.bat
```

### macOS / Linux
```bash
chmod +x orchestrator.sh && ./orchestrator.sh
```

Choose **Option 1** for first-time setup, then **Option 2** to run.

---

## 📖 Manual Installation (A to Z Guide)

If you are on a fresh machine (like a Windows Sandbox) and cannot run the automated scripts, follow this exact sequence of commands.

### 1. Install Prerequisites (Pure PowerShell - No winget)
Run these commands in an **Administrator** terminal to download and set up your environment manually:

```powershell
# 1. Create a tools folder
New-Item -Path "C:\tools" -ItemType Directory -Force

# 2. Download & Extract PHP 8.2
Invoke-WebRequest -Uri "https://windows.php.net/downloads/releases/php-8.2.12-Win32-vs16-x64.zip" -OutFile "C:\tools\php.zip"
Expand-Archive -Path "C:\tools\php.zip" -DestinationPath "C:\tools\php" -Force

# 3. Download Composer
Invoke-WebRequest -Uri "https://getcomposer.org/composer.phar" -OutFile "C:\tools\php\composer.phar"
"@echo off`nphp %~dp0composer.phar %*" | Set-Content "C:\tools\php\composer.bat"

# 4. Download & Extract Node.js 20
Invoke-WebRequest -Uri "https://nodejs.org/dist/v20.10.0/node-v20.10.0-win-x64.zip" -OutFile "C:\tools\node.zip"
Expand-Archive -Path "C:\tools\node.zip" -DestinationPath "C:\tools\node_temp" -Force
Move-Item "C:\tools\node_temp\node-v20.10.0-win-x64\*" "C:\tools\node" -Force

# 5. Add to PATH for this session
$env:Path += ";C:\tools\php;C:\tools\node"

# 6. Verify
php -v
npm -v
composer --version
```

### 2. Backend Setup
Navigate to the backend directory and install dependencies:
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret --force
```

**Configure `.env` for SQLite:**
Ensure these lines exist in your `backend/.env` (manually edit or use these commands):
```powershell
# Windows PowerShell command to update .env
(Get-Content .env) -replace 'DB_CONNECTION=.*', 'DB_CONNECTION=sqlite' -replace 'DB_DATABASE=.*', 'DB_DATABASE=database/database.sqlite' | Set-Content .env
```

**Initialize Database:**
```powershell
# Create the SQLite file (Windows)
New-Item -Path database/database.sqlite -ItemType File

# Run migrations and seeders
php artisan migrate:fresh --seed
```
*Verification: If you see "Database seeding completed successfully," proceed to Step 3.*

### 3. Frontend Setup
```bash
cd ../frontend
npm install
```

**Configure `.env`:**
Create `frontend/.env` with these values:
```powershell
# Windows PowerShell one-liner to create frontend .env
@'
VITE_API_BASE_URL=http://localhost:8000/api
VITE_WS_HOST=localhost
VITE_WS_PORT=8080
VITE_WS_KEY=p4wrtmbchpdt0bkcszul
'@ | Set-Content .env
```

### 4. Running the Application
Open **four** separate terminal windows and run one command in each:

| Window | Directory | Command |
|---|---|---|
| **1. API** | `backend` | `php artisan serve` |
| **2. Queue** | `backend` | `php artisan queue:work` |
| **3. Reverb** | `backend` | `php artisan reverb:start` |
| **4. Web** | `frontend` | `npm run dev` |

---

## 🔄 Reset & Re-Seed Database

If you want to wipe all data and start fresh with the default seed data (roles, users, sample workflows), run this from the root directory:

### Windows
```powershell
.\orchestrator.bat  # Then select option 3
```

### macOS / Linux
```bash
./orchestrator.sh    # Then select option 3
```

This restores all default credentials and sample workflows. Useful when testing or after pumping test data.

---

## 🧪 Running Tests

```bash
# macOS / Linux
cd backend && php artisan test

# Windows
cd backend; php artisan test
```

Expected output: **9 tests, 42 assertions, all passing**

Test coverage includes:
- Sequential workflow happy path
- Parallel group — both steps must complete before advancing
- `approval_mode: any` — first approval wins, second is blocked
- `approval_mode: all` — all members must approve
- Duplicate approval call idempotency
- Failed job lands in DLQ
- Admin can retry a failed job via API

---

## 🌱 Pump Test Data

To quickly populate the UI with realistic English data (users and requests at various stages):

```bash
cd backend && php artisan db:seed --class=DataPumpSeeder
```

This creates:
- 15 additional staff users with random roles
- 50 realistic requests (Purchase and Leave) with natural English payloads
- Randomly processes some requests to different stages (approved, rejected, in-progress)

Refresh the UI after running to see the updated dashboard and reports.

---

## 📡 Queue & Workers

### Queue Driver
Default: `database` (jobs stored in `jobs` table).
For production, switch to Redis: `QUEUE_CONNECTION=redis`

### Running Workers
```bash
# Development
php artisan queue:work

# Production (recommended)
php artisan queue:work --queue=default --tries=3 --backoff=5

# With Horizon (Redis only)
php artisan horizon
```

### Failed Jobs (DLQ)
Failed jobs are stored in the `failed_jobs` table after exhausting retries.

```bash
# List failed jobs
php artisan queue:failed

# Retry a specific job
php artisan queue:retry <uuid>

# Retry all failed jobs
php artisan queue:retry all
```

Admin can also retry jobs from the UI at `/admin/failed-jobs`.

### Retry Policies
| Job | Tries | Backoff |
|---|---|---|
| CreateRequestStepsJob | 3 | 3s, 10s, 30s |
| ProcessStepActionJob | 3 | 3s, 10s, 30s |
| AdvanceWorkflowJob | 3 | 3s, 10s, 30s |
| SendNotificationJob | 3 | 5s, 15s, 60s |

---

## 🗺️ API Reference

### Auth
| Method | Endpoint | Description |
|---|---|---|
| POST | `/api/auth/register` | Register new user |
| POST | `/api/auth/login` | Login, returns JWT |
| GET | `/api/auth/me` | Get current user |
| POST | `/api/auth/logout` | Logout |

### Admin
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/admin/users` | List all users |
| POST | `/api/admin/users` | Create user |
| PUT | `/api/admin/users/{id}` | Update user |
| DELETE | `/api/admin/users/{id}` | Soft delete user |
| POST | `/api/admin/users/{id}/restore` | Restore deleted user |
| GET | `/api/admin/roles` | List roles |
| POST | `/api/admin/roles` | Create role |
| DELETE | `/api/admin/roles/{id}` | Delete role (fails if used in workflow) |
| GET | `/api/admin/workflows` | List workflows |
| POST | `/api/admin/workflows` | Create workflow |
| PUT | `/api/admin/workflows/{id}` | Update workflow |
| POST | `/api/admin/workflows/{id}/activate` | Activate workflow |
| POST | `/api/admin/workflows/{id}/deactivate` | Deactivate workflow |
| POST | `/api/admin/jobs/{uuid}/retry` | Retry a failed job |

### Requests
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/requests` | List my requests |
| POST | `/api/requests` | Submit new request |
| GET | `/api/requests/{id}` | Get request detail |
| GET | `/api/requests/{id}/pending` | Get currently pending steps |
| POST | `/api/requests/{id}/admin/retry` | Admin retry failed request |

### Approvals
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/approvals/pending` | List my pending approvals |
| POST | `/api/requests/{id}/steps/{step_id}/approve` | Approve a step |
| POST | `/api/requests/{id}/steps/{step_id}/reject` | Reject a step |

### Reports
| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/reports/requests` | Request stats by status |

---

## 🔄 Workflow Definition Format

```json
{
  "name": "Purchase Request",
  "form_schema": {
    "fields": [
      { "id": "amount", "label": "Amount", "type": "number", "required": true },
      { "id": "reason", "label": "Reason", "type": "textarea", "required": true }
    ]
  },
  "steps": [
    {
      "name": "Manager Approval",
      "role_id": 2,
      "execution_group": "group_1",
      "approval_mode": "any",
      "order": 1
    },
    {
      "name": "Finance Review",
      "role_id": 3,
      "execution_group": "group_2",
      "approval_mode": "all",
      "order": 2
    },
    {
      "name": "Legal Review",
      "role_id": 4,
      "execution_group": "group_2",
      "approval_mode": "any",
      "order": 2
    }
  ]
}
```

Steps with the same `execution_group` run **in parallel**. Steps with different groups run **sequentially**. The pipeline only advances to the next group once all steps in the current group are complete.

---

## 🧠 Design Decisions

### Role Membership: Snapshot at Creation
When a request is submitted, the list of approvers for each step is **snapshotted** from current role members. Changes to role membership after submission do not affect in-flight requests. This ensures consistency and auditability.

### Real-Time: Reverb instead of Firebase
Laravel Reverb was chosen over Firebase for self-hosted simplicity, tighter Laravel integration, and no external dependency. The architecture is Firebase-compatible — the broadcasting interface can be swapped by changing `BROADCAST_CONNECTION=pusher` and pointing to Firebase via a Pusher-compatible adapter.

### Soft Deletes for Users
Deleted users are soft-deleted (`deleted_at` timestamp). All historical records (step actions, approvals) remain intact and reference the user ID. The user cannot log in but their history is preserved.

### Idempotency
`ProcessStepActionJob` checks for an existing `StepAction` record before writing. `AdvanceWorkflowJob` uses `SELECT ... FOR UPDATE` row locking to prevent race conditions during concurrent approvals.

---

## 🤖 AI Usage

This project was built with heavy assistance from **Gemini CLI** and **Claude (Anthropic)**:

- **Gemini CLI** was used to scaffold controllers, jobs, migrations, Vue components, and implement features incrementally via targeted prompts
- **Claude** was used for architecture review, identifying missing requirements, writing focused prompts for Gemini, and reviewing correctness of generated code
- All generated code was reviewed, tested, and adjusted manually
- The overall architecture, design decisions, and edge case handling were human-directed
