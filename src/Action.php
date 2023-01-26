<?php

namespace Blazervel\Blazervel;

use Blazervel\Blazervel\Actions\Traits\HasAuthorize;
use Blazervel\Blazervel\Actions\Traits\HasValidate;

abstract class Action
{
    use HasAuthorize, HasValidate;
}
