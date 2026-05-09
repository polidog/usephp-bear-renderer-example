<?php

declare(strict_types=1);

namespace MyApp\Resource\Page;

use BEAR\Resource\ResourceObject;
use Polidog\UsephpBearRenderer\Annotation\Template;

/**
 * GET /about
 *
 * #[Template] points the renderer at templates/shared/About.psx instead
 * of the FQCN convention path (templates/Page/About.psx).
 */
#[Template('shared/About.psx')]
final class About extends ResourceObject
{
    public function onGet(): static
    {
        $this->body = [
            'package' => 'polidog/usephp-bear-renderer',
            'package_url' => 'https://github.com/polidog/usephp-bear-renderer',
            'usephp_url' => 'https://github.com/polidog/usePHP',
        ];
        return $this;
    }
}
