<?php


namespace Ipedis\Bundle\Websocket\Service\Logger;


use Psr\Log\LoggerInterface;

class WebsocketEventLogger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function writeError(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function writeInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param string $message
     * @param array  $context
     */
    public function writeDebug(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Get channel log from logger.
     *
     * @return string
     */
    protected function getChannel(): string
    {
        return $this->logger->getName();
    }

    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     */
    public function writeLog(string $level, string $message, array $context = [])
    {
        $this->logger->log($level, $message, $context);
    }
}
