# usephp-bear-module-example

English / [日本語](README.ja.md)

A working BEAR.Sunday application demonstrating [polidog/usephp-bear-module](https://github.com/polidog/usephp-bear-module) — the `RenderInterface` adapter that renders PSX (TSX-like) templates from [polidog/use-php](https://github.com/polidog/usePHP).

```
GET /                  →  Hello BEAR.Sunday  (templates/Page/Index.psx via FQCN convention)
GET /counter?initial=N →  Counter page       (templates/Page/Counter.psx via FQCN convention)
GET /about             →  About page         (templates/shared/About.psx via #[Template] attribute)
```

## Requirements

- PHP 8.5+
- Composer

## Run

```bash
composer install
php -S localhost:8080 -t public
```

Then visit `http://localhost:8080/`, `/counter?initial=42`, or `/about`.

The first request to each route compiles the matching `.psx` to `var/tmp/psx/<sha1>.php`. Subsequent requests reuse the cache. For production, run `vendor/bin/usephp compile templates --cache=var/tmp/psx` during deploy and pass `autoCompile: false` in `AppModule`.

## How it's wired

### `src/Module/AppModule.php`

Installs `PackageModule` then directly binds `RenderInterface` to a `UsePhpRenderer` instance:

```php
$renderer = new UsePhpRenderer(
    templateDir: $this->appMeta->appDir . '/templates',
    cacheDir:    $this->appMeta->tmpDir . '/psx',
    autoCompile: true,
);
$this->bind(RenderInterface::class)->toInstance($renderer);
```

The published `UsePhpRendererModule` would let you write `$this->install(new UsePhpRendererModule(...))` instead, but its constructor-injection path has a DI quirk we're fixing upstream — the explicit `bind()->toInstance()` here is bullet-proof in the meantime.

### Resources (`src/Resource/Page/`)

Plain `ResourceObject` subclasses. `onGet` populates `$this->body`; the renderer takes that body and feeds it to a `.psx` template as `$props`.

- `Index.php` — convention-based template path
- `Counter.php` — convention-based template path, demonstrates passing a query parameter
- `About.php` — `#[Template('shared/About.psx')]` overrides the convention so the template lives outside `templates/Page/`

### Templates (`templates/`)

Plain PSX files. They `return` a closure `fn(array $props): Element` that produces the markup.

- `templates/Page/Index.psx` — root page
- `templates/Page/Counter.psx` — counter page
- `templates/shared/About.psx` — about page (referenced via attribute)

## Notes / known limitations

- BEAR's `RenderInterface::render($ro)` doesn't expose which `on*` method was invoked, so `#[Template]` is class-level only.
- Embedded `<style>` tags inside `.psx` collide with PSX's `{...}` brace expressions. Use inline `style="..."` attributes (or per-element `style={$str}`) instead.
- PHP 8 attribute syntax (`#[...]`) inside template TEXT content can confuse the underlying nikic/php-parser even with error recovery. Avoid literal `#[Foo]` in human-readable copy.

## License

MIT (matches BEAR.Skeleton).
