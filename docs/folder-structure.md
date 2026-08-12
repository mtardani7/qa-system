# Folder structure

`backend/app/Models` contains persistence models; `Repositories` contains database access; `Services` contains use cases; `Http/Requests`, `Http/Resources`, and `Http/Controllers/Api` form the REST boundary; `Policies`, `Notifications`, `Jobs`, and `Console/Commands` contain cross-cutting runtime behavior. `database/migrations` is the schema source of truth.

`frontend/app` contains routes and layouts. Feature-specific API services, hooks, types, and components live under `frontend/features`. Shared UI and providers are under `components`, `providers`, and `lib`.
