# 🚀 Workflow Orchestrator

A modern, full-stack workflow approval system designed for enterprise efficiency. Built with **Laravel 11** and **Vue 3**, this system allows organizations to define complex multi-step approval processes with dynamic forms and real-time notifications.

---
<img width="1536" height="1024" alt="image" src="https://github.com/user-attachments/assets/f9b7da6c-a7a1-4a15-b7b0-4336bade511c" />

## ✨ Key Features

- **Dynamic Workflow Engine**: Create custom workflows (e.g., Purchase Requests, Leave Requests) with flexible steps.
- **Parallel & Sequential Approvals**: Support for `any` (first to approve) or `all` (unanimous) approval modes.
- **Real-Time Updates**: Instant notifications and UI updates powered by **Laravel Reverb**.
- **Role-Based Access Control (RBAC)**: Secure access management using **Spatie Laravel Permission**.
- **Dynamic Form Schema**: Define request forms with diverse field types (text, number, select, date, textarea).
- **Comprehensive Dashboard**: Dedicated views for Admins (Configuration), Employees (Requests), and Approvers (Pending Actions).

## 🛠️ Tech Stack

- **Backend**: Laravel 11, PHP 8.x, JWT Auth
- **Frontend**: Vue 3, TypeScript, Vite, Tailwind CSS, Pinia
- **Real-time**: Laravel Reverb (WebSockets)
- **Database**: SQLite (default) or MySQL

---

## ⚡ Quick Start (Launch Everything)

We've made it incredibly simple to get started. Use our one-line launchers to start the API, Queue, WebSocket server, and Frontend all at once in a single tabbed terminal window.

### **Windows**
Run this from your root directory:
```powershell
.\run-orchestrator.bat
```

### **macOS / Linux**
Run this from your root directory:
```bash
chmod +x run-orchestrator.sh
./run-orchestrator.sh
```

---

## 🔑 Default Credentials

The system comes pre-seeded with the following test accounts (Password: `password`):

| Role | Email |
| :--- | :--- |
| **Admin** | `admin@test.com` |
| **Manager** | `manager@test.com` |
| **Employee** | `employee@test.com` |
| **Finance** | `finance@test.com` |

---

## 📖 Detailed Setup (Manual)

If you prefer to run components manually, follow these steps:

### 1. Backend Setup

Open a terminal in the `backend` directory:

```bash
cd backend
```

#### Configuration
Ensure your `.env` file is configured.
- **Database:** Set `DB_CONNECTION` (sqlite or mysql). If sqlite, ensure `database/database.sqlite` exists.
- **Queue:** `QUEUE_CONNECTION=database`
- **Broadcasting:**
  - If using **Pusher**: Set `BROADCAST_CONNECTION=pusher` and fill `PUSHER_*` credentials.
  - If using **Reverb** (Local): Set `BROADCAST_CONNECTION=reverb` and ensure `REVERB_*` vars are set.

#### Installation & Database
```bash
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
```

#### Start Servers
You need to run these commands in separate terminals:

**Terminal 1: API Server**
```bash
php artisan serve
```

**Terminal 2: Queue Worker** (Processes background jobs)
```bash
php artisan queue:work
```

**Terminal 3: Reverb Server** (If using Reverb for real-time)
```bash
php artisan reverb:start
```

---

### 2. Frontend Setup

Open a terminal in the `frontend` directory:

```bash
cd frontend
```

#### Configuration
Ensure `.env` matches your backend configuration.
- `VITE_API_BASE_URL=http://localhost:8000/api`
- If using **Reverb**:
  ```env
  VITE_WS_HOST=localhost
  VITE_WS_PORT=8080
  VITE_WS_KEY=... (matches backend REVERB_APP_KEY)
  ```

#### Installation & Run
```bash
npm install
npm run dev
```

---

## 💡 Usage Guide

1.  **Login**: Use the credentials provided above at `http://localhost:5173`.
2.  **Admin Flow**: Go to Admin Dashboard -> Create Workflow (define fields, steps, and approvers).
3.  **Employee Flow**: Go to Dashboard -> New Request -> Fill form -> Submit.
4.  **Approver Flow**: Login as a Manager/Finance -> Go to Approvals -> Review and Approve/Reject.
