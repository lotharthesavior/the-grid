<?php

namespace Kanata\TheGrid\Services;

use Kanata\TheGrid\Services\Interfaces\TronInterface;
use phpseclib3\File\ANSI;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

class Tron implements TronInterface
{
    const STATE_CONNECTED = 'connected';

    protected array $commandHistory;

    public function __construct(
        protected ?SSH2 $ssh = null,
        protected ?SFTP $sftp = null
    ) {
        $this->ssh->setKeepAlive(2);
    }

    public function sendOrder(string $command, callable $callback): void
    {
        $this->commandHistory[] = $command;
        $this->ssh->exec($command, fn($o) => $callback($o));
    }
}