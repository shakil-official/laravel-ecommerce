# Code Documentation

Developer reference for the Laravel e-commerce project: what every file does, every class and method, the data flow and where to change things.
For setup, Docker and diagrams see the main [README](../README.md).

> **Conventions used in the code**
> * Every class has a short description and every method has a one-line DocBlock (`/** … */`).
> * Controllers stay thin: validation lives in **Form Requests**, business rules in **Services** / **Models**.
> * Money is always `decimal(…, 2)`; status columns are **backed enums**.
> * Admin routes are under `/admin` and protected by the `auth` middleware.

---

## Contents

1. [Request flow](#1-request-flow)
2. [Directory map](#2-directory-map)
3. [Routes](#3-routes)
4. [Controllers](#4-controllers)
5. [Form Requests (validation)](#5-form-requests-validation)
6. [Models](#6-models)
7. [Enums](#7-enums)
8. [Services and Support](#8-services-and-support)
9. [Database: migrations, factories, seeders](#9-database-migrations-factories-seeders)
10. [Views, layouts and CSS](#10-views-layouts-and-css)
11. [Configuration](#11-configuration)
12. [Docker files](#12-docker-files)
13. [Tests](#13-tests)
14. [How-to recipes](#14-how-to-recipes)

---

## 1. Request flow

```
Browser -> nginx (:6180) -> php-fpm -> public/index.php
        -> bootstrap/app.php (middleware, auth redirects)
        -> routes/web.php
        -> [FormRequest validates]  -> Controller  -> Model / Service
        -> Blade view (layouts/app or layouts/admin)  -> HTML
```

*Ordering an item:* `POST /product/{slug}/order` -> `PlaceOrderRequest` (validate + normalise phone) -> `OrderController@store` -> `OrderService::place()` (server-side price snapshot, status pending/unpaid) -> `Order::creating` hook generates `order_number` and `tracking_token` -> redirect to `/order/{token}/thanks`.

*Tracking:* `POST /track` -> `TrackOrderRequest` (normalises `k7m2 9qxa w4tp` to `K7M2-9QXA-W4TP`) -> `TrackingController@lookup` -> redirect `/track/{token}` -> `TrackingController@show`.

---

## 2. Directory map

| Path | Purpose |
|------|---------|
| `app/Enums` | `OrderStatus`, `PaymentStatus`, `PaymentMethod` |
| `app/Http/Controllers` | Public (`ShopController`, `OrderController`, `TrackingController`), `Auth/LoginController`, `Admin/*` |
| `app/Http/Requests` | One Form Request per form |
| `app/Models` | `Category`, `Subcategory`, `Product`, `Order`, `User`, trait `Concerns/HasUniqueSlug` |
| `app/Services` | `ProductImageService`, `PlaceholderImageGenerator`, `OrderService` |
| `app/Support` | `Money` formatter |
| `app/Providers/AppServiceProvider.php` | Pagination style + view composers |
| `bootstrap/app.php` | Routing, guest/auth redirects |
| `config/shop.php` | Shop settings (currency, page sizes, admin account, image rules) |
| `database/migrations` | Schema |
| `database/factories` | Test / demo data builders |
| `database/seeders` | Admin user, demo catalogue, demo orders (+ `data/*.php`) |
| `database/sql/ecommerce.sql` | Ready-made MySQL export |
| `resources/views` | Blade templates |
| `public/css/app.css` | Whole design system |
| `public/vendor` | Local Bootstrap, Bootstrap Icons, Inter font (no CDN) |
| `docker/`, `docker-compose.yml`, `Makefile` | Local environment |
| `tests/Unit`, `tests/Feature` | PHPUnit tests |

---

## 3. Routes

`routes/web.php`

| Method | URL | Name | Handler | Middleware |
|--------|-----|------|---------|------------|
| GET | `/` | `shop.index` | `ShopController@index` | – |
| GET | `/subcategory/{slug}` | `shop.subcategory` | `ShopController@subcategory` | – |
| GET | `/product/{slug}` | `shop.product` | `ShopController@show` | – |
| POST | `/product/{slug}/order` | `shop.order.store` | `OrderController@store` | throttle 10/min |
| GET | `/order/{tracking_token}/thanks` | `shop.order.thanks` | `OrderController@thanks` | – |
| GET | `/track` | `shop.track` | `TrackingController@create` | – |
| POST | `/track` | `shop.track.lookup` | `TrackingController@lookup` | throttle 20/min |
| GET | `/track/{tracking_token}` | `shop.track.show` | `TrackingController@show` | throttle 60/min |
| GET | `/login` | `login` | `LoginController@create` | guest |
| POST | `/login` | `login.store` | `LoginController@store` | guest, throttle 6/min |
| POST | `/logout` | `logout` | `LoginController@destroy` | auth |
| GET | `/admin` | `admin.dashboard` | `DashboardController` | auth |
| resource | `/admin/categories` | `admin.categories.*` | `CategoryController` | auth |
| resource | `/admin/subcategories` | `admin.subcategories.*` | `SubcategoryController` | auth |
| resource | `/admin/products` | `admin.products.*` | `ProductController` | auth |
| index, show, update, destroy | `/admin/orders` | `admin.orders.*` | `Admin\OrderController` | auth |

Route model binding uses **slugs** for catalogue pages (`{product:slug}`) and the **private token** for order pages (`{order:tracking_token}`), never the numeric id or order number.

---

## 4. Controllers

### `Controller` (abstract base)
| Member | Description |
|--------|-------------|
| `PER_PAGE_OPTIONS` | `[10, 25, 50, 100]`: allowed values for the "Rows" selector |
| `perPage(Request)` | Returns `?per_page=` when allowed, otherwise `config('shop.admin_per_page')` |
| `sensitive(Response)` | Adds `Cache-Control: no-store, private`, `X-Robots-Tag: noindex, nofollow`, `Referrer-Policy: no-referrer`. Used for pages that contain a token or customer data |

### `ShopController` (public catalogue)
| Method | Description |
|--------|-------------|
| `index(Request)` | Lists **subcategory sections**, each with its products. Supports `?q=` (product name), `?category=slug`, pagination (`shop.listing_per_page` sections per page), passes `categories` and `stats` to the view |
| `subcategory(Subcategory)` | One subcategory, 12 products per page |
| `show(Product)` | Product page + up to 4 related products from the same subcategory |

### `OrderController` (public "Order Now")
| Method | Description |
|--------|-------------|
| `__construct(OrderService)` | Injects the order service |
| `store(PlaceOrderRequest, Product)` | Creates the order through `OrderService::place()`, redirects to the thank-you page |
| `thanks(Order)` | Confirmation page showing the tracking token (with `sensitive()` headers) |

### `TrackingController`
| Method | Description |
|--------|-------------|
| `create()` | Shows the token form |
| `lookup(TrackOrderRequest)` | Finds the order by normalised token. Unknown token -> back with an error on `token`; found -> redirect to `shop.track.show` |
| `show(Order)` | Timeline page (with `sensitive()` headers) |

### `Auth\LoginController`
| Method | Description |
|--------|-------------|
| `create()` | Login form |
| `store(Request)` | Validates email + password, `Auth::attempt`, regenerates the session, redirects to the intended URL or the dashboard. Wrong credentials throw a `ValidationException` on `email` |
| `destroy(Request)` | Logout, invalidate session, regenerate CSRF token |

### `Admin\DashboardController`
`__invoke()`: counts (categories, subcategories, products, orders, pending orders), **revenue = sum of paid orders only**, latest 5 orders / products, up to 8 categories.

### `Admin\CategoryController`
| Method | Description |
|--------|-------------|
| `index` | Search `?q=`, paginated, with `subcategories_count` / `products_count` |
| `create`, `store` | New category form / save (`CategoryRequest`) |
| `show` | Category with a paginated subcategory table |
| `edit`, `update` | Edit form / save |
| `destroy` | Deletes the category; subcategories and products are deleted through Eloquent hooks (so image files are removed) |

### `Admin\SubcategoryController`
| Method | Description |
|--------|-------------|
| `index` | Filters `?category_id=`, `?q=`, paginated |
| `create` | Preselects `?category_id=` |
| `store` / `update` | Validate with `SubcategoryRequest`. `update` moves the subcategory's products to the new category when `category_id` changed |
| `show` | Subcategory with a paginated product table |
| `destroy` | Deletes the subcategory and its products |

### `Admin\ProductController`
| Method | Description |
|--------|-------------|
| `__construct(ProductImageService)` | Image storage service |
| `index` | Filters `category_id`, `subcategory_id`, `q`; paginated |
| `create` / `edit` | Forms; `formData()` supplies categories and all subcategories for the dependent dropdown |
| `store` | Stores the uploaded image, then creates the product |
| `update` | Replaces the image only when a new file is uploaded (old file deleted) |
| `show`, `destroy` | Details / delete (image removed by the model) |
| `formData(Product)` *(private)* | Shared data for create/edit forms |

### `Admin\OrderController`
| Method | Description |
|--------|-------------|
| `index` | Search (number, token, name, phone), `status`, `payment_status` filters, paginated |
| `show` | Order details + status form |
| `update(OrderUpdateRequest)` | Changes status and payment status, redirects back with a flash message |
| `destroy` | Deletes the order |

---

## 5. Form Requests (validation)

All live in `app/Http/Requests`. `authorize()` returns `true` everywhere because access is enforced by routes/middleware.

| Class | Rules | Extra behaviour |
|-------|-------|-----------------|
| `CategoryRequest` | `name` required, 2-100, **unique** (ignores the category being edited); `description` max 1000 | `prepareForValidation` trims the name |
| `SubcategoryRequest` | `category_id` exists; `name` required, unique **per category** (ignores itself); `description` max 1000 | trims the name |
| `ProductRequest` | category and subcategory exist **and subcategory belongs to category**; `name` 2-150; `description` 10-5000; `image` required on create, optional on update, jpg/jpeg/png/webp, max 2048 KB; `old_price` 0-99,999,999.99; `new_price` **0.01 .. old_price** | trims the name; custom messages |
| `PlaceOrderRequest` | `customer_name` 2-100; `phone` Bangladeshi mobile (`PHONE_REGEX`); `email` optional; `address` 10-500; `quantity` 1-100; `payment_method` enum; `note` max 500 | `prepareForValidation` removes spaces/dashes and converts `+8801…` / `8801…` to `01…`; `getRedirectUrl` appends `#order-form` |
| `OrderUpdateRequest` | `status` and `payment_status` required enums | `withValidator`: **cancelled orders cannot be paid** |
| `TrackOrderRequest` | `token` required, 14 chars, `XXXX-XXXX-XXXX` | `prepareForValidation` normalises with `Order::normalizeToken` |

---

## 6. Models

### `Category`
Fillable `name, slug, description`. Uses `HasUniqueSlug`. Relations `subcategories()`, `products()`. `deleting` hook deletes subcategories through Eloquent.

### `Subcategory`
Fillable `category_id, name, slug, description`. Relations `category()`, `products()`. `deleting` hook deletes its products (so images are removed).

### `Product`
| Member | Description |
|--------|-------------|
| fillable | `category_id, subcategory_id, name, slug, description, image, old_price, new_price` |
| `category()`, `subcategory()` | `belongsTo` |
| `image_url` accessor | Public URL of the stored image, or `images/placeholder.svg` when missing |
| `has_discount` accessor | `new_price < old_price` |
| `discount_percent` accessor | Rounded whole percent, 0 when no discount or old price is 0 |
| `deleteImageFile()` | Deletes the file from the `public` disk |
| `deleted` hook | Calls `deleteImageFile()` |

### `Order`
| Member | Description |
|--------|-------------|
| `product()` | `belongsTo`; `null` after the product was deleted (snapshot fields keep the order readable) |
| `generateNumber()` | `BS-YYMMDD-XXXXX` (unique) |
| `generateToken()` | 12 random characters from `TOKEN_ALPHABET` (31 symbols, no `0 O 1 I L`) in `XXXX-XXXX-XXXX` form, unique, using `random_int` |
| `normalizeToken(string)` | Upper-cases and re-groups user input (`k7m2 9qxa-w4tp` -> `K7M2-9QXA-W4TP`) |
| `tracking_url` accessor | `route('shop.track.show', token)` |
| `masked_phone` accessor | `017******78` |
| `scopeSearch(term)` | Matches order number, token, customer name, phone |
| `creating` hook | Fills `order_number` and `tracking_token` when empty |
| casts | `status`, `payment_status`, `payment_method` are enums; prices decimals |

### `User`
Standard authenticatable; `password` is hashed by the cast; used only for the admin login.

### `Concerns\HasUniqueSlug` (trait)
| Method | Description |
|--------|-------------|
| `uniqueSlug($value, $ignoreId)` | `Str::slug` + `-2`, `-3` … until unique (empty result becomes `item`) |
| `slugExists()` | Checks another row (ignoring `$ignoreId`) |
| `bootHasUniqueSlug()` | On `saving`: creates the slug when it is empty; the slug is never regenerated afterwards, so renaming a record keeps its URL stable |

---

## 7. Enums

| Enum | Cases | Helpers |
|------|-------|---------|
| `OrderStatus` | pending, confirmed, packaging, shipped, delivered, cancelled | `label()`, `color()`, `icon()`, `description()`, `isFinal()` (delivered/cancelled), `trackingSteps()` (first five), `step()` (index or `null` for cancelled), `values()` |
| `PaymentStatus` | unpaid, paid, failed, refunded | `label()`, `color()`, `values()` |
| `PaymentMethod` | cod, bkash, nagad, bank_transfer | `label()`, `values()` |

---

## 8. Services and Support

| Class | Method | Description |
|-------|--------|-------------|
| `OrderService` | `place(Product, array): Order` | Copies product name and **current** `new_price` onto the order, computes `total = round(price × quantity, 2)`, sets `pending` / `unpaid` |
| `ProductImageService` | `store(UploadedFile)` | Saves to `storage/app/public/products`, returns the relative path |
| | `replace(?string, UploadedFile)` | Stores the new file, deletes the old one |
| | `delete(?string)` | Deletes a file; null/empty ignored |
| `PlaceholderImageGenerator` | `isAvailable()` | GD installed? |
| | `generate(label, dir)` | Stores a gradient JPEG, returns its path or `null` |
| | `render(label)` | 800×600 JPEG bytes; colours derived from the label |
| `Support\Money` | `format($amount)` | `৳ 1,250.00` using `config('shop.currency')` |
| `AppServiceProvider` | `boot()` | Bootstrap 5 paginator; composer for `layouts.app` (`navCategories`) and `layouts.admin` (`pendingOrdersCount`); both fall back safely when the DB is unavailable |

---

## 9. Database: migrations, factories, seeders

### Tables
| Table | Key columns | Notes |
|-------|-------------|-------|
| `categories` | `name` unique, `slug` unique, `description` | |
| `subcategories` | `category_id` FK cascade, `name`, `slug` unique | name unique per category |
| `products` | `category_id`, `subcategory_id` FK cascade, `slug` unique, `image`, `old_price`, `new_price` | index `(subcategory_id, created_at)` |
| `users` | `name`, `email` unique, `password` | admin only |
| `orders` | `order_number` unique, `tracking_token` unique, `product_id` FK **set null**, snapshot `product_name` + `unit_price`, `quantity`, `total`, customer fields, `payment_method`, `status` (index), `payment_status` (index) | |

Migrations run in date order; `2026_11_07_100000_add_tracking_token_to_orders_table` adds the nullable unique token and back-fills old rows (so upgrading is safe).

### Factories
`CategoryFactory`, `SubcategoryFactory`, `ProductFactory` (new price never above old price, consistent category/subcategory), `UserFactory`, `OrderFactory` (states `status(OrderStatus)`, `paid()`).

### Seeders
| Seeder | Description |
|--------|-------------|
| `DatabaseSeeder` | Calls the three below |
| `AdminUserSeeder` | `updateOrCreate` of the admin from `config('shop.admin')` |
| `DemoCatalogSeeder` | 3 categories, 8 subcategories, 22 products from `data/catalog.php` (idempotent, generates images) |
| `DemoOrderSeeder` | 6 orders from `data/orders.php`, tokens `DEMO-0001-TRK1` … `DEMO-0006-TRK6` (these tokens work on `/track`) |

---

## 10. Views, layouts and CSS

| View | Purpose |
|------|---------|
| `layouts/app` | Storefront shell: topbar, header with search, category nav, footer |
| `layouts/admin` | Sidebar shell, pending-orders badge, mobile off-canvas menu |
| `layouts/auth` | Split-screen login shell |
| `partials/assets` | Local CSS/JS includes (`css/app.css?v=3`) |
| `partials/flash` | Success / error alerts |
| `partials/table-footer` | "Showing X-Y of Z", Rows selector, page links (keeps filters) |
| `partials/product-card` | Reusable product tile |
| `shop/*` | `index` (sections), `subcategory`, `show` (gallery, price box, **order form** with live total), `order-thanks` (token + copy button), `track` (form + timeline) |
| `admin/*` | `dashboard`; `categories`, `subcategories`, `products` (index/create/edit/show + `_form`); `orders` (index/show + `_status-form`) |
| `auth/login`, `errors/404` | Login and not-found pages |

`public/css/app.css` sections: design tokens (`--brand`, …), base, buttons/forms, storefront, product card, product page, tracking (timeline), admin shell (sidebar, stat cards, tables, pills), auth, utilities.
Inline JS is kept small: dependent category -> subcategory dropdown, live order total, copy-token button, delete confirmation, mobile sidebar.

---

## 11. Configuration

`config/shop.php`

| Key | Env | Default | Meaning |
|-----|-----|---------|---------|
| `currency` | `SHOP_CURRENCY` | `৳` | Price symbol |
| `admin_per_page` | `SHOP_ADMIN_PER_PAGE` | 10 | Default admin page size |
| `listing_per_page` | `SHOP_LISTING_PER_PAGE` | 4 | Subcategory sections per storefront page |
| `admin.name/email/password` | `ADMIN_*` | Administrator / admin@example.com / password | Seeded admin |
| `image.max_kb`, `image.mimes` | – | 2048, jpg/jpeg/png/webp | Upload rules |

`bootstrap/app.php`: guests are redirected to `login`, logged-in users visiting `/login` go to `admin.dashboard`.
`phpunit.xml`: forces in-memory SQLite (`force="true"`) so tests never touch MySQL.

---

## 12. Docker files

| File | Purpose |
|------|---------|
| `docker-compose.yml` | Services `app` (php-fpm, `shop_app`), `web` (nginx, **6180**), `mysql` (**6182**), `adminer` (**6181**) |
| `docker/php/Dockerfile` | PHP 8.3-fpm with required extensions + Composer |
| `docker/php/entrypoint.sh` | Creates `.env`, installs vendor, generates key, waits for MySQL, migrates, seeds (`AUTO_SEED`), links storage, fixes permissions |
| `docker/php/php.ini` | Upload limits (10 MB) |
| `docker/nginx/default.conf` | Front controller routing, 12 MB body limit |
| `Makefile` | `up`, `down`, `fresh`, `reset`, `test`, `shell`, `db-export` |

---

## 13. Tests

Run: `docker compose exec app php artisan test`.

| Folder | Covers |
|--------|--------|
| `tests/Unit/Models` | Slugs, relations, casts, accessors, cascades, order number/token/mask/search |
| `tests/Unit/Requests` | Every rule of every Form Request (via `RequestTestHelper`, which also runs `withValidator`) |
| `tests/Unit/Enums`, `Services`, `Support` | Enums, `OrderService`, image services, `Money` |
| `tests/Feature/Admin` | Every admin controller method, pagination, order status rules |
| `tests/Feature/Auth` | Login, logout, throttling, guests blocked from every admin route, no admin links on the storefront |
| `tests/Feature` | Storefront, order placement, tracking, demo seeder, layout rendering |

---

## 14. How-to recipes

**Add a new order status.** Add the case to `OrderStatus` (label/colour/icon/description are `match` expressions, so PHP will tell you where it is missing), add it to `trackingSteps()` if it is a timeline step, extend the enum test.

**Add a payment method.** Add the case and label in `PaymentMethod`; the form and validation (`Rule::enum`) pick it up automatically.

**Change the currency symbol.** Set `SHOP_CURRENCY` in `.env`.

**Change page sizes.** `SHOP_ADMIN_PER_PAGE`, `SHOP_LISTING_PER_PAGE`, or the allowed values in `Controller::PER_PAGE_OPTIONS`.

**Add a field to products.** Migration -> `Product::$fillable` -> `ProductRequest` rules -> `admin/products/_form` -> factory -> tests.

**Change the admin password.** Set `ADMIN_PASSWORD` and run `php artisan db:seed --class=AdminUserSeeder`.

**Add an admin page.** Add a controller under `Admin`, register it inside the `admin` route group (already behind `auth`), build the view on `layouts.admin`, add a sidebar link in `layouts/admin.blade.php`.
