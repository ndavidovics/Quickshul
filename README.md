# YIOM Member Portal

The member and administrative portal for **Young Israel of Memphis**, built with Laravel 12.

**Production:** https://portal.yiom.org

---

## Overview

A private web application that serves two audiences:

- **Members** — view family info, manage profiles, see pledge and payment history, make payments via PayPal
- **Admins** — manage the full membership database, sync with QuickBooks, send email reminders, manage yahrtzeits, and review membership applications

---

## Features

### Member Portal
- Email/password and Google OAuth login
- Dashboard with outstanding balance and donation summary
- Family profile editing (contact info, family members, Hebrew names)
- Pledge and payment history with AJAX pagination
- PayPal payments for outstanding balances and general donations
- Account settings / password management

### Admin Panel
- **Members** — searchable, filterable member list; full family detail view with financials, family members, yahrtzeits, pledge/payment history, audit log; CSV export
- **Yahrtzeits** — global listing with search and date-of-death filter; add/edit/delete; CSV export with Hebrew (UTF-8 BOM for Excel compatibility)
- **Financials** — separate payments and pledges pages with search, date range filter, and CSV export
- **Email Center** — send balance reminders and annual giving statements; per-family previews; CC support for all family emails; paginated send history
- **Applications** — review public membership applications; approve (auto-creates family/user records and pushes to QuickBooks) or reject with notes
- **QuickBooks Sync** — bidirectional sync; delta (update) and full (forced) modes; conflict detection and resolution; automated nightly sync at 2 AM CST
- **User Management** — toggle admin access

### QuickBooks Integration
- OAuth2 connection
- Pulls customers, invoices (pledges), payments, and sales receipts
- Pushes new customers and profile updates
- Delta sync uses `Metadata.LastUpdatedTime`; pledge balances are refreshed after payment sync to handle QB's behavior of not updating invoice timestamps when payments are applied
- Automated nightly sync runs inline via Laravel scheduler (no queue worker required)

### Public Pages
- `/apply` — membership application form with membership level selection, dynamic family member rows, Hebrew name support with keyboard overlay
- `/pay/{token}` — token-based payment page for members without a portal login

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.3) |
| Database | MySQL |
| Auth | Laravel Auth + Google OAuth (Socialite) |
| Payments | PayPal Orders API (JS SDK) |
| Accounting | QuickBooks Online API |
| Email | Brevo (transactional SMTP) |
| Hebrew dates | Hebcal API |
| Server | Nginx + PHP-FPM, Ubuntu |
| SSL | Let's Encrypt (Certbot) |

---

## Local Setup

```bash
git clone <repo>
cd portal
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed   # if seeders exist
php artisan serve
```

Required `.env` values:
- `DB_*` — database connection
- `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI` — Google OAuth
- `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_MODE` — PayPal
- `QB_CLIENT_ID`, `QB_CLIENT_SECRET`, `QB_REDIRECT_URI`, `QB_ENVIRONMENT` — QuickBooks
- `MAIL_*` — Brevo SMTP credentials

---

## Scheduled Tasks

The Laravel scheduler must be registered in cron (already configured on the production server):

```
* * * * * www-data php /var/www/portal/artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs:
- **2:00 AM CST daily** — QuickBooks incremental sync (pulls changes since last sync)

---

## Key Directories

```
app/Http/Controllers/Admin/   — admin controllers
app/Http/Controllers/         — member-facing controllers
app/Jobs/DailyQuickBooksSync  — nightly QB sync job
app/Services/QuickBooksService.php
app/Services/EmailReminderService.php
app/Services/HebrewDateService.php
resources/views/admin/        — admin Blade views
resources/views/member/       — member portal Blade views
resources/views/apply/        — public membership application
resources/views/emails/       — email templates
routes/web.php                — all routes
routes/console.php            — scheduler definition
```
