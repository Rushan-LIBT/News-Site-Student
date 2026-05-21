# NewsLanka — Sri Lanka's Trusted News Source

A full-featured news portal built as the SE102.3 Web Based Application Development assignment.

> **Module:** SE102.3 — Web Based Application Development
> **Module Leader:** S. Naji (naji@nsbm.ac.lk)
> **Stack:** PHP 8 · MySQL 8 · Bootstrap 5 · HTML5 · CSS3 · JavaScript

---

## Features

### Public Site (8 pages)
- **Home** — Hero featured article, latest news grid, category sections, trending sidebar, breaking news ticker
- **Single Article** — Full article view with author bio, related articles, comments
- **Category** — Articles filtered by category with pagination
- **Search** — Full-text search across titles, content and excerpts
- **About Us / Contact Us** — Static + dynamic contact form (saves to DB)
- **Login / Register / Logout** — User authentication with secure password hashing

### Admin Panel (6 sections)
- **Dashboard** — Live statistics (articles, users, comments, views)
- **Articles** — Full CRUD with image upload, draft/published toggle, featured flag, slug generation, category filter, status filter, search
- **Categories** — Full CRUD with article-count display
- **Users** — Create / Delete / Promote-Demote (admin role management)
- **Comments** — Approve / Reject / Delete moderation
- **Messages** — View / Mark read / Delete contact form submissions

### Security
- PDO prepared statements (SQL injection prevention)
- Password hashing with `password_hash()` BCRYPT
- CSRF tokens on every form
- XSS protection via `htmlspecialchars()` everywhere
- Session security: `httponly`, `samesite=Lax`, `session_regenerate_id` on login
- File upload validation (MIME, size, rename, PHP blocked in uploads folder)
- `.htaccess` security headers (X-Frame-Options, nosniff, Referrer-Policy)

---

## Database Schema (7 tables)

| Table | Purpose |
|---|---|
| `users` | Site users with role (admin/user) |
| `categories` | News categories |
| `articles` | News articles (FK to users, categories) |
| `comments` | User comments on articles (with moderation status) |
| `contact_messages` | Contact form submissions |
| `tags` + `article_tags` | Article tagging (many-to-many) |

Full schema in [`database/newslanka.sql`](database/newslanka.sql).

---

## Setup Instructions

### Prerequisites
- XAMPP / WAMP / LAMP (Apache + PHP 8+ + MySQL 8+)
- Git

### Installation

1. **Clone the repository** into your web root (e.g. `htdocs/`):
   ```bash
   git clone <repo-url> News-Site-Student
   cd News-Site-Student
   ```

2. **Start Apache and MySQL** (via XAMPP control panel or equivalent).

3. **Run the installer** — open in browser:
   ```
   http://localhost/News-Site-Student/install.php
   ```
   Click "Run Installation" — this creates the `newslanka` database, all tables, and seed data with correctly hashed passwords.

4. **Visit the site:**
   - Public site: <http://localhost/News-Site-Student/public/>
   - Admin panel: <http://localhost/News-Site-Student/admin/>

5. **Delete `install.php`** after setup (security best practice in production).

### Default Credentials

| Role | Email | Password |
|---|---|---|
| Admin | `admin@newslanka.lk` | `admin123` |
| User | `saman@newslanka.lk` | `user123` |

> Change these immediately if deploying to a real server.

### Manual Setup (without installer)

If you prefer not to use `install.php`:
1. Open phpMyAdmin → import `database/newslanka.sql`.
2. The seed passwords are pre-hashed for `admin123` / `user123`. If they fail, use the installer to regenerate.
3. Edit `includes/config.php` to match your DB credentials.

---

## Folder Structure

```
News-Site-Student/
├── public/               # Web-facing pages
│   ├── index.php         # Home
│   ├── article.php       # Single article
│   ├── category.php      # Category listing
│   ├── search.php        # Search results
│   ├── about.php         # About us
│   ├── contact.php       # Contact form
│   ├── login.php / register.php / logout.php
│   └── assets/           # CSS, JS, images
├── admin/                # Admin panel
│   ├── index.php         # Dashboard
│   ├── articles.php      # Article CRUD
│   ├── categories.php    # Category CRUD
│   ├── users.php         # User management
│   ├── comments.php      # Comment moderation
│   └── messages.php      # Contact messages
├── includes/             # Shared components
│   ├── config.php        # Configuration constants
│   ├── db.php            # PDO connection
│   ├── auth.php          # Auth helpers
│   ├── functions.php     # Utility functions
│   ├── header.php        # Public site header
│   └── footer.php        # Public site footer
├── database/
│   └── newslanka.sql     # Schema + seed data
├── docs/
│   └── proposal.md       # Project report
├── install.php           # One-click DB installer
└── README.md
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, JavaScript (vanilla), Bootstrap 5.3, Font Awesome 6, Google Fonts (Inter + Playfair Display) |
| Backend | PHP 8 (PDO) |
| Database | MySQL 8 (InnoDB, utf8mb4) |
| Auth | Session-based with BCRYPT password hashing |
| Web Server | Apache (XAMPP) |

---

## Screenshots

See `docs/screenshots/` for screenshots of:
- Home page
- Article page
- Admin dashboard
- Articles management
- Login / Register
- Contact form

---

## Assignment Deliverables Coverage

| Requirement | Status |
|---|---|
| Min. 4 main pages | Done — 8 public pages |
| Database with full DML operations | Done — Insert / Update / Delete / Select across all admin CRUD |
| Admin features | Done — Full dashboard with 6 management sections |
| GitHub contributions | Done — Phased commits on `claude/news-website-project-hCjsB` |
| Website launch | Done — Runs on XAMPP/LAMP, deployable to InfinityFree / 000webhost |
| Project report | Done — `docs/proposal.md` |

---

## Author

Built for SE102.3 module assignment. Solo project.

## License

Academic use only.
