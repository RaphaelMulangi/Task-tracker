# Task Tracker

A task tracker with a calendar view, due-date reminders, and overdue tracking.
Notifications go out by **email** and **WhatsApp** (Meta Cloud API).

## Stack

- **Backend:** Laravel 13 (API-only, Sanctum token auth) — `backend/`
- **Frontend:** React + Vite + Tailwind CSS v4 — `frontend/`
- **Database:** SQLite (`backend/database/database.sqlite`) — no MySQL setup needed for local dev

## Running it locally

Backend (uses PHP 8.3, e.g. Laragon's bundled version):

```
cd backend
php artisan serve --port=8000
```

Frontend:

```
cd frontend
npm run dev
```

Then open http://localhost:5173.

### Reminders & overdue notifications

Reminder emails and overdue notifications are sent by the
`app:send-task-reminders` command, scheduled to run every minute
(see `backend/routes/console.php`). For it to actually fire, something needs
to run Laravel's scheduler continuously. In development, run:

```
cd backend
php artisan schedule:work
```

For a real deployment, add a single cron entry that runs
`php artisan schedule:run` every minute, and Laravel dispatches
`app:send-task-reminders` from there.

By default `MAIL_MAILER=log` in `backend/.env`, so reminder emails are written
to `backend/storage/logs/laravel.log` instead of actually being sent. Point
`MAIL_*` at a real SMTP provider (or something like Mailgun/SES) to send real
emails.

### WhatsApp setup

WhatsApp delivery uses Meta's Cloud API directly (no third-party middleman).
It's fully wired up in code (`app/Services/WhatsAppService.php`,
`app/Notifications/Channels/WhatsAppChannel.php`) but does nothing until you
add credentials:

1. Create a Meta app at [developers.facebook.com](https://developers.facebook.com)
   and add the **WhatsApp** product.
2. Under WhatsApp > API Setup, copy the temporary access token and the phone
   number ID, and add a recipient test number (Meta gives you a free test
   number + lets you add a few verified recipient numbers without needing
   business verification).
3. Set `WHATSAPP_TOKEN` and `WHATSAPP_PHONE_NUMBER_ID` in `backend/.env`.
4. Test it: `php artisan app:test-whatsapp 260971234567 "Hello from Task Tracker"`

That temporary token expires in 24h and free-text messages only reach
numbers inside a 24h conversation window — fine for testing. For production:
generate a permanent token via a system user in Meta Business Manager, get a
message template approved (Business Manager > WhatsApp Manager > Message
Templates), and set `WHATSAPP_TEMPLATE_NAME` so reminders can reach users
outside that 24h window.

A user only gets WhatsApp reminders if they've saved a WhatsApp number on
their Profile page — if `WHATSAPP_TOKEN` isn't set, sending is skipped
silently (logged as a debug line) rather than erroring.

## What's built

- Register/login (Sanctum token auth), per-user tasks
- Tasks: title, description, due date, reminder time, priority
- Status is computed, not just stored: **pending**, **completed**, or
  **overdue** (pending + due date in the past)
- Filterable task list (All / Pending / Completed / Overdue)
- Calendar view (month/week/day/agenda) with tasks color-coded by status
- In-app notification bell + reminder/overdue emails + WhatsApp messages
- Profile page to update name, email, and WhatsApp number

## Next steps

- Add real WhatsApp credentials (see above) and a permanent token + approved
  template for production use
- Point `MAIL_MAILER` at a real provider for production email delivery
- Deploy the scheduler (cron + `schedule:run`) somewhere persistent
