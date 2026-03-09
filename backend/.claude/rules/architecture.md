## Api structure

All endpoints are versioned under /api/v1/.
Authentication uses Laravel Sanctum with token-based auth.
Responses always use API Resources, never raw model output.
Pagination follows Laravel default with per_page parameter.

## Key Conventions

Single-action controllers for non-CRUD endpoints
Resource controllers for standard CRUD
FormRequests for all validation, no inline rules in controllers
Services for business logic, injected via constructor
Respository for model access
Enums for all status and type fields
Events + Listeners for side effects (notifications, logging)
Jobs for anything that takes more than 200ms
Policies for all authorization checks

## Controller Pattern

Controllers should be thin. They validate, delegate to a service, and return a response.
Single-action controllers are preferred for non-CRUD endpoints.

## Service Layer

Services contain business logic. They are injected via constructor. Services should not depend on Request or other HTTP-layer objects. Accept only validated data (arrays or DTOs), return models or results.

### Architectural Guardrails

- If a method exceeds ~30 lines, it is doing too much. Extract to a private method or a dedicated class.
- Controllers must never contain `if` branches for business logic. Move them to the Service.
- Services must never import `Request`, `FormRequest`, or any HTTP-layer class.
- Repositories must never contain business logic. They only query and return Eloquent models/collections.
- Jobs must implement `ShouldQueue` for anything that could block the HTTP response.

### Docker Command Prefix

All PHP commands run inside the container. Always prefix with:

```bash
docker exec smart_move_app_container php artisan [command]
docker exec smart_move_app_container ./vendor/bin/pint --dirty --format agent
docker exec smart_move_app_container php artisan test --compact [args]
docker exec smart_move_app_container ./vendor/bin/phpstan analyse
```

### Before Finalizing Any Change

1. Run `pint --dirty` to fix code style.
2. Run the affected tests to confirm they pass.
3. Confirm no new `DB::` calls, raw `env()` calls, or inline validation were introduced.
4. Confirm every new public method has a return type declaration.
