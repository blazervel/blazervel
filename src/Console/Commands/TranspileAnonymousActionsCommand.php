<?php declare (strict_types=1);

namespace Blazervel\Blazervel\Console\Commands;

use Blazervel\Blazervel\Support\Actions;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\App;
use Illuminate\Console\Command;

class TranspileAnonymousActionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blazervel:transpile:anonymous  {class?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Transpile anonymous classes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (! App::environment(['local', 'development', 'ci', 'testing'])) {
            $this->error('Cannot transpile anonymous classes in this environment (supported environments are local, development, ci, or testing).');
            return 1;
        }
        
        $classes = Actions::anonymousClasses();
        $dir = Actions::dir();
        $namespace = Actions::dirNamespace($dir);

        if ($classes->count() === 0) {
            $this->error("Could not find any anonymous classes (searching in \"{$dir}\").");
            return 1;
        }

        if ($only = $this->argument('class')) {

            $this->comment("Searching for anonymous class with alias \"$only\" as argument.");

            $only = Str::startsWith($only, '\\') ? Str::replaceFirst('\\', '', $only) : $only;
            $only = ! Str::startsWith($only, $namespace) ? "{$namespace}\\{$only}" : $only;

            $classes = $classes->filter(fn ($class, $alias) => $alias == $only);

            if ($classes->count() === 0) {
                $this->error("Could not find anonymous class with for alias \"{$only}\".");
                return 1;
            }
        }

        $results = $classes->each(function ($filePath, $alias) use ($dir) {
            try {
                $fileName = basename(Str::remove('.php', $filePath));
                $namespace = rtrim($alias, '\\' . $fileName);
                
                $classDefinition = File::get($filePath);

                $PHP_EOL_X2 = PHP_EOL . PHP_EOL;

                $transpiled = $classDefinition;

                if (Str::contains($classDefinition, '<?php declare (strict_types=1);')) {
                    $transpiled = Str::replaceFirst(
                        'declare (strict_types=1);' . PHP_EOL,
                        "declare (strict_types=1);{$PHP_EOL_X2}namespace {$namespace};" . PHP_EOL,
                        $transpiled
                    );
                } else {
                    $transpiled = Str::replaceFirst(
                        '<?php' . PHP_EOL,
                        "<?php{$PHP_EOL_X2}namespace {$namespace};" . PHP_EOL,
                        $transpiled
                    );
                }

                $transpiled = Str::replaceFirst(PHP_EOL . 'return new class', PHP_EOL . "class {$fileName}", $transpiled);
                $transpiled = Str::replaceLast(PHP_EOL . '};', PHP_EOL . "}", $transpiled);
                
                File::put($filePath, $transpiled);

            } catch (Exception $e) {

                $this->error("Unable to transpile {$alias} class. Error message: \"{$e->getMessage()}\".");

                return null;

            }

            $this->comment("Successfully transpiled {$alias} class");

            return $alias;
        });

        if ($successes = $results->whereNotNull()->count()) {
            $this->comment("Transpiled {$successes} classes.");

            Artisan::call('cache:clear');
        }
        
        if ($errors = $results->whereNull()->count()) {
            $this->warn("Unable to transpile {$errors}.");
        }

        return $errors > 0 ? 1 : 0;
    }
}
