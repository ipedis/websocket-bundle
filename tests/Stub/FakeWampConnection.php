<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Stub;

use Ratchet\ConnectionInterface;

/**
 * Stub that mimics WampConnection's dynamic methods (event, callError).
 */
final class FakeWampConnection implements ConnectionInterface
{
    /** @var list<array{method: string, args: list<mixed>}> */
    public array $calls = [];

    public function send($data)
    {
        return $this;
    }

    public function close(): void
    {
    }

    /**
     * @param list<mixed> $arguments
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->calls[] = ['method' => $name, 'args' => $arguments];

        return null;
    }
}
