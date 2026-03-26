<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Tests\Unit\Service\Logger;

use Ipedis\Bundle\Websocket\Service\Logger\WebsocketEventLogger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class WebsocketEventLoggerTest extends TestCase
{
    #[Test]
    public function write_error_delegates_to_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Something failed', ['key' => 'val']);

        $websocketEventLogger = new WebsocketEventLogger($logger);
        $websocketEventLogger->writeError('Something failed', ['key' => 'val']);
    }

    #[Test]
    public function write_error_uses_empty_context_by_default(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with('Error message', []);

        $websocketEventLogger = new WebsocketEventLogger($logger);
        $websocketEventLogger->writeError('Error message');
    }

    #[Test]
    public function write_info_delegates_to_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with('Info message', ['ctx' => 1]);

        $websocketEventLogger = new WebsocketEventLogger($logger);
        $websocketEventLogger->writeInfo('Info message', ['ctx' => 1]);
    }

    #[Test]
    public function write_debug_delegates_to_logger_info(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with('Debug message', []);

        $websocketEventLogger = new WebsocketEventLogger($logger);
        $websocketEventLogger->writeDebug('Debug message');
    }

    #[Test]
    public function write_log_delegates_with_level(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('log')
            ->with('warning', 'Watch out', ['detail' => 'x']);

        $websocketEventLogger = new WebsocketEventLogger($logger);
        $websocketEventLogger->writeLog('warning', 'Watch out', ['detail' => 'x']);
    }
}
