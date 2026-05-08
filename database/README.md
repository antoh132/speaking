# SpeakOn! — Database

## Files

| File | Purpose |
|---|---|
| `schema.sql` | Full DDL: CREATE DATABASE, all tables, indexes, and seed data |
| `seed.sql` | Seed data only (5 levels + default superadmin) |
| `migrate.php` | CLI migration runner |

## Quick Setup (phpMyAdmin)

1. Open `http://localhost/phpmyadmin`
2. Click **Import** → choose `database/schema.sql`
3. Click **Go**

The script creates `speakon_db` and all tables automatically.

## Quick Setup (CLI)

```bash
# From project root — run schema + seed data
php database/migrate.php --seed

# Drop and recreate from scratch
php database/migrate.php --fresh --seed
```

## Default Super Admin

| Field | Value |
|---|---|
| Email | `admin@speakon.id` |
| Password | `Admin@SpeakOn2024!` |
| Role | `superadmin` |

> **IMPORTANT:** Change the default password immediately after first login.

## MySQL App User (Req 9.6 — Append-Only Audit Log)

After importing the schema, create a restricted MySQL user for the application:

```sql
CREATE USER IF NOT EXISTS 'speakon_app'@'localhost'
    IDENTIFIED BY 'your_strong_password_here';

GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.users             TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.refresh_tokens    TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.login_attempts    TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.account_lockouts  TO 'speakon_app'@'localhost';
GRANT SELECT                         ON speakon_db.levels            TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.level_materials   TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.student_level_progress TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.recordings        TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.feedback          TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON speakon_db.notifications     TO 'speakon_app'@'localhost';
GRANT SELECT, INSERT                 ON speakon_db.audit_logs        TO 'speakon_app'@'localhost';
-- No UPDATE or DELETE on audit_logs — enforces append-only (Requirement 9.6)

FLUSH PRIVILEGES;
```

Then update `api/config/config.php` (or your `.env`) to use `speakon_app` credentials.
