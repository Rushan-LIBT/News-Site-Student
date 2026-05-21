# NewsLanka — Project Proposal & Report

**Module:** SE102.3 — Web Based Application Development
**Module Leader:** S. Naji
**Project:** NewsLanka News Portal
**Coursework Type:** Repeat — Element of Assessment: Web Design

---

## 1. Executive Summary

NewsLanka is a full-stack news portal designed to deliver breaking news, in-depth analysis, and category-based content to readers across Sri Lanka and beyond. The site features a complete content management backend for editors and a polished, modern reading experience for end users.

The project demonstrates competency in:
- Front-end design with HTML5, CSS3, Bootstrap 5, JavaScript
- Back-end logic with PHP 8 (PDO)
- Relational database design and Data Manipulation Language (DML) operations with MySQL
- Authentication, authorization, and session management
- Web application security best practices

---

## 2. Project Objectives

| # | Objective |
|---|---|
| 1 | Build a publicly accessible news website with a minimum of 4 main pages |
| 2 | Implement a full admin panel with create, read, update, delete operations |
| 3 | Design a relational database with proper normalization and foreign key constraints |
| 4 | Ensure the site is responsive, secure, and maintainable |
| 5 | Track project progress on GitHub through meaningful commits |

---

## 3. Target Audience

- Sri Lankan readers seeking local news in English
- Tech-savvy professionals interested in IT and innovation
- Sports fans following cricket and athletics
- Anyone interested in business, politics, and entertainment news

---

## 4. Technology Stack

### Frontend
| Tool | Purpose |
|---|---|
| HTML5 | Semantic page structure |
| CSS3 (custom) | Branding, theming, animations |
| Bootstrap 5.3 | Responsive grid, components |
| JavaScript (ES6) | Client-side interactivity, form validation |
| Font Awesome 6 | Iconography |
| Google Fonts (Inter, Playfair Display) | Typography |

### Backend
| Tool | Purpose |
|---|---|
| PHP 8 | Server-side logic |
| PDO | Database access with prepared statements |
| Sessions | User authentication state |

### Database
| Tool | Purpose |
|---|---|
| MySQL 8 (InnoDB) | Relational data storage |
| utf8mb4 charset | Full Unicode support including emoji |

---

## 5. System Architecture

```
┌─────────────┐       ┌──────────────────┐       ┌──────────────┐
│   Browser   │ <───> │  Apache + PHP 8  │ <───> │   MySQL 8    │
│ (Bootstrap) │       │   (Application)  │       │ (newslanka)  │
└─────────────┘       └──────────────────┘       └──────────────┘
                              │
                              │
                ┌─────────────┴──────────────┐
                │                            │
          Public Site                 Admin Panel
       (8 pages, public)        (6 sections, admin-only)
```

The project follows a lightweight MVC-like pattern:
- **Views:** `.php` files in `public/` and `admin/` (markup + minimal logic)
- **Controllers:** Same files handle POST/GET for their respective resources
- **Models / DB layer:** `includes/db.php` (PDO singleton) + helper functions in `includes/functions.php`
- **Auth / authorization:** `includes/auth.php` (session-based with role checks)

---

## 6. Database Design

### Entity-Relationship Overview

```
users (1) ────< articles (many) >──── (1) categories
   │                    │
   │                    └──< article_tags >── tags
   │
   └──< comments
            │
   articles >─┘

contact_messages (standalone)
```

### Tables

#### `users`
| Column | Type | Notes |
|---|---|---|
| id | INT PK AI | |
| name | VARCHAR(100) | |
| email | VARCHAR(150) UNIQUE | |
| password | VARCHAR(255) | BCRYPT hash |
| role | ENUM('admin','user') | DEFAULT 'user' |
| avatar | VARCHAR(255) | Optional |
| bio | TEXT | Optional |
| created_at | TIMESTAMP | |

#### `categories`
| Column | Type | Notes |
|---|---|---|
| id | INT PK AI | |
| name | VARCHAR(80) UNIQUE | |
| slug | VARCHAR(100) UNIQUE | URL-friendly |
| description | TEXT | |

#### `articles`
| Column | Type | Notes |
|---|---|---|
| id | INT PK AI | |
| title | VARCHAR(255) | |
| slug | VARCHAR(280) UNIQUE | |
| excerpt | VARCHAR(500) | |
| content | LONGTEXT | HTML allowed |
| image | VARCHAR(255) | Filename |
| category_id | INT FK | → categories.id |
| author_id | INT FK | → users.id |
| status | ENUM('draft','published') | |
| is_featured | TINYINT(1) | |
| views | INT UNSIGNED | |
| created_at, updated_at | TIMESTAMP | |

#### `comments`
| Column | Type | Notes |
|---|---|---|
| id | INT PK AI | |
| article_id | INT FK | → articles.id |
| user_id | INT FK | → users.id |
| comment | TEXT | |
| status | ENUM('pending','approved','rejected') | |

#### `contact_messages`
| Column | Type | Notes |
|---|---|---|
| id | INT PK AI | |
| name, email, subject | VARCHAR | |
| message | TEXT | |
| is_read | TINYINT(1) | |

#### `tags` + `article_tags`
Many-to-many tag relationship (extension feature).

---

## 7. Pages & Features

### Public Pages

| Page | File | Purpose |
|---|---|---|
| Home | `public/index.php` | Featured hero, latest news grid, category sections, trending sidebar |
| Single Article | `public/article.php` | Full article, comments, related articles |
| Category | `public/category.php` | Articles filtered by category, pagination |
| Search | `public/search.php` | Keyword search across title/content/excerpt |
| About | `public/about.php` | Static info |
| Contact | `public/contact.php` | Contact form → saves to DB |
| Login | `public/login.php` | Email + password auth |
| Register | `public/register.php` | New user signup |

### Admin Pages

| Page | DML Operations Demonstrated |
|---|---|
| Dashboard | SELECT (multiple aggregate queries for stats) |
| Articles | INSERT (new), UPDATE (edit), DELETE (remove), SELECT (list/filter/search) |
| Categories | Full CRUD |
| Users | INSERT, DELETE, UPDATE (role change), SELECT |
| Comments | UPDATE (approve/reject), DELETE, SELECT (filter by status) |
| Messages | UPDATE (read flag), DELETE, SELECT |

**Result:** Every DML operation (Insert, Update, Delete, Select) is exercised across the admin panel.

---

## 8. UI / UX Design Choices

### Design Style
Modern minimal — inspired by BBC News and The Guardian. The aesthetic prioritizes readability and content density without overwhelming the reader.

### Color Palette
| Role | Hex | Use |
|---|---|---|
| Primary (navy) | `#1a1a2e` | Header, nav, headings |
| Accent (coral red) | `#e94560` | CTAs, breaking news, hover states |
| Background | `#ffffff` / `#f8f9fa` | Content / alt sections |
| Text | `#212529` | Body copy |
| Muted | `#6c757d` | Metadata, captions |

### Typography
- **Body:** Inter (sans-serif, modern)
- **Headlines:** Playfair Display (serif, editorial feel)

### Layout Patterns
- 16:10 image-card aspect ratio for consistency
- Hero section with gradient overlay for featured story
- Three-column responsive grid → two-column on tablet → single column on mobile
- Sticky breaking-news ticker bar with CSS animation

---

## 9. Security Implementation

| Threat | Mitigation |
|---|---|
| SQL Injection | All queries use PDO prepared statements with bound parameters |
| XSS | All user-supplied output passed through `htmlspecialchars()` (`e()` helper) |
| CSRF | Token-based protection on every form (`csrf_token()` + `csrf_verify()`) |
| Session hijacking | `httponly`, `samesite=Lax`, `session_regenerate_id(true)` on login |
| Password storage | `password_hash()` with `PASSWORD_BCRYPT` |
| Brute force (basic) | Generic error messages on login failure |
| Malicious file upload | MIME-type whitelist, 5MB cap, files renamed, PHP execution blocked in uploads via `.htaccess` |
| Directory listing | `Options -Indexes` |
| Privilege escalation | `require_admin()` gatekeeper on all admin pages, role check from session |

---

## 10. Development Process & GitHub Workflow

Development was split into logical phases, each committed to the `claude/news-website-project-hCjsB` branch:

1. **Setup** — Folder structure, database schema, configuration
2. **Layout** — Header, footer, navbar partials, base CSS
3. **Public pages** — Home, article, category, search, about, contact
4. **Authentication** — Login, register, logout, session helpers
5. **Admin panel** — Dashboard + 6 CRUD modules
6. **Comments & contact** — User-submitted comment moderation, contact form
7. **Polish** — README, proposal doc, security hardening

GitHub provides full traceability of contributions via commit history.

---

## 11. Testing

| Test Case | Result |
|---|---|
| Register a new user | New user inserted into DB; auto-logged in |
| Log in as admin | Redirected to admin dashboard |
| Create new article in admin | Article appears on public home / category page |
| Submit comment without login | Redirected to login page |
| Submit comment as logged-in user | Saved as `pending`, visible after admin approval |
| Submit contact form | Saved to `contact_messages`, visible in admin |
| Search for keyword | Matching articles returned |
| Try SQL injection in search | Safely escaped, no breach |
| Try XSS in comment | Escaped on display |
| Access `/admin/*` without login | Redirected to login |
| Access `/admin/*` as non-admin user | Redirected with error flash |

---

## 12. Future Enhancements

- Rich-text WYSIWYG editor (TinyMCE / Quill) for article content
- Email notifications for new comments / contact submissions
- Newsletter integration with double opt-in
- Multi-language support (Sinhala / Tamil / English)
- Bookmarks and reading history per user
- Reactions / likes on articles
- API endpoints for a future mobile app
- Caching layer (Redis) for high-traffic deployment
- Migration to a framework (Laravel) for larger scale

---

## 13. Conclusion

NewsLanka demonstrates a complete, production-aware web application built from first principles — without relying on a framework — covering all assignment requirements and exceeding the minimum scope. The codebase is organized, secure, and ready to extend.

---

## 14. References

- PHP Manual — <https://www.php.net/manual/en/>
- Bootstrap 5 Documentation — <https://getbootstrap.com/docs/5.3/>
- OWASP Top 10 — <https://owasp.org/www-project-top-ten/>
- MySQL Documentation — <https://dev.mysql.com/doc/>
- MDN Web Docs — <https://developer.mozilla.org/>

---

*End of report.*
