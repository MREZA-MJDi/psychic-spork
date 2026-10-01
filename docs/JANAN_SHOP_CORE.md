# Janan Shop Core

## Scope

This phase hardens the storefront around three boundaries:

1. Shop Core: product queries, relations, pagination, filtering and query shape.
2. Integration Foundation: external-system identity mapping for Nila/Holoo without polluting core product tables.
3. View Organization: Store and Admin Blade structure, shared UI primitives and removal of duplicate presentation logic.

## Catalog query rule

Catalog/listing pages must not eager-load full variant and gallery collections when they only need one card image and one purchasable/default active variant.

Use:

- `primaryActiveVariant`
- `primaryGalleryMedia`

These relations use `ofMany()` so the database resolves one row per product instead of hydrating every matching child row.

Product detail pages may load full `variants` and `galleryMedia` because the interaction actually needs the complete collections.

## External integrations

Core entities do not receive vendor-specific columns such as `nila_id` or `holoo_id`.

The `integration_mappings` table stores:

- integration name
- polymorphic entity
- external ID
- optional external SKU
- metadata

This makes external synchronization idempotent and keeps Product/Variant schemas vendor-neutral.

The Nila adapter/import pipeline is intentionally not implemented until the actual Nila source contract is verified. No guessed API fields or endpoints are part of this phase.

## View rule

Blade views consume already-shaped data. Catalog cards must not trigger database queries or contain business logic that belongs in controllers/queries/services.

The visual language already accepted by the client, especially the homepage hero, is preserved while the underlying Blade/CSS/JS structure is cleaned up.

## Performance order

Schema/indexes -> query shape -> eager loading -> pagination -> N+1 protection -> concurrency -> cache.

Cache is not used as a substitute for a bad query.

## Definition of done

- Store product/category/brand listings have explicit query shapes.
- Catalog card rendering does not lazy-load variant/gallery collections.
- External mappings are unique and idempotency-friendly.
- Nila integration has a verified contract before an adapter is written.
- Store/Admin Blade structures are organized without component over-fragmentation.
- CSS has one clear ownership path and redundant overrides are removed.
- Feature tests cover query loading and integration identity.
