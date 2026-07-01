# Hi.Events Development Setup Guide

This guide covers local development setup for Hi.Events without Docker.

## Prerequisites

Before you start, ensure you have the following installed:

- **Node.js** (v18+) - [Download](https://nodejs.org)
- **PHP** (v8.2+) - [Download](https://www.php.net/downloads)
- **Composer** (v2.x) - [Download](https://getcomposer.org/download)
- **MySQL/PostgreSQL** - Database server (configure in `backend/.env`)

### Verify Installation

```bash
node --version     # Should be v18+
php --version      # Should be 8.2+
composer --version # Should be 2.x
npm --version      # Should be 8+
```

## Quick Start

### 1. Clone the Repository

```bash
git clone https://github.com/NielSyntactics/Hi.Events.git
cd Hi.Events
```

### 2. Install Dependencies

Install both frontend (npm) and backend (composer) dependencies concurrently:

```bash
npm run install:all
```

This runs:
- `cd frontend && npm install`
- `cd backend && composer install`

**Note:** If you have PHP 8.5.3, the composer installation will automatically configure platform compatibility.

### 3. Setup Environment Files

Copy environment templates:

```bash
# Backend
cp backend/.env.example backend/.env

# Frontend
cp frontend/.env.example frontend/.env
```

Edit `backend/.env` and update:
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `APP_KEY` (run `php artisan key:generate` if not set)
- Any API keys (Stripe, Sentry, etc.)

**Note:** Frontend `.env` is already configured for local HTTP development (not HTTPS).

### 4. Setup Database

Run fresh migrations and generate domain objects:

```bash
npm run migrate:fresh
```

This:
- Resets the database
- Runs all migrations
- Regenerates Laravel domain objects

## Running Development Servers

### Option A: Run Everything Together

```bash
npm run dev:full
```

Starts concurrently:
- Frontend (React SSR) on `http://127.0.0.1:5678`
- Backend (Laravel) on `http://127.0.0.1:8000`
- Queue worker (async jobs)

### Option B: Run Backend Only

```bash
npm run dev:backend
```

Starts:
- Laravel server on `http://127.0.0.1:8000`
- Queue worker

### Option C: Run Frontend Only

```bash
cd frontend && npm run dev:ssr
```

Starts React frontend on `http://127.0.0.1:5678`

## Development Workflows

### Running Tests

**Backend Unit Tests:**
```bash
cd backend && php artisan test --testsuite=Unit
```

**Backend Single Test:**
```bash
cd backend && php artisan test --filter=TestName
```

**Code Quality:**
```bash
cd backend && ./vendor/bin/pint --test
```

### Database Migrations

**Create a Migration:**
```bash
cd backend && php artisan make:migration create_xxx_table
```

**Run Migrations:**
```bash
cd backend && php artisan migrate
```

**Reset Database:**
```bash
npm run migrate:fresh
```

**After migrations, regenerate domain objects:**
```bash
cd backend && php artisan generate-domain-objects
```

### Frontend Development

**TypeScript Validation:**
```bash
cd frontend && npx tsc --noEmit
```

**Extract Translatable Strings:**
```bash
cd frontend && npm run messages:extract
```

**Compile Translations:**
```bash
cd frontend && npm run messages:compile
```

## Project URLs

| Service | URL |
|---------|-----|
| Frontend | http://127.0.0.1:5678 |
| Backend API | http://127.0.0.1:8000 |
| Login | http://127.0.0.1:5678/auth/login |
| Register | http://127.0.0.1:5678/auth/register |

## Architecture Overview

### Backend (Laravel + DDD)

- **Routes:** `backend/routes/api.php`
- **Actions:** `backend/app/Http/Actions/` - HTTP request handlers
- **Services:** `backend/app/Services/` - Domain logic
- **Repositories:** `backend/app/Repositories/` - Data access
- **Domain Objects:** `backend/app/DomainObjects/` - Auto-generated, never edit manually
- **Tests:** `backend/tests/`

**Request Flow:** `Action → Handler → Domain Service → Repository`

### Frontend (React + Vite)

- **Components:** `frontend/src/components/`
- **Queries:** `frontend/src/queries/` - React Query data fetching
- **Mutations:** `frontend/src/mutations/` - React Query mutations
- **Pages:** `frontend/src/routes/`
- **API Client:** `frontend/src/api/`

## Common Issues & Troubleshooting

### PHP 8.5.3 Dependency Errors

If you see package version conflicts with PHP 8.5.3, the platform compatibility has been configured in `backend/composer.json`. No action needed.

### Frontend 404 Errors

Ensure the backend server is running:
```bash
npm run dev:full
```

The frontend expects the API at `http://127.0.0.1:8000/api/`.

### Database Connection Errors

Verify your `backend/.env` has correct database credentials:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hievents
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then run migrations:
```bash
npm run migrate:fresh
```

### Queue Worker Errors

The queue worker (started with `npm run dev:full`) processes background jobs. Check `backend/storage/logs/` for errors.

## Recent Changes

### New npm Scripts (v1.0)

Added convenience scripts for faster development:

- **`install:all`** - Install frontend + backend dependencies concurrently
- **`dev:backend`** - Run Laravel server + queue worker
- **`dev:full`** - Run frontend + backend services together
- **`migrate:fresh`** - Reset database with fresh migrations

### PHP 8.5.3 Support

Updated `backend/composer.json` to support PHP 8.5.3 through platform configuration.

### Frontend URLs

Changed from HTTPS to HTTP for local development:
- `VITE_API_URL_CLIENT=http://127.0.0.1:8000/api`
- `VITE_FRONTEND_URL=http://127.0.0.1:5678`

## Additional Resources

- [CLAUDE.md](CLAUDE.md) - Coding standards and conventions
- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev)
- [Vite Documentation](https://vitejs.dev)

## Getting Help

If you encounter issues:

1. Check this guide's troubleshooting section
2. Check `backend/storage/logs/` for Laravel errors
3. Open browser DevTools (F12) for frontend errors
4. Review related git commits for recent changes

---

**Happy coding!** 🚀
