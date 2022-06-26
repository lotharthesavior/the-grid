<?php

namespace Tests\Unit;

use Kanata\TheGrid\Shell;
use Swoole\Process;
use Tests\TestCase;

class ShellTest extends TestCase
{
    protected int $tronPid;
    protected int $wsPid;

    public function test_tron_process_starts()
    {
        // TODO
        $this->assertTrue(true);
    }

    public function test_ws_process_starts()
    {
        // TODO
        $this->assertTrue(true);
    }

    public function test_can_connect_to_ssh_server()
    {
        $table = $this->getVerificationTable();
        $table->set('check', ['state' => 0]);

        $this->runInProcess(function(Process $worker) use (&$table) {
            $this->startInformationCluster(
                function ($message, $callback) {
                },
                function (string $message, array $status = []) use (&$table, $worker) {
                    if (!isset($status['status'])) {
                        return;
                    }

                    if (Shell::PROCESSES_STARTED_STATUS === $status['status']) {
                        $this->tronPid = $status['tron-pid'];
                        $this->wsPid = $status['ws-pid'];
                    }

                    if (Shell::CONNECTED_STATUS === $status['status']) {
                        $table->set('check', ['state' => 1]);
                        Process::kill($this->tronPid);
                        Process::kill($this->wsPid);
                        $worker->write('done!');
                    }
                }
            );
        });

        $this->assertEquals(1, $table->get('check', 'state'));
    }
}