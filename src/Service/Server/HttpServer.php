<?php

declare(strict_types=1);

namespace Ipedis\Bundle\Websocket\Service\Server;

use Ratchet\Http\HttpServer as BaseHttpServer;
use Ratchet\Http\HttpServerInterface;

class HttpServer extends BaseHttpServer
{
    public function __construct(HttpServerInterface $component)
    {
        parent::__construct($component);
        $this->_reqParser->maxSize = 4096 * 4;
    }
}
