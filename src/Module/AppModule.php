<?php
declare(strict_types=1);
namespace MyApp\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Resource\RenderInterface;
use Koriym\EnvJson\EnvJson;
use Polidog\UsephpBearRenderer\UsePhpRenderer;
use function dirname;

final class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new EnvJson())->load(dirname(__DIR__, 2));
        $this->install(new PackageModule());

        $renderer = new UsePhpRenderer(
            templateDir: $this->appMeta->appDir . '/templates',
            cacheDir:    $this->appMeta->tmpDir . '/psx',
            autoCompile: true,
        );
        // toInstance() bypasses Ray.Di's auto-constructor — necessary because
        // UsePhpRenderer's ctor params aren't bound elsewhere.
        $this->bind(RenderInterface::class)->toInstance($renderer);
    }
}
