<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Service\Logger;

use Psr\Log\LoggerInterface;

class WebsocketEventLogger
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function writeError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function writeInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function writeDebug(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function writeLog(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
