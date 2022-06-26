<?php

namespace Kanata\TheGrid\Dtos;

use Kanata\TheGrid\Validators\Host;
use Kanata\TheGrid\Validators\PassKey;
use Kanata\TheGrid\Validators\SshTypeEnum;
use Kanata\TheGrid\Validators\User;
use Spatie\DataTransferObject\DataTransferObject;

class ShellInput extends DataTransferObject
{
    #[Host]
    public string $host;

    #[User]
    public string $sshUser;

    #[PassKey]
    public string $passKey;

    #[SshTypeEnum]
    public string $sshType;

    public array $options;
}
