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
        +--> Media (remote URL, when supplied)
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

## Media

Remote image URLs can be stored as remote media references. The importer does not download files or assume an image API. Collision checks prevent the same unique media path from being attached to a different product.

A later media phase can add download/storage conversion once Nila's image contract is confirmed.

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

Run:

```bash
php artisan test --filter=NilaCatalogImporterTest
```
