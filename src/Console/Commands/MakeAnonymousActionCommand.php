<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Console\Commands;

class MakeAnonymousActionCommand extends MakeActionCommand
{
    protected $name = 'blazervel:make:anonymous';

    protected $description = 'Create a new Blazervel Anonymous Action';

    protected $type = 'Blazervel Anonymous Action';

    protected bool $anonymous = true;
}
