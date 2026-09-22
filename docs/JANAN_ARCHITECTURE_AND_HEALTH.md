# JANAN — Architecture & Health Check

## وضعیت

- Branch: `refactor/backend-foundation`
- Roles: فقط `admin` و `customer`
- Public SEO خودکار است؛ Dashboard عمداً SEO ندارد.

## 1. دامنه نهایی

هسته فقط برای فروشگاه واقعی طراحی شده است:

```text
Public Store
  Product / Category
  Cart
  Checkout
  Order
  Inventory
  Payment

Admin
  Dashboard
  Products
  Categories
  Customers
  Orders
  Inventory
  Settings
  Hero
```

Brand management چندگانه، Accounting، permission matrix، campaign engine و SEO dashboard عمداً حذف شده‌اند.

## 2. Route و Access

```text
routes/web.php
├── routes/public.php
├── routes/auth.php
└── routes/admin.php
```

Admin با `ensure.admin` و Customer با `ensure.customer` محافظت می‌شوند.

## 3. Validation

Business formها از FormRequest استفاده می‌کنند:

```text
app/Http/Requests/
├── Auth/
├── Admin/
└── Store/
```

قاعده اصلی:

```text
Request -> Controller -> Service -> Model / DB
```

## 4. Service Layer

```text
app/Services/
├── CartService.php
├── CategoryService.php
├── HeroService.php
├── InventoryService.php
├── MediaService.php
├── OrderService.php
├── PaymentService.php
├── ProductService.php
├── SeoService.php
└── SettingsService.php
```

### MediaService
مرجع upload، crop، MIME validation، replacement، deletion، URL و existence است.

### InventoryService
مرجع واحد تغییر موجودی است؛ row locking دارد و از موجودی منفی و restore دوباره جلوگیری می‌کند.

### OrderService
مرجع ساخت سفارش، آیتم‌ها، deduction و restore موجودی و تغییر status است.

### PaymentService
مرجع وضعیت `pending/paid/failed/refunded` است. درگاه آنلاین هنوز deliberately متصل نشده است.

### SettingsService
نام برند، تماس، آدرس و Footer را مدیریت می‌کند.

## 5. Model Relations

```text
User hasMany Orders
Category hasMany Products
Product belongsTo Category
Product hasMany OrderItems
Product hasMany InventoryMovements
Order belongsTo User
Order hasMany OrderItems
OrderItem belongsTo Order
OrderItem belongsTo Product
InventoryMovement belongsTo Product
InventoryMovement belongsTo User
```

## 6. چیزهای حذف‌شده

- Brand entity و Brand controllers/views
- FinancialTransaction و Accounting controllers/views
- AdminOrderItemController
- AdminImageUploader
- auto-sync کردن Admin هنگام login
- migrations قدیمی مربوط به Brand/Accounting

این حذف‌ها complexity غیرضروری را از هسته خارج کرده‌اند.

## 7. Media و Hero

Hero دارای ۳ slot است و هر slot:

- desktop image
- mobile image
- eyebrow/title/subtitle
- CTA
- active
- sort order

فرآیند:

```text
Choose -> Crop -> Preview -> Save -> Public Hero
```

Product فعلاً یک تصویر اصلی دارد. Gallery چندتصویری و ProductVariant inventory هنوز عمدی اضافه نشده‌اند.

## 8. Inventory

موجودی ابتدا برای Product ذخیره می‌شود و تمام تغییرات باید از `InventoryService` عبور کنند.

وقتی Order ساخته می‌شود، موجودی در همان transaction کنترل و کم می‌شود.
وقتی Order وارد `cancelled/returned` می‌شود، موجودی یک‌بار restore می‌شود.

## 9. Public SEO

Service: `SeoService`

پشتیبانی:

- title
- description
- robots
- canonical
- Open Graph
- Twitter
- Product JSON-LD
- `robots.txt`
- `sitemap.xml`

Cart / Checkout / Account / Login / Register و Admin noindex هستند.
SEO به DB واقعی متصل است و SEO dashboard وجود ندارد.

## 10. Dashboard UX

Dashboard فقط داده‌های کاربردی دارد:

- فروش پرداخت‌شده
- سفارش‌های پرداخت‌شده
- کل سفارش‌ها
- مشتریان
- ارزش موجودی
- سفارش‌های نیازمند پیگیری
- موجودی کم
- Hero state
- order mix
- sales flow
- آخرین سفارش‌ها
- پرفروش‌ترین محصولات

Quick Actionها فقط به عملیات روزمره فروشگاه وصل‌اند.

## 11. Public UX

Theme:

- light
- soft pink
- light sky blue
- soft gray
- white

در mobile:
- quick add محصول همیشه قابل دسترس است
- Hero تصویر mobile جدا دارد
- focus-visible برای کنترل‌های اصلی وجود دارد
- Footer از Settings واقعی تغذیه می‌شود.

## 12. Test Coverage

```text
tests/Feature/AccessAndCheckoutTest.php
tests/Feature/PublicSeoTest.php
```

پوشش اصلی:

- role access
- checkout
- stock deduction
- cancellation restore
- robots
- sitemap
- product metadata
- JSON-LD

## 13. CI

```text
.github/workflows/ci.yml
```

CI:

1. MySQL 8
2. PHP 8.2
3. Composer
4. Laravel tests
5. Node 22
6. npm ci
7. npm run build

یک failure قبلی مشخص شد: `AppServiceProvider` در زمان Composer package discovery از DB برای Settings می‌خواند. این dependency حذف شد و Settings اکنون از view composer بارگذاری می‌شود.

در زمان ثبت این سند، آخرین CI پس از این fix هنوز `in_progress` بود؛ pass نهایی را تا پایان run نباید قطعی فرض کرد.

## 14. نصب از صفر

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm run build
php artisan serve
```

برای development:

```bash
npm run dev
```

## 15. Smoke Test

Auth:
- customer login -> `/account`
- customer -> `/admin` باید blocked باشد
- admin -> `/admin`
- admin -> `/account` باید به admin dashboard برگردد

Product:
- create
- image upload/crop
- edit
- stock adjustment
- public product

Hero:
- desktop crop
- mobile crop
- active/inactive
- public home

Checkout:
- add to cart
- checkout
- order creation
- inventory deduction
- cancel
- inventory restore once

SEO:
- product title
- description
- canonical
- OG/Twitter
- JSON-LD
- robots
- sitemap

## 16. Known Intentional Limitations

- online payment gateway هنوز provider-specific نشده است
- ProductVariant با inventory مستقل هنوز ساخته نشده
- Product gallery چندتصویری هنوز ساخته نشده
- coupon/review/notification/campaign engine در هسته نیست
- accounting و multi-brand در هسته نیست
- SEO dashboard در هسته نیست

این‌ها bug محسوب نمی‌شوند؛ scope عمداً کوچک نگه داشته شده است.

## نتیجه

ساختار نهایی باید یک مسیر ثابت را حفظ کند:

```text
Request
  ↓
Thin Controller
  ↓
Focused Service
  ↓
Model
  ↓
Transactional DB
  ↓
Real Public/Admin UI
```

هر Feature جدید فقط وقتی وارد شود که نیاز عملیاتی واقعی داشته باشد و business rule جدید را در Controller پخش نکند.