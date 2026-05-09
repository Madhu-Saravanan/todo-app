# AntiGravity Todo App

QjReBLXgnO4X1Ixu

> A modern, full-featured Todo application built with **PHP (Core), MySQL, Bootstrap 5, and AJAX** — designed to run in a XAMPP environment.

---

## ✨ Features

| Feature | Details |
|---|---|
| Authentication | Signup, Login, Logout, Forgot/Reset Password |
| CSRF Protection | All forms + AJAX requests protected |
| XSS Prevention | All output escaped via `htmlspecialchars()` |
| SQL Injection | 100% PDO prepared statements |
| Todos | Create, Edit, Soft-Delete, Status Toggle (AJAX) |
| Filters | Filter by Status, Priority, Group, Search |
| Pagination | Configurable items per page |
| Groups | Create, Edit, Delete — assign todos to groups |
| Dashboard | Stats: Total / Completed / Pending / High-Priority |
| Dark / Light Mode | Persisted in `localStorage` |
| Responsive | Mobile-first Bootstrap 5 layout |
| Toast Notifications | Server-side flash + client-side AJAX toasts |

---

## 🗂️ Folder Structure

```
ToDo/
├── ajax/                   # AJAX endpoints (status update, delete)
│   ├── todo_delete.php
│   └── todo_status.php
├── assets/
│   ├── css/
│   │   ├── app.css         # Main stylesheet (dark/light mode, layout)
│   │   └── auth.css        # Auth page animated background
│   └── js/
│       └── app.js          # Theme, sidebar, AJAX, toasts, search debounce
├── config/
│   ├── config.php          # App constants, timezone, session setup
│   └── database.php        # PDO singleton connection class
├── controllers/
│   ├── AuthController.php  # Signup, Login, Logout, Reset Password
│   └── TodoController.php  # CRUD for Todos & Groups
├── database/
│   └── schema.sql          # Full MySQL schema + sample seed data
├── includes/
│   ├── auth.php            # requireAuth(), csrfField(), verifyCsrf(), e()
│   ├── header.php          # HTML <head> partial
│   ├── sidebar.php         # Sidebar + Topbar layout
│   └── footer.php          # Closing tags + JS
├── models/
│   ├── UserModel.php       # User CRUD + authentication
│   ├── TodoModel.php       # Todo CRUD, soft-delete, pagination, stats
│   └── GroupModel.php      # Group CRUD with todo counts
├── views/
│   ├── auth/
│   │   ├── login.php
│   │   ├── signup.php
│   │   ├── forgot_password.php
│   │   └── reset_password.php
│   ├── todos/
│   │   ├── index.php       # List with filters + pagination
│   │   ├── create.php
│   │   └── edit.php
│   ├── groups/
│   │   ├── index.php       # Manage groups
│   │   └── view.php        # Todos within a group
│   └── dashboard.php
├── .htaccess
├── index.php               # Entry point (redirects to login/dashboard)
└── README.md
```

---

## ⚙️ Setup Instructions (XAMPP)

### 1. Place the project
Copy the `ToDo` folder to:
```
C:\xampp\htdocs\ToDo\
```

### 2. Start XAMPP services
- Open **XAMPP Control Panel**
- Start **Apache** and **MySQL**

### 3. Import the Database

**Option A – phpMyAdmin (recommended):**
1. Open browser → `http://localhost/phpmyadmin`
2. Click **Import** tab (top menu)
3. Click **Choose File** → select `C:\xampp\htdocs\ToDo\database\schema.sql`
4. Click **Go** / Import

**Option B – MySQL CLI:**
```bash
mysql -u root -p < C:/xampp/htdocs/ToDo/database/schema.sql
```

### 4. Configure Database (if needed)
Open `config/config.php` and adjust:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'antigravity_todo');
define('DB_USER', 'root');
define('DB_PASS', '');          // Empty by default in XAMPP
define('APP_URL', 'http://localhost/ToDo');
```

### 5. Run the App
Open your browser and navigate to:
```
http://localhost/ToDo
```

---

## 🔐 Default Test Credentials

| Field    | Value             |
|----------|-------------------|
| Email    | `test@example.com` |
| Password | `Test@1234`       |

> The test user and sample todos are created by the seed data in `schema.sql`.

---

## 🛠️ Configuration Options

| Constant | Location | Default | Description |
|---|---|---|---|
| `APP_URL` | `config.php` | `http://localhost/ToDo` | Change if your folder name differs |
| `TIMEZONE` | `config.php` | `Asia/Kolkata` | Your local timezone |
| `ITEMS_PER_PAGE` | `config.php` | `10` | Todos per page |
| `SESSION_LIFETIME` | `config.php` | `86400` (24h) | Remember-me duration |

---

## 🔒 Security Features

- **CSRF Tokens** — All POST forms and AJAX requests include a CSRF token verified server-side
- **Prepared Statements** — Every SQL query uses PDO prepared statements (no raw interpolation)
- **Password Hashing** — `password_hash()` with `PASSWORD_BCRYPT` (cost 12)
- **XSS Prevention** — All output passed through `htmlspecialchars()` via the `e()` helper
- **Session Hardening** — `use_strict_mode`, `cookie_httponly`, `cookie_samesite=Strict`
- **Session Regeneration** — ID regenerated on login to prevent session fixation
- **Soft Deletes** — Todos are never permanently deleted; `deleted_at` timestamp used
- **Directory Protection** — `.htaccess` blocks direct access to sensitive folders

---

## 📝 Forgot Password (Demo Mode)

Since this is a local XAMPP app without a mail server, the reset link is written to the **PHP error log** instead of being emailed.

To find the reset URL:
1. Submit the Forgot Password form
2. Check `C:\xampp\php\logs\php_error_log` (or your XAMPP log path)
3. Look for the line: `[Password Reset] URL: http://localhost/ToDo/views/auth/reset_password.php?token=...`

---

## 🚀 Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.1+ (Core, no framework) |
| Database | MySQL 5.7+ via PDO |
| Frontend | HTML5, CSS3, Bootstrap 5.3, Vanilla JS |
| Icons | Bootstrap Icons 1.11 |
| Fonts | Google Fonts – Inter |
| Environment | XAMPP (Apache + MySQL) |

---

## 📄 License

MIT – Free to use and modify for personal or commercial projects.
