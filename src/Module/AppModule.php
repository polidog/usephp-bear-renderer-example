<?php
declare(strict_types=1);
namespace MyApp\Module;

use BEAR\Package\AbstractAppModule;
use BEAR\Package\PackageModule;
use BEAR\Resource\RenderInterface;
use Koriym\EnvJson\EnvJson;
use Polidog\UsePhp\UsePHP;
use Polidog\UsephpBearRenderer\UsePhpActionResponder;
use Polidog\UsephpBearRenderer\UsePhpRenderer;
use function dirname;

final class AppModule extends AbstractAppModule
{
    protected function configure(): void
    {
        (new EnvJson())->load(dirname(__DIR__, 2));
        $this->install(new PackageModule());

        // The UsePHP application instance carries the snapshot serializer the
        // renderer + responder share. Snapshots are signed with the secret —
        // anything posted back without the matching signature is rejected, so
        // this is the trust boundary for hook state coming from the client.
        $usePhp = (new UsePHP())->setSnapshotSecret(
            $_ENV['USEPHP_SNAPSHOT_SECRET'] ?? 'dev-snapshot-secret-change-me'
        );

        $renderer = new UsePhpRenderer(
            templateDir: $this->appMeta->appDir . '/templates',
            cacheDir:    $this->appMeta->tmpDir . '/psx',
            autoCompile: true,
            templateResolver: null,
            app: $usePhp,
        );

        // toInstance() bypasses Ray.Di's auto-constructor — necessary because
        // UsePhpRenderer's ctor params aren't bound elsewhere.
        $this->bind(RenderInterface::class)->toInstance($renderer);
        $this->bind(UsePHP::class)->toInstance($usePhp);
        $this->bind(UsePhpRenderer::class)->toInstance($renderer);
        $this->bind(UsePhpActionResponder::class)->toInstance(
            new UsePhpActionResponder($renderer)
        );
    }
}
