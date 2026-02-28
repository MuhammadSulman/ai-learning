# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 application that integrates with the OpenAI API (GPT-4o-mini). Two main features:
- **Web Chat**: Blade-based chat UI at `/` that sends prompts to OpenAI and displays responses
- **Email Generator API**: REST endpoint at `POST /api/email/generate` with streaming (SSE) support

## Development Commands

```bash
# Start all services (Sail + Vite + queue + logs)
composer dev

# Run with Sail explicitly
./vendor/bin/sail up -d

# Run tests
composer test
# or directly:
php artisan test

# Run a single test
php artisan test --filter=TestClassName
php artisan test --filter=test_method_name

# Lint / format code
./vendor/bin/pint

# Run migrations
php artisan migrate

# Initial setup (install deps, generate key, migrate, build frontend)
composer setup
```

## Docker / Sail

The app uses Laravel Sail with `compose.yaml`. Services:
- **laravel.test**: PHP 8.5 app container (port 80, configurable via `APP_PORT`)
- **mysql**: MySQL 8.4 (port 3306, forwarded port configurable via `FORWARD_DB_PORT`)

Database: `ai_learning` with MySQL credentials configured in `.env`.

## Architecture

### OpenAI Integration
- Uses `openai-php/laravel` package — access via `OpenAI::chat()->create()` facade
- Config in `config/openai.php`, requires `OPENAI_API_KEY` env var
- All calls use `gpt-4o-mini` model

### Request Flow

**Chat (web):** `routes/web.php` → `ChatController` → OpenAI facade directly → logs to `chat_logs` table → renders `chat.blade.php`

**Email (API):** `routes/api.php` → `EmailController` → `EmailGeneratorService` → OpenAI facade → logs to `email_logs` table → JSON response (or SSE stream)

### Key Patterns
- `EmailGeneratorService` in `app/Services/` handles both standard and streamed OpenAI calls; streaming uses SSE (`text/event-stream`)
- `GenerateEmailRequest` form request provides validation with defaults (temperature: 0.7, max_tokens: 1024, stream: false)
- `EmailLog::calculateCost()` computes token costs using GPT-4o-mini pricing ($0.15/1M input, $0.60/1M output)
- Both features log all interactions (including errors) to their respective tables for usage tracking
