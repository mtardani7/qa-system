# Enterprise ERD

```mermaid
erDiagram
  companies ||--o{ plants : owns
  companies ||--o{ departments : owns
  companies ||--o{ holidays : defines
  companies ||--o{ system_settings : configures
  plants ||--o{ departments : contains
  plants ||--o{ holidays : observes
  users ||--o{ audit_logs : creates
  users ||--o{ user_activities : performs
  users ||--o{ notifications : receives
  audit_logs }o--|| users : user
```
