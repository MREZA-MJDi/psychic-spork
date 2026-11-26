# Janan Nila / Holoo Integration

## Scope

This integration uses the Holoo Web Service contract documented at
https://holoostore.com/webserviceHelp.pdf. The document describes the service
base URL as `http://{IPSystem}:8080/TncHoloo/api`, a `POST /Login` call,
a bearer token returned by login, and authenticated catalog endpoints.

The adapter deliberately keeps provider-specific authentication and payload
normalization out of the catalog importer.

## Catalog mapping

| Holoo | Janan |
|---|---|
| `Product.ErpCode` | Stable external ID for product and its single imported stock variant |
| `Product.Code` | Variant SKU |
| `Product.Name` | Product name |
| `Product.Few` | Integer stock quantity; negative/fractional quantities are clamped/floored |
| `Product.SellPrice` (configurable) | Variant price |
| `Product.SideGroupErpCode` | Imported child category mapping |
| `Product.MainGroupErpCode` | Parent category mapping metadata |
| `MainGroup` / `SideGroup` | Categories managed through `integration_mappings` |
| Product image | Not imported; images, gallery order and alt text remain Janan-administered |

The price field can be changed to `SellPrice2` ... `SellPrice10` or
`selpricekarton` via `NILA_PRICE_FIELD`. Janan's product editor and storefront
label catalog prices in toman. The default multiplier of 1 therefore assumes
Holoo's selected price field is already in toman. If the Holoo installation
returns rial, set `NILA_PRICE_MULTIPLIER=0.1`; verify a known item against the
Holoo desktop application before any write import. `selpricekarton` is a carton
price and is not divided by `CountInKarton` or `CountInBasteh`; use it only when
the Janan SKU is intentionally sold at that packaging level.

Holoo's documented product list does not define product description, sale price,
size, color, active status or storefront ordering. On re-import the adapter
updates provider-owned name/SKU/price/stock and preserves those local fields
when Holoo does not supply a value. New imported products are hidden by default
(`NILA_ACTIVE_BY_DEFAULT=false`) so an administrator can review price, stock
and media before publishing.

## Configuration

Set these values in the local `.env`; do not commit real credentials:

```dotenv
NILA_ENABLED=true
NILA_BASE_URL=http://192.168.1.20:8080/TncHoloo/api
NILA_USERNAME=your_holoo_username
NILA_PASSWORD=your_holoo_password
NILA_DATABASE=your_holoo_database_name
NILA_TIMEOUT=20
NILA_PAGE_SIZE=100
NILA_MAX_PAGES=1000
NILA_PRICE_FIELD=SellPrice
NILA_PRICE_MULTIPLIER=1
NILA_ACTIVE_BY_DEFAULT=false
```

Replace the sample host, username, password and database with values supplied
by the Holoo administrator. The service must be reachable from the machine
running Laravel. In production this commonly means a trusted LAN or VPN path;
do not expose port 8080 directly to the public internet. After changing env
values on a cached Laravel installation run `php artisan config:clear` (and
refresh the production config cache during deployment as appropriate).

The documented login JSON contains a `userinfo` object with `username`,
Base64-encoded `userpass` and `dbname`. The returned `Login.Token` is sent
unchanged when it already contains `Bearer`, or prefixed with `Bearer`
otherwise.

## Safe runbook

1. Confirm Holoo Web Service is installed, started, and reachable from the
   Laravel host. The manual lists service name `TncHolooWebService` and the
   default port 8080.
2. Configure the env variables above and set `NILA_ENABLED=true`.
3. Fetch and inspect a small sample without database writes:

   ```bash
   php artisan nila:sync-products --dry-run --limit=10
   ```

4. Verify the product names, SKU values, stock, selected price field, and
   currency unit against Holoo. A dry run does not create categories/products
   or write stock movements.
5. Import a small sample first. In production the explicit safety flag is
   required:

   ```bash
   php artisan nila:sync-products --limit=10
   # Production only, after review:
   php artisan nila:sync-products --limit=10 --confirm-production
   ```

6. Review imported products and manually add/verify their media in Janan Admin.
   Only after the sample is correct should the limit be removed for a full
   catalog run.

The command imports categories before products on a write run. Each product is
written in its own transaction and its external ID is mapped, so a retry
updates mapped rows rather than creating duplicates. A catalog-wide run is not
one global transaction; if a connection fails midway, review logs and rerun
after fixing the issue.

## Excel or CSV import

1. Upload the XLS, XLSX, or CSV export at `/admin/nila`. The file is limited to 10 MiB and can omit product images. The preview groups inventory rows into products and identifies invalid rows before any catalog writes. Fill missing prices in Holoo or the export and upload it again. A group with an invalid variant is skipped in full.
2. Select the price unit during upload. A rial amount is divided by 10 once before storage, and the preview shows the resulting toman amount. Existing batches keep their original interpretation. Review the grouping, SKU, price and stock. Treat the file's stock quantities as a snapshot. Import from a current export so that a stale quantity does not replace live stock.
3. For a small pilot, use the admin controls with the database queue worker running. Set `DB_QUEUE_RETRY_AFTER=360` to allow the spreadsheet job's 300-second timeout to expire before another worker can reserve it.
4. For a large catalog, use the same reviewed upload with the CLI. Replace `42` with the batch ID from the preview URL.

   ```bash
   php artisan nila:import-spreadsheet 42 --batch-size=50 --confirm-stock-snapshot --confirm-production
   ```

   The command processes at most 50 complete product groups at a time and resumes from the saved offset on retry. An upload already owned by the queue must finish its current job before it can be resumed from the CLI. Check the final imported/failed totals and review any rejected groups in the admin panel.
5. New products and their imported variants are inactive. Review prices, stock and images before publishing from Janan Admin.

## Tests

```bash
php artisan test --filter=NilaHolooWebServiceTest
php artisan test --filter=NilaCatalogImporterTest
```

The tests use mocked HTTP responses and a test database. They verify contract
handling and field ownership; they do not prove the real Holoo installation,
database permissions, price units or network route are correct.
