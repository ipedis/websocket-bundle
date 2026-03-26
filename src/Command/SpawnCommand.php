<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Command;

use Ipedis\Bundle\Websocket\Service\Server\HttpServer;
use Ipedis\Bundle\Websocket\Service\Topic\TopicManager;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Loop;
use React\Socket\SocketServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'ip:ws:spawn', description: 'Spawn websocket server')]
class SpawnCommand extends Command
{
    public function __construct(
        protected TopicManager $topicManager,
        protected string $wsHost,
        protected int $wsPort,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $symfonyStyle->title('Websocket spawner');

        /**
         *  Event Loop.
         */
        $eventLoop = Loop::get();

        /**
         * Websocket server to handle the websocket.
         */
        $socketServer = new SocketServer(
            sprintf('%s:%s', $this->wsHost, $this->wsPort),
            [],
            $eventLoop
        );

        /**
         * Wamp server to handle subscriptions.
         */
        $wampServer = new WampServer($this->topicManager);

        /**
         * I/O server to handle the low level events (read/write) of a socket.
         */
        $ioServer = new IoServer(
            new HttpServer(
                new WsServer($wampServer)
            ),
            $socketServer,
            $eventLoop
        );

        $ioServer->run();

        $symfonyStyle->success('websocket is stopped');

        return Command::SUCCESS;
    }
}
