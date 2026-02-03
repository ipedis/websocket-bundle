# README #

***Global goal***

The websocket will handle various behavior which can be classified on 3 distinct category : 
- *tracking* ability to track and count activity on the wysiwyg. Goal is to determine what our performance per publication, pages…

- *vocalization* as admin generation tracking, we would like to be able to require mp3 generation and see, in live, the progression

- *Syncronization*  if more than one operator are working on the same publication article view, we would like to synchronize there work without having to refresh or making ajax call.

### Who do I talk to? ###

* Ipedis Mauritius

Installation
==

Update `composer.json` and add a repository:

    "repositories": [
        {
            "type": "vcs",
            "url": "bitbucket:ipedis/websocket-bundle.git"
        }
    ]
    
    
Require the library:

    "require": {
        "ipedis/websocket-bundle": "^1.0.0"
    }
----
For Php >=8.2 and Symfony >= 6.4 

    "require": {
        "ipedis/websocket-bundle": "^2.0.0"
    }


----

Configuration
==

on `config/packages` folder, create yaml configuration like following:

    ipedis_websocket:
      connection:
        websocket_host: 127.0.0.1
        websocket_port: 8081
    
*all configurations have default value so there are all optional*

create channel and handler `websocket` for monolog:

    monolog:
      channels: [YOUR_EXISTINGS_CHANNELS..., "websocket"]
    
      handlers:
        .... // Existing handlers goes here
        websocket:
          level: debug
          type:  stream
          path:  "%kernel.logs_dir%/websocket.log"
          channels: ["websocket"]
---

on `config/bundles.php` add `WebsocketBundle` as bellow:

    Ipedis\Bundle\Websocket\WebsocketBundle::class => ['all' => true]

---

Get Started: Publish and Subscribe.
==

**Create event new websocket channel**

Create service like following: 
    
    use Ipedis\Bundle\Websocket\Channel\Contract\ChannelInterface;
    use Ipedis\Bundle\Websocket\Channel\ChannelAbstract;
    
    class YouChannel extends ChannelAbstract implements ChannelInterface
    {
       ...
    }

This will automatically tag the service as ``ps.websocket_channel``

---

Start websocket server
==

To start the server, run this command
        ``php bin/console ip:ws:spawn``

