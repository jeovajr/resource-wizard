<?php

namespace ResourceWizard\Console;

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
    private static int $steps = 24;

    /**
     * The replacements for files.
     */
    private static array $keys = ['DummyUS', 'DummyUP', 'DummyLS', 'DummyLP'];

    /**
     * The replacements value for files.
     */
    private array $values;

    /**
     * The BREAD names.
     */
    private static array $bread = [
        'browse',
        'read',
        'edit',
        'add',
        'delete',
    ];

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
    protected $signature = 'wizard:build '.'{name : The resource name}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Build the new Resource';

    /**
     * Indicates if the resource will be a shared one or not
     */
    private bool $shared;

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
        $this->values = [];
        $this->shared = false;
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $generated_files = [];
        $bar = $this->output->createProgressBar(Build::$steps); // 25 steps
        $name = Str::snake(trim(strtolower(is_string($this->argument('name')) ? $this->argument('name') : '')));
        $plural = Str::snake(trim(strtolower(is_string($this->argument('plural')) ? $this->argument('plural') : '')));

        $this->shared = (bool) $this->option('shared');

        $this->values = [Str::studly($name), Str::studly($plural), $name, $plural];

        $this->info('The informed name was: ['.$name.']');
        $this->info('The determined plural is: ['.$plural.']');
        $this->line('');
        $bar->start();
        $this->info(' -> Starting the creation steps...');

        /*
         * Step 1 -> Create the resources entry
         * TODO: Create a stub file for the resources, as it will not be stored in the database
         */
        $bar->advance();
        $this->info(' -> New resource registered!');

        /*
         * Step 2 -> Create the database migration
         */
        $generated_files['migration'] = $this->generateFile('migration.php', $this->getMigrationPath(), $this->getMigrationFileName($plural), 'Create'.Str::studly($plural).'Table');
        $bar->advance();
        $this->info(' -> Created the database migration file!');

        /*
         * Step 3 -> Create the resource model
         */
        $generated_files['model'] = $this->generateFile('model.php', $this->getModelsPath(), $this->getModelFileName($name), Str::studly($name));
        $bar->advance();
        $this->info(' -> Created the resource model!');

        /*
         * Step 4 -> Create Event Dir
         */
        $this->files->makeDirectory($this->getThisEventsPath(Str::studly($name)), 0777, true, true);
        $bar->advance();
        $this->info(' -> Create Event Dir!');

        /*
         * Step 5 to 11 -> Create the resource events
         */
        foreach (Build::$events as $event) {
            $generated_files[Str::snake($event)] = $this->generateFile(($event === 'AllLoaded' ? 'sLoaded' : $event).'.php', $this->getThisEventsPath(Str::studly($name)), $this->getEventFileName($name, $event, $plural), Str::studly(($event === 'Browse' ? $plural : $name)).$event);
            $bar->advance();
            $this->info(' -> Created the resource '.Str::snake($event).' event!');
        }

        /*
         * Step 12 -> Create the resource request
         */
        $generated_files['request'] = $this->generateFile('request.php', $this->getRequestsPath(), $this->getRequestFileName($name), Str::studly($name).'Request');
        $bar->advance();
        $this->info(' -> Created the resource request!');

        /*
         * Step 13 -> Create the resource controller
         */
        $generated_files['controller'] = $this->generateFile('controller.php', $this->getControllersPath(), $this->getControllerFileName($name), Str::studly($name).'Request');
        $bar->advance();
        $this->info(' -> Created the resource controller!');

        /*
         * Step 14 -> Create the Page.vue file
         */
        $generated_files['page'] = $this->generateFile('page.vue', $this->getPagesPath(), $this->getPageFileName($plural));
        $bar->advance();
        $this->info(' -> Created the page file!');

        /*
         * Step 15 -> Create the resource BREAD dir
         */
        $this->files->makeDirectory($this->getThisBREADPath($plural), 0777, true, true);
        $bar->advance();
        $this->info(' -> Create the resource BREAD dir!');

        /*
         * Step 16 to 20 -> Create the resource BREAD vue file
         */
        foreach (Build::$bread as $b) {
            $generated_files['resource_'.$b] = $this->generateFile($b.'.vue', $this->getThisBREADPath($plural), $this->getResourceBREADFileName($plural, Str::studly($b)));
            $bar->advance();
            $this->info(' -> Created the resource '.$b.' vue file!');
        }

        /*
         * Step 21 -> Create Item js file
         */
        $generated_files['item_file'] = $this->generateFile('item.js', $this->getThisBREADPath($plural), Str::studly($plural).'.js');
        $bar->advance();
        $this->info(' -> Created the resource Item js file!');

        /*
         * Step 22 -> Create the Module js file
         */
        $generated_files['module'] = $this->generateFile('module.js', $this->getModulesPath(), $this->getModuleFileName($plural));
        $bar->advance();
        $this->info(' -> Created the Module js file!');

        /*
         * Step 23 ->  Create the DPS settings file
         */
        $generated_files['dps_page'] = $this->generateFile('dps_page.js', $this->getDPSCommonPath(), Str::snake($plural).'_page.js');
        $bar->advance();
        $this->info(' ->  Created the DPS settings file !');

        $bar->finish();
        $this->info(' -> Added resource route to Dev menu and routes!');

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
    private function getMigrationFileName(string $plural): string
    {
        return $this->getDatePrefix().'_create_'.$plural.'_table.php';
    }

    /**
     * Get the path to the migration directory.
     */
    private function getMigrationPath(): string
    {
        return $this->laravel->databasePath().DIRECTORY_SEPARATOR.'migrations';
    }

    /**
     * Get the model file name
     */
    private function getModelFileName(string $name): string
    {
        return Str::studly($name).'.php';
    }

    /**
     * Get the path to the models' directory.
     */
    private function getModelsPath(): string
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Models';
    }

    /**
     * Get the request file name
     */
    private function getRequestFileName(string $name): string
    {
        return Str::studly($name).'Request.php';
    }

    /**
     * Get the path to the requests directory.
     */
    private function getRequestsPath(): string
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Requests';
    }

    /**
     * Get the controller file name
     */
    private function getControllerFileName(string $name): string
    {
        return Str::studly($name).'Controller.php';
    }

    /**
     * Get the path to the controllers' directory.
     */
    private function getControllersPath(): string
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Http'.DIRECTORY_SEPARATOR.'Controllers'.DIRECTORY_SEPARATOR.'API';
    }

    /**
     * Get the event file name
     */
    private function getEventFileName(string $name, string $event, string $plural): string
    {
        $name = Str::studly($name);
        if ($event === 'Browse') {
            $name = Str::studly($plural);
        }

        return $name.$event.'.php';
    }

    /**
     * Get the path to the resource events directory.
     */
    private function getResourceEventsPath(): string
    {
        return $this->laravel->basePath().DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Events'.DIRECTORY_SEPARATOR.'Resources';
    }

    /**
     * Get the path to this resource event directory.
     *
     * @param  string  $name The resource name, singular, upper.
     */
    private function getThisEventsPath(string $name): string
    {
        return $this->getResourceEventsPath().DIRECTORY_SEPARATOR.$name;
    }

    /**
     * Get the page file name
     */
    private function getPageFileName(string $plural): string
    {
        return Str::studly($plural).'.vue';
    }

    /**
     * Get the path to the pages directory.
     */
    private function getPagesPath(): string
    {
        return $this->laravel->resourcePath().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'pages';
    }

    /**
     * Get the resource browse file name
     *
     * @param  string  $bread The bread option (Browse, Read, Edit, Add, Delete)
     */
    private function getResourceBREADFileName(string $plural, string $bread): string
    {
        return Str::studly($plural).Str::studly($bread).'.vue';
    }

    /**
     * Get the path to the resources' directory.
     */
    private function getResourcesPath(): string
    {
        return $this->laravel->resourcePath().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'resources';
    }

    /**
     * Get the path to this resource directory.
     *
     * @param  string  $name The resource name, plural.
     */
    private function getThisBREADPath(string $name): string
    {
        return $this->getResourcesPath().DIRECTORY_SEPARATOR.$name;
    }

    /**
     * Get the resource module file name
     */
    private function getModuleFileName(string $plural): string
    {
        return Str::snake($plural).'.js';
    }

    /**
     * Get the path to the modules' directory.
     */
    private function getModulesPath(): string
    {
        return $this->laravel->resourcePath().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'resources';
    }

    /**
     * Get the path to the dps common directory.
     */
    private function getDPSCommonPath(): string
    {
        return $this->laravel->resourcePath().DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'dps'.DIRECTORY_SEPARATOR.'common';
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
        return __DIR__.DIRECTORY_SEPARATOR.'stubs';
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
        $migrationFiles = $this->files->glob($path.DIRECTORY_SEPARATOR.'*.php');

        foreach ($migrationFiles as $migrationFile) {
            $this->files->requireOnce($migrationFile);
        }

        class_exists($name);
    }

    /**
     * Get the contents from stub template
     *
     * @param  string  $stubfile Stub file name, without stub ext
     *
     * @throws FileNotFoundException
     */
    private function getReplacedFileContents(string $stubfile): string
    {
        $stub = $this->files->get($this->stubPath().DIRECTORY_SEPARATOR.$stubfile.'.stub');
        $stub_replaced = str_replace(Build::$keys, $this->values, $stub);
        if ($this->shared) {
            $stub_replaced_shared_s = str_replace('// SharedRemoveStart', '/* Start - Code Removed for Shared Resources', $stub_replaced);
            $stub_replaced_shared_f = str_replace('// SharedRemoveEnd', ' End */', $stub_replaced_shared_s);
            $stub_replaced_shared_c = str_replace('parent::__construct("DummyLS", false); // Controller Shared', 'parent::__construct("DummyLS", true);', $stub_replaced_shared_f);
            $stub_replaced_shared_pp = str_replace('domain_channel: window.Laravel.subdomain // Account dependent', "domain_channel: 'www' // Account independent", $stub_replaced_shared_c);
            $stub_replaced_shared_q = str_replace('domain_channel: window.Laravel.subdomain, // Account dependent', "domain_channel: 'www', // Account independent", $stub_replaced_shared_pp);
            $stub_replaced_shared_p = str_replace('->where($this->getTableName() . ".service_account_id", $this->getAccountID());', ';', $stub_replaced_shared_q);
            $stub_replaced_shared_a = str_replace('window.Laravel.subdomain', "'www'", $stub_replaced_shared_p);
        } else {
            $stub_replaced_shared_s = str_replace('// SharedRemoveStart', '// Used only on Account Specific Resources', $stub_replaced);
            $stub_replaced_shared_f = str_replace('// SharedRemoveEnd', '// End of ASR code', $stub_replaced_shared_s);
            $stub_replaced_shared_c = str_replace('parent::__construct(false); // Controller Shared', 'parent::__construct();', $stub_replaced_shared_f);
            $stub_replaced_shared_a = $stub_replaced_shared_c;
        }

        return $stub_replaced_shared_a;
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
