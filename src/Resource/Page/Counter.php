<?php

declare(strict_types=1);

namespace MyApp\Resource\Page;

use BEAR\Resource\ResourceObject;

/**
 * GET /counter?initial=42
 *
 * Resource just exposes data; the matching template is at
 * templates/Page/Counter.psx (resolved via FQCN convention).
 */
final class Counter extends ResourceObject
{
    public function onGet(int $initial = 0): static
    {
        $this->body = [
            'count' => $initial,
            'label' => 'Counter',
        ];
        return $this;
    }
}
