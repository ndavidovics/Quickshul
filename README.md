# QuickShul

**Free, open-source synagogue management software.**

A multi-tenant SaaS platform — any shul can register at [quickshul.com](https://quickshul.com) and have a member portal running in minutes. If your shul already uses QuickBooks, import your members with one click.

---

## Features

- **Member & Family Management** — family records, Hebrew names, membership types, contact info
- **Pledge Tracking & Payments** — track open pledges, accept PayPal payments (per-shul merchant accounts)
- **Email Tools** — balance reminders and giving statements via each shul's own Gmail/Google Workspace account
- **Hebrew Calendar & Yahrtzeits** — minyan schedules, holiday overrides, automatic annual yahrtzeit reminders
- **QuickBooks Sync** — optional two-way sync with QuickBooks Online
- **Google Sign-In** — members log in with their Google accounts
- **Self-service registration** — any shul can register and be live in minutes

## Tech Stack

- Laravel 12 / PHP 8.3
- MySQL
- Multi-tenant: shared database, `tenant_id` on every table, subdomain-per-shul (`slug.quickshul.com`)
- Gmail API for email (per-tenant OAuth tokens)
- PayPal REST API (per-tenant credentials)
- QuickBooks Online API (optional, per-tenant)

---

## Setup

### Requirements

- PHP 8.2+, MySQL 8+, Composer
- Google Cloud project with Gmail API + People API enabled
- (Optional) PayPal developer account
- (Optional) QuickBooks developer account

### Installation

```bash
git clone https://github.com/ndavidovics/Quickshul.git
cd Quickshul
composer install
cp .env.example .env
php artisan key:generate
# Edit .env with your database and API credentials
php artisan migrate
```

### Key Environment Variables

| Variable | Description |
|---|---|
| `APP_URL` | Root domain, e.g. `https://quickshul.com` |
| `ROOT_DOMAIN` | Bare domain for subdomain extraction, e.g. `quickshul.com` |
| `DB_HOST / DB_DATABASE / DB_USERNAME / DB_PASSWORD` | MySQL connection |
| `GOOGLE_CLIENT_ID` | Google OAuth app client ID |
| `GOOGLE_CLIENT_SECRET` | Google OAuth app client secret |
| `GOOGLE_REDIRECT_URI` | Callback for member Google sign-in |
| `GMAIL_REDIRECT_URI` | Callback for admin Gmail connect |
| `QUICKBOOKS_*` | QuickBooks OAuth credentials (optional) |

---

## License

MIT
