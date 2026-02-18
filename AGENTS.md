# WorDBless — Agent Instructions

## Project Overview

WorDBless is a Composer package that lets you use WordPress core functions in PHPUnit tests without a real database. It intercepts `$wpdb` calls via a custom `db.php` drop-in and stores data in PHP arrays (or optionally SQLite), so tests run fast and require no MySQL.

- **Repository:** `Automattic/wordbless`
- **Main branch:** `trunk`
- **Package type:** `wordpress-dropin` (installed via Composer)

## Tech Stack

| Component | Version / Tool |
|---|---|
| PHP | >= 7.2.24 (platform locked to 7.4 in composer.json) |
| WordPress | ^6.6 via `roots/wordpress` |
| PHPUnit | ^7.5 \|\| ^9.5 |
| PHPUnit Polyfills | `yoast/phpunit-polyfills` ^1.0 |
| Static Analysis | PHPStan level 5 (`phpstan.neon.dist`) |
| Coding Standards | WordPress-Core (WPCS ^3.1 via `tools/`) |
| Tool Management | Phive (PHAR Installation and Verification) |
| CI | GitHub Actions (`.github/workflows/ci.yaml`) |

## Directory Structure

```
wordbless/
├── src/                        # Main source code (PSR-4: WorDBless\)
│   ├── BaseTestCase.php        # Abstract test case — extend this in your tests
│   ├── ClearCacheGroup.php     # Trait: flushes wp_object_cache for a cache group
│   ├── Composer/
│   │   └── InstallDropin.php   # Post-install hook: copies db.php + SQLite plugin
│   ├── dbless-wpdb.php         # The db.php drop-in (NO namespace, defines Db_Less_Wpdb)
│   ├── InsertId.php            # Static counter for fake auto-increment IDs
│   ├── Load.php                # Bootstrap: defines constants, loads WP, inits modules
│   ├── Metadata.php            # Generic metadata storage (used by PostMeta & UserMeta)
│   ├── Options.php             # In-memory options via alloptions filter + query interception
│   ├── PostMeta.php            # Singleton factory → Metadata('post')
│   ├── Posts.php               # In-memory post storage via wp_insert_post_data filter
│   ├── Singleton.php           # Trait: lazy-init singleton pattern
│   ├── Sqlite.php              # SQLite engine: init, table creation, cleanup
│   ├── UserMeta.php            # Singleton factory → Metadata('user')
│   ├── Users.php               # In-memory user storage via wpdb insert/update filters
│   └── WpDie.php               # Replaces wp_die handlers so they don't exit
├── tests/                      # PHPUnit tests
│   ├── bootstrap.php           # Bootstrap for "general" suite (dbless engine)
│   ├── bootstrap-uploads.php   # Bootstrap for "uploads" suite (custom upload dir)
│   ├── bootstrap-sqlite.php    # Bootstrap for "sqlite" suite
│   ├── *_Test.php              # Test files (suffix convention: _Test.php)
│   ├── includes/               # Shared test helpers
│   └── phpstan/                # PHPStan bootstrap
├── third-party/                # Vendored third-party code (do NOT modify)
│   └── sqlite-database-integration/  # WordPress SQLite integration plugin
├── tools/                      # Phive-managed PHAR tools + tool-specific Composer deps
│   ├── composer-normalize      # PHAR: composer.json normalizer
│   ├── phpcs                   # PHAR: PHP CodeSniffer
│   ├── vendor/                 # Tool-specific vendor dir (separate from root vendor/)
│   └── ...
├── wordpress/                  # WordPress core (Composer-managed, do NOT edit)
├── vendor/                     # Composer dependencies
├── composer.json
├── phpstan.neon.dist
└── phpunit.xml
```

## Commands

### Full CI pipeline

```bash
composer run-script ci
```

Runs: Phive install, composer validate, composer-normalize check, WPCS, PHPStan, then all three PHPUnit test suites.

### Run all tests

```bash
composer run-script phpunit
```

Runs `composer install` (to ensure `db.php` is copied), then executes all three test suites sequentially: general, uploads, sqlite.

### Run a single test suite

```bash
# General (dbless engine):
./vendor/phpunit/phpunit/phpunit --bootstrap tests/bootstrap.php --testsuite general

# Uploads (custom upload dir):
./vendor/phpunit/phpunit/phpunit --bootstrap tests/bootstrap-uploads.php --testsuite uploads

# SQLite (real SQLite database):
./vendor/phpunit/phpunit/phpunit --bootstrap tests/bootstrap-sqlite.php --testsuite sqlite
```

### Auto-fix code style

```bash
composer run-script fix-cs
```

Runs `phpcbf` (PHP Code Beautifier and Fixer) to auto-correct style issues in place. Requires WPCS to be installed in `tools/` first (the `ci` script does this automatically). To set up standalone:

```bash
composer require --working-dir=tools --dev 'wp-coding-standards/wpcs:^3.1.0' 'phpcsstandards/phpcsutils:^1.0' 'phpcsstandards/phpcsextra:^1.0'
tools/vendor/bin/phpcs --config-set installed_paths tools/vendor/wp-coding-standards/wpcs,tools/vendor/phpcsstandards/phpcsutils,tools/vendor/phpcsstandards/phpcsextra
```

### PHPStan

```bash
composer require --working-dir=tools --dev 'szepeviktor/phpstan-wordpress:^1.1'
tools/vendor/bin/phpstan analyze --memory-limit=2G src/
```

## Code Conventions

### Style

- **WordPress Coding Standards** (WordPress-Core ruleset) enforced by PHPCS.
- **Tabs for indentation** (see `.editorconfig`).
- **Yoda conditions** (`if ( null === $var )`).
- **PHPDoc on public/protected methods** with `@param`, `@return`.
- **`WordPress.Files.FileName` is excluded** — files use PSR-4 `PascalCase.php` naming, not WordPress `class-lowercase.php`.

### Naming

- Classes: `PascalCase` in `WorDBless\` namespace.
- Methods/functions: `snake_case`.
- Test files: `*_Test.php` suffix.
- Test methods: `test_` prefix (e.g., `test_insert_post`).
- Constants: `UPPER_SNAKE_CASE`.

### Tests

- Extend `WorDBless\BaseTestCase` (not PHPUnit's `TestCase` directly).
- Use `@before`/`#[Before]` and `@after`/`#[After]` attributes — not `setUp()`/`tearDown()`.
- BaseTestCase handles hook backup/restore and clearing all in-memory data stores between tests.

### Commits

- Imperative mood: "Fix return value" not "Fixed return value" or "Fixes return value".
- Component prefix when relevant: "Metadata: Fix return value for..." or "BaseTestCase: Add attributes for..."
- No conventional-commit prefixes (`feat:`, `fix:`, etc.).

## Architecture

### DB Engine Selection

`Load::load($db_engine)` accepts one of three engines:

- **`dbless`** (default) — No database at all. `Db_Less_Wpdb` extends `wpdb` and short-circuits all SQL. Data modules intercept WordPress hooks and store data in PHP arrays.
- **`sqlite`** — Uses the `sqlite-database-integration` plugin for a real SQLite database. Full SQL support including `WP_Query`.
- **`mysql`** — Uses real MySQL (requires a running MySQL server). Full WordPress database behavior.

The engine is set via the `DB_ENGINE` constant, defined during bootstrap.

### The db.php Drop-in

`src/dbless-wpdb.php` is copied to `wordpress/wp-content/db.php` by `Composer\InstallDropin::copy()` (runs on `post-install-cmd` and `post-update-cmd`). This file:

1. If `DB_ENGINE === 'dbless'`: Defines `Db_Less_Wpdb extends wpdb` which no-ops all database operations. The `query()` method fires the `wordbless_wpdb_query_results` filter, letting data modules intercept SQL patterns via regex.
2. If `DB_ENGINE === 'sqlite'`: Loads the SQLite integration plugin which provides its own `$wpdb` replacement.

### Data Module Pattern (dbless engine)

Each data module (Options, Posts, Users, Metadata) follows the same pattern:

1. **Singleton initialization** via `::init()` (uses the `Singleton` trait or manual equivalent).
2. **Hook registration** in the constructor — hooks into WordPress filters (`alloptions`, `wp_insert_post_data`, `wordbless_wpdb_query_results`, etc.).
3. **In-memory storage** in a public array property (`$options`, `$posts`, `$users`, `$meta`).
4. **Query interception** via `filter_query()` on the `wordbless_wpdb_query_results` filter — uses regex to match specific SQL patterns and returns results from the in-memory store.
5. **Cache clearing** via the `ClearCacheGroup` trait to keep `wp_object_cache` in sync.

### InsertId

`InsertId::$id` is a static counter starting at **10**. Each `bump_and_get()` call increments and returns it. This provides unique IDs across posts, users, and metadata. It does **not** reset between tests.

### BaseTestCase Lifecycle

`BaseTestCase` extends `Yoast\PHPUnitPolyfills\TestCases\TestCase` and provides:

- `@before` / `#[Before]` → `set_up_wordbless()`: Backs up WordPress hook globals (`$wp_actions`, `$wp_current_filter`, `$wp_filter`) on first run.
- `@after` / `#[After]` → `tear_down_wordbless()`: Restores hooks, clears Options, Posts, PostMeta, Users, UserMeta, and deletes upload files.

### Three Test Suites

Each suite has its own bootstrap file that calls `Load::load()` with different parameters:

- `bootstrap.php` → `Load::load()` (dbless, default)
- `bootstrap-uploads.php` → defines `dbless_UPLOADS` constant, then requires `bootstrap.php`
- `bootstrap-sqlite.php` → `Load::load('sqlite')`

The `phpunit.xml` maps suites to test files: `general` includes everything except `Uploads_Test.php` and `SQLite_Test.php`; `uploads` and `sqlite` each run their specific test file.

## Common Pitfalls

1. **CRITICAL: Never edit files in `wordpress/`.** This directory is Composer-managed by `roots/wordpress`. Your changes will be overwritten on `composer install` / `composer update`.

2. **CRITICAL: `db.php` is a copy, not a symlink.** After editing `src/dbless-wpdb.php`, you must re-run `composer install` (or manually copy the file) for changes to take effect. The `InstallDropin::copy()` post-install hook handles this.

3. **PHPStan needs `--memory-limit=2G`.** Without it, PHPStan will run out of memory analyzing the WordPress stubs. The `ci` script includes this flag; make sure you do too when running standalone.

4. **Three test suites require three separate bootstraps.** You can't run all tests with a single `phpunit` invocation unless you use `composer run-script phpunit`, which runs each suite with its correct bootstrap file.

5. **`WP_Query` and `WP_User_Query` don't work with the dbless engine.** The dbless engine only intercepts specific SQL patterns via regex in `filter_query()` methods. Complex query classes that generate arbitrary SQL won't return results. Use the `sqlite` engine if you need full query support.

6. **Tools live in `tools/`, not `vendor/`.** PHPCS and composer-normalize are Phive-managed PHARs installed to `tools/` when `phive install` runs (as part of `composer run-script ci`). PHPStan and WPCS are Composer-installed into `tools/vendor/`. The root `vendor/` is for the project's own dependencies. Note: the PHAR files (`tools/phpcs`, `tools/composer-normalize`) only exist after running `phive install`; before that, use `tools/vendor/bin/` paths.

7. **`dbless-wpdb.php` intentionally has no namespace.** It defines global classes (`Db_Less_Wpdb`) because WordPress expects `db.php` to set up the global `$wpdb`. Don't add a namespace to this file.

8. **`third-party/` is vendored code — don't modify it.** The `sqlite-database-integration/` directory is a copy of the WordPress SQLite integration plugin. Updates should come from upstream.

9. **`InsertId::$id` starts at 10 and doesn't reset between tests.** The first issued ID is 11 (pre-increment). Don't write tests that depend on specific ID values. Use the returned ID from `wp_insert_post()`, `wp_insert_user()`, etc.
