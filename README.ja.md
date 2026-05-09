# usephp-bear-renderer-example

[English](README.md) / 日本語

[polidog/usephp-bear-renderer](https://github.com/polidog/usephp-bear-renderer) を使った動く BEAR.Sunday アプリケーションのサンプルです。`usephp-bear-renderer` は BEAR の `RenderInterface` 実装で、[polidog/use-php](https://github.com/polidog/usePHP) の PSX(TSX 風)テンプレートでリソースをレンダリングします。

```
GET /                  →  Hello BEAR.Sunday  (templates/Page/Index.psx を FQCN 規約で解決)
GET /counter?initial=N →  Counter ページ      (templates/Page/Counter.psx を FQCN 規約で解決)
GET /about             →  About ページ        (templates/shared/About.psx を #[Template] 属性で指定)
```

## 必要環境

- PHP 8.5 以上
- Composer

## 動かす

```bash
composer install
php -S localhost:8080 -t public
```

ブラウザで `http://localhost:8080/`、`/counter?initial=42`、`/about` を開いてください。

各ルートへの初回リクエスト時に該当 `.psx` が `var/tmp/psx/<sha1>.php` にコンパイルされ、以降のリクエストはキャッシュを使い回します。本番では deploy 時に `vendor/bin/usephp compile templates --cache=var/tmp/psx` を流し、`AppModule` の `autoCompile` を `false` に切り替えるのが推奨です。

## 配線(どこで何をしているか)

### `src/Module/AppModule.php`

`PackageModule` を install したあと、`RenderInterface` を直接 `UsePhpRenderer` インスタンスに bind しています。

```php
$renderer = new UsePhpRenderer(
    templateDir: $this->appMeta->appDir . '/templates',
    cacheDir:    $this->appMeta->tmpDir . '/psx',
    autoCompile: true,
);
$this->bind(RenderInterface::class)->toInstance($renderer);
```

公開されている `UsePhpRendererModule` を使えば `$this->install(new UsePhpRendererModule(...))` の一行で済むはずですが、コンストラクタインジェクションの経路に DI 上の不具合があり、現状は inline の `bind()->toInstance()` が確実です(本家側 follow-up で対応予定)。

### リソース (`src/Resource/Page/`)

普通の `ResourceObject` のサブクラスです。`onGet` で `$this->body` を埋め、レンダラーがその `$body` を `.psx` テンプレートに `$props` として渡します。

- `Index.php` — FQCN 規約のテンプレートパス
- `Counter.php` — FQCN 規約 + クエリパラメータの受け渡し
- `About.php` — `#[Template('shared/About.psx')]` で規約を上書きし、`templates/Page/` の外にテンプレートを置く例

### テンプレート (`templates/`)

純粋な PSX ファイル。`fn(array $props): Element` のクロージャを `return` するだけです。

- `templates/Page/Index.psx` — トップページ
- `templates/Page/Counter.psx` — カウンターページ
- `templates/shared/About.psx` — About ページ(属性経由で参照)

## 仕様メモ・既知の制限

- BEAR の `RenderInterface::render($ro)` は呼び出された `on*` メソッドを通知してくれないため、`#[Template]` はクラス単位のみのサポートです。メソッド単位の override は動的に判別できないため意図的に未対応にしています。
- `.psx` 内に `<style>...</style>` を直接書くと、CSS の `{...}` が PSX のブレース式と衝突します。要素ごとの `style="..."` 属性または `style={$str}` を使ってください。
- テンプレートの**テキスト中**に PHP 8 のアトリビュート構文(`#[Foo]`)を含めると、エラー回復モードでも nikic/php-parser を混乱させることがあります。説明文の中で `#[...]` を引用するのは避け、代わりに「Template attribute」のように書くのが安全です。

## 仕組み

```
HTTP リクエスト
   ↓
BEAR.Sunday Router → リソース(Counter / Index / About)
   ↓
ResourceObject::onGet → $this->body を array でセット
   ↓
ResourceObject::__toString
   ↓
UsePhpRenderer::render($ro)
   ├─ resolveTemplatePath: #[Template] → FQCN 規約
   ├─ loadCompiled: var/tmp/psx/<sha1>.php(なければ Compiler 実行)
   ├─ require → Closure 取得 → $body を渡して呼び出し → Element
   └─ usePHP の Renderer で Element → HTML
   ↓
$ro->view ← HTML
   ↓
HttpResponder が echo
```

## 関連リポジトリ

- [polidog/usePHP](https://github.com/polidog/usePHP) — PSX コンパイラと `H::xxx()` ランタイム
- [polidog/usephp-bear-renderer](https://github.com/polidog/usephp-bear-renderer) — このサンプルが使っている BEAR 用アダプタ

## ライセンス

MIT(BEAR.Skeleton と同じ)。
