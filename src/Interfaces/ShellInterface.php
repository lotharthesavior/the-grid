<?php

namespace Kanata\TheGrid\Interfaces;

use Kanata\TheGrid\Dtos\ShellInput;
use Kanata\TheGrid\Dtos\ClusterOutput;

/**
 * We call Shell Terminal "The Grid".
 *
 * This is the point of orchestration.
 */

interface ShellInterface
{
    public function __construct(ShellInput $input);

    /**
     * Here we bootstrap the communication with The Grid.
     *
     * This procedure starts a WebSocket Server for real time interaction between
     * the different parts: the user and Tron. Tron play for the users!
     *
     * Expected Sub-processes:
     *   - WebSocket Server
     *   - Tron - the shell command executor
     *
     * @param array $options
     * @return void
     */
    public function start(array $options): void;
}