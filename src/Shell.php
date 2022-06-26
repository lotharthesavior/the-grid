<?php

namespace Kanata\TheGrid;

use Kanata\TheGrid\Dtos\ShellInput;
use Kanata\TheGrid\Interfaces\ShellInterface;
use Kanata\TheGrid\Services\SshConnector;
use Kanata\TheGrid\Services\Tron;
use Kanata\TheGrid\Services\WebSocket;
use phpseclib3\Net\SSH2;
use Swoole\Process;
use Swoole\WebSocket\Server;

class Shell implements ShellInterface
{
    const STARTING_STATUS = 'starting';
    const PROCESSES_STARTED_STATUS = 'processes-started';
    const CONNECTING_STATUS = 'connecting';
    const CONNECTED_STATUS = 'connected';

    protected SSH2 $ssh;
    protected SshConnector $sshConnector;

    public function __construct(
        protected ShellInput $input,
        ?SshConnector $sshConnector = null
    ) {
        $this->sshConnector = $sshConnector ?? new SshConnector(
            $this->input->host,
            $this->input->sshType,
            $this->input->sshUser,
            $this->input->passKey
        );
    }

    public function start(array $options): void {
        $options = $this->getOptions($options);
        $callback = $options['tronCallback'];

        $callback('Starting...', ['status' => self::STARTING_STATUS]);

        // Create processes...
        $websocketProcess = $this->startWebSocketProcess($options);
        $tronProcess = $this->startTronProcess($callback);

        // Start processes...
        $wsPid = $websocketProcess->start();
        $tronPid = $tronProcess->start();
        $callback('Processes started: WS:' . $wsPid . ', Tron:' . $tronPid, [
            'status' => self::PROCESSES_STARTED_STATUS,
            'tron-pid' => $tronPid,
            'ws-pid' => $wsPid,
        ]);

        $callback('Connecting...', ['status' => self::CONNECTING_STATUS]);
        $connected = $tronProcess->read();
        if ($connected !== Tron::STATE_CONNECTED) {
            Process::kill($wsPid);
            Process::kill($tronPid);
            $callback('Not Connected to SSH service!', []);
            exit;
        }
        $callback('Connected to SSH service!', ['status' => self::CONNECTED_STATUS]);

        // Connected! Let's picture this cluster of information!
        while($wsInput = $websocketProcess->read()) {
            $tronProcess->push($wsInput);
            $commandOut = $tronProcess->read();
            $websocketProcess->push($commandOut);
        }
    }

    private function getOptions(array $options): array
    {
        return array_merge([
            WebSocket::WS_PORT => 8004,
            WebSocket::WS_HOST => '0.0.0.0',
            'tronCallback' => fn() => null,
        ], $options);
    }

    private function startWebSocketProcess(array $options): Process
    {
        $process = new Process(function(Process $worker) use ($options) {
            $worker->name('the-grid-ws-server');

            $callback = $options['tronCallback'];

            $options['messageCallback'] = function(Server $server, int $fd, string $message) use ($worker, $callback) {
                if (!empty($message)) {
                    $worker->write($message);
                } else {
                    $worker->write("");
                }
                $recv = $worker->pop();
                $callback($recv, []);
                $server->push($fd, $recv);
            };

            $ws = new WebSocket;
            $ws->startServer($options);
        });
        $process->useQueue();
        return $process;
    }

    private function startTronProcess(callable $callback): Process
    {
        $process = new Process(function(Process $worker) use ($callback) {
            $worker->name('the-grid-tron');

            $tron = new Tron($this->sshConnector->getSsh());

            $worker->write(Tron::STATE_CONNECTED);

            while ($recv = $worker->pop()) {
                if (null !== $callback) {
                    $callback('Command received from master by Tron: ' . $recv, []);
                }
                $tron->sendOrder($recv, function($output) use ($worker, $recv) {
                    if (!empty($output)) {
                        $worker->write($output);
                    } else {
                        $worker->write('');
                    }
                });
            }
        });
        $process->useQueue();
        return $process;
    }
}