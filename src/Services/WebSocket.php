<?php

namespace Kanata\TheGrid\Services;

use Kanata\TheGrid\Services\Interfaces\WebSocketInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class WebSocket implements WebSocketInterface
{
    const WS_PORT = 'ws-port';
    const WS_HOST = 'ws-host';

    public function startServer(array $options): void
    {
        $websocket = new Server($options['ws-host'], $options[self::WS_PORT]);

        $this->handleMessageEvent($websocket, $options);
        $this->handleRequestEvent($websocket, $options);
        $this->handleShutdownEvent($websocket, $options);

        $websocket->start();
    }

    private function handleShutdownEvent(Server $websocket, array $options): void
    {
        if (!isset($options['shutdownCallback'])) {
            $websocket->on('shutdown', fn(Server $s) => null);
            return;
        }

        $websocket->on(
            'shutdown',
            fn(Server $s) => call_user_func($options['shutdownCallback'], $s)
        );
    }

    private function handleMessageEvent(Server $websocket, array $options): void
    {
        if (!isset($options['messageCallback'])) {
            $websocket->on('message', fn(Server $s, Frame $f) => null);
            return;
        }

        $websocket->on(
            'message',
            fn(Server $s, Frame $f) => call_user_func($options['messageCallback'], $s, $f->fd, $f->data)
        );
    }

    private function handleRequestEvent(Server $websocket, array $options): void
    {
        if (!isset($options['requestCallback'])) {
            $websocket->on('request', function(Request $request, Response $response) {
                $response->header("Content-Type", "text/html");
                $response->header("Charset", "UTF-8");
                $response->end(file_get_contents(__DIR__ . '/../../views/web-interface.html'));
            });
            return;
        }

        $websocket->on(
            'request',
            fn(Request $r, Response $re) => call_user_func($options['requestCallback'], $r, $re)
        );
    }
}