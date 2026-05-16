# CyberIndex

> Cyberpunk-styled personal portfolio + PHP admin panel

[中文](./README.md) | English

---

## Preview

### Frontend Home

![Frontend Home](docs/frontend-home.png)

### About Page

![About Page](docs/frontend-about.png)

### Admin Dashboard

![Admin Dashboard](docs/admin-dashboard.png)

### Admin Editor

![Admin Editor](docs/admin-profile.png)

---

## Introduction

CyberIndex is a lightweight personal portfolio system with a cyberpunk/glitch design aesthetic. The frontend delivers a visually striking personal homepage, while the backend provides full content management — every piece of text is editable from the admin panel.

Minimal tech stack: vanilla PHP 8.3+ with no framework dependencies; SQLite as the database for zero-config deployment.

## Features

**Frontend**
- CRT scanlines, chromatic aberration, neon glow, chamfered corner cards
- Multi-page: Home, About (bio + awards timeline), Projects (list + detail), Contact
- All text dynamically rendered from database, fully customizable
- Responsive design, mobile-friendly
- `prefers-reduced-motion` accessibility support

**Admin Panel**
- Installation wizard (environment check → admin setup → auto database creation)
- Shared layout component with unified sidebar navigation
- Full CRUD: profile, skills, projects, awards, contacts
- Dashboard: stats, system info, login logs, quick actions
- All frontend text editable from admin (nav, buttons, titles, descriptions, footer, etc.)

**Security**
- Argon2id password hashing
- CSRF token protection
- PDO prepared statements (SQL injection prevention)
- XSS output escaping
- IP-based rate limiting (5 attempts / 15 minutes)
- Secure sessions (HttpOnly + Secure + SameSite=Strict)
- SQLite stored in randomized 32-char hash directory
- install.php auto-locks after installation

## Requirements

- PHP >= 8.3
- pdo_sqlite extension
- Argon2id support
- Nginx / Apache

## Quick Start

1. Deploy to your web server
2. Visit `/install.php` and follow the wizard
3. Visit `/admin/` to log in and manage content
4. Visit `/` to see the frontend

## Nginx Config Reference

```nginx
location ~ ^/(data|core)/ { deny all; }
location ~ config\.inc\.php$ { deny all; }
location ~ \.(db|sqlite|sqlite3)$ { deny all; }
```

## Project Structure

```
├── index.php              # Frontend home
├── about.php              # About page
├── projects.php           # Projects list + detail
├── contact.php            # Contact page
├── style.css              # Frontend styles
├── install.php            # Installation wizard
├── core/
│   ├── config.php         # Config loader
│   ├── db.php             # SQLite connection (WAL mode)
│   ├── functions.php      # CSRF, XSS, rate limiting
│   └── session.php        # Secure session
├── admin/
│   ├── index.php          # Login + dashboard
│   ├── auth.php           # Auth middleware
│   ├── layout.php         # Shared layout component
│   ├── profile.php        # Profile editor
│   ├── skills.php         # Skills management
│   ├── projects.php       # Projects management
│   ├── awards.php         # Awards management
│   ├── contact.php        # Contacts management
│   ├── settings.php       # System settings
│   └── assets/admin.css   # Admin styles
└── docs/                  # Screenshots
```

## License

MIT
