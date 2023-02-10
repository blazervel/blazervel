<?php

namespace Blazervel\Blazervel\Actions\Traits;

trait HasViewPath
{
    final protected static function viewPath(): string
    {
        $class = get_called_class();                       // App\Actions\Inertia\Pages\ContactUs
        $viewPath = explode('Actions\\Inertia\\', $class); // ['Pages', 'ContactUs']
        $viewPath = implode('/', $viewPath);               // Pages/ContactUs

        return $viewPath;
    }
}
