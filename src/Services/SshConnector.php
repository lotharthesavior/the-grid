<?php

namespace Kanata\TheGrid\Services;

use Kanata\TheGrid\Services\Abstractions\SecureShell;
use phpseclib3\Net\SSH2;

class SshConnector extends SecureShell
{
    protected string $type = SSH2::class;
}