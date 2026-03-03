# Backend - Smart Move

A Laravel 12 REST API backend with token-based authentication using Laravel Sanctum.

---

## Stack

| Layer            | Technology                     |
| ---------------- | ------------------------------ |
| Runtime          | PHP 8.4 (FPM Alpine)           |
| Framework        | Laravel 12                     |
| Authentication   | Laravel Sanctum 4 (dual-token) |
| Database         | MySQL 8                        |
| Cache / Queue    | Redis 7                        |
| Web Server       | Nginx 1.27                     |
| Mail (dev)       | Mailpit                        |
| DTOs             | Spatie Laravel Data 4          |
| Testing          | PHPUnit 11                     |
| Formatter        | Laravel Pint                   |
| Containerization | Docker + Docker Compose        |

---

## Architecture

The application follows a layered architecture:

```
Request → FormRequest (validation)
        → Controller (thin, delegates)
        → Service (business logic)
        → Repository (data access)
        → Eloquent Model
        → API Resource (response)
```

Key patterns used:

- **Repository pattern** — `UserRepositoryInterface` / `UserRepository`
- **Service layer** — `AuthService`, `UserService`
- **DTOs** — `UserDto` via Spatie Laravel Data
- **Events / Listeners** — `UserRegistration` triggers email workflows
- **Form Requests** — all validation is centralized
- **API Resources** — consistent response shape via `UserResource`
- **Enums** — `TokenAbility` for Sanctum token abilities

---

## Docker Services

| Container | Image                | Exposed Port           | Purpose               |
| --------- | -------------------- | ---------------------- | --------------------- |
| `app`     | PHP 8.4 FPM (custom) | 9000                   | Application runtime   |
| `nginx`   | nginx:1.27-alpine    | 8000                   | Reverse proxy         |
| `db`      | MySQL 8              | 3307                   | Database              |
| `redis`   | redis:7.4-alpine     | 6379                   | Cache & queue backend |
| `queue`   | PHP 8.4 FPM (custom) | —                      | Queue worker          |
| `mailpit` | axllent/mailpit      | 8025 (UI), 1025 (SMTP) | Email testing         |

All services share the `smart_move_network` bridge network.

---

## Setup

### Prerequisites

- Docker and Docker Compose installed

### 1. Clone and navigate to the backend

```bash
cd backend
```

### 2. Copy the environment file

```bash
cp .env.example .env
```

Edit `.env` and set the required variables (see [Environment Variables](#environment-variables) below).

### 3. Start the containers

```bash
docker compose -f docker/docker-compose.dev.yml up -d --build
```

### 4. Install dependencies

```bash
docker exec app composer install
```

### 5. Generate the application key

```bash
docker exec app php artisan key:generate
```

### 6. Run migrations

```bash
docker exec app php artisan migrate
```

The API is now available at `http://localhost:8000/api/v1/`.

---

## Environment Variables

```dotenv
APP_NAME=Laravel
APP_ENV=local
APP_KEY=                          # generated via php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=backend
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum token lifetimes (in minutes)
ACCESS_TOKEN_EXPIRATION_TIME=1440    # 24 hours
REFRESH_TOKEN_EXPIRATION_TIME=10080  # 7 days
```

---

## Auth Flow

Authentication uses Laravel Sanctum with a **dual-token** strategy:

- **Access token** — short-lived (24h), sent in the `Authorization: Bearer` header on every request.
- **Refresh token** — long-lived (7d), stored in a secure HTTP-only cookie (`refreshToken`), never exposed to JavaScript.

### Token Abilities

| Ability              | Value                | Used for                    |
| -------------------- | -------------------- | --------------------------- |
| `ACCESS_API`         | `access-api`         | All protected API endpoints |
| `ISSUE_ACCESS_TOKEN` | `issue-access-token` | Refresh endpoint only       |

### Endpoints

All routes are prefixed with `/api/v1`.

| Method | Path                             | Auth           | Description                |
| ------ | -------------------------------- | -------------- | -------------------------- |
| `POST` | `/auth/register`                 | —              | Register a new user        |
| `POST` | `/auth/login`                    | —              | Login and receive tokens   |
| `GET`  | `/auth/email/verify/{id}/{hash}` | signed URL     | Verify email address       |
| `POST` | `/auth/reset-password`           | —              | Send a password reset link |
| `POST` | `/auth/update-password`          | —              | Apply a password reset     |
| `POST` | `/refresh-token`                 | refresh cookie | Get a new access token     |

### Token lifecycle

```
POST /auth/register  or  POST /auth/login
        │
        ▼
AuthService::generateTokens()
        │
        ├─ Access token  (expires in 24h)  ──► returned in response body (meta.accessToken)
        └─ Refresh token (expires in 7d)   ──► set as HTTP-only cookie (refreshToken)
        │
        ▼
Client stores the access token and attaches it to every request:
    Authorization: Bearer <accessToken>
        │
        ▼
Access token expires
        │
        ▼
POST /refresh-token  (cookie sent automatically by the browser)
        │
SetBearerTokenFromCookie middleware reads the cookie
and injects it as the Authorization header
        │
        ▼
Sanctum validates ability:issue-access-token
        │
        ▼
AuthService::refreshToken()
    ├─ Deletes all existing tokens for the user
    └─ Issues a new access token + refresh token pair
        │
        ▼
New access token returned in body, new cookie set
```

### Response structure

Successful login / register response:

```json
{
    "data": {
        "user": {
            "id": 1,
            "name": "John",
            "surname": "Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2026-03-03T10:00:00.000000Z",
            "updated_at": "2026-03-03T10:00:00.000000Z"
        }
    },
    "meta": {
        "accessToken": "<token>",
        "accessTokenExpiresAt": "2026-03-04T10:00:00+00:00"
    },
    "message": null
}
```

The refresh token is set as a `Set-Cookie` header and is never included in the JSON body.

### Email verification

After registration, a `UserRegistration` event is fired which triggers two listeners:

- `SendVerifyAccountEmail` (synchronous) — sends a signed verification link valid for 60 minutes.
- `SendWelcomeEmail` (queued) — sends a welcome email via the queue worker.

### Password reset

1. Client calls `POST /auth/reset-password` with `{ "email": "..." }`.
2. Backend sends a reset link to `FRONTEND_URL/reset-password?token=X&email=Y`.
3. Frontend collects the new password and calls `POST /auth/update-password` with `{ token, email, password, password_confirmation }`.
4. Backend resets the password and invalidates all existing tokens for that user.

---

## Useful Commands

Run all commands inside the app container:

```bash
# Run tests
docker exec app php artisan test --compact

# Run a specific test file
docker exec app php artisan test --compact tests/Feature/AuthTest.php

# Format code
docker exec app ./vendor/bin/pint --dirty

# Tail logs
docker exec app php artisan pail

# Open a Tinker shell
docker exec -it app php artisan tinker
```

---

## Mail Testing

Mailpit captures all outgoing emails in development.
Open the Mailpit UI at `http://localhost:8025` to inspect sent emails.
