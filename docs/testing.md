# Testing

## Struktur

```
tests/
├── Feature/
│   └── {Module}/
│       └── {ActionTest}.php     → Test full flow (HTTP → Action → DB)
└── TestCase.php
```

## Rules

1. Setiap module HARUS punya feature test — minimal CRUD + authorization.
2. Feature test = full flow — HTTP request → response, pakai database (SQLite in-memory).
3. Kalau ada perubahan di module, test-nya juga HARUS di-update.
4. Test harus cover: CRUD operations, validation, authorization (ownership check), edge cases.
5. Run tests: `php artisan test --testsuite=Feature`
6. Gunakan `RefreshDatabase` trait — setiap test class reset database.
7. Naming convention: `test_{action}_{context}` (e.g., `test_user_can_create_note`).
8. Auth di test: Pakai `$this->actingAs($user)`.
9. Setiap endpoint yang butuh auth harus ada test unauthenticated returns 401.
10. Setiap endpoint yang punya ownership check harus ada test returns 403 untuk user lain.
11. Factory pakai `guessFactoryNamesUsing` — taruh di `database/factories/`.
12. Jangan pakai raw SQL yang DB-specific di repository — pakai Laravel query builder (whereYear, whereMonth, dll) supaya compatible SQLite (test) + MariaDB (production).
