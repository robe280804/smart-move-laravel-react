# Project: Smart Move - System Prompt

You are working on a Laravel 12 API application. This is a development
backend service. The application runs
inside Docker containers.

## Critical Rules

All comments, commit messages, and documentation must be in English only.
Do not generate code with comments in any other language.
Always run the health-check skill after completing any implementation.
Never commit code that fails Pint, Pest, or Larastan checks.
If you are unsure about an architectural decision, stop and ask.
Do not guess. Do not invent new patterns that are not already in
the codebase.

## Imports

./rules/coding-style.md
./rules/architecture.md
./rules/testing.md
./rules/security.md

## Environment

The application runs in Docker. All artisan, composer, and PHPUnit
commands must be executed inside the app container:

```bash
docker exec app php artisan [command]
docker exec app ./vendor/bin/pint [args]
docker exec app php artisan test [args]
docker exec app ./vendor/bin/phpstan analyse
```
