# Janan Nila Integration Foundation

## Scope

This phase establishes the boundary between Janan and Nila without inventing an external API contract.

Public information identifies Nila as a connector between WooCommerce stores and Holoo accounting software, including synchronization of product name, price and inventory. The exact Nila/Holoo API payloads and endpoints are not treated as known application contracts here.

## Architecture

```
Nila API / connector
        |
        v
Nila adapter (future, contract-specific)
        |
        v
ExternalProductData / ExternalVariantData
        |
        v
NilaCatalogImporter
        |
        +--> Product
        +--> ProductVariant
        +--> IntegrationMapping

Media is intentionally outside this write path.
Nila/Holoo owns catalog data; Janan Admin owns product media.
```

The importer only consumes normalized DTOs. This keeps provider-specific field names, authentication and transport out of the domain/catalog write path.

## Idempotency

Every imported product and variant is identified through `integration_mappings`:

- `integration = nila`
- `entity_type = App\\Models\\Product` or `App\\Models\\ProductVariant`
- `external_id = provider identifier`
- variant mappings also retain `external_sku`

A repeated import updates the mapped record instead of creating a duplicate.

A local variant with the same SKU but no Nila mapping is rejected rather than silently adopted. This prevents an ambiguous SKU from hijacking an existing catalog record.

Soft-deleted mapped products/variants are restored during import.

## Transaction boundary

Each product is imported in its own database transaction with retry attempts. A bad product therefore does not roll back an entire catalog batch.

For larger catalogs, the caller should chunk records and dispatch `ImportNilaProductBatch` jobs. The job intentionally does not know how Nila's API works.

## Field ownership

The catalog sync has an explicit ownership boundary:

| Data | Source of truth |
|---|---|
| Product name, slug, descriptions | Nila / Holoo |
| Category and brand mapping | Nila / verified Janan mapping |
| Variant SKU, price, sale price, stock | Nila / Holoo |
| Product active/featured/sort state | Nila contract when supplied |
| Product gallery images | **Janan Admin** |
| Primary image / image order | **Janan Admin** |
| Image alt text | **Janan Admin** |
| Uploaded media files | **Janan Admin** |

The normalized Nila DTO contains no image field.

NilaCatalogImporter must never create, update, delete, reorder or replace rows in media. A Nila re-import therefore cannot overwrite an image that an administrator uploaded manually.

Image upload, replacement, deletion, ordering and primary-image selection belong to the Admin Product Media Manager.
## What is intentionally not implemented yet

- Nila authentication details
- Nila endpoint URLs
- Nila pagination fields
- Nila webhook payloads
- Nila category/brand identifiers
- Nila order/invoice creation
- Automatic image downloading
- Bidirectional stock writes

Those belong behind a verified adapter contract. Guessing them would couple Janan to an API we have not verified.

## Tests

`tests/Feature/NilaCatalogImporterTest.php` covers:

- idempotent product/variant import
- price and stock update on repeated import
- rejection of an unmapped SKU collision
- product and variant external mappings
- protection of Janan-managed media during Nila re-import

Run:

```bash
php artisan test --filter=NilaCatalogImporterTest
```
