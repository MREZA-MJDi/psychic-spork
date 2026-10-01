# Janan Foundation Audit

Branch: `foundation/architecture-database-2026-09-30`

## Working rules

- Prefer the simplest architecture that correctly represents the use case.
- Do not introduce a service, repository, DTO, event, listener, or job without a concrete complexity/reuse/integration reason.
- Keep core catalog relationships explicit.
- Use polymorphic relations for genuinely shared resources such as media.
- Fix schema, relation shape, indexes, query shape, eager loading, pagination, and concurrency before adding cache.
- Keep external integrations behind explicit application/integration boundaries.
- Preserve transaction boundaries around multi-write business operations.

## Audit order

1. Database schema and migrations
2. Model relationships, casts, scopes, and N+1 risks
3. Public catalog and product queries
4. Cart and checkout queries
5. Admin catalog/order/inventory queries
6. FormRequest validation and authorization boundaries
7. Real use-case Actions/Services
8. Jobs, events, notifications, and failure handling
9. External integrations
10. Production, deployment, rollback, queues, scheduler, and observability

## Known findings to verify

- Product creation currently permits a nullable SKU, so controller code must never read the validated SKU as a guaranteed array key.
- Product slug generation is expected when slug input is empty and must remain collision-safe.
- Admin product listing eager-loads full variants and gallery media; verify whether the listing view actually needs all columns/rows before optimizing it.
- Authorization currently relies heavily on `is_admin`; this is sufficient only for the current simple boundary and should be reviewed before wholesale/cheque permissions are added.
- Media handling crosses database and filesystem boundaries and needs explicit failure/orphan-file handling.
- Dashboard and home-page aggregation should be audited for query count and unnecessary data loading before introducing caching.

## Definition of done for this branch

The branch is ready for review when each changed area has:

- a concrete reason for the change,
- a safe migration/rollback story where schema changes exist,
- correct relationships and indexes,
- no obvious N+1 introduced,
- validation and authorization at the correct boundary,
- transaction/idempotency handling where required,
- focused tests or a documented reason a test is not yet practical.

No merge to `main` is implied by this document.
