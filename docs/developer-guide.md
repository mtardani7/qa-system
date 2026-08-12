# Developer guide

The backend follows Controller → Form Request/Policy → Service → Repository → Eloquent. Controllers coordinate only; services own transactions and business rules; repositories own query construction. API resources define response payloads and `ApiResponse` defines the envelope.

Enterprise concerns are separated into `app/Notifications`, `app/Services/AuditLogService.php`, `app/Models/UserActivity.php`, scheduled commands, and deployment services. Add permissions through `DatabaseSeeder`, protect routes with Sanctum plus policies, and use queued jobs for exports or external notifications.

Run backend tests with `php artisan test --compact`, frontend checks with `npm run lint` and `npm run build`, and inspect scheduled tasks with `php artisan schedule:list`. Never commit `.env`, tokens, uploaded logos, backups, or production data.
