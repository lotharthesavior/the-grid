<?php

namespace Kanata\TheGrid\Services\Abstractions;

use Exception;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;

abstract class SecureShell
{
    const PASSWORD_TYPE = 'password';
    const PRIVATE_KEY_TYPE = 'private_key';

    protected SSH2|SFTP $ssh;

    /**
     * @param string $host
     * @param string $type
     * @param string $username
     * @param string $password
     * @throws Exception In case SFTP connection fails.
     */
    public function __construct(string $host, string $type, string $username, string $password)
    {
        $this->ssh = new $this->type($host);

        if (self::PASSWORD_TYPE === $type) {
            if (!$this->ssh->login($username, $password)) {
                throw new Exception('Login failed');
            }
        } else {
            $key = PublicKeyLoader::load(file_get_contents($password));
            if (!$this->ssh->login($username, $key)) {
                throw new Exception('Login failed');
            }
        }
        $this->ssh->setTimeout(0);
    }

    public function getSsh(): SSH2|SFTP
    {
        return $this->ssh;
    }
}