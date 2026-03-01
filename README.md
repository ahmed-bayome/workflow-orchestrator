# 🚀 Workflow Orchestrator

A modern, full-stack workflow approval system designed for enterprise efficiency. Built with **Laravel 11** and **Vue 3**, this system allows organizations to define complex multi-step approval processes with dynamic forms and real-time notifications.

---

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

## 📖 Standard Setup (Manual)

If you prefer to run components manually, follow the detailed instructions in [RUN.md](file:///c:/dev/monglish-assignment/RUN.md).

1.  **Backend**: `cd backend && composer install && php artisan migrate:fresh --seed`
2.  **Frontend**: `cd frontend && npm install && npm run dev`