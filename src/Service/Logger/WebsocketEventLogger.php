<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Service\Logger;

use Psr\Log\LoggerInterface;

class WebsocketEventLogger
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function writeError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function writeInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function writeDebug(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Get channel log from logger.
     */
    protected function getChannel(): string
    {
        return $this->logger->getName();
    }

    public function writeLog(string $level, string $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
