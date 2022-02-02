<?php
namespace Ipedis\Bundle\Websocket\Service\Topic;

use Doctrine\ORM\EntityManagerInterface;
use Ipedis\Bundle\Websocket\Channel\ChannelRegistry;
use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
use Ipedis\Bundle\Websocket\Exception\ChannelNotFoundException;
use Ipedis\Bundle\Websocket\Service\Logger\WebsocketEventLogger;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Wamp\WampServerInterface;

class TopicManager implements WampServerInterface
{
    const PING_TOPIC = 'ping';

    /**
     * @var ChannelRegistry
     */
    protected ChannelRegistry $registry;

    /**
     * @var WebsocketEventLogger
     */
    protected WebsocketEventLogger $logger;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    public function __construct(
        ChannelRegistry $channelRegistry,
        WebsocketEventLogger $logger,
        EntityManagerInterface $em
    ) {
        $this->em = $em;
        $this->registry = $channelRegistry;
        $this->logger = $logger;
    }

    /**
     * A request to subscribe to a topic has been made.
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic                 $topic The topic to subscribe to
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        /*
         * Refresh connection if timeout
         */
        $this->refreshDbConnection();

        /*
         * Log incoming request
         */
        $this->logger->writeInfo(sprintf('Received subscribe request for topic {%s}', $topic->getId()));
        try {
            /*
             * Get channel where to subscribe for the topic
             */
            $channel = $this->registry->getChannelForPattern($topic->getId());
            /*
             * Persist topic
             */
            $channel->persistTopic($topic->getId(), $topic);
            /*
             * process subscribe
             */
            $channel->onSubscribe($conn, $topic);
        } catch (ChannelNotFoundException $exception) {
            $this->logger->writeError($exception->getMessage());
        }
    }

    /**
     * A client is attempting to publish content to a subscribed connections on a URI.
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic                 $topic    The topic the user has attempted to publish to
     * @param string                       $event    Payload of the publish
     * @param array                        $exclude  A list of session IDs the message should be excluded from (blacklist)
     * @param array                        $eligible A list of session Ids the message should be send to (whitelist)
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        /*
         * Refresh connection if timeout
         */
        $this->refreshDbConnection();

        /*
         * Log incoming request
         */
        $this->logger->writeInfo(sprintf('Received publish request for topic {%s}', $topic->getId()));

        /*
         * Pinging websocket to keep connection alive
         */
        if (self::PING_TOPIC === $topic->getId()) {
            // Ping
            $conn->event($topic->getId(), json_encode(['message' => 'pong']));

            return;
        }

        /*
         * Transform event payload to array.
         */
        if (is_array($event)) {  // Front send array of string we he want to regenerate.
            $payload = $event;
        } else { // Worker send json string.
            $payload = json_decode($event, true);
        }

        try {
            /*
             * Get the appropriated channel
             */
            $channel = $this->registry->getChannelForPattern($topic->getId());

            /*
             * Execute onpublish process
             */
            $channel->onPublish($conn, $topic, $payload);
        } catch (ChannelNotFoundException $exception) {
            $this->logger->writeError($exception->getMessage());
        }
    }

    /**
     * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
     * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method.
     *
     * @param ConnectionInterface $conn
     * @param \Exception          $e
     *
     * @throws \Exception
     */
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        foreach ($this->registry->getChannels() as $channel) {
            $channel->onError($conn, $e);
        }
        $this->logger->writeError(sprintf('Websocket got error: %s', $e->getMessage()));
    }

    /**
     * An RPC call has been received.
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string                       $id     The unique ID of the RPC, required to respond to
     * @param string|Topic                 $topic  The topic to execute the call against
     * @param array                        $params Call parameters received from the client
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        $conn->callError($id, $topic, 'RPC not supported');
    }

    /**
     * When a new connection is opened it will be passed to this method.
     *
     * @param ConnectionInterface $conn The socket/connection that just connected to your application
     *
     * @throws \Exception
     */
    public function onOpen(ConnectionInterface $conn)
    {
    }

    /**
     * This is called before or after a socket is closed (depends on how it's closed).  SendMessage to $conn will not result in an error if it has already been closed.
     *
     * @param ConnectionInterface $conn The socket/connection that is closing/closed
     *
     * @throws \Exception
     */
    public function onClose(ConnectionInterface $conn)
    {
        /** @var ChannelInterface $channel */
        foreach ($this->registry->getChannels() as $channel) {
            $channel->onClose($conn);
        }
    }

    /**
     * A request to unsubscribe from a topic has been made.
     * No need to anything, since WampServer adds and removes subscribers to Topics automatically.
     *
     * @param \Ratchet\ConnectionInterface $conn
     * @param string|Topic                 $topic The topic to unsubscribe from
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
    }

    /**
     * Refresh db connection.
     */
    protected function refreshDbConnection()
    {
        $this->logger->writeInfo(sprintf('Database connection status %s', $this->em->getConnection()->isConnected()));

        if (false === $this->em->getConnection()->isConnected()) {
            $this->em->getConnection()->close();
            $this->em->getConnection()->connect();

            $this->logger->writeInfo('Database connection reset');
        }
    }
}
