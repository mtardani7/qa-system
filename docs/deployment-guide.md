# QA Management System deployment

1. Copy `backend/.env.example` to `backend/.env` and set `APP_KEY`, strong database credentials, `APP_URL`, `DB_*`, `REDIS_HOST=redis`, `CACHE_STORE=redis`, and `QUEUE_CONNECTION=redis`.
2. Configure the frontend API base URL for the public hostname.
3. Run `docker compose build` and `docker compose up -d`.
4. Run migrations once: `docker compose exec backend php artisan migrate --force`.
5. Create the first administrator using the approved provisioning process; do not ship default credentials.
6. Verify `docker compose ps`, `/up`, queue processing, scheduler, database connectivity, and backup creation.

Backups are written to `storage/app/backups` by `qms:backup-database`. Mount that path to durable encrypted storage and test restoration regularly. Terminate TLS at Nginx or the load balancer, restrict PostgreSQL and Redis to the private network, rotate `APP_KEY` only with a planned session/token migration, and keep `APP_DEBUG=false` in production.
