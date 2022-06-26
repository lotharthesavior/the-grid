<?php

namespace Deployer\Services;

use Kanata\TheGrid\Services\Abstractions\SecureShell;
use phpseclib3\Net\SFTP;

class SftpConnector extends SecureShell
{
    protected string $type = SFTP::class;
}