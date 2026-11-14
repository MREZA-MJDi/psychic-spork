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
| Product name | Nila / Holoo (slug is generated safely by Janan) |
| Product descriptions | **Janan Admin** unless a verified source contract explicitly owns them |
| Category and brand | **Janan Admin** until a verified mapping is explicitly configured |
| Variant SKU, price, sale price, stock | Nila / Holoo |
| Product active/featured/sort state | **Janan Admin**; spreadsheet imports start inactive |
| Product gallery images | **Janan Admin** |
| Primary image / image order | **Janan Admin** |
| Image alt text | **Janan Admin** |
| Uploaded media files | **Janan Admin** |

The normalized Nila DTO contains no image field.

NilaCatalogImporter must never create, update, delete, reorder or replace rows in media. A Nila re-import therefore cannot overwrite an image that an administrator uploaded manually.

Image upload, replacement, deletion, ordering and primary-image selection belong to the Admin Product Media Manager.

## Admin spreadsheet workflow (CSV / XLSX)

The current dashboard flow is deliberately separate from a live Nila API connection.

1. An administrator uploads a CSV/XLSX file from the Nila section. The file is stored on the private local disk.
2. Upload parses and validates the file and creates a preview batch only. It does not create or update products or variants.
3. The administrator reviews row errors and grouped product codes. A product group is the base Holoo code before the `*` suffix; each suffixed code is a separate variant SKU.
4. Start with **5 product groups** for the pilot. After reviewing the result, continue in bounded requests of 10, 25 or at most 50 groups.
5. Each product group is imported transactionally. An invalid row skips the entire group; other valid groups in the same request can continue.
6. Batch offset and imported/failed counters are persisted so a batch can resume after a successful chunk. A recent `processing` state blocks duplicate concurrent requests; stale processing batches can be retried after the recovery window.
7. Imported products and variants remain inactive. An administrator completes category, brand, descriptions, images, activation and merchandising in the product dashboard.

Current spreadsheet fields:

- `holooCode`: base product code plus variant suffix; full value is the variant external ID and SKU.
- `holooName`: product/variant name; the trailing hyphen segment is treated as a color candidate, not as a trusted category.
- `holooRegularPrice`: required numeric variant price. Blank values are invalid and never coerced to zero.
- `holooStockQuantity`: numeric variant stock.
- `holooCustomerCode`: retained as source metadata only until its business meaning is verified; it is not used as a product or customer identifier.

The importer reuses `products`, `product_variants` and `integration_mappings`. The only spreadsheet-specific table is `nila_import_batches`, which tracks private upload files and batch progress. It does not create a second catalog schema.

## Storefront catalog delivery

- Admin products, shop products, category product lists, and brand product lists use database pagination.
- Category and brand directories are also database-paginated (24 cards per page).
- The homepage featured carousel receives up to 36 active products; the immersive Hero separately accepts up to 36 active products explicitly selected as Hero.
- Hero capacity is enforced in the service and shown in the admin product list. Inactive products cannot be newly added to Hero.

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
