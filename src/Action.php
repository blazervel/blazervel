<?php

namespace Blazervel\Blazervel;

use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\AsObject;
use Lorisleiva\Actions\Concerns\AsJob;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\AsListener;
use Lorisleiva\Actions\Concerns\AsFake;

class Action
{
    use AsController;
    use AsObject;
    // use AsJob;
    // use AsCommand;
    // use AsListener;
    // use AsFake;

    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    // protected $rootView = 'blazervel::app';


    // public function getRootView()
    // {
    //     return $this->rootView;
    // }

    protected function validate(array $rules = null): array
    {
        $request = request();

        if ($this->rules ?? $rules ?: false) {
            $request->validate(
                $this->rules
            );
        }

        return $request->all();
    }
}
