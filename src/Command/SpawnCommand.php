<?php
namespace Ipedis\Bundle\Websocket\Command;

use Ipedis\Bundle\Websocket\Service\Server\HttpServer;
use Ipedis\Bundle\Websocket\Service\Topic\TopicManager;
use Ratchet\Server\IoServer;
use Ratchet\Wamp\WampServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory;
use React\Socket\Server;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


class SpawnCommand extends Command
{
    protected static $defaultName = 'ip:ws:spawn';

    /**
     * @var string
     */
    protected $wsHost;

    /**
     * @var int
     */
    protected $wsPort;

    /**
     * @var TopicManager
     */
    protected $topicManager;

    public function __construct(
        TopicManager $topicManager,
        string $wsHost,
        int $wsPort,
        string $name = null
    ) {
        parent::__construct($name);
        $this->wsHost = $wsHost;
        $this->wsPort = $wsPort;
        $this->topicManager = $topicManager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Spawn websocket server')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Websocket spawner');

        /**
         *  Event Loop.
         */
        $eventLoop = Factory::create();

        /**
         * Websocket server to handle the websocket.
         */
        $websocketServer = new Server(
            sprintf('%s:%s', $this->wsHost, $this->wsPort),
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
            $websocketServer,
            $eventLoop
        );

        $ioServer->run();

        $io->success('websocket is stopped');
    }
}
