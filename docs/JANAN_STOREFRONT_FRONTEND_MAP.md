# Janan storefront frontend map

The storefront uses one shared shell and one shared responsive source of truth.

## Shared shell

`resources/views/layouts/store.blade.php` owns the header, main content slot, footer, cart drawer, mobile bottom navigation, and conditional Vite asset list.

## Page composition

Home: `resources/views/home/index.blade.php`

1. Immersive hero
2. Category discovery
3. Featured products
4. Editorial discovery
5. Store signals
6. Brand discovery

Move or reorder homepage sections in this file only. The section internals live in `resources/views/components/store/`.

Catalog pages:
- `resources/views/products/index.blade.php`
- `resources/views/categories/index.blade.php`
- `resources/views/categories/show.blade.php`
- `resources/views/brands/index.blade.php`
- `resources/views/brands/show.blade.php`

Product detail: `resources/views/products/show.blade.php` owns the visual/gallery stage, purchase stage, product information, and related products.

## Styling ownership

`resources/css/core.css` — reset and shared font faces.
`resources/css/app.css` — Janan tokens and shared storefront base.
`resources/css/store-structure.css` — shared layout primitives and structural surfaces.
`resources/css/store-polish.css` — component/detail presentation and interaction polish.
`resources/css/store-customer-uiux.css` — customer-facing catalog/UI layer.
`resources/css/store-responsive.css` — shared storefront responsive behavior, mobile navigation, and immersive viewport rules.
`resources/css/home.css` — Home-only visual component styling.
`resources/css/editorial-hero.css` — immersive hero visual behavior.
`resources/css/wholesale.css` — wholesale-only styling.
`resources/css/admin.css` — admin responsive and admin UI styling.

Do not add a new responsive stylesheet for a storefront page. Put shared breakpoint behavior in `store-responsive.css`.

## Media

`resources/views/components/store/image.blade.php` is the shared storefront image component for ordinary images. It carries loading/alt metadata and a consistent broken-image fallback.

`App\\Models\\Media::getUrlAttribute()` generates storefront media URLs. Files are served through `App\\Http\\Controllers\\StoreMediaController`.

Use `primaryGalleryMedia` and `primaryActiveVariant` for lightweight product listings. Full galleries belong to the product detail page.

Interactive gallery thumbnails in product detail intentionally keep their explicit markup because they need gallery data attributes.

## Where to change things

Move Home sections: `resources/views/home/index.blade.php`.
Change a Home section internally: the matching file under `resources/views/components/store/`.
Change shared layout: `resources/css/store-structure.css`.
Change storefront responsive behavior: `resources/css/store-responsive.css`.
Change product detail structure/behavior: `resources/views/products/show.blade.php` and `resources/js/product-show.js`.
Change media persistence/serving: `app/Services/MediaService.php`, `app/Models/Media.php`, and `app/Http/Controllers/StoreMediaController.php`.
