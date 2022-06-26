<?php

namespace Kanata\TheGrid\Commands;

use Dotenv\Dotenv;
use Kanata\TheGrid\Dtos\ShellInput;
use Kanata\TheGrid\Services\WebSocket;
use Kanata\TheGrid\Shell;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Validation;
use function Co\run;

class StartTheGridCommand extends Command
{
    protected static $defaultName = 'start';

    protected static $defaultDescription = 'Start The Grid.';

    protected function configure(): void
    {
        $this->setHelp(self::$defaultDescription)
            ->addArgument('config-file', InputArgument::REQUIRED, 'Configuration file to be evaluated for SSH Connection.')
            ->addOption('ws-port', null, InputOption::VALUE_OPTIONAL, 'Custom WebSocket Port.')
            ->addOption('ws-host', null, InputOption::VALUE_OPTIONAL, 'Custom WebSocket Host.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $v = $input->getOption('verbose');
        $config = $input->getArgument('config-file');
        $wsHost = $input->getOption('ws-host');
        $wsPort = $input->getOption('ws-port');

        if (!$this->validate($io, $wsHost, $wsPort)) {
            return Command::FAILURE;
        }

        $this->loadEnv($config);

        $shell = new Shell(new ShellInput(
            host: $_ENV['SSH_HOST'],
            sshUser: $_ENV['SSH_USER'],
            passKey: $_ENV['SSH_PASSKEY'],
            sshType: $_ENV['SSH_TYPE'],
            options: json_decode($_ENV['SSH_OPTIONS'])
        ));

        $options = [];

        if (null !== $wsPort) {
            $options[WebSocket::WS_PORT] = $wsPort;
        }

        if (null !== $wsHost) {
            $options[WebSocket::WS_HOST] = $wsHost;
        }

        $options['tronCallback'] = function(string $o, array $state = []) use ($v, $io) {
            if ($v) {
                $io->info('Callback: ' . $o);
            }
        };

        $shell->start($options);

        $this->listenKillSignals();

        return Command::SUCCESS;
    }

    private function loadEnv(string $config): void
    {
        $configInfo = pathinfo($config);
        $dotenv = Dotenv::createImmutable($configInfo['dirname'], $configInfo['basename']);
        $dotenv->safeLoad();
    }

    private function listenKillSignals(): void
    {
        run(function() {
            System::waitSignal(SIGKILL, -1);
        });
    }

    private function validate(SymfonyStyle $io, ?string $wsHost, ?int $wsPort): bool
    {
        $violations = [];
        $validator = Validation::createValidator();

        if (null !== $wsHost) {
            $portViolations = $validator->validate($wsHost, [
                new Type('string'),
                new Ip,
            ]);
            foreach ($portViolations as $portViolation) {
                $violations[] = $portViolation;
            }
        }

        if (null !== $wsPort) {
            $hostViolations = $validator->validate($wsPort, [new Type('integer')]);
            foreach ($hostViolations as $hostViolation) {
                $violations[] = $hostViolation;
            }
            $io->error('Invalid Inputs: ' . PHP_EOL . implode(PHP_EOL, $violations));
        }

        return empty($violations);
    }
}