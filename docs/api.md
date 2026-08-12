# Sprint 5 API additions

All routes require `Authorization: Bearer <sanctum-token>` and use the existing `{success,message,data}` envelope.

`/api/v1/companies`, `/departments`, `/holidays`, and `/system-settings` provide paginated REST CRUD. Use `search`, `page`, `per_page`, `sort`, and `direction` where applicable. `/api/v1/notifications` lists the authenticated user's notifications; POST `/notifications/{id}/read` and `/notifications/read-all` update read state. `/api/v1/audit-logs` and `/api/v1/user-activities` are permission-protected operational read APIs.

Enterprise permissions are `enterprise.view`, `enterprise.manage`, and `audit.view`. Assign them to roles through the seeder or an administrative provisioning workflow.
