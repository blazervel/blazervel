<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Console\Commands;

class MakeInertiaActionCommand extends MakeActionCommand
{
    protected $name = 'blazervel:make:inertia';

    protected $description = 'Create a new Blazervel Inertia Controller Action';

    protected $type = 'Blazervel Inertia Action';

    protected bool $inertia = true;
}