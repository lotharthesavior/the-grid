<?php

namespace Kanata\TheGrid\Services\Interfaces;

interface WebSocketInterface
{
    public function startServer(array $options): void;
}