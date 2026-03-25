# Security Audit & Monitoring System

## Overview

The application implements an event-driven security monitoring system that logs security-relevant activity to a dedicated log channel and sends throttled email alerts for critical events.

All security events flow through a single pipeline:

```
Controller / Exception Handler / Job
  └─ dispatches Event (FailedLogin, SecurityAlert, AdminAction)
       └─ HandleSecurityEvent listener (queued)
            ├─ Logs to security channel (always)
            └─ Sends SecurityAlertNotification (only for critical events, throttled)
```

## Security Log Channel

**File:** `storage/logs/security.log` (daily rotation, 90-day retention)

**Configuration:** `config/logging.php` → `security` channel

```env
SECURITY_LOG_LEVEL=info   # minimum level to log
```

**Log format:**

```
[SECURITY] WARNING | failed_login | IP: 192.168.1.1 | User: guest | Email: attacker@example.com
[SECURITY] INFO    | password_change | IP: 10.0.0.5 | User: 42 | User changed their password.
[SECURITY] ERROR   | ai_generation_failure | IP: queue | User: 7 | Workout plan 15 generation failed: timeout
[SECURITY] INFO    | admin_action | IP: 10.0.0.1 | User: 1 | Action: delete_user | Target: 99 | Admin deleted user (email: user@example.com).
```

## Events

### `FailedLogin`

| Field   | Type                | Description                      |
| ------- | ------------------- | -------------------------------- |
| `email` | `string`            | Email address that was attempted |
| `ip`    | `string`            | Client IP address                |
| `type`  | `SecurityEventType` | Always `failed_login`            |

**Dispatched from:** `AuthController::login()` — when credentials do not match.

### `SecurityAlert`

| Field     | Type                | Description                     |
| --------- | ------------------- | ------------------------------- |
| `type`    | `SecurityEventType` | Event category (see enum below) |
| `ip`      | `string`            | Client IP or `'queue'` for jobs |
| `userId`  | `?int`              | Authenticated user ID, or null  |
| `details` | `string`            | Human-readable context          |

**Dispatched from:**

- `ChangePasswordController` — type `password_change`
- `UserController::destroy()` — type `account_deletion`
- `GenerateWorkoutPlanJob::failed()` — type `ai_generation_failure`
- `bootstrap/app.php` exception handler — type `forbidden_access` (403) or `unhandled_exception`

### `AdminAction`

| Field          | Type                | Description                                |
| -------------- | ------------------- | ------------------------------------------ |
| `adminId`      | `int`               | Admin user performing the action           |
| `action`       | `string`            | Action name (`update_user`, `delete_user`) |
| `ip`           | `string`            | Client IP address                          |
| `targetUserId` | `?int`              | User being acted upon                      |
| `details`      | `string`            | Additional context                         |
| `type`         | `SecurityEventType` | Always `admin_action`                      |

**Dispatched from:** `AdminUserController::update()` and `AdminUserController::destroy()`

## SecurityEventType Enum

```php
enum SecurityEventType: string
{
    case FailedLogin          = 'failed_login';
    case PasswordChange       = 'password_change';
    case AccountDeletion      = 'account_deletion';
    case AdminAction          = 'admin_action';
    case ForbiddenAccess      = 'forbidden_access';
    case UnhandledException   = 'unhandled_exception';
    case AiGenerationFailure  = 'ai_generation_failure';
    case AiCreditsExhausted   = 'ai_credits_exhausted';
}
```

## Email Alerts

**Notification class:** `SecurityAlertNotification` (queued)

**Recipient:** `ADMIN_EMAIL` env variable → `config('app.admin_email')`

### Which events trigger email

Only **critical** event types:

| Event Type              | Severity | Sends Email |
| ----------------------- | -------- | ----------- |
| `failed_login`          | Warning  | No          |
| `password_change`       | Info     | No          |
| `account_deletion`      | Warning  | No          |
| `admin_action`          | Info     | No          |
| `forbidden_access`      | Warning  | Yes         |
| `unhandled_exception`   | Error    | Yes         |
| `ai_generation_failure` | Error    | Yes         |
| `ai_credits_exhausted`  | Error    | Yes         |

### Throttling

Emails are throttled using `Cache::add()` with a **30-minute** TTL per event type.

This means: if 100 forbidden access errors happen in 5 minutes, only the first one triggers an email. The next email for the same event type can be sent after 30 minutes.

Cache key format: `security_alert_throttle:{event_type_value}`

### Email content

Subject: `[SECURITY] SmartMove — {event_type}`

Body includes:

- Event type
- Details string
- Timestamp

## Listener: HandleSecurityEvent

**Class:** `App\Listeners\HandleSecurityEvent`
**Queue:** Yes (`ShouldQueue`)
**Handles:** `FailedLogin | SecurityAlert | AdminAction` (union type, registered explicitly via `Event::listen()`)

### Log level mapping

| Event Type              | Log Level |
| ----------------------- | --------- |
| `failed_login`          | `warning` |
| `password_change`       | `info`    |
| `account_deletion`      | `warning` |
| `admin_action`          | `info`    |
| `forbidden_access`      | `warning` |
| `unhandled_exception`   | `error`   |
| `ai_generation_failure` | `error`   |
| `ai_credits_exhausted`  | `error`   |

## Configuration Checklist

```env
# Required for email alerts
ADMIN_EMAIL=admin@yourdomain.com

# Optional — defaults to 'info'
SECURITY_LOG_LEVEL=info

# Required for throttle cache (already in place)
CACHE_STORE=redis
```

## File Inventory

| File                                              | Role                                |
| ------------------------------------------------- | ----------------------------------- |
| `app/Enums/SecurityEventType.php`                 | Event type enum                     |
| `app/Events/FailedLogin.php`                      | Failed login event                  |
| `app/Events/SecurityAlert.php`                    | General security event              |
| `app/Events/AdminAction.php`                      | Admin action event                  |
| `app/Listeners/HandleSecurityEvent.php`           | Queued listener — logs + notifies   |
| `app/Notifications/SecurityAlertNotification.php` | Email notification                  |
| `config/logging.php`                              | `security` channel definition       |
| `bootstrap/app.php`                               | Exception handler (403 + unhandled) |
| `app/Providers/AppServiceProvider.php`            | Event-listener registration         |

## Dispatch Points

| Controller / Job                       | Event           | Type                    |
| -------------------------------------- | --------------- | ----------------------- |
| `AuthController::login()`              | `FailedLogin`   | `failed_login`          |
| `ChangePasswordController::__invoke()` | `SecurityAlert` | `password_change`       |
| `UserController::destroy()`            | `SecurityAlert` | `account_deletion`      |
| `AdminUserController::update()`        | `AdminAction`   | `admin_action`          |
| `AdminUserController::destroy()`       | `AdminAction`   | `admin_action`          |
| `GenerateWorkoutPlanJob::failed()`     | `SecurityAlert` | `ai_generation_failure` or `ai_credits_exhausted` |
| Exception handler (403)                | `SecurityAlert` | `forbidden_access`      |
| Exception handler (other)              | `SecurityAlert` | `unhandled_exception`   |
