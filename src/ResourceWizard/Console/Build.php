<?php

namespace Jeovajr\ResourceWizard\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Build Resource Command line.
 * Syntax:
 * <pre><code>
 * php artisan wizard:build [resource name]
 * </code></pre>
 * plural* is optional.
 *
 * Example:
 * <pre><code>
 * php artisan wizard:build person
 * </code></pre>
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
class Build extends Command
{
    use Replacer;

    /**
     * The Composer instance.
     */
    private Composer $composer;

    /**
     * The filesystem instance.
     */
    private Filesystem $files;

    /**
     * The total creation steps for the progress bar.
     */
    private static int $steps = 12;

    /**
     * The event names.
     */
    private static array $events = [
        'Browse',
        'Read',
        'Edit',
        'Add',
        'Delete',
        'Lock',
        'Unlock',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:build {name : The resource name}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Build the new Resource';

    private string $singular;

    private string $plural;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Composer $composer, Filesystem $files)
    {
        parent::__construct();

        $this->composer = $composer;
        $this->files = $files;
        $this->singular = '';
        $this->plural = '';
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $generated_files = [];
        $progressBar = $this->output->createProgressBar(self::$steps); // 12 step
        $this->singular = Str::singular(Str::headline(is_string($this->argument('name')) ? $this->argument('name') : ''));
        $this->plural = Str::plural(Str::headline(is_string($this->argument('name')) ? $this->argument('name') : ''));

        $this->info('Starting to build the resource ['.$this->singular.']');
        $this->line('');
        $progressBar->start();
        $this->info(' -> Generating resource files...');

        /*
         * Step 1 -> Create DB migration
         */
        $generated_files['migration'] = $this->generateFile(
            stub_name: 'Database'.DIRECTORY_SEPARATOR.'create-migration.php',
            path: $this->getMigrationBasePath(),
            file_name: $this->getMigrationFileName($this->singular),
            class_name: 'Create'.Str::studly($this->plural).'Table'
        );
        $progressBar->advance();
        $this->info(' -> Database migration file created!');

        /*
         * Step 2 -> Create Model
         */
        $generated_files['model'] = $this->generateFile(
            stub_name: 'Database'.DIRECTORY_SEPARATOR.'Model.php',
            path: $this->getModelsBasePath(),
            file_name: $this->getModelFileName($this->singular),
            class_name: Str::studly($this->singular)
        );
        $progressBar->advance();
        $this->info(' -> Model class created!');

        /*
         * Step 3 -> Create Factory
         */
        $generated_files['factory'] = $this->generateFile(
            stub_name: 'Database'.DIRECTORY_SEPARATOR.'Factory.php',
            path: $this->getFactoriesBasePath(),
            file_name: $this->getFactoryFileName($this->singular),
            class_name: Str::studly($this->singular).'Factory'
        );
        $progressBar->advance();
        $this->info(' -> Factory class created!');

        /*
         * Step 4 -> Create Form Request
         */
        $generated_files['request'] = $this->generateFile(
            stub_name: 'Requests'.DIRECTORY_SEPARATOR.'Request.php',
            path: $this->getRequestsBasePath(),
            file_name: $this->getRequestFileName($this->singular),
            class_name: Str::studly($this->singular).'Request'
        );
        $progressBar->advance();
        $this->info(' -> Form Request class created!');

        /*
         * Step 5 to 11 -> Create Events
         */
        foreach (self::$events as $event) {
            $generated_files[Str::snake($event)] = $this->generateFile(
                stub_name: 'Events'.DIRECTORY_SEPARATOR.$event.'.php',
                path: $this->getEventsPath($this->singular),
                file_name: $this->getEventFileName($this->singular, $event),
                class_name: Str::studly(($event === 'Browse' ? $this->plural : $this->singular)).$event
            );
            $progressBar->advance();
            $this->info(' -> Created the '.Str::studly(($event === 'Browse' ? $this->plural : $this->singular)).' '.$event.' event!');
        }

        $progressBar->finish();
        $this->info(' -> Everything is done!');

        $this->line('');
        $this->info('** Generated Files! **');
        $this->line('');

        foreach ($generated_files as $file) {
            $basename = pathinfo($file, PATHINFO_BASENAME);
            $dirname = pathinfo($file, PATHINFO_DIRNAME);

            $this->line($dirname.DIRECTORY_SEPARATOR.$basename);
        }

        $this->line('');
        $this->info('** Finished! **');
        $this->line('');
    }

    /**
     * Get the migration file name
     */
    private function getMigrationFileName(string $name): string
    {
        return $this->getDatePrefix().'_create_'.Str::plural(Str::snake($name)).'_table.php';
    }

    /**
     * Get the path to the migration directory.
     */
    private function getMigrationBasePath(): string
    {
        return $this->laravel->databasePath().DIRECTORY_SEPARATOR.'migrations';
    }

    /**
     * Get the model file name
     */
    private function getFactoryFileName(string $name): string
    {
        return Str::singular(Str::studly($name)).'Factory.php';
    }

    /**
     * Get the path to the migration directory.
     */
    private function getFactoriesBasePath(): string
    {
        $path = $this->laravel->databasePath().DIRECTORY_SEPARATOR.'factories'.DIRECTORY_SEPARATOR.'ResourceWizard';
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the model file name
     */
    private function getModelFileName(string $name): string
    {
        return Str::singular(Str::studly($name)).'.php';
    }

    /**
     * Get the path to the models' directory.
     */
    private function getModelsBasePath(): string
    {
        $path = $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models'.DIRECTORY_SEPARATOR.'ResourceWizard';
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the request file name
     */
    private function getRequestFileName(string $name): string
    {
        return Str::singular(Str::studly($name)).'Request.php';
    }

    /**
     * Get the path to the requests' directory.
     */
    private function getRequestsBasePath(): string
    {
        $path = $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Requests'.DIRECTORY_SEPARATOR.'ResourceWizard';
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the controller file name
     */
    private function getControllerFileName(string $name): string // @phpstan-ignore-line
    {
        return Str::plural(Str::studly($name)).'Controller.php';
    }

    /**
     * Get the path to the controllers' directory.
     */
    private function getControllersBasePath(): string // @phpstan-ignore-line
    {
        $path = $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'ResourceWizard';
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the event file name
     */
    private function getEventFileName(string $name, string $event): string
    {
        $name = Str::singular(Str::studly($name));
        if ($event === 'Browse') {
            $name = Str::plural($name);
        }

        return $name.$event.'.php';
    }

    /**
     * Get the path to the resource events directory.
     */
    private function getBaseEventsPath(): string
    {
        $path = $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Events'.DIRECTORY_SEPARATOR.'ResourceWizard';
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the path to this resource event directory.
     *
     * @param  string  $name The resource name.
     */
    private function getEventsPath(string $name): string
    {
        $path = $this->getBaseEventsPath().DIRECTORY_SEPARATOR.Str::plural(Str::studly($name));
        if (! $this->files->exists($path)) {
            $this->files->makeDirectory($path, 0755, true, true);
        }

        return $path;
    }

    /**
     * Get the date prefix for the migration.
     */
    private function getDatePrefix(): string
    {
        return date('Y_m_d_His');
    }

    /**
     * Get the path to the stubs.
     */
    private function stubPath(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs';
    }

    /**
     * Ensure that a class with the given name doesn't already exist on the
     * given path.
     *
     *
     *
     * @throws InvalidArgumentException|FileNotFoundException
     */
    private function ensureClassDoesntAlreadyExist(string $name, string $path): void
    {
        $files = $this->files->glob($path.DIRECTORY_SEPARATOR.'*.php');

        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }

        class_exists($name);
    }

    /**
     * Get the contents from stub template
     *
     * @param  string  $file Stub file name, without stub ext
     *
     * @throws FileNotFoundException
     */
    private function getReplacedFileContents(string $file): string
    {
        $stub = $this->files->get($this->stubPath().DIRECTORY_SEPARATOR.$file.'.stub');

        return str_replace($this->getSearchKeys(), $this->getSearchValues($this->singular), $stub);
    }

    /**
     * Generates a file based on passed parameters.
     * Gets the stub file content, replace the Dummy Values, create the file
     * on informed path, dump composer autoload, return full file path.
     *
     * If class name is informed, checks if class already exists on path.
     *
     * @param  string  $stub_name  The Stub file name, on stubs directory, without stub ext
     * @param  string  $path       The path where the file will be created.
     * @param  string  $file_name  The filename.
     * @param  string|null  $class_name Optional Class Name, to check if it exists. Useful for PHP.
     *
     * @throws FileNotFoundException
     */
    private function generateFile(string $stub_name, string $path, string $file_name, string $class_name = null): string
    {
        if ($class_name !== null) {
            $this->ensureClassDoesntAlreadyExist($class_name, $path);
        }

        $stub = $this->getReplacedFileContents($stub_name);
        $full_path = $path.DIRECTORY_SEPARATOR.$file_name;

        $this->files->put($full_path, $stub);

        $this->composer->dumpAutoloads();

        return $full_path;
    }
}
