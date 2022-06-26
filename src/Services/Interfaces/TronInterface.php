<?php

namespace Kanata\TheGrid\Services\Interfaces;

use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

/**
 * Tron play for the users!
 */

interface TronInterface
{
    public function __construct(?SSH2 $ssh = null, ?SFTP $sftp = null);

    /**
     * This method orders Tron to execute a command and to serve you
     * its output.
     *
     * @param string $command
     * @param callable $callback
     * @return void
     */
    public function sendOrder(string $command, callable $callback): void;
}