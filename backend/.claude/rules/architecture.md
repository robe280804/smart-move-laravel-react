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
