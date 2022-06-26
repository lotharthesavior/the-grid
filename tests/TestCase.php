<?php

namespace Tests;

use Kanata\TheGrid\Dtos\ShellInput;
use Kanata\TheGrid\Services\SshConnector;
use Kanata\TheGrid\Services\WebSocket;
use Kanata\TheGrid\Shell;
use Mockery;
use phpseclib3\Net\SSH2;
use PHPUnit\Framework\TestCase as TestCaseBase;
use Swoole\Process;
use Swoole\Table;
use Swoole\WebSocket\Server;

class TestCase extends TestCaseBase
{
    protected function startInformationCluster(callable $execCallback, callable $startCallback)
    {
        $shell = new Shell(new ShellInput(
            host: '127.0.0.1',
            sshUser: 'root',
            passKey: 'some-pass',
            sshType: 'password',
            options: json_decode('[]')
        ), $this->getSshConnectorMock($execCallback));

        $shell->start(['tronCallback' => $startCallback]);
    }

    protected function getSshConnectorMock(callable $execCallback)
    {
        $sshConnector = Mockery::mock(SshConnector::class);
        $ssh = Mockery::mock(SSH2::class);
        $ssh->shouldReceive('setKeepAlive');
        $ssh->shouldReceive('exec')->andReturnUsing($execCallback);
        $sshConnector->shouldReceive('getSsh')->andReturn($ssh);
        return $sshConnector;
    }

    protected function getVerificationTable()
    {
        $table = new Table(1024);
        $table->column('state', Table::TYPE_INT);
        $table->create();
        return $table;
    }

    protected function runInProcess(callable $callback, int $timeout = 5)
    {
        $process = new Process($callback);
        $process->setTimeout($timeout);
        $pid = $process->start();
        $process->read();
        Process::kill($pid);
    }
}