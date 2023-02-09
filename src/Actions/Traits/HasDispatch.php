<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Actions\Traits;

use Blazervel\Blazervel\ClosureCollection;
use Closure;
use Illuminate\Foundation\Bus\PendingChain;
use Illuminate\Support\Facades\Bus;

trait HasDispatch
{
    final protected function chain(Closure ...$handlers): PendingChain
    {
        $handlers = new ClosureCollection(...$handlers);

        return Bus::chain(
            $handlers->all()
        );
    }

    final protected static function dispatch(mixed ...$parameters): mixed
    {

    }

    final protected function dispatchAfterResponse(Closure|callable $handler): mixed
    {
        return Bus::dispatchAfterResponse($handler);
    }
}