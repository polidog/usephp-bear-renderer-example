<?php

declare(strict_types=1);

namespace MyApp\Resource\App;

use BEAR\Resource\ResourceObject;

/**
 * Counter API resource.
 *
 * Holds the increment/decrement/reset rules for the counter so that the
 * Page resource (and the PSX template under it) doesn't have to know how
 * a count transitions. Page\Counter calls this resource via the BEAR
 * resource client (`app://self/counter`) and the PSX template's onClick
 * handlers post a precomputed `next` value back through usephp.js.
 *
 * Stateless: state lives in the request (current `count`) and in the
 * usePHP snapshot on the page; this resource is the pure transition
 * function.
 */
final class Counter extends ResourceObject
{
    /**
     * POST app://self/counter
     *
     * @param int    $count  Current count.
     * @param string $action `inc` | `dec` | `reset`. Anything else is treated
     *                       as the identity transition (returns count unchanged).
     */
    public function onPost(int $count = 0, string $action = ''): static
    {
        $next = match ($action) {
            'inc' => $count + 1,
            'dec' => $count - 1,
            'reset' => 0,
            default => $count,
        };

        $this->body = ['count' => $next];

        return $this;
    }
}
