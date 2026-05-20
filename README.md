# T.MAN PHARMA — RD Document System

Vue 3 frontend + PHP/SQLite backend for pharmaceutical R&D forms.

## Forms included
| Page | Form | Title |
|------|------|-------|
| `/stability` | F-RD-FD-012 | แบบฟอร์มการศึกษาความคงสภาพของผลิตภัณฑ์ (Stability Program) |
| `/dissolution` | F-RD-FD-006 | แบบฟอร์มส่งวิเคราะห์ Dissolution Profile |
| `/quality-check` | F-RD-FD-005 | แบบฟอร์มการตรวจสอบคุณภาพวัตถุดิบและเภสัชภัณฑ์ |

## Requirements
- **Node.js** v18+ (for frontend)
- **PHP** 8.1+ with SQLite extension enabled (php_pdo_sqlite)

## Setup & Run

### 1. Start the PHP backend (port 8000)
```bash
cd backend
php -S localhost:8000
```

### 2. Install and start the Vue frontend (port 5173)
```bash
cd frontend
npm install
npm run dev
```

### 3. Open in browser
```
http://localhost:5173
```

## How it works
- Frontend proxies `/api/*` requests to `http://localhost:8000` (configured in `vite.config.js`)
- Backend stores all form data as JSON in an SQLite database (`backend/database/pharma.db`)
- Database file is auto-created on first request

## Build for production
```bash
cd frontend
npm run build
```
Then serve the `frontend/dist/` folder alongside the `backend/` with any PHP server (Apache/Nginx).
For Apache, copy the `backend/.htaccess` URL rewriting rules.
