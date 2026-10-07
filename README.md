# Extraction Document

An AI-powered document extraction service — upload an invoice or contract and get structured, validated data back, with a human-in-the-loop review step for anything the AI isn't confident about.

## Problem

Manually transcribing data from invoices and contracts into structured records is slow and error-prone. This service automates the extraction using a multimodal LLM (Gemini), while treating AI output as untrusted input — validating it, flagging low-confidence results, and routing them to a human reviewer rather than blindly trusting whatever comes back.

## How it works

1. A user uploads a PDF document through a Vue (Inertia) interface.
2. The upload is saved and a background job is queued immediately — the user isn't blocked waiting on the AI call.
3. A queued worker (Laravel Horizon) sends the document to Gemini, which reads the PDF directly (multimodal input, no separate OCR step) and returns structured JSON matching a defined schema.
4. The extracted data, raw AI response, token usage, and latency are stored against the document.
5. *(Planned)* Low-confidence extractions are routed to a review screen where a human can correct fields before final approval.

## Current status

This project is under active development. Working today:
- PDF upload with validation
- Async extraction via Laravel Horizon + Redis queues
- Real multimodal extraction via the Gemini API (tested against real Italian-language invoices)
- Status tracking (`received` → `extracted`)

Not yet implemented (see Roadmap below):
- Confidence scoring and the review/approval workflow
- Retry/backoff and failed-job handling
- Cost-per-document tracking (token counts are captured; cost calculation is not yet wired up)
- Eval suite with measured accuracy
- Docker setup and CI

## Tech Stack

- **Backend**: Laravel 13
- **Frontend**: Vue 3 via Inertia.js
- **Queues**: Laravel Horizon (Redis)
- **AI**: Google Gemini API (multimodal — PDFs sent directly, no separate OCR)
- **Auth**: Laravel Breeze

## Roadmap

- [x] State machine: `received` → `extracted` → `validated` → `needs_review` → `approved`
- [x] Confidence scoring per extracted field
- [ ] Review screen for human correction of low-confidence fields
- [ ] Retry/backoff on extraction failures, with a failed-job path
- [ ] Cost-per-document calculation and observability (token usage, latency, cost)
- [ ] Eval suite: labeled document set, per-field accuracy reporting, CI regression detection
- [ ] Docker + CI
- [ ] Architecture Decision Records (ADRs) for key design choices (e.g. why queues, why Gemini over direct OCR, retry policy)

## Known Limitations

- Currently tested against invoice-type PDFs only; contract extraction is planned but unverified.
- No authorization check yet preventing a user from viewing another user's document by guessing its ID — planned via a Laravel Policy.
- Relies on Gemini's free tier, which is rate-limited; not yet built for production-scale throughput.

## Setup

### Requirements
- PHP 8.5+, Composer
- Node.js 20+ (22+ recommended)
- Redis
- A Gemini API key ([Google AI Studio](https://aistudio.google.com/apikey))

### Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Add to `.env`:
```
GEMINI_API_KEY=your-key-here
GEMINI_MODEL=gemini-flash-lite-latest
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
```

```bash
php artisan migrate
```

### Running locally

Run each of these in a separate terminal:

```bash
php artisan serve
npm run dev
php artisan horizon
```

Then visit `http://localhost:8000`, register an account, and upload a PDF invoice from `/documents`.

## Author

Barbara Palumbo
Backend & Full-Stack Software Developer

[LinkedIn](https://www.linkedin.com/in/barbara-palumbo-b3356a18b)

## License

This project is licensed under the [MIT License](LICENSE).