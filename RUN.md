# 🚀 Run Instructions for Workflow Orchestrator

## 1. Backend Setup

Open a terminal in `backend`:

```bash
cd backend
```

### Configuration
Ensure your `.env` file is configured.
- **Database:** Set `DB_CONNECTION` (sqlite or mysql). If sqlite, ensure `database/database.sqlite` exists.
- **Queue:** `QUEUE_CONNECTION=database`
- **Broadcasting:**
  - If using **Pusher**: Set `BROADCAST_CONNECTION=pusher` and fill `PUSHER_*` credentials.
  - If using **Reverb** (Local): Set `BROADCAST_CONNECTION=reverb` and ensure `REVERB_*` vars are set.

### Installation & Database
```bash
composer install
php artisan key:generate
php artisan jwt:secret
php artisan migrate:fresh --seed
```

### Start Servers
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

## 2. Frontend Setup

Open a terminal in `frontend`:

```bash
cd frontend
```

### Configuration
Ensure `.env` matches your backend configuration.
- `VITE_API_BASE_URL=http://localhost:8000/api`
- If using **Reverb**:
  ```env
  VITE_WS_HOST=localhost
  VITE_WS_PORT=8080
  VITE_WS_KEY=... (matches backend REVERB_APP_KEY)
  ```

### Installation & Run
```bash
npm install
npm run dev
```

## 3. Usage

1.  Open `http://localhost:5173`
2.  **Login Credentials** (from Seeders):
    - **Admin:** `admin@test.com` / `password`
    - **Manager:** `manager@test.com` / `password`
    - **Employee:** `employee@test.com` / `password`

### Workflow
1.  **Admin:** Go to Admin Dashboard -> Create Workflow (with dynamic forms & steps).
2.  **Employee:** Go to Dashboard -> New Request -> Submit a request.
3.  **Approver:** Login as Manager/Finance -> Go to Approvals -> Approve/Reject.
