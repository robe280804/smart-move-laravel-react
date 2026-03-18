# Project: Smart Move - System Prompt

You are working on a Laravel 12 API application. This is a development
backend service

### Context & Discovery

- Always read sibling files before creating a new one. Pattern-matching existing code is more reliable than inventing structure.
- Read the relevant Service, Repository, and FormRequest before touching a Controller. Understand the full call chain first.
- When adding a feature, identify all layers it touches (route → controller → request → service → repository → resource) before writing any code.
- Prefer `Grep` and `Glob` over guessing file locations. Never assume a class exists without verifying it.

Bash(where php 2>&1 || echo "not found")
⎿  C:\Users\UTENTE\.config\herd\bin\php.bat
● Bash(cd "C:\Users\UTENTE\IdeaProjects\laravel\smart-move-laravel-react \backend" && php.bat vendor/bin/pint app/Providers/AppServiceProvider.php routes/api.php 2>&1) ⎿  { "result": "pass" }

## Critical Rules

All comments, commit messages, and documentation must be in English only.
Do not generate code with comments in any other language.
Always run the health-check skill after completing any implementation.
Never commit code that fails Pint, Pest, or Larastan checks.
If you are unsure about an architectural decision, stop and ask.
Do not guess. Do not invent new patterns that are not already in
the codebase.

- Never generate inline validation. Always create a FormRequest: ` php artisan make:request`.
- Never return raw model data from a controller. Always generate an API Resource: ` php artisan make:resource`.
- Never use `env()` directly in code. Always reference `config()`.
- Never use `DB::` raw queries. Use `Model::query()` with Eloquent.
- Always add return type declarations. No untyped methods.

### Incremental Work

- Make one focused change at a time. Do not refactor surrounding code unless explicitly asked.
- After each logical step (controller, service, test), verify it compiles and passes before moving on.
- Run only the affected tests after each change: ` php artisan test --compact --filter=TestName`.

## Imports

./rules/coding-style.md
./rules/architecture.md
./rules/testing.md
./rules/security.md
