<?php

namespace Jeovajr\ResourceWizard\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

/**
 * Create a new Resource Command.
 * Syntax:
 * <pre><code>
 * php artisan wizard:create-new [resource name]
 * </code></pre>
 * plural* is optional.
 *
 * Example:
 * <pre><code>
 * php artisan wizard:create-new person
 * </code></pre>
 *
 * @author        Jeova Goncalves <jeova.goncalves1@gmail.com>
 * @copyright (c) 2023, Jeova Goncalves.
 */
class Create extends Command
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
    private static int $steps = 1;

    private string $resourceName;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:create-new {name : The resource name} ';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Create a new Resource';

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
        $this->resourceName = '';
    }

    /**
     * Execute the console command.
     *
     * @throws FileNotFoundException
     */
    public function handle(): void
    {
        $generated_files = [];
        $progressBar = $this->output->createProgressBar(self::$steps); // 1 step
        $this->resourceName = Str::singular(Str::headline(is_string($this->argument('name')) ? $this->argument('name') : ''));

        $this->info('Starting to create new resource ['.$this->resourceName.']');
        $this->line('');
        $progressBar->start();
        $this->info(' -> Generating resource file...');

        /*
         * Step 1 -> Create the resources entry
         */
        $resourcesPath = $this->getResourcePath();
        if (! $this->files->exists($resourcesPath)) {
            $this->files->makeDirectory($resourcesPath, 0755, true, true);
        }
        $generated_files['resource'] = $this->generateFile('resource.php', $resourcesPath, Str::kebab($this->resourceName).'.php');
        $progressBar->finish();
        $this->info(' -> New resource registered!');

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
     * Get the path to the migration directory.
     */
    private function getResourcePath(): string
    {
        return $this->laravel->configPath().DIRECTORY_SEPARATOR.'resources';
    }

    /**
     * Get the path to the stubs.
     */
    private function stubPath(): string
    {
        return __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'stubs';
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

        return str_replace($this->getSearchKeys(), $this->getSearchValues($this->resourceName), $stub);
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
     *
     * @throws FileNotFoundException
     */
    private function generateFile(string $stub_name, string $path, string $file_name): string
    {
        $stub = $this->getReplacedFileContents($stub_name);
        $full_path = $path.DIRECTORY_SEPARATOR.$file_name;

        $this->files->put($full_path, $stub);

        $this->composer->dumpAutoloads();

        return $full_path;
    }
}
