<?php

namespace Blazervel\Blazervel\Console\Commands;

use Blazervel\Blazervel\Actions\Config\Actions;
use Blazervel\Blazervel\Actions\Config\Controllers;
use Blazervel\Blazervel\Actions\Config\BlazervelControllers;
use Blazervel\Blazervel\Actions\Config\Jobs;
use Blazervel\Blazervel\Actions\Config\Translations;
use Blazervel\Blazervel\Actions\Config\Routes;
use Blazervel\Blazervel\Actions\Config\Models;
use Blazervel\Blazervel\Actions\Config\Notifications;
use Blazervel\Blazervel\Providers\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Command;

class BuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blazervel:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate json config/schema files for Blazervel';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $directory = ServiceProvider::path('dist/config');

        File::ensureDirectoryExists($directory);

        collect([
            Translations::class,
            Routes::class,
            Models::class,
            BlazervelControllers::class,
            // Notifications::class,
            // Actions::class,
            // Jobs::class,
        ])->each(fn ($helper) => (
            file_put_contents(
                "{$directory}/{$helper::outputFileName()}",
                $helper::outputFileContents()
            )
        ));

        $this->comment('Blazervel json config files built for translations, routes, models, notifications, actions, and jobs');

        return 0;
    }
}
