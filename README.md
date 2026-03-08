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

## ✅ Prerequisites

Make sure the following are installed before proceeding:

- **PHP 8.2+** with extensions: `pdo_sqlite`, `mbstring`, `openssl`, `curl`
- **Composer**
- **Node.js 20+ & npm**

---

## 🔑 Default Credentials

Seeded automatically on fresh install (password: `password`):

| Role | Email |
|---|---|
| **Admin** | `admin@test.com` |
| **Manager** | `manager@test.com` |
| **Employee** | `employee@test.com` |
| **Finance** | `finance@test.com` |
| **Legal** | `legal@test.com` |
| **CEO** | `ceo@test.com` |
| **HR** | `hr@test.com` |

---

## 📖 Installation

> **Note:** The automated setup scripts (`orchestrator.bat` / `orchestrator.sh`) are provided for convenience but may not work on all machines. Follow the manual steps below for a reliable setup.

### Step 1 — Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret --force
```

Create the SQLite database file:

```bash
# macOS / Linux
touch database/database.sqlite

# Windows (PowerShell)
New-Item -Path database/database.sqlite -ItemType File
```

Open `backend/.env` and set:

```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
BROADCAST_CONNECTION=reverb
QUEUE_CONNECTION=database
MAIL_MAILER=log

REVERB_APP_ID=808080
REVERB_APP_KEY=p4wrtmbchpdt0bkcszul
REVERB_APP_SECRET=your-reverb-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY=p4wrtmbchpdt0bkcszul
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

Run migrations and seed the database:

```bash
php artisan migrate:fresh --seed
```

✅ If you see `Database seeding completed successfully`, you're good to go.

### Step 2 — Frontend

```bash
cd ../frontend
npm install
```

Create `frontend/.env`:

```env
VITE_API_BASE_URL=http://localhost:8000/api
VITE_WS_HOST=localhost
VITE_WS_PORT=8080
VITE_WS_KEY=p4wrtmbchpdt0bkcszul
VITE_WS_CLUSTER=mt1
```

### Step 3 — Run the Application

Open **four** separate terminal windows, each from the project root:

| Terminal | Directory | Command |
|---|---|---|
| **1. API** | `backend` | `php artisan serve` |
| **2. Queue** | `backend` | `php artisan queue:work` |
| **3. WebSocket** | `backend` | `php artisan reverb:start` |
| **4. Frontend** | `frontend` | `npm run dev` |

Then open **http://localhost:5173** in your browser.

---

## 🔄 Reset & Re-Seed Database

To wipe all data and restore default seed data (roles, users, sample workflows):

```bash
cd backend && php artisan migrate:fresh --seed
```

---

## 🌱 Pump Test Data

To populate the UI with realistic data (50 requests at various stages):

```bash
cd backend && php artisan db:seed --class=DataPumpSeeder
```

This creates 15 additional staff users and 50 realistic Purchase/Leave requests at various approval stages.

---

## 🧪 Running Tests

```bash
cd backend && php artisan test
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

## 📡 Queue & Workers

Default queue driver is `database`. For production, switch to Redis: `QUEUE_CONNECTION=redis`

```bash
# Development
php artisan queue:work

# Production (recommended)
php artisan queue:work --queue=default --tries=3 --backoff=5
```

### Failed Jobs

```bash
php artisan queue:failed          # List failed jobs
php artisan queue:retry <uuid>    # Retry specific job
php artisan queue:retry all       # Retry all
```

Admins can also retry jobs from the UI at `/admin/failed-jobs`.

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
| DELETE | `/api/admin/roles/{id}` | Delete role |
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
| GET | `/api/requests/{id}/pending` | Get pending steps |
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

Steps with the same `execution_group` run **in parallel**.
