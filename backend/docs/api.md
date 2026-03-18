# API Documentation

## Rate Limiting

Rate limiting is implemented via Laravel's `RateLimiter` facade and applied per route group using the `throttle:` middleware.
Counters are stored in **Redis** (`CACHE_STORE=redis`).

All limits are enforced **per authenticated user ID**, falling back to **IP address** for unauthenticated routes.
When a limit is exceeded the API returns `HTTP 429 Too Many Requests` with a `Retry-After` header.

### Limiters

| Name             | Limit       | Keyed by     | Applied to                                                                       |
| ---------------- | ----------- | ------------ | -------------------------------------------------------------------------------- |
| `auth`           | 10 req/min  | IP           | `POST /auth/register`, `POST /auth/login`, `GET /auth/email/verify/…`            |
| `password-reset` | 5 req/min   | IP           | `POST /auth/reset-password`, `POST /auth/update-password`                        |
| `token-refresh`  | 20 req/min  | user ID / IP | `POST /refresh-token`                                                            |
| `api`            | 120 req/min | user ID / IP | All standard authenticated CRUD routes                                           |
| `ai-generation`  | 5 req/min   | user ID / IP | `POST /agent/generate-workout`                                                   |
| `payments`       | 10 req/min  | user ID / IP | `GET /payments/plan`, `POST /payments/checkout`, `POST /payments/billing-portal` |

### Rationale

- **`auth` (10/min)** — Prevents brute-force attacks on login and registration.
- **`password-reset` (5/min)** — Extra tight because each request triggers an email delivery.
- **`token-refresh` (20/min)** — Called silently on every page load; allows normal SPA usage while blocking abuse.
- **`api` (120/min)** — Covers all standard reads and mutations; generous enough for normal usage.
- **`ai-generation` (5/min)** — The most expensive endpoint (LLM call). Strictly limited regardless of subscription plan. Plan-level generation quotas (3 total / 10 per month / 20 per month) are enforced separately at the service layer.
- **`payments` (10/min)** — Prevents checkout and billing-portal redirect spam.

### Configuration

Limiters are defined in `app/Providers/AppServiceProvider::configureRateLimiting()`.

Redis must be available and `CACHE_STORE=redis` must be set in `.env`.

```
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```
